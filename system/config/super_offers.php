<?php

$_['super_offers'] = array(
    'status'            => false,

    'events' => array(
        'admin/view/catalog/product_form/after' => 'extension/event/super_offers/view_options_for_product_after',
        'admin/model/catalog/product/editProduct/after' => 'extension/event/super_offers/edit_product_after',
        'admin/view/catalog/option_form/before' => 'extension/event/super_offers/view_edit_option_before',
        'admin/model/catalog/option/editOption/after' => 'extension/event/super_offers/edit_option_after',
    )
);