<?php
/**
*
* NOTICE OF LICENSE
*
* This source file is subject to the Apache License, Version 2.0
* that is bundled with this package in the file LICENSE.
* It is also available through the world-wide-web at this URL:
* http://www.apache.org/licenses/LICENSE-2.0
*
*  @author    presta.studio
*  @copyright Copyright (c) 2023 presta.studio
*  @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License, Version 2.0
*/

class OmnibusEuFreeCronModuleFrontController extends ModuleFrontController
{
    /** @var bool If set to true, will be redirected to authentication page */
    public $auth = false;

    /** @var bool */
    public $ajax;

    public function display()
    {
        $this->ajax = 1;

        if (Configuration::get('OMNIBUSEUFREE_CRON_STATUS', null, null, null, 2) == 1 && Tools::getIsset('token') && Tools::getIsset('type')) {
            if (Configuration::get('OMNIBUSEUFREE_CRON_TOKEN') == Tools::getValue('token')) {
                $type = (int) Tools::getValue('type');

                if ($type == 1) {
                    $Omnibus = new OmnibusEuFree;
                    $InsertDataCounter = $Omnibus->insertAllProductsToOmnibusTable();
                    PrestaShopLogger::addLog('[CRON] Omnibus Directive module by presta.studio - Updated price history. Number of products checked: ' . $InsertDataCounter);
                }

                if ($type == 2) {
                    $Omnibus = new OmnibusEuFree;
                    $OldDataCounter = $Omnibus->removeOldDataFromOmnibusTable();
                    PrestaShopLogger::addLog('[CRON] Omnibus Directive module by presta.studio - Deleted old data. Number of records deleted in database: ' . $OldDataCounter);
                }
            }
        }
    }
}