<?php

namespace g_cat;
use pro_request\Browser;

class g_cat
{
    private $setting;

    function __construct($setting)
    {
        $this->setting = $setting;
    }

    public function getLanguageCodes()
    {
        return array(
            'en-US',
            // 'ru-RU',
        );
    }

    public function getDownloadLink($languageCode)
    {
        return "http://www.google.com/basepages/producttype/taxonomy-with-ids.{$languageCode}.txt";
    }

    public function getGoogleProductCategories()
    {
        $languageCode = $this->setting['languageCode'];

        $browser = new Browser(array(
            'timeout' => 30,
        ));

        $response = $browser->get($this->getDownloadLink($languageCode), array());

        $status = (int)$response->getStatusCode();
        $txt = (string)$response->getContent();

        return $this->parseTxt($txt);
    }

    public function parseTxt($txt)
    {
        $lines = explode("\n", $txt);

        // remove first line
        array_shift($lines);

        $categories = array();

        foreach ($lines as $line) {
            $ex = explode("-", $line);
            $ex = array_map('trim', $ex);
            if (!$ex) { return; }

            $id = intval(array_shift($ex));
            $rest = array_pop($ex);

            $categories[] = array(
                'categoryId' => $id,
                'rest'       => mb_strtolower($rest, 'UTF-8'),
            );
        }

        foreach ($categories as $k => $cat) {
            $catEx = explode(">", $cat['rest']);
            $catEx = array_map('trim', $catEx);

            if ($catEx) {
                $categories[$k]['title'] = array_pop($catEx);

                $search = mb_strtolower(implode(" > ", $catEx), 'UTF-8');

                if (strcmp(mb_strtolower($categories[$k]['title'], 'UTF-8'), $search) === 0) {
                    $categories[$k]['parentId'] = false;
                } else {
                    foreach ($categories as $c) {
                        if (strcmp($c['rest'], $search) === 0) {
                            $categories[$k]['parentId'] = $c['categoryId'];
                            break;
                        }
                    }
                }

            } else {
                $categories[$k]['title'] = trim($cat['rest']);
                $categories[$k]['parentId'] = false;
            }
        }

        return $categories;
    }
}