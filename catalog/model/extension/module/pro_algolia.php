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

        $this->storeItemTypes = array(
            \pro_algolia\constant::PRODUCT,
        );
    }

    private function log($message)
    {
        if (isset($this->setting['debug']) && $this->setting['debug']) {
            $this->log->write(strtoupper($this->codename)." :: {$message}");
        }
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
            WHERE `storeItemId` = '". $this->db->escape($itemId) ."'
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

        $objectMaxSize = (int) $this->setting['object_max_size'];

        $this->pro_algolia->initClient();
        $this->pro_algolia->initIndex();

        foreach ($this->storeItemTypes as $storeItemType) {
            $nextItems = $this->getNext((int) $this->setting['batch_size'], null, $storeItemType);

            $preparedData = array();
            $computedHashes = array();

            // prepare array of operations which would be processed
            $operationsToProcess = array();
            foreach ($this->operationTypes as $operationType) {
                $operationsToProcess[$operationType] = array();
            }

            foreach ($nextItems as $nextItemKey => $next) {
                $itemObjectID = $this->getIDForItem($next['storeItemType'], $next['storeItemId']);
                $itemData = $this->prepareDataForItem($next['storeItemType'], $next['storeItemId']);

                if ($itemObjectID) {
                    // add objectID for better connection
                    // betwen preparedData and queueItem
                    $nextItems[$nextItemKey]['objectID'] = $itemObjectID;

                    // if it's not an array
                    // then we probably failed at the prepareDataForItem step
                    if (!is_array($itemData)) {
                        $itemData = array();
                        if ($next['operation'] === \pro_algolia\constant::SAVE) {
                            $this->addToQueueLog(
                                pro_algolia\constant::UNDEFINED,
                                '`itemData` is empty',
                                $next['_id']
                            );

                            $newQueueItemId = $this->addItemToQueue(
                                $next['storeItemType'],
                                $next['storeItemId'],
                                \pro_algolia\constant::DELETE
                            );

                            if ($newQueueItemId) {
                                $this->addToQueueLog(
                                    pro_algolia\constant::UNDEFINED,
                                    "moved to -> {$newQueueItemId}",
                                    $next['_id']
                                );
                            }

                            $this->updateQueueStatus($next['_id'], pro_algolia\constant::MOVED);

                            // item should not be processed right now
                            unset($nextItems[$nextItemKey]);
                            continue;
                        }
                    }

                    // all objects require objectID
                    $itemData['objectID'] = $itemObjectID;

                    // algolia has a restriction on the object size
                    // by default no more then 10KB
                    $itemDataBytesCount = pro_algolia\hash::countBytesInItemData($itemData);
                    if ($objectMaxSize && $itemDataBytesCount > $objectMaxSize) {
                        $this->updateQueueStatus($next['_id'], pro_algolia\constant::ERROR);
                        $this->addToQueueLog(
                            pro_algolia\constant::ERROR,
                            "size {$itemDataBytesCount} is more then {$objectMaxSize}",
                            $next['_id']
                        );
                        // TODO: should we remove it from algolia index?
                        unset($nextItems[$nextItemKey]);
                        continue;
                    }

                    pro_algolia\sort::sortRecurvice($itemData);
                    $preparedData[$itemObjectID] = $itemData;

                } else {
                    $this->updateQueueStatus($next['_id'], pro_algolia\constant::ERROR);
                    $this->addToQueueLog(pro_algolia\constant::ERROR, '`objectID` is empty', $next['_id']);
                    // item doesn't have an objectId so we can't do anything
                    unset($nextItems[$nextItemKey]);
                }
            }

            // recreate the array indexes
            // we require them because getObjects method
            // return results in the exact order they were asked for
            $nextItems = array_slice($nextItems, 0);

            // for getObjects method we need only objectIds
            $preparedItemObjectsIds = array_map(function($item) {
                return $item['objectID'];
            }, $nextItems);

            // don't do anything if we don't have the items
            if (!$preparedItemObjectsIds) {
                continue;
            }

            // check if objects exist in the index START
            try {
                $getObjectsResult = $this->pro_algolia->getObjects($preparedItemObjectsIds, [
                    // we want all attributes to make the compare
                    'attributesToRetrieve' => '*'
                ]);
            } catch (\Exception $e) {
                $this->log($e);
            }

            if (isset($getObjectsResult['results']) && is_array($getObjectsResult['results'])) {
                // results returned in order they were requested

                foreach ($getObjectsResult['results'] as $resultKey => $resultValue) {
                    if (!isset($nextItems[$resultKey])) {
                        // TODO: log error
                        continue;
                    }

                    $queueItem = $nextItems[$resultKey];

                    if (!isset($preparedData[$queueItem['objectID']])) {
                        $this->updateQueueStatus($queueItem['_id'], pro_algolia\constant::ERROR);
                        $this->addToQueueLog(
                            pro_algolia\constant::UNDEFINED,
                            "no prepared data for this item",
                            $queueItem['_id']
                        );
                        continue;
                    }

                    $preparedItemData = $preparedData[$queueItem['objectID']];

                    // re-sort values for better comparison
                    pro_algolia\sort::sortRecurvice($preparedItemData);
                    if ($resultValue && is_array($resultValue)) {
                        pro_algolia\sort::sortRecurvice($resultValue);
                    }

                    // hash both the store value and value from algolia to compare them
                    $itemDataHash = \pro_algolia\hash::hashItemData($preparedItemData);
                    $resultValueHash = \pro_algolia\hash::hashItemData($resultValue);

                    switch ($queueItem['operation']) {
                        case \pro_algolia\constant::SAVE:
                            if ($resultValue && is_array($resultValue)) {
                                if ($itemDataHash !== $resultValueHash) {
                                    $operationsToProcess[$queueItem['operation']][] = $preparedItemData;

                                    $this->log('before -> '.json_encode($resultValue));
                                    $this->log('after  -> '.json_encode($preparedItemData));

                                    $diff = \pro_algolia\compare::compareArrays($resultValue, $preparedItemData);
                                    $this->addToQueueLog(
                                        pro_algolia\constant::DIFF,
                                        @json_encode($diff),
                                        $queueItem['_id']
                                    );
                                } else {
                                    $this->updateQueueStatus($queueItem['_id'], pro_algolia\constant::SUCCESS);
                                    $this->addToQueueLog(
                                        pro_algolia\constant::UNDEFINED,
                                        "item have not changed",
                                        $queueItem['_id']
                                    );
                                }
                            } else {
                                $operationsToProcess[$queueItem['operation']][] = $preparedItemData;
                            }
                            break;
                        case \pro_algolia\constant::DELETE:
                            if ($resultValue && is_array($resultValue)) {
                                $operationsToProcess[$queueItem['operation']][] = $preparedItemData;
                            } else {
                                $this->updateQueueStatus($queueItem['_id'], pro_algolia\constant::SUCCESS);
                                $this->addToQueueLog(
                                    pro_algolia\constant::UNDEFINED,
                                    "already removed",
                                    $queueItem['_id']
                                );
                            }
                            break;
                        default:
                            $this->updateQueueStatus($queueItem['_id'], pro_algolia\constant::ERROR);
                            $this->addToQueueLog(
                                pro_algolia\constant::UNDEFINED,
                                "operation not found",
                                $queueItem['_id']
                            );
                            break;
                    }
                }

            } else {
                // set status for all of them to ERROR
                foreach ($nextItems as $queueItem) {
                    $this->updateQueueStatus($queueItem['_id'], pro_algolia\constant::ERROR);
                }
                continue;
            }

            foreach ($operationsToProcess as $operationType => $operationData) {
                if (!$operationData) {
                    continue;
                }

                try {
                    switch ($operationType) {
                        case \pro_algolia\constant::SAVE:
                            $result = $this->pro_algolia->saveObjects($operationData);
                            $resultBody = $result->getBody();
                            break;
                        case \pro_algolia\constant::DELETE:
                            $deleteOperationData = array_map(function($item) {
                                return $item['objectID'];
                            }, $operationData);

                            $result = $this->pro_algolia->deleteObjects($deleteOperationData);
                            $resultBody = $result->getBody();
                            break;
                    }
                } catch (\Exception $e) {
                    $this->log($e);
                }

                $operationResultJson = isset($resultBody) ? json_encode($resultBody) : null;
                $this->log("`{$operationType}` OPERATION RESULT {$operationResultJson}");

                if (isset($resultBody)) {
                    $resultIds = \pro_algolia\pro_algolia::getObjectIdsFromResposeBody($resultBody);
                    foreach ($resultIds as $resultObjectID) {
                        // find queueItem by resulting objectID
                        // update status to success
                        $queueItem = $this->findItemByObjectIdInQueue($nextItems, $resultObjectID);
                        if ($queueItem) {
                            $this->updateQueueStatus($queueItem['_id'], pro_algolia\constant::SUCCESS);
                        }
                    }
                } else {
                    // search for the objectId in the queueItems
                    // set status for all of them to ERROR
                    foreach ($operationData as $operationDataItem) {
                        if (isset($operationDataItem['objectID'])) {
                            $queueItem = $this->findItemByObjectIdInQueue($nextItems, $operationDataItem['objectID']);
                            if ($queueItem) {
                                $this->updateQueueStatus($queueItem['_id'], pro_algolia\constant::ERROR);
                            }
                        }
                    }
                }
            }
        }

        return $workResult;
    }

    private function findItemByObjectIdInQueue(array $queueItems, $itemObjectID)
    {
        foreach ($queueItems as $key => $value) {
            if (isset($value['objectID'])
            && strcmp($value['objectID'], $itemObjectID) === 0) {
                return $value;
            }
        }

        return false;
    }

    private function getNext(int $limit, string $operationType = null, string $storeItemType = null)
    {
        $sql = "SELECT *
        FROM `". DB_PREFIX . pro_algolia\constant::QUEUE_TABLE . "`
        WHERE `status` = '" . pro_algolia\constant::UNDEFINED . "'";

        if ($operationType !== null) {
            $sql .= " AND `operation` = '". $this->db->escape($operationType) ."' ";
        }

        if ($storeItemType !== null) {
            $sql .= " AND `storeItemType` = '". $this->db->escape($storeItemType) ."' ";
        }

        $sql .= " LIMIT ". (int)$limit;

        return $this->db->query($sql)->rows;
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
        $objectMaxSize = (int) $this->setting['object_max_size'];

        switch ($itemType) {
            case \pro_algolia\constant::PRODUCT:
                $this->load->model("{$this->route}/product");
                return $this->model_extension_module_pro_algolia_product->prepareData($itemId, $objectMaxSize);
                break;
        }
    }
}
