<?php

require_once dirname(__FILE__) . '/Sj4webSupplierSettings.php';

class Sj4webReturnService
{
    public function groupReturnBySupplier(OrderReturn $orderReturn)
    {
        $sql = 'SELECT
                    COALESCE(p.id_supplier, 0) as id_supplier,
                    COALESCE(s.name, \'No supplier\') as supplier_name,
                    ord.product_id,
                    ord.product_attribute_id,
                    ord.product_name,
                    ord.product_quantity,
                    p.reference as product_reference,
                    p.ean13 as product_ean13,
                    p.mpn as product_mpn,
                    pa.reference as combination_reference,
                    pa.ean13 as combination_ean13,
                    pa.mpn as combination_mpn,
                    p.is_virtual
                FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON ord.product_id = p.id_product
                LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON p.id_supplier = s.id_supplier
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON ord.product_attribute_id = pa.id_product_attribute
                WHERE ord.id_order_return = ' . (int) $orderReturn->id . '
                  AND p.is_virtual = 0
                ORDER BY supplier_name, ord.product_name';

        $results = Db::getInstance()->executeS($sql);
        if (!$results) {
            return [];
        }

        $groupedProducts = [];
        foreach ($results as $row) {
            $supplierId = (int) $row['id_supplier'];

            if (!isset($groupedProducts[$supplierId])) {
                $groupedProducts[$supplierId] = [
                    'supplier_name' => $row['supplier_name'],
                    'items' => [],
                    'total_qty' => 0,
                ];
            }

            $reference = (!empty($row['combination_reference']) && $row['product_attribute_id'] > 0)
                ? $row['combination_reference']
                : $row['product_reference'];

            $ean13 = (!empty($row['combination_ean13']) && $row['product_attribute_id'] > 0)
                ? $row['combination_ean13']
                : $row['product_ean13'];

            $mpn = (!empty($row['combination_mpn']) && $row['product_attribute_id'] > 0)
                ? $row['combination_mpn']
                : $row['product_mpn'];

            $groupedProducts[$supplierId]['items'][] = [
                'name' => $row['product_name'],
                'reference' => $reference ?: '',
                'ean13' => $ean13 ?: '',
                'mpn' => $mpn ?: '',
                'qty' => (int) $row['product_quantity'],
            ];

            $groupedProducts[$supplierId]['total_qty'] += (int) $row['product_quantity'];
        }

        return $groupedProducts;
    }

    public function buildReturnData(OrderReturn $orderReturn, $idSupplier, $idShop)
    {
        $groupedProducts = $this->groupReturnBySupplier($orderReturn);

        if (!isset($groupedProducts[$idSupplier])) {
            throw new Exception('No returned products found for supplier ' . $idSupplier . ' in this return.');
        }

        $supplierSettings = Sj4webSupplierSettings::getBySupplier($idSupplier, $idShop);
        if (!$supplierSettings || !$supplierSettings->hasReturnAddress()) {
            throw new Exception('Return address missing for supplier ' . $idSupplier . '. Please configure it before generating return slips.');
        }

        $supplierData = $groupedProducts[$idSupplier];
        $order = new Order($orderReturn->id_order);
        $shop = new Shop($idShop);
        $shopLogo = Configuration::get('PS_LOGO', null, null, $idShop);
        $logoUrl = $shopLogo ? _PS_IMG_DIR_ . $shopLogo : null;

        $supplier = $idSupplier > 0 ? new Supplier($idSupplier) : null;

        return [
            'return_id' => (int) $orderReturn->id,
            'return_ref' => $orderReturn->id,
            'order_id' => (int) $order->id,
            'order_ref' => $order->reference,
            'return_date' => date('d/m/Y H:i', strtotime($orderReturn->date_add)),
            'order_lang_id' => (int) $order->id_lang,
            'shop' => [
                'name' => $shop->name,
                'address' => $this->getShopAddress($shop),
                'logo' => $logoUrl,
            ],
            'supplier' => [
                'id' => $idSupplier,
                'name' => $supplier ? $supplier->name : 'No supplier',
            ],
            'return_address' => [
                'company_name' => $supplierSettings->company_name,
                'contact_name' => $supplierSettings->contact_name,
                'address1' => $supplierSettings->address1,
                'address2' => $supplierSettings->address2,
                'postcode' => $supplierSettings->postcode,
                'city' => $supplierSettings->city,
                'phone' => $supplierSettings->phone,
                'lead_time' => $supplierSettings->lead_time,
            ],
            'return_number' => 'RET-' . $order->reference . '-' . $idSupplier . '-' . $orderReturn->id,
            'items' => $supplierData['items'],
        ];
    }

    protected function getShopAddress(Shop $shop)
    {
        $address = Configuration::get('PS_SHOP_ADDR1', null, null, $shop->id);
        $address2 = Configuration::get('PS_SHOP_ADDR2', null, null, $shop->id);
        $postcode = Configuration::get('PS_SHOP_CODE', null, null, $shop->id);
        $city = Configuration::get('PS_SHOP_CITY', null, null, $shop->id);

        $fullAddress = $address;
        if ($address2) {
            $fullAddress .= "\n" . $address2;
        }
        if ($postcode || $city) {
            $fullAddress .= "\n" . trim($postcode . ' ' . $city);
        }

        return $fullAddress;
    }
}