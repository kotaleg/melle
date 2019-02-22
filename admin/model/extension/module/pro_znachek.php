<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleProZnachek extends Model
{
    public function getZnachki()
    {
        return array(
            'new'   => 'Значек "Новинка"',
            // 'act'   => 'Значек "Акция"',
            'hit'   => 'Значек "Хит"',
        );
    }
}