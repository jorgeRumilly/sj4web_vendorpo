{*
* Order block template for admin order page
*}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-truck"></i>
        {l s='Vendor Purchase Orders' d='Modules.Sj4webvendorpo.Admin'}
    </div>
    <div class="panel-body">
        {if $supplier_groups}
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{l s='Supplier' d='Modules.Sj4webvendorpo.Admin'}</th>
                            <th>{l s='Products' d='Modules.Sj4webvendorpo.Admin'}</th>
                            <th>{l s='Total Qty' d='Modules.Sj4webvendorpo.Admin'}</th>
                            <th>{l s='Status' d='Modules.Sj4webvendorpo.Admin'}</th>
                            <th>{l s='Actions' d='Modules.Sj4webvendorpo.Admin'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $supplier_groups as $supplier_id => $supplier_data}
                            <tr>
                                <td>
                                    <strong>{$supplier_data.supplier_name|escape:'html':'UTF-8'}</strong>
                                    <br><small>ID: {$supplier_id}</small>
                                </td>
                                <td>{count($supplier_data.items)} {l s='products' d='Modules.Sj4webvendorpo.Admin'}</td>
                                <td><span class="badge badge-info">{$supplier_data.total_qty}</span></td>
                                <td>
                                    {* Check if supplier has return address *}
                                    <span class="badge badge-success">{l s='Ready' d='Modules.Sj4webvendorpo.Admin'}</span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{$link->getAdminLink('AdminSj4webVendorPo')|escape:'html':'UTF-8'}&action=po&id_order={$order->id}&id_supplier={$supplier_id}&token={$admin_token}"
                                           class="btn btn-default btn-sm"
                                           target="_blank">
                                            <i class="icon-download"></i>
                                            {l s='Generate PO PDF' d='Modules.Sj4webvendorpo.Admin'}
                                        </a>
                                        <a href="{$link->getAdminLink('AdminSj4webSupplierSettings')|escape:'html':'UTF-8'}&id_supplier={$supplier_id}"
                                           class="btn btn-default btn-sm">
                                            <i class="icon-edit"></i>
                                            {l s='Edit Settings' d='Modules.Sj4webvendorpo.Admin'}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info">
                <i class="icon-info-circle"></i>
                {l s='Purchase order PDFs contain product references, EAN13, MPN, and quantities only (no prices).' d='Modules.Sj4webvendorpo.Admin'}
            </div>

        {else}
            <div class="alert alert-warning">
                <i class="icon-warning"></i>
                {l s='No physical products with suppliers found in this order.' d='Modules.Sj4webvendorpo.Admin'}
            </div>
        {/if}
    </div>
</div>