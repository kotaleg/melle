<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModulePROAlgolia extends Model
{
    private $codename = 'pro_algolia';
    private $route = 'extension/module/pro_algolia';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');
        $this->load->model('extension/pro_patch/url');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
        $this->pro_algolia = new \pro_algolia\pro_algolia($this->setting);
    }

    private function log($message)
    {
        $this->log->write(strtoupper($this->codename)." :: {$message}");
    }

    public function addProductToQueue($productId)
    {
        return $this->addItemToQueue(\pro_algolia\constant::PRODUCT, $productId);
    }

    private function addItemToQueue($itemType, $itemId)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . pro_algolia\constant::QUEUE_TABLE ."`
            SET `storeItemId` = '". (int) $itemId ."',
                `storeItemType` = '". $this->db->escape($itemType) ."',

                `status` = '". pro_algolia\constant::UNDEFINED ."',

                `createDate` = NOW(),
                `updateDate` = NOW()");

        return $this->db->getLastId();
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
        $result['processed'] = 0;

        try {

            $preparedData = array();
            $queueItemsIds = array();

            foreach ($this->getNext($this->setting['batch_size']) as $next) {
                try {
                    $itemData = $this->prepareDataForItem($next['storeItemType'], $next['storeItemId']);

                    if ($itemData && isset($itemData['objectID'])) {
                        $preparedData[$itemData['objectID']] = $itemData;
                        $queueItemsIds[] = $next['_id'];
                    } else {
                        $this->updateQueueStatus($next['_id'], pro_algolia\constant::ERROR);
                        $this->addToQueueLog(pro_algolia\constant::ERROR, 'Data for item is empty', $next['_id']);
                    }

                    $result['processed']++;
                } catch (Exception $e) {
                    $this->updateQueueStatus($next['_id'], pro_algolia\constant::ERROR);
                    $this->addToQueueLog(pro_algolia\constant::ERROR, (string)$e, $next['_id']);
                }
            }

            $this->pro_algolia->initClient();
            $this->pro_algolia->initIndex();

            $saveResult = $this->pro_algolia->saveObjects($preparedData);
            $saveResultBody = $saveResult->getBody();
            $this->log(json_encode($saveResultBody));

            foreach ($queueItemsIds as $queueItemId) {
                $this->updateQueueStatus($queueItemId, pro_algolia\constant::SUCCESS);
            }

        } catch (Exception $e) {
            $this->addToQueueLog(pro_algolia\constant::ERROR, (string)$e);
        }

        return $result;
    }

    private function getNext($limit)
    {
        return $this->db->query("SELECT *
            FROM `". DB_PREFIX . pro_algolia\constant::QUEUE_TABLE . "`
            WHERE `status` = '" . pro_algolia\constant::UNDEFINED . "'
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

    private function getIndexObject($objectId)
    {
        return $this->db->query("SELECT *
            FROM `". DB_PREFIX . pro_algolia\constant::INDEX_OBJECT_TABLE . "`
            WHERE `objectId` = '" . (int) $objectId . "'")->row;
    }

    private function setIndexObject($objectId, $storeItemType, $storeItemId)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . pro_algolia\constant::INDEX_OBJECT_TABLE ."`
            SET `objectId` = '" . $this->db->escape($objectId) . "',
                `storeItemType` = '" . $this->db->escape($storeItemType) . "',
                `storeItemId` = '" . (int) $storeItemId . "',
                `createDate` = NOW()
                `updateDate` = NOW()");
        
        return $this->db->getLastId();
    }

    private function deleteIndexObject($objectId)
    {
        return $this->db->query("DELETE
            FROM `". DB_PREFIX . pro_algolia\constant::INDEX_OBJECT_TABLE . "`
            WHERE `objectId` = '" . (int) $objectId . "'");
    }

    private function touchIndexObject($objectId)
    {
        return $this->db->query("UPDATE
            `". DB_PREFIX . pro_algolia\constant::INDEX_OBJECT_TABLE . "`
            SET `updateDate` = NOW()
            WHERE `objectId` = '" . (int) $objectId . "'");
    }
}
