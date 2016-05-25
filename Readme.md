PayFast Prestashop Cloud Plugin v1.1.0
-------------------------------------------------------------------------------
Copyright © 2010-2016 PayFast (Pty) Ltd

LICENSE:

This payment module is free software; you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published
by the Free Software Foundation; either version 3 of the License, or (at
your option) any later version.

This payment module is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
License for more details.

Please see http://www.opensource.org/licenses/ for a copy of the GNU Lesser
General Public License.

INTEGRATION:
1. Download the PayFast addon from the PrestaShop addons site
2. Navigate to modules in the admin dashboard of your PrestaShop cloud site
3. Click on ‘Add New Module’ in the top right corner of the screen and follow the prompts
4. Select the PayFast addon downloaded in step 1 for upload
5. If you are not able to select a file for upload you may need to logout of the PrestaShop addons site (navigate to your profile, select ‘Preferences’ and then ‘Log out of addon account’), then repeat the installation process from step 2
6. Click on the “Install” button to install the module
7. Once the module is installed, click on “Configure” below the PayFast name.
8. The PayFast options will then be shown, and you will see the module is ready to be tested.
9.Leave everything as per default and click “Save” in order to test in sandbox mode

How can I test that it is working correctly?
If you followed the installation instructions above, the module is in “test” mode and you can test it by purchasing from your site as a buyer normally would. You will be redirected to PayFast for payment and can login with the user account detailed above and make payment using the balance in their wallet.

You will not be able to directly “test” a credit card or Instant EFT in the sandbox, but you don”t really need to. The inputs to and outputs from PayFast are exactly the same, no matter which payment method is used, so using the wallet of the test user will give you exactly the same results as if you had used another payment method.

I’m ready to go live! What do I do?
In order to make the module “LIVE”, follow the instructions below:

1. Login to the PrestaShop Back Office
2. Using the top navigation bar, navigate to Modules
3. Click on Payments & Gateways to expand the options
4. Under PayFast, click on the “Configure” link
5. In the PayFast Settings block, use the following settings:
6. Mode = “Live”
7. Merchant ID = <Login to PayFast -> Integration Page>
8. Merchant Key = <Login to PayFast -> Integration Page>
8. Debugging = Unchecked
9. Click Save


******************************************************************************
*                                                                            *
*    Please see the URL below for all information concerning this module:    *
*                                                                            *
*                 https://www.payfast.co.za/shopping-carts/prestashop/       *
*                                                                            *
******************************************************************************
