{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *}


{*<div class="po-title">*}
{*    {l s='Purchase Order' d='Modules.Sj4webvendorpo.Admin'} {$po_number|escape:'html':'UTF-8'}*}
{*</div>*}

{*<div style="margin-bottom: 20px;">*}
{*    <strong>{l s='Order Reference' d='Modules.Sj4webvendorpo.Admin'}:</strong> {$order_ref|escape:'html':'UTF-8'}<br>*}
{*    <strong>{l s='Order Date' d='Modules.Sj4webvendorpo.Admin'}:</strong> {$order_date|escape:'html':'UTF-8'}<br>*}
{*    <strong>{l s='PO Date' d='Modules.Sj4webvendorpo.Admin'}:</strong> {date('d/m/Y H:i')}*}
{*</div>*}

<table class="product" width="100%" cellpadding="4" cellspacing="0">

    {assign var='widthColProduct' value=$layout.product.width}

    <thead>
    <tr>
        <th class="product header small"
            width="{$layout.reference.width}%">{l s='Reference' d='Modules.Sj4webvendorpo.Admin'}</th>
        <th class="product header small"
            width="{$layout.ean13.width}%">{l s='EAN13' d='Modules.Sj4webvendorpo.Admin'}</th>
        <th class="product header small"
            width="{$widthColProduct}%">{l s='Product' d='Modules.Sj4webvendorpo.Admin'}</th>
        <th class="product header small"
            width="{$layout.quantity.width}%">{l s='Quantity' d='Modules.Sj4webvendorpo.Admin'}</th>
    </tr>
    </thead>

    <tbody>
    {foreach $items as $item}
        {cycle values=["color_line_even", "color_line_odd"] assign=bgcolor_class}
        <tr class="product {$bgcolor_class}">
            <td class="product center">{$item.reference|escape:'html':'UTF-8'}</td>
            <td class="product center">{$item.ean13|escape:'html':'UTF-8'}</td>
            <td class="product left">{$item.name|escape:'html':'UTF-8'}</td>
            <td style="text-align: center;" class="product center"><strong>{$item.qty}</strong></td>
        </tr>
    {/foreach}
    </tbody>

</table>
