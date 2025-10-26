<?php

class Sj4webSupplierSettings extends ObjectModel
{
    public $id_sj4web_supplier_settings;
    public $id_shop;
    public $id_supplier;
    public $company_name;
    public $contact_name;
    public $address1;
    public $address2;
    public $postcode;
    public $city;
    public $phone;
    public $lead_time;
    public $show_email_on_pdf;
    public $show_phone_on_pdf;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'sj4web_supplier_settings',
        'primary' => 'id_sj4web_supplier_settings',
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_supplier' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'company_name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'contact_name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255],
            'address1' => ['type' => self::TYPE_STRING, 'validate' => 'isAddress', 'required' => true, 'size' => 255],
            'address2' => ['type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 255],
            'postcode' => ['type' => self::TYPE_STRING, 'validate' => 'isPostCode', 'required' => true, 'size' => 32],
            'city' => ['type' => self::TYPE_STRING, 'validate' => 'isCityName', 'required' => true, 'size' => 128],
            'phone' => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 64],
            'lead_time' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255],
            'show_email_on_pdf' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'show_phone_on_pdf' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
    }

    public static function getBySupplier($id_supplier, $id_shop = null)
    {
        if (!$id_shop) {
            $id_shop = (int) Context::getContext()->shop->id;
        }

        $sql = 'SELECT `id_sj4web_supplier_settings`
                FROM `' . _DB_PREFIX_ . 'sj4web_supplier_settings`
                WHERE `id_supplier` = ' . (int) $id_supplier . '
                AND `id_shop` = ' . (int) $id_shop;

        $id = Db::getInstance()->getValue($sql);

        return $id ? new self($id) : null;
    }

    public static function getAllByShop($id_shop = null)
    {
        if (!$id_shop) {
            $id_shop = (int) Context::getContext()->shop->id;
        }

        $sql = 'SELECT s.*, ss.id_sj4web_supplier_settings, ss.company_name as settings_company_name,
                       ss.lead_time, ss.show_email_on_pdf, ss.show_phone_on_pdf, 
                       ss.date_add as settings_date_add, ss.date_upd as settings_date_upd
                FROM `' . _DB_PREFIX_ . 'supplier` s
                INNER JOIN `' . _DB_PREFIX_ . 'sj4web_supplier_settings` ss
                    ON s.id_supplier = ss.id_supplier AND ss.id_shop = ' . (int) $id_shop . '
                WHERE s.active = 1
                ORDER BY s.name ASC';

        return Db::getInstance()->executeS($sql);
    }

    public function add($autodate = true, $null_values = false)
    {
        if ($autodate) {
            $this->date_add = date('Y-m-d H:i:s');
            $this->date_upd = date('Y-m-d H:i:s');
        }

        return parent::add($autodate, $null_values);
    }

    public function update($null_values = false)
    {
        $this->date_upd = date('Y-m-d H:i:s');

        return parent::update($null_values);
    }

    public function getFormattedAddress()
    {
        $address = $this->company_name;
        if ($this->contact_name) {
            $address .= "\n" . $this->contact_name;
        }
        $address .= "\n" . $this->address1;
        if ($this->address2) {
            $address .= "\n" . $this->address2;
        }
        $address .= "\n" . $this->postcode . ' ' . $this->city;
        if ($this->phone) {
            $address .= "\n" . $this->phone;
        }

        return $address;
    }

    public function hasReturnAddress()
    {
        return !empty($this->company_name) && !empty($this->address1) &&
               !empty($this->postcode) && !empty($this->city);
    }
    public static function getAvailableSuppliers($id_shop = null)
    {
        if (!$id_shop) {
            $id_shop = (int) Context::getContext()->shop->id;
        }

        $sql = 'SELECT s.id_supplier, s.name
                FROM `' . _DB_PREFIX_ . 'supplier` s
                LEFT JOIN `' . _DB_PREFIX_ . 'sj4web_supplier_settings` ss
                    ON s.id_supplier = ss.id_supplier AND ss.id_shop = ' . (int) $id_shop . '
                WHERE s.active = 1 AND ss.id_supplier IS NULL
                ORDER BY s.name ASC';

        return Db::getInstance()->executeS($sql);
    }

}