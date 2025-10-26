<?php
/**
 * Module: sj4web_vendorpo
 * Description: HTML Template for Vendor Purchase Orders
 */

require_once _PS_CLASS_DIR_ . 'pdf/HTMLTemplate.php';

class Sj4webHTMLTemplateVendorPO extends HTMLTemplate
{
    /**
     * @var Order
     */
    public $order;

    /**
     * @var array Vendor PO data (products, supplier, etc.)
     */
    public $poData;

    /**
     * @param array $poData Data prepared by Sj4webVendorPoService
     * @param Smarty $smarty
     *
     * @throws PrestaShopException
     */
    public function __construct($poData, $smarty)
    {
        $this->poData = $poData;
        $this->order = new Order((int)$poData['order_id']);
        $this->smarty = $smarty;

        // Set header data
        $this->date = $poData['order_date'];
        $this->title = $poData['po_number'];

        // Set shop context
        $this->shop = new Shop((int)$this->order->id_shop);
    }

    /**
     * Returns the template's HTML header.
     *
     * @return string HTML header
     */
    public function getHeader()
    {
        // Custom header assignment to avoid SSL issues with logo
        $this->assignCustomHeaderData();
        $this->smarty->assign([
            'header' => $this->trans('Purchase Order', [], 'Modules.Sj4webvendorpo.Admin'),
        ]);

        return $this->smarty->fetch($this->getTemplate('header'));
    }

    /**
     * Assign header data without SSL logo check
     */
    protected function assignCustomHeaderData()
    {
        $id_shop = (int)$this->shop->id;
        $shop_name = Configuration::get('PS_SHOP_NAME', null, null, $id_shop);

        $logo = $this->getLogo();
        $width = 0;
        $height = 0;

        // Avoid SSL getimagesize() issues in local dev
        if (!empty($logo) && file_exists(_PS_IMG_DIR_ . $logo)) {
            $imagePath = _PS_IMG_DIR_ . $logo;
            if (file_exists($imagePath)) {
                $imageSize = @getimagesize($imagePath);
                if ($imageSize !== false) {
                    list($width, $height) = $imageSize;
                }
            }
        }

        // Limit the height of the logo for the PDF render
        $maximum_height = 100;
        if ($height > $maximum_height) {
            $ratio = $maximum_height / $height;
            $height *= $ratio;
            $width *= $ratio;
        }

        $this->smarty->assign([
            'logo_path' => !empty($logo) ? _PS_IMG_DIR_ . $logo : '',
            'img_ps_dir' => _PS_IMG_DIR_,
            'img_update_time' => Configuration::get('PS_IMG_UPDATE_TIME'),
            'date' => $this->date,
            'title' => $this->title,
            'shop_name' => $shop_name,
            'shop_details' => Configuration::get('PS_SHOP_DETAILS', null, null, (int)$id_shop),
            'width_logo' => $width,
            'height_logo' => $height,
        ]);
    }

    /**
     * Returns the template's HTML content.
     *
     * @return string HTML content
     */
    public function getContent()
    {
        $invoiceAddressPatternRules = json_decode(Configuration::get('PS_INVCE_INVOICE_ADDR_RULES'), true);
        $deliveryAddressPatternRules = json_decode(Configuration::get('PS_INVCE_DELIVERY_ADDR_RULES'), true);

        $invoice_address = new Address((int)$this->order->id_address_invoice);
        $country = new Country((int)$invoice_address->id_country);
        $formatted_invoice_address = AddressFormat::generateAddress($invoice_address, $invoiceAddressPatternRules, '<br />', ' ');
        $phone_invoice = ($invoice_address->phone) ?? $invoice_address->phone_mobile;

        $delivery_address = null;
        $formatted_delivery_address = '';
        if (!empty($this->order->id_address_delivery)) {
            $delivery_address = new Address((int)$this->order->id_address_delivery);
            $formatted_delivery_address = AddressFormat::generateAddress($delivery_address, $deliveryAddressPatternRules, '<br />', ' ');
            $phone_delivery = ($delivery_address->phone) ?? $delivery_address->phone_mobile;
        }
        $customer = new Customer((int)$this->order->id_customer);

        // add email to pdf if option is activated
        $show_email_on_pdf = (bool)$this->poData['supplier']['show_email_on_pdf'];
        if ($show_email_on_pdf) {
            if ($customer->email) {
                $formatted_invoice_address .= '<br />' . $this->trans('Email: %email%', ['%email%' => $customer->email], 'Modules.Sj4webvendorpo.Admin');
                if($formatted_delivery_address && $delivery_address) {
                    $formatted_delivery_address .= '<br />' . $this->trans('Email: %email%', ['%email%' => $customer->email], 'Modules.Sj4webvendorpo.Admin');
                }
            }
        }

        // add phone to pdf if option is activated
        $show_phone_on_pdf = (bool)$this->poData['supplier']['show_phone_on_pdf'];
        if ($show_phone_on_pdf) {
            if ($phone_invoice) {
                $formatted_invoice_address .= '<br />' . $this->trans('Phone: %phone%', ['%phone%' => $phone_invoice], 'Modules.Sj4webvendorpo.Admin');
            }
            if ($delivery_address && isset($phone_delivery) && $phone_delivery) {
                $formatted_delivery_address .= '<br />' . $this->trans('Phone: %phone%', ['%phone%' => $phone_delivery], 'Modules.Sj4webvendorpo.Admin');
            }
        }

        $layout = $this->computeLayout(['has_discount' => false]);

        $legal_free_text = Hook::exec('displayInvoiceLegalFreeText', ['order' => $this->order]);
        if (!$legal_free_text) {
            $legal_free_text = Configuration::get('PS_INVOICE_LEGAL_FREE_TEXT', (int)Context::getContext()->language->id, null, (int)$this->order->id_shop);
        }

        // @todo: add parure handling if needed

//        $order_details = $this->order_invoice->getProducts();
//        $has_parure = false;
//        $list_produit_parures = [];
//        foreach ($order_details as $key => $order_detail) {
//            $id_product = (int) $order_detail['product_id'];
//            $product = new Product($id_product, false, (int) $this->order->id_lang);
//            if($product->is_parure) {
//                $has_parure = true;
//                $id_product_attribute = (int)$order_detail['product_attribute_id'];
//                $quantity = $order_detail['product_quantity'];
//                $list_produit_parures[] = $this->get_parure_item($id_product_attribute, $id_product, $quantity);
//                if($order_detail['customizedDatas']) {
//                    $customized_datas = $order_detail['customizedDatas'][$order_detail['id_address_delivery']][$order_detail['id_customization']]['datas']['1'];
//                    foreach ($customized_datas as $customized_data) {
//                        foreach ($customized_data['list_product_parure'] as $product_parure){
//                            $id_product_attribute = (int)$product_parure['id_product_attribute'];
//                            $id_product = (int)$product_parure['id_product'];
//                            $parure_quantity = (int)$product_parure['quantity'] * $quantity;
//                            $list_produit_parures[] = $this->get_parure_item($id_product_attribute, $id_product, $parure_quantity);
//                        }
//                    }
//                }
//            } else {
//                unset($order_details[$key]);
//            }
//        }


        // Assign all PO data to template
        $this->smarty->assign([
            'po_number' => $this->poData['po_number'],
            'order_ref' => $this->poData['order_ref'],
            'order_date' => $this->poData['order_date'],
            'supplier' => $this->poData['supplier'],
            'invoice_address' => $formatted_invoice_address,
            'delivery_address' => $formatted_delivery_address,
            'layout' => $layout,
            'customer' => $customer,
            'addresses' => ['invoice' => $invoice_address, 'delivery' => $delivery_address],
            'items' => $this->poData['items'],
            'shop_name' => $this->poData['shop']['name'],
            'shop_address' => $this->poData['shop']['address'],
            'shop_logo' => $this->poData['shop']['logo'] ?? null,
            'order' => $this->order,
            'legal_free_text' => $legal_free_text,
        ]);

        $tpls = [
            'style_tab' => $this->renderTemplate('invoice.style-tab'),
            'addresses_tab' => $this->renderTemplate('supplier-vendorpo.addresses-tab'),
            'items_tab' => $this->renderTemplate('supplier-vendorpo.products-tab'),
            'note_tab' => $this->renderTemplate('supplier-vendorpo.note-tab'),
            'summary_tab' => $this->renderTemplate('supplier-vendorpo.summary-tab'),
        ];

        $this->smarty->assign($tpls);

//        // Try to get custom template from module, fallback to default
//        $customTemplate = $this->getCustomTemplate('supplier-vendorpo');
//        if ($customTemplate) {
//            return $this->smarty->fetch($customTemplate);
//        }
//
//        // Fallback to default template
//        return $this->smarty->fetch($this->getTemplate('vendor_po'));

        return $this->renderTemplate('supplier-vendorpo');
    }

    /**
     * Get custom template from module directory
     *
     * @param string $templateName
     * @return string|false Template path or false
     */
    protected function getCustomTemplate($templateName)
    {
        $templatePath = _PS_MODULE_DIR_ . 'sj4web_vendorpo/views/templates/pdf/' . $templateName . '.tpl';

        if (file_exists($templatePath)) {
            return $templatePath;
        }

        return false;
    }

    protected function renderTemplate($templateName)
    {
        $customTemplate = $this->getCustomTemplate($templateName);
        if ($customTemplate) {
            return $this->smarty->fetch($customTemplate);
        }
        return $this->smarty->fetch($this->getTemplate($templateName));
    }


    /**
     * Returns the template's HTML footer.
     *
     * @return string HTML footer
     */
    public function getFooter()
    {
        $id_shop = (int)$this->shop->id;
        $iso_pays = 'FR';
        if (isset($this->order->id_address_delivery) && $this->order->id_address_delivery) {
            $delivery_address = new Address((int)$this->order->id_address_delivery);
            $country = new Country($delivery_address->id_country);
            if ($country) {
                $iso_pays = $country->iso_code;
            }
        }
        $this->smarty->assign(array(
            'available_in_your_account' => $this->available_in_your_account,
            'shop_fax' => Configuration::get('PS_SHOP_FAX', null, null, $id_shop),
            'shop_phone' => Configuration::get('PS_SHOP_PHONE', null, null, $id_shop),
            'shop_email' => Configuration::get('PS_SHOP_EMAIL', null, null, $id_shop),
            'free_text' => Configuration::get('PS_INVOICE_FREE_TEXT', (int)Context::getContext()->language->id, null, $id_shop),
            'iso_delivery' => $iso_pays,
        ));
        $footer_text = Tools::getPdfFooter();
        $this->smarty->assign($footer_text);
        return $this->smarty->fetch($this->getTemplate('footer'));
    }

    /**
     * Compute layout for PO table based on parameters.
     *
     * @param array $params Parameters to adjust layout (e.g., has_discount)
     * @return array Computed layout with column widths
     */
    protected function computeLayout(array $params): array
    {
        $layout = [
            'reference' => [
                'width' => 15,
            ],
            'ean13' => [
                'width' => 15,
            ],
            'product' => [
                'width' => 58,
            ],
            'quantity' => [
                'width' => 12,
            ],
        ];

        if (isset($params['has_discount']) && $params['has_discount']) {
            $layout['before_discount'] = ['width' => 0];
            $layout['product']['width'] -= 7;
            $layout['reference']['width'] -= 3;
        }

        $total_width = 0;
        $free_columns_count = 0;
        foreach ($layout as $data) {
            if ($data['width'] === 0) {
                ++$free_columns_count;
            }

            $total_width += $data['width'];
        }

        $delta = 100 - $total_width;

        foreach ($layout as $row => $data) {
            if ($data['width'] === 0) {
                $layout[$row]['width'] = $delta / $free_columns_count;
            }
        }

        $layout['_colCount'] = count($layout);

        return $layout;
    }

    /**
     * Returns the template filename.
     *
     * @return string filename
     */
    public function getFilename()
    {
        return sprintf(
            'BC%s-%s.pdf',
            $this->poData['order_ref'],
            $this->poData['supplier']['name']
        );
    }

    /**
     * Returns the template filename when using bulk rendering.
     *
     * @return string filename
     */
    public function getBulkFilename()
    {
        return 'purchase-orders.pdf';
    }

    /**
     * Translation wrapper for module
     *
     * @param string $string
     * @param array $params
     * @param string $domain
     * @return string
     */
    protected function trans($string, $params = [], $domain = 'Modules.Sj4webvendorpo.Admin')
    {
        return Context::getContext()->getTranslator()->trans($string, $params, $domain);
    }
}
