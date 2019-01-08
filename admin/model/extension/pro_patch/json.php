<?php
/*
 *  location: admin/model
 *
 */
class ModelExtensionProPatchJson extends Model
{
    public function parseJson($json)
    {
        $parsed = json_decode($json, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return array('error' => 'Parse error.');
        }

        return $parsed === null ? array() : $parsed;
    }
}