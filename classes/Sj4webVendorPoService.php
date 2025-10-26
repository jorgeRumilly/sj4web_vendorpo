<?php

require_once dirname(__FILE__) . '/Sj4webSupplierSettings.php';

class Sj4webVendorPoService
{
    protected $module;

    public function __construct($module = null)
    {
        $this->module = $module;
    }

    public function groupOrderProductsBySupplier(Order $order)
    {
        $sql = 'SELECT
                    COALESCE(p.id_supplier, 0) as id_supplier,
                    COALESCE(s.name, \'No supplier\') as supplier_name,
                    od.product_id,
                    od.product_attribute_id,
                    od.product_name,
                    od.product_quantity,
                    p.reference as product_reference,
                    p.ean13 as product_ean13,
                    p.mpn as product_mpn,
                    pa.reference as combination_reference,
                    pa.ean13 as combination_ean13,
                    pa.mpn as combination_mpn,
                    p.is_virtual
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON od.product_id = p.id_product
                LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON p.id_supplier = s.id_supplier
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON od.product_attribute_id = pa.id_product_attribute
                WHERE od.id_order = ' . (int) $order->id . '
                  AND p.is_virtual = 0
                ORDER BY supplier_name, od.product_name';

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

    public function groupCartProductsBySupplier(Cart $cart)
    {
        $products = $cart->getProducts();
        if (!$products) {
            return [];
        }

        $groupedProducts = [];
        foreach ($products as $product) {
            if ($product['is_virtual']) {
                continue;
            }

            $supplierId = (int) ($product['id_supplier'] ?: 0);
            $supplierName = $product['supplier_name'] ?: 'No supplier';

            if (!isset($groupedProducts[$supplierId])) {
                $groupedProducts[$supplierId] = [
                    'supplier_name' => $supplierName,
                    'items' => [],
                    'total_qty' => 0,
                ];
            }

            $reference = (!empty($product['attributes_small']) && $product['id_product_attribute'] > 0)
                ? ($product['reference'] ?: $product['reference'])
                : $product['reference'];

            $groupedProducts[$supplierId]['items'][] = [
                'name' => $product['name'] . ($product['attributes_small'] ? ' | ' . $product['attributes_small'] : ''),
                'reference' => $reference ?: '',
                'ean13' => $product['ean13'] ?: '',
                'mpn' => $product['mpn'] ?: '',
                'qty' => (int) $product['cart_quantity'],
            ];

            $groupedProducts[$supplierId]['total_qty'] += (int) $product['cart_quantity'];
        }

        return $groupedProducts;
    }

    public function getSupplierSettings($idSupplier, $idShop)
    {
        return Sj4webSupplierSettings::getBySupplier($idSupplier, $idShop);
    }

    public function buildPoData(Order $order, $idSupplier, $idShop)
    {
        $groupedProducts = $this->groupOrderProductsBySupplier($order);

        if (!isset($groupedProducts[$idSupplier])) {
            throw new Exception('No products found for supplier ' . $idSupplier . ' in this order.');
        }

        $supplierData = $groupedProducts[$idSupplier];
        $supplierSettings = $this->getSupplierSettings($idSupplier, $idShop);

        $shop = new Shop($idShop);
        $shopLogo = Configuration::get('PS_LOGO', null, null, $idShop);
        $logoUrl = $shopLogo ? _PS_IMG_DIR_ . $shopLogo : null;

        $supplier = $idSupplier > 0 ? new Supplier($idSupplier) : null;

        return [
            'order_id' => (int) $order->id,
            'order_ref' => $order->reference,
            'order_date' => date('d/m/Y H:i', strtotime($order->date_add)),
            'order_lang_id' => (int) $order->id_lang,
            'shop' => [
                'name' => $shop->name,
                'address' => $this->getShopAddress($shop),
                'logo' => $logoUrl,
            ],
            'supplier' => [
                'id' => $idSupplier,
                'name' => $supplier ? $supplier->name : 'No supplier',
                'return_address' => $supplierSettings ? [
                    'company_name' => $supplierSettings->company_name,
                    'contact_name' => $supplierSettings->contact_name,
                    'address1' => $supplierSettings->address1,
                    'address2' => $supplierSettings->address2,
                    'postcode' => $supplierSettings->postcode,
                    'city' => $supplierSettings->city,
                    'phone' => $supplierSettings->phone,
                    'lead_time' => $supplierSettings->lead_time,
                ] : null,
                'show_email_on_pdf' => (bool) $supplierSettings->show_email_on_pdf,
                'show_phone_on_pdf' => (bool) $supplierSettings->show_phone_on_pdf
            ],
            'po_number' => 'PO-' . $order->reference . '-' . $idSupplier,
            'items' => $supplierData['items'],
        ];
    }

    public function buildSupplierSummaryForCart(Cart $cart, $idShop)
    {
        $groupedProducts = $this->groupCartProductsBySupplier($cart);
        return $this->buildSupplierSummary($groupedProducts, $idShop);
    }

    public function buildSupplierSummaryForOrder(Order $order, $idShop)
    {
        $groupedProducts = $this->groupOrderProductsBySupplier($order);
        return $this->buildSupplierSummary($groupedProducts, $idShop);
    }

    protected function buildSupplierSummary($groupedProducts, $idShop)
    {
        if (empty($groupedProducts)) {
            return ['package_count' => 0, 'packages' => []];
        }

        $packages = [];
        $packageIndex = 1;

        foreach ($groupedProducts as $supplierId => $supplierData) {
            $supplierSettings = $this->getSupplierSettings($supplierId, $idShop);
            $leadTime = $supplierSettings ? $supplierSettings->lead_time : null;

            // Get supplier name (avoid "No supplier" display)
            $supplierName = $supplierData['supplier_name'];
            if ($supplierId == 0 || $supplierName === 'No supplier') {
//                $supplierName = $this->trans('Supplier without specified delay');
                $supplierName = Context::getContext()->getTranslator()->trans('Supplier without specified delay', [], 'Modules.Sj4webvendorpo.Shop');
            }

            $packages[] = [
                'number' => $packageIndex++,
                'supplier_id' => $supplierId,
                'supplier_name' => $supplierName,
//                'lead_time' => $leadTime ?: $this->trans('Not specified'),
                'lead_time' => $leadTime ?: Context::getContext()->getTranslator()->trans('Not specified'),
                'item_count' => count($supplierData['items']),
                'items' => $supplierData['items'],
            ];
        }

        return [
            'package_count' => count($groupedProducts),
            'packages' => $packages,
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

    protected function trans($string)
    {
        if ($this->module && method_exists($this->module, 'trans')) {
            return $this->module->trans($string, [], 'Modules.Sj4webvendorpo.Shop');
        }
        return $string;
    }
}