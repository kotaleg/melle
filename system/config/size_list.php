<?php

$_['size_list'] = array(
    'status'            => false,

    'events' => array(
        'admin/view/catalog/product_form/after' => 'extension/event/size_list/view_size_list_for_product_after',
        'admin/model/catalog/product/editProduct/after' => 'extension/event/size_list/edit_product_after',
    )
);