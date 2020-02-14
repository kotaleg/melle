<?php
/*
 *  location: catalog/model
 *
 */
class ModelExtensionProPatchLoad extends Model
{
    public function view($template, $data)
    {
        if (substr($template, -3) == 'tpl') {
            $template = substr($template, 0, -4);
        }

        if (VERSION < '2.2.0.0') {
            return $this->load->view($template.'.tpl', $data);
        } else {
            return $this->load->view($template, $data);
        }
    }
}