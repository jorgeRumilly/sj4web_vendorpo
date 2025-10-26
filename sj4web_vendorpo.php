<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Sj4web_vendorpo extends Module
{
    public function __construct()
    {
        $this->name = 'sj4web_vendorpo';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'SJ4WEB.FR';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.1.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('SJ4WEB - Vendor POs and Supplier Settings', [], 'Modules.Sj4webvendorpo.Admin');
        $this->description = $this->trans('Generate vendor purchase orders and manage supplier settings', [], 'Modules.Sj4webvendorpo.Admin');

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', [], 'Modules.Sj4webvendorpo.Admin');

        if (!Configuration::get('SJ4WEB_VENDORPO_INSTALLED')) {
            $this->warning = $this->trans('No name provided', [], 'Modules.Sj4webvendorpo.Admin');
        }
    }

    public function install()
    {
        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install()
            && $this->installTab()
            && $this->registerHook('displayAdminOrderMain')
            && $this->registerHook('displayCheckoutSummaryTop')
            && $this->registerHook('displayShoppingCartFooter')
            && $this->registerHook('displayBeforeCarrier')
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('displayHeader')
            && Configuration::updateValue('SJ4WEB_VENDORPO_INSTALLED', true)
            && Configuration::updateValue('SJ4WEB_VENDORPO_MULTI_CARRIER', true)
            && Configuration::updateValue('SJ4WEB_VENDORPO_MIN_SHIPMENTS', 2);
    }

    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');

        return $this->uninstallTab()
            && parent::uninstall()
            && Configuration::deleteByName('SJ4WEB_VENDORPO_INSTALLED')
            && Configuration::deleteByName('SJ4WEB_VENDORPO_MULTI_CARRIER')
            && Configuration::deleteByName('SJ4WEB_VENDORPO_MIN_SHIPMENTS');
    }

    protected function installTab()
    {
        $parentTabId = (int) Tab::getIdFromClassName('AdminCatalog');

        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminSj4webSupplierSettings';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Vendor POs and Supplier Settings', [], 'Modules.Sj4webvendorpo.Admin', $lang['locale']);
        }
        $tab->id_parent = $parentTabId;
        $tab->module = $this->name;

        return $tab->add();
    }

    protected function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminSj4webSupplierSettings');
        if ($idTab) {
            $tab = new Tab($idTab);
            return $tab->delete();
        }

        return true;
    }

    public function hookDisplayHeader($params)
    {
        if(!isset($this->context->controller->php_self)){
            return;
        }
        if(in_array($this->context->controller->php_self, ['order-confirmation','order', 'checkout'])){
            // Register assets for confirmation page
            $this->registerFrontAssets();
        }

    }

    public function hookDisplayAdminOrderMain($params)
    {
        if (!isset($params['id_order'])) {
            return '';
        }

        $order = new Order((int) $params['id_order']);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        require_once dirname(__FILE__) . '/classes/Sj4webVendorPoService.php';
        $service = new Sj4webVendorPoService($this);
        $supplierGroups = $service->groupOrderProductsBySupplier($order);

        if (empty($supplierGroups)) {
            return '';
        }

        $this->context->smarty->assign([
            'order' => $order,
            'supplier_groups' => $supplierGroups,
            'module_dir' => $this->_path,
            'admin_token' => Tools::getAdminTokenLite('AdminSj4webVendorPo'),
        ]);

        return $this->display(__FILE__, 'views/templates/admin/order_block.tpl');
    }

    public function hookDisplayCheckoutSummaryTop($params)
    {
//        $this->registerFrontAssets();
        return $this->displaySupplierSummary('summary');
    }

    public function hookDisplayShoppingCartFooter($params)
    {
        return '';
    }

    public function hookDisplayBeforeCarrier($params)
    {
//        $this->registerFrontAssets();
        return $this->displaySupplierSummary('carrier');
    }

    public function hookDisplayOrderConfirmation($params)
    {
        if (!isset($params['order'])) {
            return '';
        }

        $order = $params['order'];
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        require_once dirname(__FILE__) . '/classes/Sj4webVendorPoService.php';
        $service = new Sj4webVendorPoService($this);
        $supplierSummary = $service->buildSupplierSummaryForOrder($order, (int) $this->context->shop->id);

        $minShipments = (int) Configuration::get('SJ4WEB_VENDORPO_MIN_SHIPMENTS', 2);

        if (empty($supplierSummary) || $supplierSummary['package_count'] < $minShipments) {
            return '';
        }

        // Register assets for confirmation page
//        $this->registerFrontAssets();

        $this->context->smarty->assign([
            'supplier_summary' => $supplierSummary,
            'display_type' => 'confirmation',
        ]);

        return $this->display(__FILE__, 'views/templates/front/order_confirmation_supplier.tpl');
    }

    protected function displaySupplierSummary($type = 'summary')
    {
        if (!Configuration::get('SJ4WEB_VENDORPO_MULTI_CARRIER')) {
            return '';
        }

        $cart = $this->context->cart;
        if (!Validate::isLoadedObject($cart)) {
            return '';
        }

        require_once dirname(__FILE__) . '/classes/Sj4webVendorPoService.php';
        $service = new Sj4webVendorPoService($this);
        $supplierSummary = $service->buildSupplierSummaryForCart($cart, (int) $this->context->shop->id);

        $minShipments = (int) Configuration::get('SJ4WEB_VENDORPO_MIN_SHIPMENTS', 2);

        if (empty($supplierSummary) || $supplierSummary['package_count'] < $minShipments) {
            return '';
        }

        $this->context->smarty->assign([
            'supplier_summary' => $supplierSummary,
            'display_type' => $type,
        ]);

        return $this->display(__FILE__, 'views/templates/front/checkout_supplier_summary.tpl');
    }

    /**
     * Register CSS and JS assets for front-office display
     */
    protected function registerFrontAssets()
    {
        // Register CSS
        $this->context->controller->registerStylesheet(
            'module-sj4web-vendorpo-supplier-summary',
            'modules/' . $this->name . '/views/css/supplier_summary.css',
            [
                'media' => 'all',
                'priority' => 200,
            ]
        );

        // Register JS
        $this->context->controller->registerJavascript(
            'module-sj4web-vendorpo-supplier-summary',
            'modules/' . $this->name . '/views/js/supplier_summary.js',
            [
                'position' => 'bottom',
                'priority' => 200,
            ]
        );
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitSj4webVendorpoConfig')) {
            Configuration::updateValue('SJ4WEB_VENDORPO_MULTI_CARRIER', (bool) Tools::getValue('SJ4WEB_VENDORPO_MULTI_CARRIER'));
            Configuration::updateValue('SJ4WEB_VENDORPO_MIN_SHIPMENTS', (int) Tools::getValue('SJ4WEB_VENDORPO_MIN_SHIPMENTS'));

            $output .= $this->displayConfirmation($this->trans('Settings updated', [], 'Modules.Sj4webvendorpo.Admin'));
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Multi-carrier settings', [], 'Modules.Sj4webvendorpo.Admin'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Enable multi-carrier', [], 'Modules.Sj4webvendorpo.Admin'),
                        'name' => 'SJ4WEB_VENDORPO_MULTI_CARRIER',
                        'desc' => $this->trans('Show multiple shipments based on suppliers', [], 'Modules.Sj4webvendorpo.Admin'),
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->trans('Yes', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->trans('No', [], 'Admin.Global'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Minimum shipments to display', [], 'Modules.Sj4webvendorpo.Admin'),
                        'name' => 'SJ4WEB_VENDORPO_MIN_SHIPMENTS',
                        'desc' => $this->trans('Display shipment info only if number of shipments is greater than this value', [], 'Modules.Sj4webvendorpo.Admin'),
                        'class' => 'fixed-width-sm',
                        'suffix' => $this->trans('shipments', [], 'Modules.Sj4webvendorpo.Admin'),
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
                'buttons' => [
                    [
                        'type' => 'link',
                        'title' => $this->trans('Manage Supplier Settings', [], 'Modules.Sj4webvendorpo.Admin'),
                        'icon' => 'process-icon-cogs',
                        'href' => $this->context->link->getAdminLink('AdminSj4webSupplierSettings'),
                        'class' => 'btn btn-default pull-right',
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitSj4webVendorpoConfig';
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        $helper->fields_value['SJ4WEB_VENDORPO_MULTI_CARRIER'] = Configuration::get('SJ4WEB_VENDORPO_MULTI_CARRIER');
        $helper->fields_value['SJ4WEB_VENDORPO_MIN_SHIPMENTS'] = Configuration::get('SJ4WEB_VENDORPO_MIN_SHIPMENTS', 2);

        return $helper->generateForm([$fieldsForm]);
    }
}