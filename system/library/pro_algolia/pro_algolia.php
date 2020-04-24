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
            $this->setting['admin_api_key'],
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

}
