<?php

$_['catalog_option'] = array(
    'status' => false,
    'debug' => true,

    'events' => array(
        'admin/view/catalog/option_list/after' => 'extension/event/catalog_option/view_catalog_option_list_after',
        'admin/view/catalog/option_form/before' => 'extension/event/catalog_option/view_catalog_option_form_before',
    )
);
