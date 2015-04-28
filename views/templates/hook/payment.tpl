{*
* 2011 - 2015 PayFast
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to support@payfast.co.za so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PayFast <support@payfast.co.za>
*  @copyright  2011 - 2015 PayFast
*  @license http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*}

<p class="payment_module">
<form id='payfastPayNow' action="{$data.payfast_url|escape:'htmlall':'UTF-8'}" method="post">
    <p class="payment_module">
    {foreach $data.info as $k=>$v}
        <input type="hidden" name="{$k|escape:'UTF-8'}" value="{$v|escape:'htmlall':'UTF-8'}" />
    {/foreach}
     <a href='#' onclick='document.getElementById("payfastPayNow").submit();return false;'>{$data.payfast_paynow_text|escape:'htmlall':'UTF-8'}
      {if $data.payfast_paynow_logo=='on'} <img align='{$data.payfast_paynow_align}' alt='Pay Now With PayFast' title='Pay Now With PayFast' src="{$base_dir}modules/payfast/views/img/logo.png">{/if}</a>
       <noscript><input type="image" src="{$base_dir|escape:'htmlall':'UTF-8'}modules/payfast/views/img/logo.png"></noscript>
    </p>
</form>
</p>