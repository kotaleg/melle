<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModulePROAlgolia extends Model
{
    private $codename = 'pro_algolia';
    private $route = 'extension/module/pro_algolia';

    private $operationTypes;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');
        $this->load->model('extension/pro_patch/url');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
        $this->pro_algolia = new \pro_algolia\pro_algolia($this->setting);

        $this->operationTypes = array(
            \pro_algolia\constant::SAVE,
            \pro_algolia\constant::DELETE,
        );
    }

    private function log($message)
    {
        $this->log->write(strtoupper($this->codename)." :: {$message}");
    }

    public function queueSaveProduct($productId)
    {
        return $this->addItemToQueue(
            \pro_algolia\constant::PRODUCT,
            $productId,
            \pro_algolia\constant::SAVE
        );
    }

    public function queueDeleteProduct($productId)
    {
        return $this->addItemToQueue(
            \pro_algolia\constant::PRODUCT,
            $productId,
            \pro_algolia\constant::DELETE
        );
    }

    private function addItemToQueue($itemType, $itemId, $operationType)
    {
        if ($this->isItemInQueue($itemType, $itemId, $operationType)) {
            return;
        }

        $this->db->query("INSERT INTO `". DB_PREFIX . pro_algolia\constant::QUEUE_TABLE ."`
            SET `storeItemId` = '". (int) $itemId ."',
                `storeItemType` = '". $this->db->escape($itemType) ."',

                `operation` = '". $this->db->escape($operationType) ."',
                `status` = '". pro_algolia\constant::UNDEFINED ."',

                `createDate` = NOW(),
                `updateDate` = NOW()");

        return $this->db->getLastId();
    }

    private function isItemInQueue($itemType, $itemId, $operationType)
    {
        return $this->db->query("SELECT *
            FROM `". DB_PREFIX . pro_algolia\constant::QUEUE_TABLE . "`
            WHERE `storeItemId` = '". (int) $itemId ."'
            AND `storeItemType` = '". $this->db->escape($itemType) ."'
            AND `operation` = '" . $this->db->escape($operationType) . "'
            AND `status` = '" . pro_algolia\constant::UNDEFINED . "'")->row;
    }

    private function updateQueueStatus($queueId, $status)
    {
        $this->db->query("UPDATE `". DB_PREFIX . pro_algolia\constant::QUEUE_TABLE ."`
            SET `status` = '". $this->db->escape($status) ."'
            WHERE `_id` = '". (int) $queueId ."'");
    }

    private function addToQueueLog($type, $message, $queueId = false)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . pro_algolia\constant::QUEUE_LOG_TABLE ."`
            SET `queueId` = '" . (int) $queueId . "',
                `type` = '" . $this->db->escape($type) . "',
                `message` = '" . $this->db->escape($message) . "',
                `createDate` = NOW()");
    }

    public function work()
    {
        $workResult['processed'] = 0;

        try {

            foreach ($this->operationTypes as $operationType) {

                $preparedData = array();

                foreach ($this->getNext($operationType, $this->setting['batch_size']) as $next) {
                    try {
                        $itemData = $this->prepareDataForItem($next['storeItemType'], $next['storeItemId']);

                        if ($itemData && isset($itemData['objectID'])) {

                            $itemDataHash = $this->hashItemData($itemData);

                            if (!$this->getIndexObject($itemData['objectID'], $itemDataHash, $operationType)) {
                                $preparedData[$itemData['objectID']] = $itemData;
                            }

                            // TODO: update queue status depend on the api call status
                            $this->updateQueueStatus($next['_id'], pro_algolia\constant::SUCCESS);
                        } else {
                            $this->updateQueueStatus($next['_id'], pro_algolia\constant::ERROR);
                            $this->addToQueueLog(pro_algolia\constant::ERROR, 'Data for item is empty', $next['_id']);
                        }

                        $workResult['processed']++;
                    } catch (Exception $e) {
                        $this->updateQueueStatus($next['_id'], pro_algolia\constant::ERROR);
                        $this->addToQueueLog(pro_algolia\constant::ERROR, (string)$e, $next['_id']);
                    }
                }

                if (!$preparedData) {
                    continue;
                }

                $this->pro_algolia->initClient();
                $this->pro_algolia->initIndex();

                switch ($operationType) {
                    case \pro_algolia\constant::SAVE:
                        $result = $this->pro_algolia->saveObjects($preparedData);
                        $resultBody = $result->getBody();
                        break;

                    case \pro_algolia\constant::DELETE:
                        $result = $this->pro_algolia->deleteObjects($preparedData);
                        $resultBody = $result->getBody();
                        break;
                }

                if ($this->setting['debug']) {
                    $this->log("`{$operationType}` OPERATION RESULT".json_encode($resultBody));
                }

                if ($resultBody && is_array($resultBody)) {
                    foreach ($resultBody as $resultBatch) {
                        if (isset($resultBatch['objectIDs']) && is_array($resultBatch['objectIDs'])) {
                            foreach ($resultBatch['objectIDs'] as $resultObjectID) {

                                if (isset($preparedData[$resultObjectID])) {
                                    $resultObjectHash = $this->hashItemData($preparedData[$resultObjectID]);
                                } else {
                                    continue;
                                }

                                if ($this->getIndexObject($resultObjectID)) {
                                    $this->updateIndexObjectDataHash(
                                        $resultObjectID,
                                        $resultObjectHash
                                    );
                                    $this->updateIndexObjectStatus(
                                        $resultObjectID,
                                        $operationType
                                    );
                                } else {
                                    $this->setIndexObject(
                                        $resultObjectID,
                                        $resultObjectHash,
                                        $operationType
                                    );
                                }

                            }
                        }
                    }
                }

            }

        } catch (Exception $e) {
            $this->addToQueueLog(pro_algolia\constant::ERROR, (string)$e);
        }

        return $workResult;
    }

    private function getNext($operationType, $limit)
    {
        return $this->db->query("SELECT *
            FROM `". DB_PREFIX . pro_algolia\constant::QUEUE_TABLE . "`
            WHERE `status` = '" . pro_algolia\constant::UNDEFINED . "'
            AND `operation` = '". $this->db->escape($operationType) ."'
            LIMIT ". (int)$limit)->rows;
    }

    private function prepareDataForItem($itemType, $itemId)
    {
        switch ($itemType) {
            case \pro_algolia\constant::PRODUCT:
                $this->load->model("{$this->route}/product");
                return $this->model_extension_module_pro_algolia_product->prepareData($itemId);
                break;
        }
    }

    private function hashItemData($data)
    {
        return hash('sha256', json_encode($data));
    }

    private function getIndexObject($objectId, $objectDataHash = null, $status = null)
    {
        $sql = "SELECT *
            FROM `". DB_PREFIX . pro_algolia\constant::INDEX_OBJECT_TABLE . "`
            WHERE `objectId` = '" . $this->db->escape($objectId) . "'";

        if ($objectDataHash !== null) {
            $sql .= " AND `objectDataHash` = '" . $this->db->escape($objectDataHash) . "' ";
        }

        if ($status !== null) {
            $sql .= " AND `status` = '" . $this->db->escape($status) . "' ";
        }

        return $this->db->query($sql)->row;
    }

    private function setIndexObject($objectId, $objectDataHash, $status)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . pro_algolia\constant::INDEX_OBJECT_TABLE ."`
            SET `objectId` = '" . $this->db->escape($objectId) . "',
                `objectDataHash` = '" . $this->db->escape($objectDataHash) . "',
                `status` = '" . $this->db->escape($status) . "',
                `createDate` = NOW(),
                `updateDate` = NOW()");

        return $this->db->getLastId();
    }

    private function deleteIndexObject($objectId)
    {
        return $this->db->query("DELETE
            FROM `". DB_PREFIX . pro_algolia\constant::INDEX_OBJECT_TABLE . "`
            WHERE `objectId` = '" . (int) $objectId . "'");
    }

    private function updateIndexObjectDataHash($objectId, $objectDataHash)
    {
        return $this->db->query("UPDATE
            `". DB_PREFIX . pro_algolia\constant::INDEX_OBJECT_TABLE . "`
            SET `objectDataHash` = '" . $this->db->escape($objectDataHash) . "',
                `updateDate` = NOW()
            WHERE `objectId` = '" . (int) $objectId . "'");
    }

    private function updateIndexObjectStatus($objectId, $status)
    {
        return $this->db->query("UPDATE
            `". DB_PREFIX . pro_algolia\constant::INDEX_OBJECT_TABLE . "`
            SET `status` = '" . $this->db->escape($status) . "',
                `updateDate` = NOW()
            WHERE `objectId` = '" . (int) $objectId . "'");
    }
}
