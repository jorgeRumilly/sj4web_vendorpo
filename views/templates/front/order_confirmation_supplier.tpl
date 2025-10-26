{*
* Order confirmation supplier summary
* Displays complete shipping information on order confirmation page
*
* Variables:
* - $supplier_summary (array) - Supplier summary data
*}

{if $supplier_summary && $supplier_summary.package_count > 0}
    <div class="sj4web-supplier-summary sj4web-summary-carrier">
        <div class="shipping-info">
            <h4>
                <i class="material-icons">local_shipping</i>
                {l s='Shipping Information' d='Modules.Sj4webvendorpo.Shop'}
            </h4>

            <p class="package-intro">
                {l s='This order will be shipped in %d separate shipments.' sprintf=[$supplier_summary.package_count] d='Modules.Sj4webvendorpo.Shop'}
                <a href="#" class="toggle-shipments-detail" data-action="show">
                    <i class="material-icons">expand_more</i>
                    <span class="shippingexpand_text"
                          data-show-text="{l s='Show details' d='Modules.Sj4webvendorpo.Shop'}"
                          data-hide-text="{l s='Hide details' d='Modules.Sj4webvendorpo.Shop'}">{l s='Show details' d='Modules.Sj4webvendorpo.Shop'}</span>
                </a>
            </p>

            {include file='module:sj4web_vendorpo/views/templates/front/_partials/packages_list.tpl'
                     packages=$supplier_summary.packages
                     collapsible=true}
        </div>
    </div>
{/if}