<?php
/**
 * payfast.php
 *
 * Copyright (c) 2011 PayFast (Pty) Ltd
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
 * @version    2.1.0
 * @date       12/12/2013
 * 
 * @copyright 2013 PayFast (Pty) Ltd
 * @license   http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://www.payfast.co.za/help/prestashop
 */

if (!defined('_PS_VERSION_'))
	exit;

define( 'PF_SOFTWARE_NAME', 'PrestaShop' );
define( 'PF_SOFTWARE_VER', Configuration::get('PS_INSTALL_VERSION') );
define( 'PF_MODULE_NAME', 'PayFast-Prestashop' );
define( 'PF_MODULE_VER', '2.1.1' );
define( 'PF_DEBUG', ( Configuration::get('PAYFAST_LOGS')  ? true : false ) );

$pfFeatures = 'PHP '.phpversion().';';


if (in_array( 'curl', get_loaded_extensions() ))
{
	define( 'PF_CURL', '' );
	$pfVersion = curl_version();
	$pfFeatures .= ' curl '.$pfVersion['version'].';';
}
else
	$pfFeatures .= ' nocurl;';


define( 'PF_USER_AGENT', PF_SOFTWARE_NAME.'/'.PF_SOFTWARE_VER.' ('.trim( $pfFeatures ).') '.PF_MODULE_NAME.'/'.PF_MODULE_VER );
define( 'PF_TIMEOUT', 15 );
define( 'PF_EPSILON', 0.01 );
define( 'PF_ERR_AMOUNT_MISMATCH', 'Amount mismatch' );
define( 'PF_ERR_BAD_ACCESS', 'Bad access of page' );
define( 'PF_ERR_BAD_SOURCE_IP', 'Bad source IP address' );
define( 'PF_ERR_CONNECT_FAILED', 'Failed to connect to PayFast' );
define( 'PF_ERR_INVALID_SIGNATURE', 'Security signature mismatch' );
define( 'PF_ERR_MERCHANT_ID_MISMATCH', 'Merchant ID mismatch' );
define( 'PF_ERR_NO_SESSION', 'No saved session found for ITN transaction' );
define( 'PF_ERR_ORDER_ID_MISSING_URL', 'Order ID not present in URL' );
define( 'PF_ERR_ORDER_ID_MISMATCH', 'Order ID mismatch' );
define( 'PF_ERR_ORDER_INVALID', 'This order ID is invalid' );
define( 'PF_ERR_ORDER_NUMBER_MISMATCH', 'Order Number mismatch' );
define( 'PF_ERR_ORDER_PROCESSED', 'This order has already been processed' );
define( 'PF_ERR_PDT_FAIL', 'PDT query failed' );
define( 'PF_ERR_PDT_TOKEN_MISSING', 'PDT token not present in URL' );
define( 'PF_ERR_SESSIONID_MISMATCH', 'Session ID mismatch' );
define( 'PF_ERR_UNKNOWN', 'Unkown error occurred' );
define( 'PF_MSG_OK', 'Payment was successful' );
define( 'PF_MSG_FAILED', 'Payment has failed' );
define( 'PF_MSG_PENDING',
	'The payment is pending.Please note, you will receive another Instant'.
	' Transaction Notification when the payment status changes to'.
	' "Completed", or "Failed"' );

class PayFast extends PaymentModule
{
	const LEFT_COLUMN = 0;
	const RIGHT_COLUMN = 1;
	const FOOTER = 2;
	const DISABLE = -1;
	const SANDBOX_MERCHANT_KEY = '46f0cd694581a';
	const SANDBOX_MERCHANT_ID = '10000100';  
	public function __construct()
	{
		$this->name = 'payfast';
		$this->tab = 'payments_gateways';
		$this->version = '2.1.1';  
		$this->currencies = true;
		$this->currencies_mode = 'radio';        
	parent::__construct();              
		$this->author  = 'PayFast';
		$this->page = basename(__FILE__, '.php');

		$this->displayName = $this->l('PayFast');
		$this->description = $this->l('Accept payments by credit card, EFT and cash from both local and international buyers, 
			quickly and securely with PayFast.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');        

		/* For 1.4.3 and less compatibility */
		$update_config = array('PS_OS_CHEQUE' => 1, 'PS_OS_PAYMENT' => 2, 'PS_OS_PREPARATION' => 3, 'PS_OS_SHIPPING' => 4, 'PS_OS_DELIVERED' => 5, 
			'PS_OS_CANCELED' => 6, 'PS_OS_REFUND' => 7, 'PS_OS_ERROR' => 8, 'PS_OS_OUTOFSTOCK' => 9, 'PS_OS_BANKWIRE' => 10, 'PS_OS_PAYPAL' => 11, 
			'PS_OS_WS_PAYMENT' => 12);
		foreach ($update_config as $u => $v)
			if (!Configuration::get($u) || (int)Configuration::get($u) < 1)
			{
				if (defined('_'.$u.'_') && (int)constant('_'.$u.'_') > 0)
					Configuration::updateValue($u, constant('_'.$u.'_'));
				else
					Configuration::updateValue($u, $v);
			}

	}

	public function install()
	{
		unlink(dirname(__FILE__).'/../../cache/class_index.php');
		if (!parent::install()
			|| !$this->registerHook('payment') 
			|| !$this->registerHook('paymentReturn') 
			|| !Configuration::updateValue('PAYFAST_MERCHANT_ID', '') 
			|| !Configuration::updateValue('PAYFAST_MERCHANT_KEY', '') 
			|| !Configuration::updateValue('PAYFAST_LOGS', '1') 
			|| !Configuration::updateValue('PAYFAST_MODE', 'test')
			|| !Configuration::updateValue('PAYFAST_PAYNOW_TEXT', 'Pay Now With')
			|| !Configuration::updateValue('PAYFAST_PAYNOW_LOGO', 'on')  
			|| !Configuration::updateValue('PAYFAST_PAYNOW_ALIGN', 'right')
			|| !Configuration::updateValue('PAYFAST_PASSPHRASE', ''))
			return false;


		return true;
	}

	public function uninstall()
	{
		unlink(dirname(__FILE__).'/../../cache/class_index.php');
		return ( parent::uninstall() 
			&& Configuration::deleteByName('PAYFAST_MERCHANT_ID') 
			&& Configuration::deleteByName('PAYFAST_MERCHANT_KEY') 
			&& Configuration::deleteByName('PAYFAST_MODE') 
			&& Configuration::deleteByName('PAYFAST_LOGS')
			&& Configuration::deleteByName('PAYFAST_PAYNOW_TEXT') 
			&& Configuration::deleteByName('PAYFAST_PAYNOW_LOGO')            
			&& Configuration::deleteByName('PAYFAST_PAYNOW_ALIGN')
			&& Configuration::deleteByName('PAYFAST_PASSPHRASE')
			);

	}

	public function getContent()
	{
		$errors = array();
		$html = '<div class="bootstrap">';

		/* Update configuration variables */
		if (Tools::isSubmit( 'submitPayfast' ))
		{
			if ($paynow_text = Tools::getValue( 'payfast_paynow_text' ))
				Configuration::updateValue( 'PAYFAST_PAYNOW_TEXT', $paynow_text );

			if ($paynow_logo = Tools::getValue( 'payfast_paynow_logo' ))
				Configuration::updateValue( 'PAYFAST_PAYNOW_LOGO', $paynow_logo );
			
			if ($paynow_align = Tools::getValue( 'payfast_paynow_align' ))
				Configuration::updateValue( 'PAYFAST_PAYNOW_ALIGN', $paynow_align );
			
			if ($pass_phrase = Tools::getValue( 'payfast_passphrase' ))
				Configuration::updateValue( 'PAYFAST_PASSPHRASE', $pass_phrase );

			$mode = ( Tools::getValue( 'payfast_mode' ) == 'live' ? 'live' : 'test' );
			Configuration::updateValue('PAYFAST_MODE', $mode );
			if ($mode != 'test')
			{
				if (( $merchant_id = Tools::getValue( 'payfast_merchant_id' ) ) && preg_match('/[0-9]/', $merchant_id ))
					Configuration::updateValue( 'PAYFAST_MERCHANT_ID', $merchant_id );
				else
					$errors[] = '<div class="warning warn"><h3>'.$this->l( 'Merchant ID seems to be wrong' ).'</h3></div>';
				

				if (( $merchant_key = Tools::getValue( 'payfast_merchant_key' ) ) && preg_match('/[a-zA-Z0-9]/', $merchant_key ))
					Configuration::updateValue( 'PAYFAST_MERCHANT_KEY', $merchant_key );
				else
					$errors[] = '<div class="warning warn"><h3>'.$this->l( 'Merchant key seems to be wrong' ).'</h3></div>';

				if (!count( $errors ))
					Tools::redirectAdmin( AdminController::$currentIndex.'&configure=payfast&token='.Tools::getValue( 'token' ).'&conf=4' );
			}
			if (Tools::getValue( 'payfast_logs' ))
			
				Configuration::updateValue( 'PAYFAST_LOGS', 1 );
			
			else
			
				Configuration::updateValue( 'PAYFAST_LOGS', 0 );
			 
			foreach (array('displayLeftColumn', 'displayRightColumn', 'displayFooter') as $hook_name)
				if ($this->isRegisteredInHook($hook_name))
					$this->unregisterHook($hook_name);
			if (Tools::getValue('logo_position') == self::LEFT_COLUMN)
				$this->registerHook('displayLeftColumn');
			else if (Tools::getValue('logo_position') == self::RIGHT_COLUMN)
			$this->registerHook('displayRightColumn'); 
			else if (Tools::getValue('logo_position') == self::FOOTER)
			$this->registerHook('displayFooter'); 
			if (method_exists ('Tools', 'clearSmartyCache'))
			
				Tools::clearSmartyCache();                       
		}             

		/* Display errors */
		if (count($errors))
		{
			$html .= '<ul style="color: red; font-weight: bold; margin-bottom: 30px; width: 506px; background: #FFDFDF; border: 1px dashed #BBB;
			padding: 10px;">';
			foreach ($errors as $error)
				$html .= '<li>'.$error.'</li>';
			$html .= '</ul>';
		}


		$block_position_list = array(
			self::DISABLE => $this->l('Disable'),
			self::LEFT_COLUMN => $this->l('Left Column'),
			self::RIGHT_COLUMN => $this->l('Right Column'),
			self::FOOTER => $this->l('Footer'));

		if ($this->isRegisteredInHook('displayLeftColumn'))
		
			$current_logo_block_position = self::LEFT_COLUMN;
		
		elseif ($this->isRegisteredInHook('displayRightColumn'))
		
			$current_logo_block_position = self::RIGHT_COLUMN; 
		
		elseif ($this->isRegisteredInHook('displayFooter'))
		
			$current_logo_block_position = self::FOOTER;
		
		else
		
			$current_logo_block_position = -1;


	/* Display settings form */
		$html .= '<div class="row"><div class="col-md-6">
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		  <fieldset>
		  <legend><a href="https://www.payfast.co.za" target="_blank">
							<img src="'.__PS_BASE_URI__.'modules/payfast/views/img/payfast.png" alt="PayFast" boreder="0" /></a>'.$this->l('Settings').'
							</legend>
			<div class="row">
				<p>'.$this->l('Use the "Test" mode to test out the module then you can use the "Live" mode if no problems arise.
								Remember to insert your merchant key and ID for the live mode.').'</p>
				 <div class="col-md-4">
					<label>
					  '.$this->l('Mode').'
					</label>
					</div>
					<div class="col-md-8">
					  <select name="payfast_mode">
						<option value="live"'.(Configuration::get('PAYFAST_MODE') == 'live' ? ' selected="selected"' : '').'>'.$this->l('Live').'

						</option>
						<option value="test"'.(Configuration::get('PAYFAST_MODE') == 'test' ? ' selected="selected"' : '').'>'.$this->l('Test')
					   .'</option>
					  </select>
					</div>
				 </div>
			 <div class="row">
				<p>'.$this->l('You can find your ID and Key in your PayFast account > My Account > Integration.').'</p>
				<div class="col-md-4">
					<label>
					  '.$this->l('Merchant ID').'
					</label>
				</div>
				<div class="col-md-8">
				  <input type="text" name="payfast_merchant_id" value="'.Tools::getValue('payfast_merchant_id',
					Configuration::get('PAYFAST_MERCHANT_ID')).'" >
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<label>
					  '.$this->l('Merchant Key').'
					</label>
					</div>
				<div class="col-md-8">
					<input type="text" name="payfast_merchant_key" value="'.trim(Tools::getValue('payfast_merchant_key',
					Configuration::get('PAYFAST_MERCHANT_KEY'))).'" />
				</div>
			<div class="row">
			<p>'.$this->l('ONLY INSERT A VALUE INTO THE SECURE PASSPHRASE IF YOU HAVE SET THIS ON THE INTEGRATION PAGE OF THE LOGGED IN AREA OF THE
								PAYFAST WEBSITE!!!!!').'</p>
				<div class="col-md-4">'.
				'<label>
				  '.$this->l('Secure Passphrase').'
				</label>
			</div>
			<div class="col-md-8">
			  <input type="text" name="payfast_passphrase" value="'.trim(Tools::getValue('payfast_passphrase', 
				Configuration::get('PAYFAST_PASSPHRASE'))).'" />
				</div>
			</div>
			<div class="row">
			<p>'.$this->l('You can log the server-to-server communication.The log file for debugging can be found at ').' '
			   .__PS_BASE_URI__.'modules/payfast/payfast.log.'.$this->l('If activated, be sure to protect it by putting a.htaccess file in the
					same directory.If not, the file will be readable by everyone.').'</p>
				<div class="col-md-4">
					<label>
					  '.$this->l('Debug').'
					</label>
				</div>
				<div class="col-md-8">
				  <input type="checkbox" name="payfast_logs"'.(Tools::getValue('payfast_logs',
					Configuration::get('PAYFAST_LOGS')) ? ' checked="checked"' : '').' />
				</div>
			</div>
			<div class="row">
				<p>'.$this->l('During checkout the following is what the client gets to click on to pay with PayFast.').'</p>
				<div class="col-md-4">
					<label>&nbsp;</label>
				 </div>
				<div class="col-md-8">
				'.Configuration::get('PAYFAST_PAYNOW_TEXT');

		   if (Configuration::get('PAYFAST_PAYNOW_LOGO') == 'on')
			
				$html .= '<img align="'.Configuration::get('PAYFAST_PAYNOW_ALIGN').'" alt="Pay Now With PayFast" title="Pay Now With PayFast"
					src="'.__PS_BASE_URI__.'modules/payfast/views/img/logo.png">';
			$html .= '</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<label>
					'.$this->l('PayNow Text').'
					</label>
				</div>
				<div class="col-md-8">
					<input type="text" name="payfast_paynow_text" value="'.Configuration::get('PAYFAST_PAYNOW_TEXT').'">
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<label>
					'.$this->l('PayNow Logo').'
					</label>
				</div>
				<div class="col-md-8">
					<input type="radio" name="payfast_paynow_logo" value="off"
					'.( Configuration::get('PAYFAST_PAYNOW_LOGO') == 'off' ? ' checked="checked"' : '').'"> &nbsp; '.$this->l('None').'<br>
					<input type="radio" name="payfast_paynow_logo" value="on"
					'.( Configuration::get('PAYFAST_PAYNOW_LOGO') == 'on' ? ' checked="checked"' : '').'
					"> &nbsp; <img src="'.__PS_BASE_URI__.'modules/payfast/views/img/logo.png">
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<label>
					'.$this->l('PayNow Logo Align').'
					</label>
				</div>
				<div class="col-md-8">
					<input type="radio" name="payfast_paynow_align" value="left"
				'.( Configuration::get('PAYFAST_PAYNOW_ALIGN') == 'left' ? ' checked="checked"' : '').'"> &nbsp; '.$this->l('Left').'<br>
					<input type="radio" name="payfast_paynow_align" value="right"
				'.( Configuration::get('PAYFAST_PAYNOW_ALIGN') == 'right' ? ' checked="checked"' : '').'"> &nbsp; '.$this->l('Right').'
				</div>
			</div>
			<div class="row">
				<p>'.$this->l('Where would you like the the Secure Payments made with PayFast image to appear on your website?').'</p>
			   <div class="col-md-4">
					 <label>
								'.$this->l('Select the image position').'
								<label>
			   </div>
			   <div class="col-md-8">
							  <select class="form-control" name="logo_position">';
								foreach ($block_position_list as $position => $translation)
								{
									$selected = ($current_logo_block_position == $position) ? 'selected="selected"' : '';
									$html .= '<option value="'.$position.'" '.$selected.'>'.$translation.'</option>';
								}
						$html .= '</select></div>
			</div>

			<div style="float:right;"><input type="submit" name="submitPayfast" class="button" value="'.$this->l('   Save   ').'" />
			</div><div class="clear"></div>
		  </fieldset>
		</form></div><div class="col-md-6">
		<fieldset>
		  <legend><img src="../img/admin/warning.gif" />'.$this->l('Information').'</legend>
		  <p>- '.$this->l('In order to use your PayFast module, you must insert your PayFast Merchant ID and Merchant Key above.').'</p>
		  <p>- '.$this->l('Any orders in currencies other than ZAR will be converted by prestashop prior to be sent to the PayFast payment gateway.').
		  '<p>
		  <p>- '.$this->l('It is possible to setup an automatic currency rate update using crontab.You will simply have to create a cron job with 
			currency update link available at the bottom of "Currencies" section.').'<p>
		</fieldset></div></div></div>
		';
		return $html;
	}

	private function _displayLogoBlock($position)
	{
		$filler = '';
		if ($position)
			$filler .= '';

		return '<div style="text-align:center;"><a href="https://www.payfast.co.za" target="_blank" title="Secure Payments With PayFast">
		<img src="'.__PS_BASE_URI__.$filler.'modules/payfast/views/img/secure_logo.png" width="150" /></a></div>';
	}

	public function hookDisplayRightColumn($params)
	{
		return $this->_displayLogoBlock(self::RIGHT_COLUMN);
	}

	public function hookDisplayLeftColumn($params)
	{
		return $this->_displayLogoBlock(self::LEFT_COLUMN);
	}  

	public function hookDisplayFooter($params)
	{
		$html = '<section id="payfast_footer_link" class="footer-block col-xs-12 col-sm-2">        
		<div style="text-align:center;"><a href="https://www.payfast.co.za" target="_blank" title="Secure Payments With PayFast">
		<img src="'.__PS_BASE_URI__.'modules/payfast/views/img/secure_logo.png"  /></a></div>
		</section>';
		return $html;
	}    

	public function hookPayment($params)
	{ 
		$cookie = $this->context->cookie->payfast;
		$cart = $this->context->cart;
		if (!$this->active)
		
			return;        
		
		// Buyer details
		$customer = new Customer((int)$cart->id_customer);        
		$to_currency = new Currency(Currency::getIdByIsoCode('ZAR'));
		$from_currency = new Currency((int)$cookie->id_currency);       
		$total = $cart->getOrderTotal();

		$pf_amount = Tools::convertPriceFull( $total, $from_currency, $to_currency );       
		$data = array();

		$currency = $this->getCurrency((int)$cart->id_currency);
		if ($cart->id_currency != $currency->id)
		{
			// If PayFast currency differs from local currency
			$cart->id_currency = (int)$currency->id;
			$cookie->id_currency = (int)$cart->id_currency;
			$cart->update();
		}        

		// Use appropriate merchant identifiers
		// Live
		if (Configuration::get('PAYFAST_MODE') == 'live')
		{
			$data['info']['merchant_id'] = Configuration::get('PAYFAST_MERCHANT_ID');
			$data['info']['merchant_key'] = Configuration::get('PAYFAST_MERCHANT_KEY');
			$data['payfast_url'] = 'https://www.payfast.co.za/eng/process';
		}
		// Sandbox
		else
		{
			$data['info']['merchant_id'] = self::SANDBOX_MERCHANT_ID;
			$data['info']['merchant_key'] = self::SANDBOX_MERCHANT_KEY; 
			$data['payfast_url'] = 'https://sandbox.payfast.co.za/eng/process';
		}
		$data['payfast_paynow_text'] = Configuration::get('PAYFAST_PAYNOW_TEXT');        
		$data['payfast_paynow_logo'] = Configuration::get('PAYFAST_PAYNOW_LOGO');      
		$data['payfast_paynow_align'] = Configuration::get('PAYFAST_PAYNOW_ALIGN');
		// Create URLs
		$data['info']['return_url'] = $this->context->link->getPageLink( 'order-confirmation', null, null, 
			'key='.$cart->secure_key.'&id_cart='.(int)$cart->id.'&id_module='.(int)$this->id);
		$data['info']['cancel_url'] = Tools::getHttpHost( true ).__PS_BASE_URI__;
		$data['info']['notify_url'] = Tools::getHttpHost( true ).__PS_BASE_URI__.'modules/payfast/validation.php?itn_request=true';
	
		$data['info']['name_first'] = $customer->firstname;
		$data['info']['name_last'] = $customer->lastname;
		$data['info']['email_address'] = $customer->email;
		$data['info']['m_payment_id'] = $cart->id;
		$data['info']['amount'] = number_format( sprintf( '%01.2f', $pf_amount ), 2, '.', '' );
		$data['info']['item_name'] = Configuration::get('PS_SHOP_NAME').' purchase, Cart Item ID #'.$cart->id; 
		$data['info']['custom_int1'] = $cart->id;       
		$data['info']['custom_str1'] = $cart->secure_key;                   
		
		$pf_output = '';
		// Create output string
		foreach (($data['info']) as $key => $val)
			$pf_output .= $key.'='.urlencode( trim( $val ) ).'&';
		$pass_phrase = Configuration::get( 'PAYFAST_PASSPHRASE' );
		if (empty( $pass_phrase ) || Configuration::get('PAYFAST_MODE') != 'live')
		
			$pf_output = Tools::substr( $pf_output, 0, -1 );
		
		else
		
			$pf_output = $pf_output.'passphrase='.urlencode( $pass_phrase );
		
		$data['info']['signature'] = md5( $pf_output );           
		$this->context->smarty->assign( 'data', $data );         
		return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
		
			return;
		
		$test = __FILE__;

		return $this->display($test, 'views/templates/hook/payment_return.tpl');
	}


	/**
	 * self::pflog
	 *
	 * Log function for logging output.
	 *
	 * @author Jonathan Smit
	 * @param $msg String Message to log
	 * @param $close Boolean Whether to close the log file or not
	 */
	public static function pflog( $msg = '', $close = false )
	{
		static $fh = 0;

		// Only log if debugging is enabled
		if (PF_DEBUG)
		{
			if ($close)
				fclose( $fh );
			else
			{
				// If file doesn't exist, create it
				if (!$fh)
				{
					$pathinfo = pathinfo( __FILE__ );
					$fh = fopen( $pathinfo['dirname'].'/payfast.log', 'a+' );
				}

				// If file was successfully created
				if( $fh )
				{
					$line = date( 'Y-m-d H:i:s' ).' : '.$msg."\n";

					fwrite( $fh, $line );
				}
			}
		}
	}

	/**
	 * pfGetData
	 *
	 * @author Jonathan Smit
	 */
	public static function pfGetData()
	{
		// Posted variables from ITN
		$pfData = $_POST;

		// Strip any slashes in data
		foreach( $pfData as $key => $val )
			$pfData[$key] = Tools::stripslashes( $val );

		// Return "false" if no data was received
		if( sizeof( $pfData ) == 0 )
			return( false );
		else
			return( $pfData );
	}

	/**
	 * pfValidSignature
	 *
	 * @author Jonathan Smit
	 */
	public static function pfValidSignature( $pfData = null, &$pfParamString = null, $pfPassphrase=null )
	{
		// Dump the submitted variables and calculate security signature
		foreach ($pfData as $key => $val)
		{
			if ($key != 'signature')
				$pfParamString .= $key.'='.urlencode( $val ).'&';
			else
				break;
		}

		$pfParamString = Tools::substr( $pfParamString, 0, -1 );

		if (is_null( $pfPassphrase ) ||  Configuration::get('PAYFAST_MODE') != 'live')
			$tempParamString = $pfParamString;
		else
			$tempParamString = $pfParamString."&passphrase=".urlencode( $pfPassphrase );

		$signature = md5( $tempParamString );

		$result = ($pfData['signature'] == $signature );

		self::pflog( 'Signature = '.( $result ? 'valid' : 'invalid' ) );

		return( $result );
	}

	/**
	 * pfValidData
	 *
	 * @author Jonathan Smit
	 * @param $pfHost String Hostname to use
	 * @param $pfParamString String Parameter string to send
	 * @param $proxy String Address of proxy to use or NULL if no proxy
	 */
	public static function pfValidData( $pfHost = 'www.payfast.co.za', $pfParamString = '', $pfProxy = null )
	{
		self::pflog( 'Host = '.$pfHost );
		self::pflog( 'Params = '.$pfParamString );

		// Use cURL (if available)
		if (defined( 'PF_CURL' ) && is_callable( 'curl_init' ))
		{
			// Variable initialization
			$url = 'https://'.$pfHost.'/eng/query/validate';

			// Create default cURL object
			$ch = curl_init();

			// Set cURL options - Use curl_setopt for freater PHP compatibility
			// Base settings
			curl_setopt( $ch, CURLOPT_USERAGENT, PF_USER_AGENT );  // Set user agent
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );      // Return output as string rather than outputting it
			curl_setopt( $ch, CURLOPT_HEADER, false );             // Don't include header in output
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

			// Standard settings
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $pfParamString );
			curl_setopt( $ch, CURLOPT_TIMEOUT, PF_TIMEOUT );
			if (!empty( $pfProxy ))
				curl_setopt( $ch, CURLOPT_PROXY, $pfProxy);

			// Execute CURL
			$response = curl_exec( $ch );
			curl_close( $ch );
		}
		// Use fsockopen
		else
		{
			// Variable initialization
			$header = '';
			$response = '';
			$headerDone = false;

			// Construct Header
			$header = "POST /eng/query/validate HTTP/1.0\r\n";
			$header .= "Host: ".$pfHost."\r\n";
			$header .= "User-Agent: ".PF_USER_AGENT."\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: ".Tools::strlen( $pfParamString )."\r\n\r\n";

			// Connect to server
			$socket = fsockopen( 'ssl://'.$pfHost, 443, $errno, $errstr, PF_TIMEOUT );

			// Send command to server
			fputs( $socket, $header.$pfParamString );

			// Read the response from the server
			while( !feof( $socket ) )
			{
				$line = fgets( $socket, 1024 );

				// Check if we are finished reading the header yet
				if (strcmp( $line, "\r\n" ) == 0)
				{
					// read the header
					$headerDone = true;
				}
				// If header has been processed
				else if ($headerDone)
				{
					// Read the main response
					$response .= $line;
				}
			}

		}

		self::pflog( "Response:\n".print_r( $response, true ) );

		// Interpret Response
		$lines = explode( "\r\n", $response );
		$verifyResult = trim( $lines[0] );

		if (strcasecmp( $verifyResult, 'VALID' ) == 0)
			return( true );
		else
			return( false );
	}

	/**
	 * pfValidIP
	 *
	 * @author Jonathan Smit
	 * @param $sourceIP String Source IP address
	 */
	public static function pfValidIP( $sourceIP )
	{
		// Variable initialization
		$validHosts = array(
			'www.payfast.co.za',
			'sandbox.payfast.co.za',
			'w1w.payfast.co.za',
			'w2w.payfast.co.za',
			);

		$validIps = array();

		foreach( $validHosts as $pfHostname )
		{
			$ips = gethostbynamel( $pfHostname );

			if( $ips !== false)
				$validIps = array_merge( $validIps, $ips );
		}

		// Remove duplicates
		$validIps = array_unique( $validIps );

		self::pflog( "Valid IPs:\n".print_r( $validIps, true ) );

		if (in_array( $sourceIP, $validIps ))
			return( true );
		else
			return( false );
	}

	/**
	 * pfAmountsEqual
	 *
	 * Checks to see whether the given amounts are equal using a proper floating
	 * point comparison with an Epsilon which ensures that insignificant decimal
	 * places are ignored in the comparison.
	 *
	 * eg.100.00 is equal to 100.0001
	 *
	 * @author Jonathan Smit
	 * @param $amount1 Float 1st amount for comparison
	 * @param $amount2 Float 2nd amount for comparison
	 */
	public static function pfAmountsEqual( $amount1, $amount2 )
	{
		if (abs( (float) $amount1  - (float) $amount2  ) > PF_EPSILON)
			return( false );
		else
			return( true );
	}

}


