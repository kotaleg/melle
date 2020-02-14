<?php
/*
 *  location: catalog/model
 *
 */
class ModelExtensionProPatchLanguage extends Model
{
    public function loadStrings($strings)
    {
        $result = array();

        if (is_array($strings)) {
            foreach ($strings as $s) {
                $string = trim($s);
                $result[$string] = $this->language->get($string);
            }
        }

        return $result;
    }
}