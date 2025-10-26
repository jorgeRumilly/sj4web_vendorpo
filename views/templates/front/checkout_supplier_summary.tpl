{*
* Checkout supplier summary template
* Displays shipping information based on display mode
*
* Variables:
* - $supplier_summary (array) - Supplier summary data
* - $display_type (string) - 'summary' or 'carrier'
*}

{if $supplier_summary && $supplier_summary.package_count > 0}
    {if $display_type == 'summary'}
        {* MODE SUMMARY - displayCheckoutSummaryTop *}
        <div class="sj4web-supplier-summary sj4web-summary-simple">
            <div class="shipping-info-simple">
                <h4>
                    <i class="material-icons">local_shipping</i>
                    {l s='Shipping Information' d='Modules.Sj4webvendorpo.Shop'}
                </h4>
                <p>
                    {l s='This order will be shipped in %d separate shipments.' sprintf=[$supplier_summary.package_count] d='Modules.Sj4webvendorpo.Shop'}
                    <span class="text-muted">({l s='detailed information available when choosing delivery method' d='Modules.Sj4webvendorpo.Shop'})</span>
                </p>
            </div>
        </div>

    {elseif $display_type == 'carrier'}
        {* MODE CARRIER - displayBeforeCarrier with toggle *}
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
{/if}