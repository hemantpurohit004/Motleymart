<?php
/**
* Copyright 2009 Amazon.com, Inc. or its affiliates. All Rights Reserved.
*
* Licensed under the Apache License, Version 2.0 (the "License").
* You may not use this file except in compliance with the License.
* A copy of the License is located at
*
*    http://aws.amazon.com/apache2.0/
*
* or in the "license" file accompanying this file.
* This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
* either express or implied. See the License for the specific language governing permissions
* and limitations under the License.
*
*
* @brief Configuration File
* @catagory Checkout By Amazon  - Configuration
*
*/

/*
 * This flag indicates whether merchant is opting for signed carts or not If
 * this flag is set to false then no accessKeyId,SecretKey pairs will be
 * loaded and if the merchant endpoint recieves any signed IOPNs then it
 * will be not be processed.
 *
 * If the merchant is expecting signed IOPNs then this flag has to be set to
 * true and the accessKeyId,accessKey pairs should be mentioned in the
 * merchant.properties file
 */
define('SUPPORT_SIGNED_CARTS','true');

$IOPN_dirPath =  JPATH_SITE . '/plugins/system/qtcamazon_easycheckout/lib/iopn_processing/';

define('MERCHANT_ACCESS_KEY_PROPERTIES_NAME', 'AWSSecretKeyList');
// define('MERCHANT_PROPERTIES_FILE','prop/merchant.properties');
// Changed paths
define('MERCHANT_PROPERTIES_FILE', JPATH_SITE . '/plugins/system/qtcamazon_easycheckout/lib/iopn_processing/prop/merchant.properties');

define('DEBUG',true);


/*
 *  Please *do not* edit the following settings
 */

define('TRUE_FLAG','true');

 // 15 minute window.
define('TIMESTAMP_WINDOW',900);


define("LIB", $IOPN_dirPath . 'lib/');
define("SOURCE", $IOPN_dirPath .'source/');

// libraries required.
ini_set('include_path','.:' .
                       LIB . ':' .
                       LIB . 'PEAR-1.7.2/PEAR:' .
                       ini_get('include_path'));

define("XMLNS_VERSION_TAG", 'http://payments.amazon.com/checkout/2008-11-30/');

/* Signature Algorithm used. */
define("HMAC_SHA1_ALGORITHM","sha1");

/* Schema Files */
define('EVENT_NOTIFICATION_SCHEMA_FILE','schema/iopn.xsd');
define('ORDER_SCHEMA_FILE','schema/order.xsd');


//Path setting
define("LOG_DIR", $IOPN_dirPath .'log/');
define('LOG_FILE', LOG_DIR . 'cbaiopn.log');

//including the required files
require_once (LIB . 'Crypt_HMAC-1.0.1/HMAC.php');
require_once (LIB . 'PEAR-1.7.2/PEAR/PEAR.php');

require_once(SOURCE . 'CBAIOPNProcessor.php');
require_once(SOURCE . 'CBAUtils.php');

if(!DEBUG){
  error_reporting(E_ERROR);
}

?>
