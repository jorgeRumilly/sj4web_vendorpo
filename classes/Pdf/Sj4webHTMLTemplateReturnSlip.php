<?php
/**
 * Module: sj4web_vendorpo
 * Description: HTML Template for Return Slips to Suppliers
 */

require_once _PS_CLASS_DIR_ . 'pdf/HTMLTemplate.php';

class Sj4webHTMLTemplateReturnSlip extends HTMLTemplate
{
    /**
     * @var OrderReturn
     */
    public $orderReturn;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var array Return slip data (products, supplier, return address, etc.)
     */
    public $returnData;

    /**
     * @param array $returnData Data prepared by Sj4webReturnService
     * @param Smarty $smarty
     *
     * @throws PrestaShopException
     */
    public function __construct($returnData, $smarty)
    {
        $this->returnData = $returnData;
        $this->orderReturn = new OrderReturn((int) $returnData['return_id']);
        $this->order = new Order((int) $returnData['order_id']);
        $this->smarty = $smarty;

        // Set header data
        $this->date = $returnData['return_date'];
        $this->title = $returnData['return_number'];

        // Set shop context
        $this->shop = new Shop((int) $this->order->id_shop);
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
            'header' => $this->trans('Return Slip', [], 'Modules.Sj4webvendorpo.Admin'),
        ]);

        return $this->smarty->fetch($this->getTemplate('header'));
    }

    /**
     * Assign header data without SSL logo check
     */
    protected function assignCustomHeaderData()
    {
        $id_shop = (int) $this->shop->id;
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
            'shop_details' => Configuration::get('PS_SHOP_DETAILS', null, null, (int) $id_shop),
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
        // Prepare supplier with return_address for template compatibility
        $supplier = $this->returnData['supplier'];
        $supplier['return_address'] = $this->returnData['return_address'];

        // Assign all return data to template
        $this->smarty->assign([
            'return_number' => $this->returnData['return_number'],
            'return_ref' => $this->returnData['return_ref'],
            'return_date' => $this->returnData['return_date'],
            'order_ref' => $this->returnData['order_ref'],
            'supplier' => $supplier,
            'items' => $this->returnData['items'],
            'shop_name' => $this->returnData['shop']['name'],
            'shop_address' => $this->returnData['shop']['address'],
            'shop_logo' => isset($this->returnData['shop']['logo']) ? $this->returnData['shop']['logo'] : null,
            'order' => $this->order,
            'order_return' => $this->orderReturn,
        ]);

        // Try to get custom template from module, fallback to default
        $customTemplate = $this->getCustomTemplate('return_slip');
        if ($customTemplate) {
            return $this->smarty->fetch($customTemplate);
        }

        // Fallback to default template
        return $this->smarty->fetch($this->getTemplate('return_slip'));
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

    /**
     * Returns the template's HTML footer.
     *
     * @return string HTML footer
     */
    public function getFooter()
    {
        $this->smarty->assign([
            'available_in_your_account' => false,
            'shop_address' => $this->getShopAddress(),
            'shop_phone' => Configuration::get('PS_SHOP_PHONE', null, null, (int) $this->shop->id),
            'shop_email' => Configuration::get('PS_SHOP_EMAIL', null, null, (int) $this->shop->id),
            'free_text' => '',
        ]);

        return $this->smarty->fetch($this->getTemplate('footer'));
    }

    /**
     * Returns the template filename.
     *
     * @return string filename
     */
    public function getFilename()
    {
        return sprintf(
            'return-%s-%s-%s.pdf',
            $this->returnData['order_ref'],
            $this->returnData['supplier']['id'],
            $this->returnData['return_ref']
        );
    }

    /**
     * Returns the template filename when using bulk rendering.
     *
     * @return string filename
     */
    public function getBulkFilename()
    {
        return 'return-slips.pdf';
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
