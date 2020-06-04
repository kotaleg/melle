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

    public function getCredentials()
    {
        return array(
            'appId' => (string) $this->setting['app_id'],
            'searchApiKey' => (string) $this->setting['search_api_key'],
            'indexName' => (string) $this->setting['index_name'],
        );
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
            SET `status` = '". $this->db->escape($status) ."',
                `updateDate` = NOW()
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

        $objectMaxSize = 10000;

        try {

            foreach ($this->operationTypes as $operationType) {

                $preparedData = array();

                $nextItems = $this->getNext($operationType, $this->setting['batch_size']);
                $computedHashes = array();

                foreach ($nextItems as $next) {
                    try {
                        $itemObjectID = $this->getIDForItem($next['storeItemType'], $next['storeItemId']);
                        $itemData = $this->prepareDataForItem($next['storeItemType'], $next['storeItemId']);

                        if ($itemObjectID) {

                            if (!is_array($itemData)) {
                                $itemData = array();

                                if ($operationType === \pro_algolia\constant::SAVE) {
                                    $this->addToQueueLog(pro_algolia\constant::UNDEFINED, '`itemData` is empty', $next['_id']);
                                    // TODO: add item to remove queue
                                }
                            }
                            $itemData['objectID'] = $itemObjectID;

                            $itemDataBytesCount = $this->countBytesInItemData($itemData);
                            if ($objectMaxSize && $itemDataBytesCount > $objectMaxSize) {
                                $this->updateQueueStatus($next['_id'], pro_algolia\constant::ERROR);
                                $this->addToQueueLog(
                                    pro_algolia\constant::ERROR, 
                                    "size {$itemDataBytesCount} is more then {$objectMaxSize}",
                                    $next['_id']
                                );
                                // TODO: should we remove it from algolia index?
                                continue;
                            }

                            $itemDataHash = $this->hashItemData($itemData);
                            $computedHashes[$itemObjectID] = $itemDataHash;

                            // $indexObjectLocal = $this->getIndexObject($itemObjectID);

                            if (!$this->getIndexObject($itemObjectID, $itemDataHash, $operationType)) {
                                $preparedData[$itemObjectID] = $itemData;
                            }

                            // TODO: update queue status depend on the api call status
                            $this->updateQueueStatus($next['_id'], pro_algolia\constant::SUCCESS);
                        } else {
                            $this->updateQueueStatus($next['_id'], pro_algolia\constant::ERROR);
                            $this->addToQueueLog(pro_algolia\constant::ERROR, '`objectID` is empty', $next['_id']);
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
                        $preparedData = array_map(function($item) {
                            return $item['objectID'];
                        }, $preparedData);

                        // check if objects exist in the index START
                        $checkResult = $this->pro_algolia->getObjects($preparedData);
                        $checkResultBody = $checkResult->getBody();
                        $checkResultIds = $this->getObjectIdsFromResposeBody($checkResultBody);

                        foreach ($checkResultIds as $checkObjectID) {
                            if (isset($preparedData[$checkObjectID])) {

                                unset($preparedData[$checkObjectID]);

                                if (isset($computedHashes[$checkObjectID])) {
                                    $checkObjectHash = $computedHashes[$checkObjectID];
                                } else {
                                    if ($this->setting['debug']) {
                                        $this->log("`HASH DO NOT FOUND FOR `{$checkObjectID}`");
                                    }
                                    continue;
                                }

                                if ($this->getIndexObject($checkObjectID)) {
                                    $this->updateIndexObjectDataHash(
                                        $checkObjectID,
                                        $checkObjectHash
                                    );
                                    $this->updateIndexObjectStatus(
                                        $checkObjectID,
                                        $operationType
                                    );
                                } else {
                                    $this->setIndexObject(
                                        $checkObjectID,
                                        $checkObjectHash,
                                        $operationType
                                    );
                                }
                            }
                        }
                        // check if objects exist in the index END

                        if (!$preparedData) {
                            continue;
                        }

                        $result = $this->pro_algolia->deleteObjects($preparedData);
                        $resultBody = $result->getBody();
                        break;
                }

                if ($this->setting['debug']) {
                    $operationResultJson = isset($resultBody) ? json_encode($resultBody) : null;
                    $this->log("`{$operationType}` OPERATION RESULT {$operationResultJson}");
                }

                if (isset($resultBody)) {
                    $resultIds = $this->getObjectIdsFromResposeBody($resultBody);
                    foreach ($resultIds as $resultObjectID) {

                        if (isset($computedHashes[$resultObjectID])) {
                            $resultObjectHash = $computedHashes[$resultObjectID];
                        } else {
                            if ($this->setting['debug']) {
                                $this->log("`HASH DO NOT FOUND FOR `{$resultObjectID}`");
                            }
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

        } catch (Exception $e) {
            $this->addToQueueLog(pro_algolia\constant::ERROR, (string)$e);
        }

        return $workResult;
    }

    private function getObjectIdsFromResposeBody($resultBody)
    {
        $objectIDs = array();
        if ($resultBody && is_array($resultBody)) {
            foreach ($resultBody as $resultBatch) {
                if (isset($resultBatch['objectIDs']) && is_array($resultBatch['objectIDs'])) {
                    foreach ($resultBatch['objectIDs'] as $resultObjectID) {
                        $objectIDs[] = $resultObjectID;
                    }
                }
            }
        }
        return $objectIDs;
    }

    private function getNext($operationType, $limit)
    {
        return $this->db->query("SELECT *
            FROM `". DB_PREFIX . pro_algolia\constant::QUEUE_TABLE . "`
            WHERE `status` = '" . pro_algolia\constant::UNDEFINED . "'
            AND `operation` = '". $this->db->escape($operationType) ."'
            LIMIT ". (int)$limit)->rows;
    }

    private function getIDForItem($itemType, $itemId)
    {
        switch ($itemType) {
            case \pro_algolia\constant::PRODUCT:
                $this->load->model("{$this->route}/product");
                return $this->model_extension_module_pro_algolia_product->getId($itemId);
                break;
        }
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

    private function countBytesInItemData($data)
    {   
        $json = json_encode($data);
        return ini_get('mbstring.func_overload') ? mb_strlen($json , '8bit') : strlen($json);
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
            WHERE `objectId` = '" . $this->db->escape($objectId) . "'");
    }

    private function updateIndexObjectDataHash($objectId, $objectDataHash)
    {
        return $this->db->query("UPDATE
            `". DB_PREFIX . pro_algolia\constant::INDEX_OBJECT_TABLE . "`
            SET `objectDataHash` = '" . $this->db->escape($objectDataHash) . "',
                `updateDate` = NOW()
            WHERE `objectId` = '" . $this->db->escape($objectId) . "'");
    }

    private function updateIndexObjectStatus($objectId, $status)
    {
        return $this->db->query("UPDATE
            `". DB_PREFIX . pro_algolia\constant::INDEX_OBJECT_TABLE . "`
            SET `status` = '" . $this->db->escape($status) . "',
                `updateDate` = NOW()
            WHERE `objectId` = '" . $this->db->escape($objectId) . "'");
    }
}
