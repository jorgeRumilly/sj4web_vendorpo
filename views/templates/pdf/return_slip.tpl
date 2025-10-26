<style>
table { border-collapse: collapse; width: 100%; }
th { background: #f0f0f0; padding: 8px; text-align: left; border: 1px solid #ddd; }
td { padding: 5px; border-bottom: 1px solid #ddd; }
.header-section { margin-bottom: 20px; }
.logo { max-width: 150px; max-height: 75px; }
.company-info { float: left; width: 45%; }
.supplier-info { float: right; width: 45%; text-align: right; }
.clear { clear: both; }
.return-title { text-align: center; font-size: 18px; font-weight: bold; margin: 20px 0; }
.items-table { margin-top: 20px; }
.footer-info { margin-top: 30px; font-size: 10px; color: #666; }
.return-address { background: #f9f9f9; padding: 10px; border: 1px solid #ddd; margin: 10px 0; }
</style>

<div class="header-section">
    <div class="company-info">
        {if $shop_logo}
            <img src="{$shop_logo}" alt="{$shop_name}" class="logo" />
        {/if}
        <h3>{$shop_name|escape:'html':'UTF-8'}</h3>
        <div>{$shop_address|nl2br}</div>
    </div>

    <div class="supplier-info">
        <h4>{l s='Supplier' d='Modules.Sj4webvendorpo.Admin'}</h4>
        <strong>{$supplier.name|escape:'html':'UTF-8'}</strong>
    </div>

    <div class="clear"></div>
</div>

<div class="return-title">
    {l s='Return Slip' d='Modules.Sj4webvendorpo.Admin'} {$return_number|escape:'html':'UTF-8'}
</div>

<div style="margin-bottom: 20px;">
    <strong>{l s='Original Order' d='Modules.Sj4webvendorpo.Admin'}:</strong> {$order_ref|escape:'html':'UTF-8'}<br>
    <strong>{l s='Return ID' d='Modules.Sj4webvendorpo.Admin'}:</strong> {$return_ref|escape:'html':'UTF-8'}<br>
    <strong>{l s='Return Date' d='Modules.Sj4webvendorpo.Admin'}:</strong> {$return_date|escape:'html':'UTF-8'}
</div>

<div class="return-address">
    <h4>{l s='Return Address' d='Modules.Sj4webvendorpo.Admin'}</h4>
    {$supplier.return_address.company_name|escape:'html':'UTF-8'}<br>
    {if $supplier.return_address.contact_name}
        {$supplier.return_address.contact_name|escape:'html':'UTF-8'}<br>
    {/if}
    {$supplier.return_address.address1|escape:'html':'UTF-8'}<br>
    {if $supplier.return_address.address2}
        {$supplier.return_address.address2|escape:'html':'UTF-8'}<br>
    {/if}
    {$supplier.return_address.postcode|escape:'html':'UTF-8'} {$supplier.return_address.city|escape:'html':'UTF-8'}<br>
    {if $supplier.return_address.phone}
        {l s='Phone' d='Modules.Sj4webvendorpo.Admin'}: {$supplier.return_address.phone|escape:'html':'UTF-8'}<br>
    {/if}
</div>

<div class="items-table">
    <h4>{l s='Returned Items' d='Modules.Sj4webvendorpo.Admin'}</h4>
    <table>
        <thead>
            <tr>
                <th>{l s='Reference' d='Modules.Sj4webvendorpo.Admin'}</th>
                <th>{l s='EAN13' d='Modules.Sj4webvendorpo.Admin'}</th>
                <th>{l s='Product' d='Modules.Sj4webvendorpo.Admin'}</th>
                <th>{l s='MPN' d='Modules.Sj4webvendorpo.Admin'}</th>
                <th style="text-align: center;">{l s='Quantity' d='Modules.Sj4webvendorpo.Admin'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $items as $item}
                <tr>
                    <td>{$item.reference|escape:'html':'UTF-8'}</td>
                    <td>{$item.ean13|escape:'html':'UTF-8'}</td>
                    <td>{$item.name|escape:'html':'UTF-8'}</td>
                    <td>{$item.mpn|escape:'html':'UTF-8'}</td>
                    <td style="text-align: center;"><strong>{$item.qty}</strong></td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>

<div class="footer-info">
    <p>{l s='Please use this return slip when sending back the products to the supplier.' d='Modules.Sj4webvendorpo.Admin'}</p>
    <p>{l s='Generated automatically by' d='Modules.Sj4webvendorpo.Admin'} {$shop_name|escape:'html':'UTF-8'} - {date('d/m/Y H:i')}</p>
</div>