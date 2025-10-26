<?php

$sql = [];

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'sj4web_supplier_settings` (
    `id_sj4web_supplier_settings` int(11) NOT NULL AUTO_INCREMENT,
    `id_shop` int(11) NOT NULL,
    `id_supplier` int(11) NOT NULL,
    `company_name` varchar(255) NOT NULL,
    `contact_name` varchar(255) DEFAULT NULL,
    `address1` varchar(255) NOT NULL,
    `address2` varchar(255) DEFAULT NULL,
    `postcode` varchar(32) NOT NULL,
    `city` varchar(128) NOT NULL,
    `phone` varchar(64) DEFAULT NULL,
    `lead_time` varchar(255) DEFAULT NULL,
    `show_email_on_pdf` TINYINT(1) DEFAULT 0,
    `show_phone_on_pdf` TINYINT(1) DEFAULT 0,
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id_sj4web_supplier_settings`),
    UNIQUE KEY `unique_shop_supplier` (`id_shop`, `id_supplier`),
    KEY `idx_shop` (`id_shop`),
    KEY `idx_supplier` (`id_supplier`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}