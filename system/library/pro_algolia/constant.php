<?php
namespace pro_algolia;

class constant
{
    const CODENAME = 'pro_algolia';
    const ROUTE = 'extension/module/pro_algolia';

    /* TABLES START */
    const INDEX_OBJECT_TABLE = 'pro_algolia_index_object';
    const QUEUE_TABLE = 'pro_algolia_queue';
    const QUEUE_LOG_TABLE = 'pro_algolia_queue_log';
    /* TABLES END */

    /* STORE ITEM TYPE START */
    const PRODUCT = 'product';
    /* STORE ITEM TYPE END */

    /* OPERATIONS START */
    const DELETE = 'delete';
    const SAVE = 'save';
    /* OPERATIONS END */

    /* STATUS HELPERS START */
    const ERROR = 'error';
    const SUCCESS = 'success';
    const UNDEFINED = 'undefined';
    /* STATUS HELPERS END */
}