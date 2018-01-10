<?php
require_once("signature/common/cart/CartFactory.php");
/**
 * Returns a simple static cart to generate a signature from,
 * and the final complete cart html.
 *
 * Copyright 2008-2011 Amazon.com, Inc., or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *    http://aws.amazon.com/apache2.0/
 *
 * or in the "license" file accompanying this file.
 * This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific language governing permissions and limitations under the License.
 */
class XMLCartFactory extends CartFactory {
   protected static $CART_ORDER_INPUT_FIELD ="type:merchant-signed-order/aws-accesskey/1;order:[ORDER];signature:[SIGNATURE];aws-access-key-id:[AWS_ACCESS_KEY_ID]";

   public function XMLCartFactory() {
   }

   	/** ADDED by vm foR com_quick2cart integration
	 * Constructor
	 *
	 * @param   INTEGER  $liveMode  Mode of operation like live or sandbox mode
	 *
	 * @return   instance
	 */
	public static function getInstanceForCurrentMode($liveMode)
	{
		if ($liveMode)
		{
			CartFactory::$CART_JAVASCRIPT_START = "<script type=\"text/javascript\" src=\"https://images-na.ssl-images-amazon.com/images/G/01/cba/js/jquery.js\"></script>\n <script type=\"text/javascript\" src=\"https://static-eu.payments-amazon.com/cba/js/in/PaymentWidgets.js\"></script>\n";
		}
		else
		{
			CartFactory::$CART_JAVASCRIPT_START = "<script type=\"text/javascript\" src=\"https://images-na.ssl-images-amazon.com/images/G/01/cba/js/jquery.js\"></script>\n<script type=\"text/javascript\" src=\"https://static-eu.payments-amazon.com/cba/js/in/sandbox/PaymentWidgets.js\"></script>\n<!-- For Switching to Production, comment out the lines above and uncomment the lines below -->\n<!-- <script type=\"text/javascript\" src=\"https://static-eu.payments-amazon.com/cba/js/in/PaymentWidgets.js\"></script>-->\n";
		}

		$instance = new self();

		// Update the script variable
		$instance->CART_JAVASCRIPT_START = "<script type=\"text/javascript\" src=\"https://images-na.ssl-images-amazon.com/images/G/01/cba/js/jquery.js\"></script>\n <script type=\"text/javascript\" src=\"https://static-eu.payments-amazon.com/cba/js/in/PaymentWidgets.js\"></script>\n";

    	return $instance;



		//~ parent::__construct();
		//~ $workingMode = $this->params->get('workingMode', 0);
//~
		//~ if (!defined('Q2C_AMAZOON_CKOUT_PLUG_WORKING_MODE'))
		//~ {
			//~ define('Q2C_AMAZOON_CKOUT_PLUG_WORKING_MODE', $workingMode);
		//~ }

	}

   	/** ADDED by vm forcom_quick2cart integration: Return current loaded javascript according to live or sanbox mode
	 * Constructor
	 *
	 * @param   INTEGER  $liveMode  Mode of operation like live or sandbox mode
	 *
	 * @return   instance
	 */
	public static function getCART_JAVASCRIPT_START()
	{
		return CartFactory::$CART_JAVASCRIPT_START;

		// return $this->CART_JAVASCRIPT_START;
	}

   /**
    * Gets cart html fragment used to generate entire cart html
    * Base 64 encode the cart.
    *
    */
    public function getCart($merchantID, $awsAccessKeyID) {
        $cartXML = $this->getCartXML($merchantID, $awsAccessKeyID);
        return base64_encode($cartXML);
    }

   /**
    * Returns the concatenated cart used for signature generation.
    * @see CartFactory
    */
   public function getSignatureInput($merchantID, $awsAccessKeyID) {
        return $this->getCartXML($merchantID, $awsAccessKeyID);
   }

   /**
    * Returns a finalized full cart html including the base 64 encoded cart,
    * signature, and buy button image link.
    */
   public function getCartHTML($merchantID, $awsAccessKeyID, $signature) {

    $cartHTML = '';
	$cartHTML = $cartHTML . CartFactory::$CART_JAVASCRIPT_START;
	$cartHTML = $cartHTML . CartFactory::$CBA_BUTTON_DIV;
	// construct the order-input section
	$encodedCart = $this->getCart($merchantID, $awsAccessKeyID);

	//~ $input = ereg_replace("\\[ORDER\\]", $encodedCart, XMLCartFactory::$CART_ORDER_INPUT_FIELD);
	//~ $input = ereg_replace("\\[SIGNATURE\\]", $signature, $input);
	//~ $input = ereg_replace("\\[AWS_ACCESS_KEY_ID\\]", $awsAccessKeyID, $input);
	//~ $widgetScript = ereg_replace("\\[CART_TYPE\\]", "XML",CartFactory::$STANDARD_CHECKOUT_WIDGET_SCRIPT);
	//~ $widgetScript = ereg_replace("\\[MERCHANT_ID\\]", $merchantID,$widgetScript);
	//~ $widgetScript =ereg_replace ("\\[CART_VALUE\\]",$input ,$widgetScript);

        $input = preg_replace("/\[ORDER\]/", $encodedCart, XMLCartFactory::$CART_ORDER_INPUT_FIELD);
        $input = preg_replace("/\[SIGNATURE\]/", $signature, $input);
        $input = preg_replace("/\[AWS_ACCESS_KEY_ID\]/", $awsAccessKeyID, $input);
        $widgetScript = preg_replace("/\[CART_TYPE\]/", "XML",CartFactory::$STANDARD_CHECKOUT_WIDGET_SCRIPT);
        $widgetScript = preg_replace("/\[MERCHANT_ID\]/", $merchantID,$widgetScript);
        $widgetScript =preg_replace ("/\[CART_VALUE\]/",$input ,$widgetScript);


        $cartHTML = $cartHTML . $widgetScript;

	return $cartHTML;
   }

    /**
     * Replace with your own cart here to try out
     * different promotions, tax, shipping, etc.
     *
     * @param merchantID
     * @param awsAccessKeyID
     */
    private function getCartXML($merchantID, $awsAccessKeyID) {

		// Quick2cart hack start
		require_once (JPATH_SITE . '/plugins/system/qtcamazon_easycheckout/qtcamazon_easycheckout/qtceasycheckouthelper.php');
		$Qtceasycheckouthelper = new Qtceasycheckouthelper;
		return $Qtceasycheckouthelper->getCartXML($merchantID, $awsAccessKeyID);

		// Quick2cart hack END


       /* return
	    "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
	    "<Order xmlns=\"http://payments.amazon.com/checkout/2008-06-15/\">" .
	    "    <ClientRequestId>123457</ClientRequestId>" .
	    "    <Cart>" .
	    "    <Items>" .
	    "      <Item>" .
	    "         <SKU>CALVIN-HOBBES</SKU>" .
	    "         <MerchantId>" . $merchantID . "</MerchantId>" .
	    "         <Title>The Complete Calvin and Hobbes from PHP Sample Code XML</Title>" .
	    "         <Description>By Bill Watterson From PHP Sample Code XML</Description>" .
	    "         <Price>" .
	    "            <Amount>2.50</Amount>" .
	    "            <CurrencyCode>INR</CurrencyCode>" .
	    "         </Price>" .
	    "         <Quantity>1</Quantity>" .
	    "         <Weight>" .
	    "            <Amount>8.5</Amount>" .
	    "            <Unit>kg</Unit>" .
	    "         </Weight>" .
	    "         <Category>Books</Category>" .
	    "      </Item>" .
	    "    </Items>" .
	    "    </Cart>" .
	    "</Order>";
	    * */
    }
}
