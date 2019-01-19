<?php
/*
 *  location: admin/model
 *
 */
class ModelExtensionProPatchUrl extends Model
{
    public function ajax($route, $url = '', $secure = true)
    {
        return str_replace('&amp;', '&', $this->url->link($route, $url, $secure));
    }
}