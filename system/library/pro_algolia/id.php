<?php
namespace pro_algolia;

use pro_algolia\constant;

class id
{
    public static function generateId($data)
    {
        return hash('md5', $data);
    }

    public static function generateIdForProduct($productId)
    {
        if (!$productId) {
            return null;
        }

        return self::generateId(constant::PRODUCT.'-'.$productId);
    }
}