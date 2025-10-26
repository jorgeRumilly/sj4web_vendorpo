<?php

require_once dirname(__FILE__) . '/../../classes/Sj4webSupplierSettings.php';
require_once dirname(__FILE__) . '/../../classes/Sj4webSupplierPresenter.php';

class AdminSj4webSupplierSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'sj4web_supplier_settings';
        $this->className = 'Sj4webSupplierSettings';
        $this->identifier = 'id_sj4web_supplier_settings';

        $this->bootstrap = true;
        $this->context = Context::getContext();

        parent::__construct();

        $this->meta_title = $this->trans('Vendor POs & Supplier Settings', [], 'Modules.Sj4webvendorpo.Admin');

        $this->fields_list = [
            'id_supplier' => [
                'title' => $this->trans('Supplier ID', [], 'Modules.Sj4webvendorpo.Admin'),
                'width' => 50,
                'type' => 'text',
            ],
            'name' => [
                'title' => $this->trans('Supplier Name', [], 'Modules.Sj4webvendorpo.Admin'),
                'width' => 'auto',
                'type' => 'text',
            ],
            'configured' => [
                'title' => $this->trans('Status', [], 'Modules.Sj4webvendorpo.Admin'),
                'width' => 100,
                'type' => 'bool',
                'align' => 'center',
                'callback' => 'displayConfiguredStatus',
            ],
            'company_name' => [
                'title' => $this->trans('Company', [], 'Modules.Sj4webvendorpo.Admin'),
                'width' => 'auto',
                'type' => 'text',
            ],
            'lead_time' => [
                'title' => $this->trans('Lead Time', [], 'Modules.Sj4webvendorpo.Admin'),
                'width' => 150,
                'type' => 'text',
            ],
        ];

        $this->actions = ['edit', 'delete'];

        $this->fields_form = [
            'legend' => [
                'title' => $this->trans('Supplier Settings', [], 'Modules.Sj4webvendorpo.Admin'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->trans('Supplier', [], 'Modules.Sj4webvendorpo.Admin'),
                    'name' => 'id_supplier',
                    'required' => true,
                    'options' => [
                        'query' => [],
                        'id' => 'id_supplier',
                        'name' => 'name'
                    ],
                    'col' => 6,
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Company Name', [], 'Modules.Sj4webvendorpo.Admin'),
                    'name' => 'company_name',
                    'required' => true,
                    'col' => 6,
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Contact Name', [], 'Modules.Sj4webvendorpo.Admin'),
                    'name' => 'contact_name',
                    'col' => 6,
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Address Line 1', [], 'Modules.Sj4webvendorpo.Admin'),
                    'name' => 'address1',
                    'required' => true,
                    'col' => 6,
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Address Line 2', [], 'Modules.Sj4webvendorpo.Admin'),
                    'name' => 'address2',
                    'col' => 6,
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Postal Code', [], 'Modules.Sj4webvendorpo.Admin'),
                    'name' => 'postcode',
                    'required' => true,
                    'col' => 3,
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('City', [], 'Modules.Sj4webvendorpo.Admin'),
                    'name' => 'city',
                    'required' => true,
                    'col' => 6,
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Phone', [], 'Modules.Sj4webvendorpo.Admin'),
                    'name' => 'phone',
                    'col' => 4,
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Lead Time', [], 'Modules.Sj4webvendorpo.Admin'),
                    'name' => 'lead_time',
                    'desc' => $this->trans('e.g. 48-72h, 2-3 weeks', [], 'Modules.Sj4webvendorpo.Admin'),
                    'col' => 4,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Display customer Email on pdf', [], 'Modules.Sj4webvendorpo.Admin'),
                    'name' => 'show_email_on_pdf',
                    'desc' => $this->trans('Add customer email information in PO pdf', [], 'Modules.Sj4webvendorpo.Admin'),
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
                    'type' => 'switch',
                    'label' => $this->trans('Display customer phone on pdf', [], 'Modules.Sj4webvendorpo.Admin'),
                    'name' => 'show_phone_on_pdf',
                    'desc' => $this->trans('Add customer phone information in PO pdf', [], 'Modules.Sj4webvendorpo.Admin'),
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
            ],
            'submit' => [
                'title' => $this->trans('Save', [], 'Modules.Sj4webvendorpo.Admin'),
            ],
        ];
    }

    public function renderList()
    {
        $suppliers = Sj4webSupplierSettings::getAllByShop($this->context->shop->id);

        // Ajouter le statut "configured" et formatter les données
        foreach ($suppliers as &$supplier) {
            $supplier['configured'] = true; // Toujours true car INNER JOIN
            $supplier['company_name'] = $supplier['settings_company_name'];
        }

        $this->_list = $suppliers;

        return parent::renderList();
    }

    public function renderForm()
    {
        $id_settings = (int) Tools::getValue('id_sj4web_supplier_settings');

        if ($id_settings) {
            // Mode EDIT - Afficher le nom du supplier en disabled
            $settings = new Sj4webSupplierSettings($id_settings);
            $supplier = new Supplier($settings->id_supplier);

            $this->fields_form['input'][0] = [
                'type' => 'text',
                'label' => $this->trans('Supplier', [], 'Modules.Sj4webvendorpo.Admin'),
                'name' => 'supplier_name_display',
                'value' => $supplier->name,
                'disabled' => true,
                'col' => 6,
            ];

            // Ajouter champ hidden pour id_supplier
            array_unshift($this->fields_form['input'], [
                'type' => 'hidden',
                'name' => 'id_supplier',
            ]);
        } else {
            // Mode ADD - Dropdown avec suppliers disponibles
            $availableSuppliers = Sj4webSupplierSettings::getAvailableSuppliers($this->context->shop->id);
            $this->fields_form['input'][0]['options']['query'] = $availableSuppliers;
        }

        return parent::renderForm();
    }

    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        return false;
    }

    public function displayConfiguredStatus($value, $row)
    {
        return $row['configured']
            ? '<span class="badge badge-success">' . $this->trans('Configured', [], 'Modules.Sj4webvendorpo.Admin') . '</span>'
            : '<span class="badge badge-danger">' . $this->trans('Not Configured', [], 'Modules.Sj4webvendorpo.Admin') . '</span>';
    }

    public function processAdd()
    {
        $id_supplier = (int) Tools::getValue('id_supplier');
        if (!$id_supplier) {
            $this->errors[] = $this->trans('Supplier is required', [], 'Modules.Sj4webvendorpo.Admin');
            return false;
        }

        // Vérifier si supplier déjà configuré
        $existing = Sj4webSupplierSettings::getBySupplier($id_supplier, $this->context->shop->id);
        if ($existing) {
            $this->errors[] = $this->trans('Settings already exist for this supplier', [], 'Modules.Sj4webvendorpo.Admin');
            return false;
        }

        $_POST['id_shop'] = $this->context->shop->id;

        return parent::processAdd();
    }

    public function postProcess()
    {
        if (Tools::getValue('id_supplier') && !Tools::getValue('id_sj4web_supplier_settings')) {
            $existing = Sj4webSupplierSettings::getBySupplier(
                (int) Tools::getValue('id_supplier'),
                $this->context->shop->id
            );
            if ($existing) {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminSj4webSupplierSettings') .
                    '&id_sj4web_supplier_settings=' . $existing->id . '&updatesj4web_supplier_settings');
            } else {
                $this->action = 'add';
            }
        }

        return parent::postProcess();
    }
}