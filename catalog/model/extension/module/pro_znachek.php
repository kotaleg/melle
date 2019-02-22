<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleProZnachek extends Model
{
    public function getZnachek($type, $show_check = false)
    {
        if (!empty($type)
        && array_key_exists($type, $this->getZnachki())) {
            if ($show_check) {
                if ($this->shouldShow($type)) {
                    return $this->getZnachki()[$type];
                }
            } else {
                return $this->getZnachki()[$type];
            }
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

    public function isZnachek($type)
    {
        if (!empty($type) && array_key_exists($type, $this->getZnachki())) {
            return true;
        }

        return false;
    }

    public function shouldShow($type)
    {
        $allowed = array('new', 'hit');

        if (!empty($type) && array_key_exists($type, $allowed)) {
            return true;
        }

        return false;
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