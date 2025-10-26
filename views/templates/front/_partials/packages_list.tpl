{*
* Partial template: Packages list
* Shared component for displaying shipment packages
*
* Required variables:
* - $packages (array) - Array of package data
* - $collapsible (bool) - Whether the list is collapsible (default: false)
*}

{if $packages}
    <div class="packages-details {if isset($collapsible) && $collapsible}collapsible{/if}">
        {foreach $packages as $package}
            <div class="package-item">
                <div class="package-header">
                    <strong>
                        <i class="material-icons">play_arrow</i>
                        {l s='Shipment' d='Modules.Sj4webvendorpo.Shop'} {$package.number}
                    </strong>
                    <span class="package-delay">
                        <i class="material-icons">schedule</i>
                        {$package.lead_time|escape:'html':'UTF-8'}
                    </span>
                </div>
                <div class="package-content">
                    <ul class="package-items">
                        {foreach $package.items as $item}
                            <li>
                                <span class="item-qty">{$item.qty}x</span>
                                <span class="item-name">{$item.name|escape:'html':'UTF-8'}</span>
                                {if $item.reference}
                                    <span class="item-ref">(Ref: {$item.reference|escape:'html':'UTF-8'})</span>
                                {/if}
                            </li>
                        {/foreach}
                    </ul>
                </div>
            </div>
        {/foreach}
    </div>
{/if}