<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleProZnachek extends Model
{
    public function getZnachek($type)
    {
        if (!empty($type) && array_key_exists($type, $this->getZnachki())) {
            return $this->getZnachki()[$type];
        }

        return false;
    }

    public function getZnachekClass($type)
    {
        if (!empty($type) && array_key_exists($type, $this->getZnachki())) {
            return $type;
        }

        return '';
    }

    public function getZnachki()
    {
        return array(
            'new'   => 'Новинка',
            'act'   => 'Акция',
            'hit'   => 'Хит',
        );
    }
}