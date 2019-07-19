<?php

namespace pro_request;
require_once __DIR__ . '/vendor/autoload.php';

use Buzz\Client\Curl;

class Browser extends \Buzz\Browser
{
    protected $setting;

    function __construct($setting = array())
    {
        $this->setting = $setting;

        $client = new Curl();

        if (isset($setting['timeout'])) {
            $client->setTimeout($setting['timeout']);
        }

        parent::__construct($client);
    }

    public function getBaseUrl()
    {
        return $this->$base_url;
    }
}