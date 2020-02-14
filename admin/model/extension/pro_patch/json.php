<?php
/*
 *  location: admin/model
 *
 */
class ModelExtensionProPatchJson extends Model
{
    public function parseJson($json)
    {
        $result = json_decode((string)$json, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }
    }
}