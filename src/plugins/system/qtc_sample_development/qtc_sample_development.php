<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');
jimport('joomla.application.application');

if (!defined('DS'))
{
	define('DS', '/');
}

/**
 * Sample developement plguin  for developers
 *
 * @package     Plgshare_For_Discounts
 * @subpackage  site
 * @since       1.0
 */
class PlgSystemQtc_Sample_Development extends JPlugin
{
	/**
	 * This event is triggered after the framework has loaded and initialised and the router has routed the client request.
	 *
	 * @return  '';
	 *
	 * @since   2.3.2
	 */
	public function onAfterRoute()
	{
		$affiliate_status = trim($this->params->get('affiliate_status'));

		if (!empty($affiliate_status))
		{
			$affiliate = $this->params->get('affiliate');

			switch ($affiliate)
			{
				// Needed parameter affiliate id parameter name eg qtcIdev_affiliate_id
				// Set cookie variable name

				case 'iDevAffi':
					$jinput     = JFactory::getApplication()->input;
					$affilate_id = $jinput->get("qtcIdev_affiliate_id", '', "INT");

					if (!empty($affilate_id))
					{
						$expireMin = $this->params->get('qtcCookieExp', 420);

						// $expire = time()+3600*24*30;
						// Convert in to minute
						$expire = time() + $expireMin * 60;
						$statis = setcookie("qtcIdev_affiliate_id", $affilate_id, $expire, "/");
					}

				break;

				case 'PostAffiPro':
				break;
			}
		}
	}

	/**
	 * This method called return current affiliate system name
	 *
	 * @return  boolean
	 *
	 * @since   2.2.2
	 */
	private function getAffilateSystem()
	{
		$affiliate_status = trim($this->params->get('affiliate_status'));
		$affiliateSys = '';

		if (!empty($affiliate_status))
		{
			$affiliateSys = $this->params->get('affiliate', '');
		}

		return $affiliateSys;
	}

	/**
	 * This method should is called after creating the order table entry with pending status
	 *
	 * @param   array  $order  Contain order and order item information
	 * @param   array  $data   Latest Post details.
	 *
	 * @return  boolean
	 *
	 * @since   2.2.2
	 */
	public function OnAfterq2cOrder($order, $data)
	{
		$affilateSystem = $this->getAffilateSystem();

		if (!empty($affilateSystem))
		{
			switch ($affilateSystem)
			{
				case 'iDevAffi':

					$affiliate_status = trim($this->params->get('idev_affiliate_method', "basedOnAffiliateId"));

					if ($affiliate_status == "basedOnAffiliateId")
					{
						if (!empty($_COOKIE['qtcIdev_affiliate_id']))
						{
							// Check for cookie
							$aff_id = $_COOKIE['qtcIdev_affiliate_id'];

							$order_id = $order['order']->id;

							// Found cookie then add in db
							if (!empty($aff_id) && $order_id)
							{
								// Check for existacce of recorde. (For safer side)
								$db = JFactory::getDBO();
								$query = $db->getQuery(true);
								$query->select('a.order_id');
								$query->from('#__kart_affiliate AS a');
								$query->where("a.order_id = " . $order_id)
								->where("a.affiliate_id = " . $aff_id);

								$db->setQuery($query);
								$result = $db->loadResult();

								$action = "insertObject";
								$row = new stdClass;

								if (!empty($result))
								{
									$action = "updateObject";

									// $row->id = $result;
								}

								$row->order_id = $order_id;
								$row->affiliate_id = $aff_id;
								$row->client = "idevaffiliate";

								if (!$db->$action('#__kart_affiliate', $row, 'order_id'))
								{
									echo $db->stderr();
									$app = JFactory::getApplication();
									$app->enqueueMessage(JText::_('COM_QUICK2CART_ERROR_WHILE_ADDING_AFF_ENTRY'), 'error');

									return false;
								}
							}

							break;
						}
					}
			}
		}
	}

	/**
	 * For commission code per commisssion. http://www.idevlibrary.com/docs/Coupon_Code_Commissioning.pdf
	 * This method called when order is updated.
	 *
	 * @param   Object  $orderobj        Order detail
	 * @param   Object  $orderIitemInfo  Full order detail including the order item detail
	 *
	 * @return  ''
	 *
	 * @since   2.2
	 */
	public function Onq2cOrderUpdate($orderobj, $orderIitemInfo)
	{
		$mainframe = JFactory::getApplication();
		$input     = $mainframe->input;

		$affiliate_status = trim($this->params->get('affiliate_status'));
		$db    = JFactory::getDBO();

		if (!empty($affiliate_status))
		{
			if ($orderobj->status == 'C')
			{
			// Http://www.yoursite.com/affiliate/scripts/sale.php?TotalCost=120.50&OrderID=ORD_12345XYZ&ProductID=test+product

				$baseurl = $this->params->get('baseurl');

				if ($baseurl)
				{
					$affiliate = $this->params->get('affiliate');
					$executeCurl = 1;

					switch ($affiliate)
					{
						case 'iDevAffi':

							if (empty($orderobj->id))
							{
								return;
							}

							/*  Here you have to add base url as a idevaffiliate path eg http://YOUR_SITE_BASE_PATH/IDEVAFFILIATE_DIR/

							 START IDEVAFFILIATE TRACKING
							----------------------------
							$idev_saleamount = $order['details']['BT']->order_subtotal;
							$idev_ordernum = $order['details']['BT']->order_number;
							$idev_coupon_discount = $order['details']['BT']->coupon_discount;
							$idev_coupon_code = $order['details']['BT']->coupon_code;
							echo "<img border=\"0\" src=$idev_saleamount = $idev_saleamount - $idev_coupon_discount;
							\"http://durgesh.tekdi.net/idevaffiliate/sale.php?profile=90&idev_saleamt=$idev_saleamount&idev_ordernum=$idev_ordernum&coupon_code=$idev_coupon_code\" width=\"1\" height=\"1\">";
							----------------------------
							END IDEVAFFILIATE TRACKING
							*/

							$affiliate_status = trim($this->params->get('idev_affiliate_method', "basedOnAffiliateId"));

							$affiliate_link = $baseurl . "sale.php?profile=72198" . "&idev_ordernum=" . $orderobj->id;

							if ($affiliate_status == "basedOnAffiliateId")
							{
								$affiliate_link = $affiliate_link . "&idev_saleamt=" . $orderobj->amount;

								// Get affilate id
								$db = JFactory::getDBO();
								$query = $db->getQuery(true);
								$query->select('a.affiliate_id');
								$query->from('#__kart_affiliate AS a');
								$query->where("a.order_id = " . $orderobj->id);

								$db->setQuery($query);
								$affiliate_id = $db->loadResult();

								$affiliate_link = $affiliate_link . "&affiliate_id=" . $affiliate_id;
							}
							else
							{
								// Coupon based:
								$amtData = $this->getOrderCoupon($orderobj, $orderIitemInfo);

								if (!empty($amtData))
								{
									$applicableAmount = !empty($amtData['applicableAmount']) ? $amtData['applicableAmount'] : 0;
									$couponCode = !empty($amtData['couponCode']) ? $amtData['couponCode'] : 0;

									$affiliate_link = $affiliate_link . "&idev_saleamt=" . $applicableAmount;

									if (!empty($couponCode))
									{
										$affiliate_link = $affiliate_link . "&coupon_code=" . $couponCode;
									}
									else
									{
										$executeCurl = 0;
									}
								}
							}

						break;
						case 'PostAffiPro':
							$affiliate_link = $baseurl . "/affiliate/scripts/sale.php?TotalCost=" . $orderobj->amount . "&OrderID=" . $orderobj->id;
						break;
					}

					if ($executeCurl == 1)
					{
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $affiliate_link);

						// Other  //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						$data = curl_exec($ch);

						// Check if any error occured
						if (curl_errno($ch))
						{
							$curlmsg = 'Curl error: ' . curl_error($ch);
						}
						else
						{
							$curlmsg = '1';
						}

						curl_close($ch);
					}
				}
			}
		}
	}

	/**
	 *  This function is used to get order's coupon detail
	 *
	 * @param   Object  $orderobj        Order detail
	 * @param   Object  $orderIitemInfo  Full order detail including the order item detail
	 *
	 * @return  ''
	 *
	 * @since   2.8
	 */
	private function getOrderCoupon($orderobj, $orderIitemInfo)
	{
		$couponCode = '';
		$idev_affiliate_method = $this->params->get('amount_consideration', "totCartAmt");

		// Applicable item price: price of all item on which discount is applied.
		$applicableAmount = 0;

		if (!empty($orderIitemInfo) && !empty($orderIitemInfo["items"]))
		{
			foreach ($orderIitemInfo["items"] as $oitem)
			{
				if (!empty($oitem->coupon_code))
				{
					$couponCode = $oitem->coupon_code;
					$applicableAmount = $applicableAmount + $oitem->product_final_price;
				}
			}
		}

		if ($idev_affiliate_method == "applicableAmt")
		{
			$amount = $applicableAmount;
		}
		else
		{
			$amount = $orderobj->amount;
		}

		$data["applicableAmount"] = $amount;
		$data["couponCode"] = $couponCode;

		return $data;
	}

	/**
	 * Function used as a trigger before sending order email
	 *
	 * @param   Object  $order          [order detail ]
	 * @param   String  $email_subject  [email subject]
	 * @param   String  $email_body     [email Body]
	 */
	/*public function OnBeforeq2cOrderUpdateEmail($order, $email_subject, $email_body)
	{
	}*/

	/**
	 *  Function used as a trigger before sending the order data to respective payment gateway's onTP_GetHTML() trigger  which is used to build payment form which is displayed on checkout form
	 *
	 * @param   Object  $order_vars  Order detail
	 */
	/*public function OnBeforeq2cPay($order_vars)
	{
			$usd_exchange_status =trim( $this->params->get('usd_exchange_status'));
		if(!empty($usd_exchange_status))
		{
		$comquick2cartHelper = new comquick2cartHelper();
		$currency = $comquick2cartHelper->getCurrencySession();
		if($currency){
		switch($currency){
		case 'USD':
		$convert_val = $this->params->get("usd_exchange");
		if($convert_val){
		$order_vars->amount = $order_vars->amount * $convert_val;
		}
		break;
		}
		}
		}
		return $order_vars;
	}
	*/

	/**
	 *  Function used as a trigger before deleting the order
	 *
	 * @param   Integer  $order_id  order id
	 *
	 * @return ''
	 */
	/*public function Onq2cOrderDelete($order_id)
	{
	}*/

	/****** --------------------------- Cart --------------------------- */

	/**
	 * Function used as a trigger before adding product in cart
	 *
	 * @param   [type]  $cartId  [description]
	 * @param   [type]  $item    [description]
	 *
	 * @return ''
	 */
	/*public function OnBeforeq2cAdd2Cart($cartId, $item)
	{
	}*/

	/**
	 * Function used as a trigger after removing the products from cart
	 *
	 * @param   Array  $cartDetail  Cart detail including cart items detail and item attribute detail
	 *
	 * @return ''
	 */
	/*public function OnAfterq2cRemovefromCart($cartDetail)
	{
	}*/

	/****** --------------------------- Related to cart display --------------------------- */
	/**
	 * Function used as a trigger before displaying the cart on checkout view
	 *
	 * @return ''
	 */
	/*public function OnBeforeq2cCheckoutCartDisplay()
	{
		return '';
	}*/

	/**
	 * Function used as a trigger after displaying the cart on checkout view
	 *
	 * @return ''
	 */
	public function OnAfterq2cCheckoutCartDisplay()
	{
		return "";
	}

	/**
	 * Function used as a trigger before displaying the cart in module
	 *
	 * @return  [type]  [description]
	 */
	/*public function onBeforeCartModule()
	{
		return '';
	}*/

	/**
	 * Function used as a trigger after displaying the cart in module
	 *
	 * @return  [type]  [description]
	 */
	/*public function onAfterCartModule()
	{
		return '';
	}*/

	/****** --------------------------- Store releated  --------------------------- */
	/**
	 * Function used as a trigger before edit the store
	 *
	 * @param   Integer  $store_id  Store id
	 *
	 * @return  ''
	 */
	/*public function qtcOnBeforeEditStore($store_id)
	{
	}*/

	/**
	 * Function used as a trigger before saving the store detail
	 *
	 * @param   object  $post  Create store post detail
	 *
	 * @return  ''
	 */
	/*public function qtcOnBeforeSaveStore($post)
	{
	}*/

	/**
	 * Function used as a trigger after saving the store detail
	 *
	 * @param   object   $post     Create store post detail
	 * @param   integer  $storeid  Store id
	 *
	 * @return  [type]            [description]
	 */
	/*public function qtcOnAfterSaveStore($post, $storeid)
	{
	}*/

	/**
	 * @param : cartdata array - cart details
	 *
	 * @return array()
	 * {
	 *	['tabName'] = "" , // tab name
	 *	['nextstepid'] = ""				// show tab
	 * ['tabPlace']  =''  // require in future
	 * ['html to return']
	 * }
	 * */

	/**
	 * [Forrently used for only pickup date description]
	 *
	 * @param   [type]  $cartData  [description]
	 *
	 * @return  [type]             [description]
	 */
	/*public function qtcaddTabOnCheckoutPage($cartData)
	{
		return '';
	}*/

	/****** --------------------------- Checkout releated  --------------------------- */

	/**
	 * Function used as a trigger after saving the checkout detail
	 *
	 * @param   [type]  $orderid  [order id]
	 * @param   [type]  $post     [Checkout page post data]
	 *
	 * @return  [type]            [return modified post data]
	 */
	/*public function qtcAfterCheckoutDetailSave($orderid, $post)
	{
	}*/

	/** corrently used for only pickup date
	 * @param : orderid  -
	 * @param : orderInfo  - order info details
	 * @param : orderitems  - orderItem info details
	 *
	 * @return array()
	 *  {
	 * ['tabPlace']  =''  // require in future
	 * ['html to return']
	 * }
	 * */

	/**
	 * [Ccorrently used for only pickup date description]
	 *
	 * @param   integer  $orderid     [description]
	 * @param   array    $orderInfo   [description]
	 * @param   array    $orderitems  [description]
	 *
	 * @return  [type]            [return]
	 */
	/*public function addHtmlOnOrderDetailPage($orderid = 0, $orderInfo = array(), $orderitems = array())
	{
	}*/

	/****** --------------------------- Add prodct releated  --------------------------- */

	/**
	 * This trigger will be called after saving product description
	 *
	 * @param   Integer  $item_id     Item id - unique product id from com_quick2cart component
	 * @param   Array    $att_detail  Attribute detail
	 * @param   String   $sku         Stock keeping unit
	 * @param   String   $client      Client like com_zoo,com_k2,com_content,com_quick2cart etc
	 *
	 * @return  ''
	 */
	/*public function OnAfterq2cProductSave($item_id, $att_detail, $sku, $client)
	{
	}*/

	/**
	 * This trigger will be called before saving basic detail
	 *
	 * @param   object  $itempost  Object of post.
	 * @param   string  $action    action while insert or update object.
	 *
	 * @return  boolean
	 *
	 * @since   2.2.2
	 */
	/*public function beforeSavingProductBasicDetail($itempost, $action)
	{
	}*/

	/**
	 * This trigger will be called before saving single attribute.
	 *
	 * @param   Array  $att_detail  Attribute detail array along with option.
	 *
	 * @return  Array  Modified Attribute details.
	 *
	 * @since   2.2.4
	 */
	public function OnBeforeq2cAttributeSave($att_detail)
	{
	/*$att_detail contails
			Array
			(
				[attri_name] => color
				[attri_id] => 17
				[fieldType] => Select
				[iscompulsary_attr] => on
				[attri_opt] => Array
					(
						[0] => Array
							(
								[id] => 25
								[name] => Yellow
								[prefix] => +
								[currency] => Array
									(
										[USD] => 0.00
									)

								[order] => 1
							)

						[1] => Array
							(
								[id] => 26
								[name] => red
								[prefix] => +
								[currency] => Array
									(
										[USD] => 0.00
									)

								[order] => 2
							)


					)

				[item_id] => 15
			)
		*/
	}

	/**
	 * This trigger is called while changing the steps from checkout page]
	 *
	 * @return  [type]            [return]
	 */
	/*public function OnAfterQ2cStepChange()
	{
		return'';
	}*/
}
