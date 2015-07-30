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

<p>{l s='Your order on' mod='payfast'} <span class="bold">{$shop_name|escape:'htmlall':'UTF-8'}</span> {l s='is complete.' mod='payfast'}
    <br /><br />
    {l s='You chose the PayFast method.' mod='payfast'}
    <br /><br /><span class="bold">{l s='Your order will be sent shortly.' mod='payfast'}</span>
    <br /><br />{l s='For any questions or for further information, please contact our' mod='payfast'} <a href="{$link->getPageLink('contact-form.php', true)|escape:'htmlall':'UTF-8'}">{l s='customer support' mod='payfast'}</a>.
</p>