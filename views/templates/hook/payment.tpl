{*
* Copyright (c) 2008 PayFast (Pty) Ltd
* You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
* Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
*
*}

<p class="payment_module">
<form id='payfastPayNow' action="{$data.payfast_url|escape:'htmlall':'UTF-8'}" method="post">
    <p class="payment_module">
    {foreach $data.info as $k=>$v}
        <input type="hidden" name="{$k|escape:'UTF-8'}" value="{$v|escape:'htmlall':'UTF-8'}" />
    {/foreach}
     <a href='#' onclick='document.getElementById("payfastPayNow").submit();return false;'>{$data.payfast_paynow_text|escape:'htmlall':'UTF-8'}
      {if $data.payfast_paynow_logo=='on'} <img align='{$data.payfast_paynow_align|escape:'htmlall':'UTF-8'}' alt='Pay Now With PayFast' title='Pay Now With PayFast' src="{$base_dir|escape:'htmlall':'UTF-8'}modules/payfast/views/img/logo.png">{/if}</a>
       <noscript><input type="image" src="{$base_dir|escape:'htmlall':'UTF-8'}modules/payfast/views/img/logo.png"></noscript>
    </p>
</form>
</p>