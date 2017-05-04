<?php
/**
 * payfast.php
 *
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 *
 * @author    Ron Darby<ron.darby@payfast.co.za>
 * @version   1.0.0
 * @date      02/09/2015
 *
 * @link      http://www.payfast.co.za/help/prestashop
 */

define('PF_SOFTWARE_NAME', 'PrestaShop-Cloud');
define('PF_SOFTWARE_VER', Configuration::get('PS_INSTALL_VERSION'));
define('PF_MODULE_NAME', 'PayFast-Prestashop-Cloud');
define('PF_MODULE_VER', '1.0.1');
define('PF_DEBUG', (Configuration::get('PAYFAST_LOGS') ? true : false ));

if (in_array('curl', get_loaded_extensions())) {
    define('PF_CURL', '');

}

define('PF_USER_AGENT', PF_SOFTWARE_NAME . '/' . PF_SOFTWARE_VER . ' - ' . PF_MODULE_NAME . '/' . PF_MODULE_VER);
define('PF_TIMEOUT', 15);
define('PF_EPSILON', 0.01);
define('PF_ERR_AMOUNT_MISMATCH', 'Amount mismatch');
define('PF_ERR_BAD_ACCESS', 'Bad access of page');
define('PF_ERR_BAD_SOURCE_IP', 'Bad source IP address');
define('PF_ERR_CONNECT_FAILED', 'Failed to connect to PayFast');
define('PF_ERR_INVALID_SIGNATURE', 'Security signature mismatch');
define('PF_ERR_MERCHANT_ID_MISMATCH', 'Merchant ID mismatch');
define('PF_ERR_NO_SESSION', 'No saved session found for ITN transaction');
define('PF_ERR_ORDER_ID_MISSING_URL', 'Order ID not present in URL');
define('PF_ERR_ORDER_ID_MISMATCH', 'Order ID mismatch');
define('PF_ERR_ORDER_INVALID', 'This order ID is invalid');
define('PF_ERR_ORDER_NUMBER_MISMATCH', 'Order Number mismatch');
define('PF_ERR_ORDER_PROCESSED', 'This order has already been processed');
define('PF_ERR_PDT_FAIL', 'PDT query failed');
define('PF_ERR_PDT_TOKEN_MISSING', 'PDT token not present in URL');
define('PF_ERR_SESSIONID_MISMATCH', 'Session ID mismatch');
define('PF_ERR_UNKNOWN', 'Unkown error occurred');
define('PF_MSG_OK', 'Payment was successful');
define('PF_MSG_FAILED', 'Payment has failed');
define('PF_MSG_PENDING', 'The payment is pending.Please note, you will receive another Instant Transaction Notification
 when the payment status changes to "Completed", or "Failed"');
