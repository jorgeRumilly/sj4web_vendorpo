<?php

class Sj4webSupplierPresenter
{
    public static function prepareSupplierListForTemplate($suppliers)
    {
        $prepared = [];

        foreach ($suppliers as $supplier) {
            $hasSettings = !empty($supplier['id_sj4web_supplier_settings']);

            $prepared[] = [
                'id_supplier' => (int) $supplier['id_supplier'],
                'name' => $supplier['name'],
                'active' => (bool) $supplier['active'],
                'configured' => $hasSettings,
                'company_name' => $hasSettings ? $supplier['settings_company_name'] : '',
                'lead_time' => $hasSettings ? $supplier['lead_time'] : '',
                'show_email_on_pdf' => $hasSettings ? $supplier['show_email_on_pdf'] : 0,
                'show_phone_on_pdf' => $hasSettings ? $supplier['show_phone_on_pdf'] : 0,
                'date_configured' => $hasSettings ? $supplier['settings_date_add'] : '',
                'edit_link' => Context::getContext()->link->getAdminLink('AdminSj4webSupplierSettings') .
                             '&id_sj4web_supplier_settings=' . ($hasSettings ? $supplier['id_sj4web_supplier_settings'] : '') .
                             '&id_supplier=' . $supplier['id_supplier'] .
                             '&' . ($hasSettings ? 'update' : 'add') . 'sj4web_supplier_settings',
            ];
        }

        return $prepared;
    }

    public static function prepareSupplierSummaryForCheckout($supplierSummary)
    {
        if (empty($supplierSummary) || $supplierSummary['package_count'] === 0) {
            return null;
        }

        return [
            'package_count' => $supplierSummary['package_count'],
            'package_label' => $supplierSummary['package_count'] > 1 ? 'packages' : 'package',
            'suppliers_with_lead_time' => $supplierSummary['suppliers'],
        ];
    }

    public static function prepareOrderSupplierGroupsForAdmin($supplierGroups)
    {
        $prepared = [];

        foreach ($supplierGroups as $supplierId => $supplierData) {
            $prepared[] = [
                'id_supplier' => $supplierId,
                'supplier_name' => $supplierData['supplier_name'],
                'item_count' => count($supplierData['items']),
                'total_qty' => $supplierData['total_qty'],
                'has_return_address' => false, // Will be set in template
                'po_link' => Context::getContext()->link->getAdminLink('AdminSj4webVendorPo') .
                           '&action=po&id_supplier=' . $supplierId,
            ];
        }

        return $prepared;
    }
}