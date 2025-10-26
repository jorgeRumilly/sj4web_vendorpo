<?php

require_once dirname(__FILE__) . '/../../classes/Sj4webVendorPoService.php';
require_once dirname(__FILE__) . '/../../classes/Sj4webReturnService.php';
require_once dirname(__FILE__) . '/../../classes/Pdf/Sj4webHTMLTemplateVendorPO.php';
require_once dirname(__FILE__) . '/../../classes/Pdf/Sj4webHTMLTemplateReturnSlip.php';

class AdminSj4webVendorPoController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();

        parent::__construct();

        $this->meta_title = $this->trans('Vendor Purchase Orders', [], 'Modules.Sj4webvendorpo.Admin');
    }

    public function postProcess()
    {
        $action = Tools::getValue('action');

        if (!Tools::getAdminTokenLite('AdminSj4webVendorPo') ||
            Tools::getValue('token') !== Tools::getAdminTokenLite('AdminSj4webVendorPo')) {
            $this->errors[] = $this->trans('Invalid token', [], 'Modules.Sj4webvendorpo.Admin');
            return false;
        }

        switch ($action) {
            case 'po':
                return $this->processPurchaseOrder();
            case 'return':
                return $this->processReturnSlip();
            default:
                $this->errors[] = $this->trans('Invalid action', [], 'Modules.Sj4webvendorpo.Admin');
                return false;
        }
    }

    protected function processPurchaseOrder()
    {
        $idOrder = (int) Tools::getValue('id_order');
        $idSupplier = (int) Tools::getValue('id_supplier');

        if (!$idOrder || !$idSupplier) {
            $this->errors[] = $this->trans('Missing order or supplier ID', [], 'Modules.Sj4webvendorpo.Admin');
            return false;
        }

        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            $this->errors[] = $this->trans('Order not found', [], 'Modules.Sj4webvendorpo.Admin');
            return false;
        }

        try {
            $module = Module::getInstanceByName('sj4web_vendorpo');
            $service = new Sj4webVendorPoService($module);
            $poData = $service->buildPoData($order, $idSupplier, $this->context->shop->id);

            // Create HTML template
            $template = new Sj4webHTMLTemplateVendorPO($poData, $this->context->smarty);

            // Generate PDF using PrestaShop standard PDF class
            $pdf = new PDF($order, 'VendorPO', $this->context->smarty);
            $pdf->pdf_renderer->setFontForLang($this->context->language->iso_code);
            $pdf->pdf_renderer->createHeader($template->getHeader());
            $pdf->pdf_renderer->createFooter($template->getFooter());
            $pdf->pdf_renderer->createContent($template->getContent());
            $pdf->pdf_renderer->writePage();
            $pdf->pdf_renderer->render($template->getFilename());

        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }

        return true;
    }

    protected function processReturnSlip()
    {
        $idOrderReturn = (int) Tools::getValue('id_order_return');
        $idSupplier = (int) Tools::getValue('id_supplier');

        if (!$idOrderReturn || !$idSupplier) {
            $this->errors[] = $this->trans('Missing return or supplier ID', [], 'Modules.Sj4webvendorpo.Admin');
            return false;
        }

        $orderReturn = new OrderReturn($idOrderReturn);
        if (!Validate::isLoadedObject($orderReturn)) {
            $this->errors[] = $this->trans('Order return not found', [], 'Modules.Sj4webvendorpo.Admin');
            return false;
        }

        try {
            $service = new Sj4webReturnService();
            $returnData = $service->buildReturnData($orderReturn, $idSupplier, $this->context->shop->id);

            // Create HTML template
            $template = new Sj4webHTMLTemplateReturnSlip($returnData, $this->context->smarty);

            // Generate PDF using PrestaShop standard PDF class
            $pdf = new PDF($orderReturn, 'ReturnSlip', $this->context->smarty);
            $pdf->pdf_renderer->setFontForLang($this->context->language->iso_code);
            $pdf->pdf_renderer->createHeader($template->getHeader());
            $pdf->pdf_renderer->createFooter($template->getFooter());
            $pdf->pdf_renderer->createContent($template->getContent());
            $pdf->pdf_renderer->writePage();
            $pdf->pdf_renderer->render($template->getFilename());

        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }

        return true;
    }

    public function init()
    {
        parent::init();

        if (Tools::getValue('action')) {
            $this->postProcess();
        }
    }
}