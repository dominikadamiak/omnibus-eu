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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'omnibus_eu_free` (
    `id_omnibuseufree` int(11) NOT NULL AUTO_INCREMENT,
    `id_product` int(11) UNSIGNED NOT NULL,
    `id_product_attribute` int(11) UNSIGNED NOT NULL,
    `price` decimal(20,6) NOT NULL DEFAULT 0.000000,
    `id_currency` int(11) NOT NULL DEFAULT 0,
    `is_default_currency` int(11) NOT NULL DEFAULT 0,
    `is_last` int(11) NOT NULL DEFAULT 0,
    `currency_conversion_rate` decimal(13,6) NOT NULL DEFAULT 0.000000,
    `date_add` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY  (`id_omnibuseufree`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'omnibus_eu_free`
ADD KEY `id_product` (`id_product`),
ADD KEY `id_product_attribute` (`id_product_attribute`);';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
