<?php
/*
 *  location: system/library
 */

namespace pro_mailq;

class constant
{
    const CODENAME = 'pro_mailq';
    const ROUTE = 'extension/module/pro_mailq';
    const TYPE = 'module';

    /* TABLES START */
    const QUEUE_TABLE = 'pro_mailq_queue';
    const ATTACHMENT_TABLE = 'pro_mailq_attachment';
    const LOG_TABLE = 'pro_mailq_log';
    /* TABLES END */

    const SUCCESS = 'success';
    const ERROR = 'error';
    const UNDEFINED = 'undefined';
}
