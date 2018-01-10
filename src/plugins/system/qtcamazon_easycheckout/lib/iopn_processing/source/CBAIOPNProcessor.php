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
* @brief Class for processing Checkout By Amazon Request
* @catagory Checkout By Amazon Instant Order Processing Notification sample code.
* @copyright Portions copyright 2009 Amazon.com, Inc.
* @license Apache License v2.0, please see LICENSE.txt
* @access public
*
*/

/**
* This array contains the types of notifications which can be processed.
*/
$event_array = array(
						'NewOrderNotification',
						'OrderCancelledNotificaiton',
						'OrderReadyToShipNotification'
					);

class CBAIOPNProcessor{

  var $RequestXML;
  var $SignedOrder = false;
  var $Signature;
  var $UUID;
  var $Timestamp;
  var $NotificationData;
  var $NotificationType;
  var $AWSAccessKeyId;
  var $AWSSecretAccessKey;
  var $IsAccessKeyListConfigured = false;
  var $AccessKeyToSecretKeyMap;


  function CBAIOPNProcessor(){
    ob_start();
    LogRequest();
  }

/* Initialize merchant configuration */
  function Initialize(){
	 /*
         * Check whether the merchant has opted for signed carts
         * and load the awsSecretKeys only if it is true.
         *
         * Mechant can opt for the signed carts in settings present
         * in the SellerCentral
         *
         */
	if(strcmp(SUPPORT_SIGNED_CARTS,TRUE_FLAG) == 0){
	    // Read merchant configurations.
	    if(!($this->LoadAWSAccessKeys())){
		WriteLog("Couldnt load the AWSAccessKeys. Sending response code 500.");
		$this->SendInternalServerError();
	    }
        }
  }

  /* Process the POST request and set appropriate flag */
  function AuthenticateRequest(){
    if($_POST){
     if($_POST['Signature']){
		$this->SignedOrder = true;
        $this->Signature = $_POST['Signature'];
      }else {
      		WriteLog("This is NOT a signed CART!");
      }

     if($_POST['UUID']){
       	$this->UUID = $_POST['UUID'];
      }

     if($_POST['Timestamp']){
        $this->Timestamp = $_POST['Timestamp'];
      }

      if($_POST['AWSAccessKeyId']){
      	$this->AWSAccessKeyId = $_POST['AWSAccessKeyId'];
      }

      /*
      * Extract the AWSSecretKey present in merchant.properties file. This is a demo code and hence the
      * secret key is stored in plain text format. Usually the secret key should be stored in a secure
      * storage and should be accessed from there.
      */
      if(isset($this->AccessKeyToSecretKeyMap[$this->AWSAccessKeyId])){
			WriteLog("AWSSecretAccessKey is present corresponding to the AWSAccessKeyId.");
			$this->AWSSecretAccessKey = $this->AccessKeyToSecretKeyMap[$this->AWSAccessKeyId];
        }


      if(!($this->IsAccessKeyListConfigured) && $this->SignedOrder){
      	 WriteLog("No Access key is specified in the merchant.properties file, " .
                  "where as, the Key is configured on the CBA side." .
                  "Please specify the access key in the merchant.properties file, so that IOPN request can be validated..." .
                  "Sending response code 500."
                  );
         $this->SendInternalServerError();
	 return;
      }else if(!($this->IsAccessKeyListConfigured) && !($this->SignedOrder)){
      			//Merchant has not configured access key at all.
      			return;
      }else if(empty($this->UUID) || empty($this->Timestamp) || empty($this->AWSAccessKeyId) || !($this->SignedOrder)){
      		WriteLog("UUID/Timestamp/AWSAccessKeyId/Signature missing
      		         in the Notification request. Sending response code 500."
      		         );
      		$this->SendInternalServerError();
		return;
      }else if(empty($this->AWSSecretAccessKey)){
      		/* Merchant has configured the AWSAccessKeyList in the properties file. But the accessKeyId passed in the
      		* request is not present in that list.
      		*/
      		WriteLog("AWSAccessKeyId is not present in the merchant.properties file. Sending response code 500.");
      		$this->SendInternalServerError();
		return;
      }

      // Proceed with Signature validation as we now have all the information needed.
      //Get the current time measured in the number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT).
      $TimeStamp_now = time();

      // If the timestamp is older than timestamp window then discard the request
      if(strtotime($this->Timestamp) < ($TimeStamp_now - TIMESTAMP_WINDOW)){
            WriteLog("Rejecting the Notification as this is older than 15 minutes. Sending response code 403.");
      	  	$this->SendPermissionDeniedError();
       }

      if(!$this->IsValidSignature()){
			WriteLog("Not a valid signature!!! Sending response code 403.");
      	    $this->SendPermissionDeniedError();
       }

    }else{
    	WriteLog("GET not allowed. Sending 500 response code.");
    	$this->SendInternalServerError();
    }
  }

  /* Process the Notification data and its associated type. */
  function ValidateNotificationData() {
  	$xml = '';
  	global $event_array;

  	WriteLog("Starting validation of Notification Data!");

  	// Checks if the request has the Notification data.
  	if($_POST['NotificationData']){
        $this->NotificationData = stripslashes($_POST['NotificationData']);
        $xml = $this->NotificationData;
    }else{
    	WriteLog("Notification data is absent. Sending response code 500.");
      	$this->SendInternalServerError();
    }

    if($_POST['NotificationType']){
     	$this->NotificationType =  $_POST['NotificationType'];
    }else {
    	WriteLog("Notification Type is absent in the request. Sending response code 500.");
     	$this->SendInternalServerError();
    }

    //Checks if the Notification Type is a subscribed one.
    $NotificationValue=array_search($this->NotificationType, $event_array);
    if (isset($NotificationValue)){
    	 WriteLog("Valid notification type.");
 	 	 if(!$this->IsValidXML($xml) or empty($xml)){
 	 	 		WriteLog("Validation of XML against schema FAILED. Please check if the schema is recent one.");
    	  }

    	 $this->RequestXML = simplexml_load_string($xml);
    	 WriteLog("Validation of Notification Data Completed!");
    }
  }

  /* validates the xml using the schema file */
  function IsValidXML($xml){
      $doc = new DOMDocument();
      $doc->loadXML($xml);
      if($doc->schemaValidate(EVENT_NOTIFICATION_SCHEMA_FILE)){
        return true;
      }else{
        return false;
      }
  }

  /* checks whether request is valid via signature cmp */
  function IsValidSignature(){
    $data = $this->UUID . $this->Timestamp;
    $signature = $this->GenerateSignature($data);

	   return ($signature == $this->Signature);
  }

 /**
  * @brief returns the encrypted order signature
  * @return a based64 encoded encrypted order signature
  * @see HMAC.php
  */
  function GenerateSignature($data){
    $signature_calculator = new Crypt_HMAC($this->AWSSecretAccessKey, HMAC_SHA1_ALGORITHM);
    $signature = $signature_calculator->hash($data);
    $binary_signature = pack('H*', $signature);
    return base64_encode($binary_signature);
  }

  /* sends the response code of 500 incase of any internal errror. */
  function SendInternalServerError() {
  	header('HTTP/1.1 500 Internal Server Error');
  }

  /* sends the response code of 403 if unable to verify the signature. */
  function SendPermissionDeniedError(){
  	header('HTTP/1.1 403 Forbidden');
  }

  /* Process the request xml object */
  //~ function ProcessRequestXML(){
  	//~ WriteLog("Starting Processing of request XML.");
  	//~ // Merchant fetches the required fields from requestXML.
  //~ }

	function ProcessRequestXML()
	{
		$NotificationData = $this->NotificationData;

		// Quick2cart hack start
		require_once JPATH_SITE . '/plugins/system/qtcamazon_easycheckout/qtcamazon_easycheckout/qtceasycheckouthelper.php';
		$Qtceasycheckouthelper = new Qtceasycheckouthelper;
		$Qtceasycheckouthelper->processIOPN($NotificationData);

//~
		//~ if (strpos($xmldata, 'NewOrderNotification') !== false) {
		//~ $NotificationType = 'NewOrderNotification';
		//~ }
//~
		//~ if (strpos($xmldata, 'OrderCancelledNotification') !== false) {
		//~ $NotificationType = 'OrderCancelledNotification';
		//~ }
//~
		//~ if (strpos($xmldata, 'OrderReadyToShipNotification') !== false) {
		//~ $NotificationType = 'OrderReadyToShipNotification';
		//~ }
//~
		//~ $NotificationReferenceId = (string)$xml->NotificationReferenceId[0];
		//~ $OrderChannel = (string)$xml->ProcessedOrder->OrderChannel;
		//~ $AmazonOrderID = (string)$xml->ProcessedOrder->AmazonOrderID;
		//~ $OrderDate = (string)$xml->ProcessedOrder->OrderDate;
//~
//~
		//~ $BuyerName = (string)$xml->ProcessedOrder->BuyerInfo->BuyerName;
		//~ $BuyerEmailAddress = (string)$xml->ProcessedOrder->BuyerInfo->BuyerEmailAddress;
//~
		//~ //ShippingAddress
		//~ $Name = (string)$xml->ProcessedOrder->ShippingAddress->Name ;
		//~ $AddressFieldOne = (string)$xml->ProcessedOrder->ShippingAddress->AddressFieldOne ;
		//~ $AddressFieldTwo = (string)$xml->ProcessedOrder->ShippingAddress->AddressFieldTwo;
		//~ $City     = (string)$xml->ProcessedOrder->ShippingAddress->City;
		//~ $State      = (string)$xml->ProcessedOrder->ShippingAddress->State;
		//~ $PostalCode   = (string)$xml->ProcessedOrder->ShippingAddress->PostalCode;
		//~ $CountryCode    = (string)$xml->ProcessedOrder->ShippingAddress->CountryCode;
//~
		//~ $ShippingServiceLevel = (string)$xml->ProcessedOrder->ShippingServiceLevel;
//~
//~
//~
		//~ $myfile = fopen("people.txt", "w");
		//~ fwrite($myfile, $Name);
		//~ fclose($myfile);
		//~ WriteLog("Starting Processing of request XML.");
		//~ header('HTTP/1.1 200 OK');

		// Merchant fetches the required fields from requestXML.
	}

  /**
     * This method performs the task of loading all the accessKeyId, accessKey
     * pairs so that they can be used during signature authentication. Currently
     * the source for the pairs is the merchant.properties file. It parses the
     * AWSSecretKeyList present in the following format :
     *
     * AWSSecretKeyList = (AWSAccessKeyId1,AWSSecretKey1), (AWSAccessKeyId2,AWSSecretKey2)
     *
     * But merchant can change the source or the format or both of the AWSSecretKeyList
     * and correspondingly change the logic present in this method.
     *
   */

  function LoadAWSAccessKeys() {
        //Read merchant properties file line by line and store them into array.
  	$lines = file(MERCHANT_PROPERTIES_FILE);

        //This is a demo code and hence the parsing doesnt take into account all the scenarios.
	//Iterate through each of the lines.
	foreach ($lines as $line_num => $line) {
	   $args = explode('=', $line, 2);

           //look for the line which contains the AWSAccessList properties.
	   if(0 == strcmp($args[0],MERCHANT_ACCESS_KEY_PROPERTIES_NAME)){
			  $args[1] = trim($args[1]);
		          $newstr = ltrim($args[1],"(");
			  $newstr = rtrim($newstr,")");

                         //split the string around "),(" and store them into array this will give you individual accessKeyId
			  $chars = preg_split('/[)][\s]*[,][\s]*[(]/', $newstr, -1);

                         //For each of the accessKeyId and secretAccessKey pair obtained, segregate them into accessKeyID and secretKey arrays.
			  foreach ($chars as $i => $val) {
				$keys = explode(',',$val);
				$accessKeyID[] = trim($keys[0]);
				$secretKey[] = trim($keys[1]);
			  }
                          //Make a one to one mapping between accessKeyId and corresponding secretAccessKey and store them into AccessKeyToSecretKeyMap array.
			  $this->AccessKeyToSecretKeyMap = array_combine($accessKeyID, $secretKey);
		}
 	}

 	if(!empty($this->AccessKeyToSecretKeyMap)){
		WriteLog("Access List configuration loaded successfully.");
 		$this->IsAccessKeyListConfigured = true;
		return true;
	}else{
		return false;
	}
  }
}
?>

