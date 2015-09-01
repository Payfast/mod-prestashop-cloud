<?php
/**
 * validtion.php
 *
 * Copyright (c) 2015 PayFast (Pty) Ltd
 *
 * LICENSE:
 *
 * This payment module is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation; either version 3 of the License, or (at
 * your option) any later version.
 *
 * This payment module is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.See the GNU Lesser General Public
 * License for more details.
 *
 * @author    Ron Darby<ron.darby@payfast.co.za>
 * @version   1.0.0
 * @date      20/03/2015
 *
 * @copyright 2015 PayFast (Pty) Ltd
 * @license   http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link      http://www.payfast.co.za/help/prestashop
 */

include(dirname(__FILE__) . '/../../config/config.inc.php' );
include(dirname(__FILE__) . '/payfast.php' );

/**
 * Check if this is an ITN request
 * Has to be done like this (as opposed to "exit" as processing needs
 * to continue after this check.
 */
if (Tools::getValue('itn_request') == 'true') {
    /* Variable Initialization */
    $pf_error = false;
    $pf_err_msg = '';
    $pf_done = false;
    $pf_data = array();
    $pf_host = ((Configuration::get('PAYFAST_MODE') == 'live' ) ? 'www' : 'sandbox' ) . '.payfast.co.za';
    $pf_order_id = '';
    $pf_param_string = '';
    $payfast = new PayFast();

    PayFast::pflog('PayFast ITN call received');
    /* Notify PayFast that information has been received */
    if (!$pf_error && !$pf_done) {
        header('HTTP/1.0 200 OK');
        flush();

    }

    /* Get data sent by PayFast */
    if (!$pf_error && !$pf_done) {
        PayFast::pflog('Get posted data');
        /* Posted variables from ITN */
        $pf_data = PayFast::pfGetData();
        PayFast::pflog('PayFast Data: ' . print_r($pf_data, true));
        if ($pf_data === false) {
            $pf_error = true;
            $pf_err_msg = PF_ERR_BAD_ACCESS;
        }
    }

    /* Verify security signature */
    if (!$pf_error && !$pf_done) {
        PayFast::pflog('Verify security signature');

        $pass_phrase = Configuration::get('PAYFAST_PASSPHRASE');
        $pf_pass_phrase = empty($pass_phrase ) ? null : $pass_phrase;
        /* If signature different, log for debugging */
        if (!PayFast::pfValidSignature($pf_data, $pf_param_string, $pf_pass_phrase)) {
            $pf_error = true;
            $pf_err_msg = PF_ERR_INVALID_SIGNATURE;
        }
    }

    /* Verify source IP (If not in debug mode) */
    if (!$pf_error && !$pf_done && !PF_DEBUG) {
        PayFast::pflog('Verify source IP');
        if (!PayFast::pfValidIP($_SERVER[ 'REMOTE_ADDR' ])) {
            $pf_error = true;
            $pf_err_msg = PF_ERR_BAD_SOURCE_IP;
        }
    }

    /* Get internal cart */
    if (!$pf_error && !$pf_done) {
        /* Get order data */
        $cart = new Cart((int) $pf_data[ 'm_payment_id' ]);

        PayFast::pflog("Purchase:\n" . print_r($cart, true));
    }

    /* Verify data received */
    if (!$pf_error) {
        PayFast::pflog('Verify data received');
        $pf_valid = PayFast::pfValidData($pf_host, $pf_param_string);
        if (!$pf_valid) {
            $pf_error = true;
            $pf_err_msg = PF_ERR_BAD_ACCESS;
        }
    }
    /* Check data against internal order */
    if (!$pf_error && !$pf_done) {
        /* PayFast::pflog('Check data against internal order' ); */
        $from_currency = new Currency(Currency::getIdByIsoCode('ZAR'));
        $to_currency = new Currency((int) $cart->id_currency);
        $total = Tools::convertPriceFull($pf_data[ 'amount_gross' ], $from_currency, $to_currency);
        /* Check order amount */
        if (strcasecmp($pf_data[ 'custom_str1' ], $cart->secure_key) != 0) {
            $pf_error = true;
            $pf_err_msg = PF_ERR_SESSIONID_MISMATCH;
        }
    }

    $vendor_name = Configuration::get('PS_SHOP_NAME');
    $vendor_url = Tools::getShopDomain(true, true);

    /* Check status and update order */
    if (!$pf_error && !$pf_done) {
        PayFast::pflog('Check status and update order');

        $sessionid = $pf_data[ 'custom_str1' ];
        $transaction_id = $pf_data[ 'pf_payment_id' ];
        if (empty(Context::getContext()->link )) {
            Context::getContext()->link = new Link();
        }

        switch ($pf_data[ 'payment_status' ]) {
            case 'COMPLETE':
                PayFast::pflog('- Complete');

                /* Update the purchase status */
                $payfast->validateOrder(
                    (int) $pf_data[ 'custom_int1' ],
                    _PS_OS_PAYMENT_,
                    (float) $total,
                    $payfast->displayName,
                    null,
                    array('transaction_id' => $transaction_id),
                    null,
                    false,
                    $pf_data[ 'custom_str1' ]
                );
                break;

            case 'FAILED':
                PayFast::pflog('- Failed');

                /* If payment fails, delete the purchase log */
                $payfast->validateOrder(
                    (int) $pf_data[ 'custom_int1' ],
                    _PS_OS_ERROR_,
                    (float) $total,
                    $payfast->displayName,
                    null,
                    array('transaction_id' => $transaction_id),
                    null,
                    false,
                    $pf_data[ 'custom_str1' ]
                );

                break;

            case 'PENDING':
                PayFast::pflog('- Pending');

                /* Need to wait for "Completed" before processing */
                break;

            default:
                /* If unknown status, do nothing (safest course of action) */
                break;
        }
    }

    /* If an error occurred */
    if ($pf_error) {
        PayFast::pflog('Error occurred: ' . $pf_err_msg);
    }

    /* Close log */
    PayFast::pflog('', true);
    exit();
}
