<?php

class pro_discount
{
    const DISCOUNT_TABLE = 'pd_discounts';
    const CATEGORY_TABLE = 'pd_category';
    const PRODUCT_TABLE = 'pd_product';
    const MANUFACTURER_TABLE = 'pd_manufacturer';
    const CUSTOMER_TABLE = 'pd_customer';

    const SALE = 'sale';
    const SALE_COUNT = 'sale_count';

    const MONEY = 'money';
    const PERCENT = 'percent';

    private $codename = 'pro_discount';
    private $route = 'extension/total/pro_discount';

    function __construct($registry)
    {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
    }

    public function getSpecialPrice()
    {
        //
    }
}