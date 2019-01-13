<?php
class ModelApiImport1CLanguage extends Model
{
    public function getLanguages()
    {
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();

        return array_map(function($l) {
            if ($l['status']) { return $l['language_id']; }
        }, $languages);
    }
}