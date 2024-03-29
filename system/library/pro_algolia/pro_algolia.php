<?php

namespace pro_algolia;
require_once __DIR__ . '/vendor/autoload.php';

use Algolia\AlgoliaSearch\SearchClient;

class pro_algolia
{
    private $setting;
    private $client;
    private $index;

    function __construct($setting)
    {
        $this->setting = $setting;
    }

    public function initClient()
    {
        $this->client = SearchClient::create(
            $this->setting['app_id'],
            $this->setting['admin_api_key']
        );

        return true;
    }

    public function initIndex()
    {
        if (!$this->client) {
            return null;
        }

        if (isset($this->setting['index_name'])
        && $this->setting['index_name']) {
            $this->index = $this->client->initIndex($this->setting['index_name']);
            return true;
        }

        return false;
    }

    public function saveObjects($objects)
    {
        if (!$this->index) {
            return null;
        }

        return $this->index->saveObjects($objects);
    }

    public function deleteObjects($objects)
    {
        if (!$this->index) {
            return null;
        }

        return $this->index->deleteObjects($objects);
    }

    public function getObjects($objectsIds, $options = array())
    {
        if (!$this->index) {
            return null;
        }

        return $this->index->getObjects($objectsIds, $options);
    }

    public static function getObjectIdsFromResposeBody($resultBody)
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
}
