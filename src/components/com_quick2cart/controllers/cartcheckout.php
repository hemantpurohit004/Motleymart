<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

jimport('joomla.application.component.controller');

/**
 * Cartcheckout controller class.
 *
 * @since  1.0.0
 */
class Quick2cartControllercartcheckout extends Quick2cartController
{
	/**
	 * Function used to set cookie
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function setCookieCur()
	{
		$jinput     = JFactory::getApplication()->input;
		$data       = $jinput->post;
		$calledFrom = $jinput->get("view", "", "STRING");
		$multi_curr = $data->get('multi_curr');
		$expire     = time() + 60 * 60 * 24 * 7;
		setcookie("qtc_currency", $multi_curr, $expire, "/");
		$qtc_current_url = $data->get('qtc_current_url', '', 'RAW');

		if (!empty($qtc_current_url))
		{
			$link = $qtc_current_url;
		}
		else
		{
			$comquick2cartHelper = new comquick2cartHelper;

			// Get Item ID
			$iId              = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=' . $calledFrom);

			if ($calledFrom == "cart")
			{
				$link = JUri::root()
				. substr(JRoute::_('index.php?option=com_quick2cart&view=cart&tmpl=component&Itemid=' . $iId, false), strlen(JUri::base(true)) + 1);
			}
			else
			{
				$link = JUri::root() . substr(JRoute::_('index.php?option=com_quick2cart&view=cartcheckout&Itemid=' . $iId, false), strlen(JUri::base(true)) + 1);
			}
		}

		$this->setRedirect($link);
	}

	/**
	 * Function used to clear coupon
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function clearcop()
	{
		$jinput  = JFactory::getApplication()->input;
		$session = JFactory::getSession();
		$cops    = $session->get('coupon');
		$session->clear('coupon');
	}

	/**
	 * Function used to get coupon
	 *
	 * @return  json
	 *
	 * @since  1.0.0
	 */
	public function isExistPromoCode()
	{
		$jinput  = JFactory::getApplication()->input;
		$session = JFactory::getSession();
		$user    = JFactory::getUser();
		$db      = JFactory::getDBO();
		$coupon_code  = $jinput->get('coupon_code', '', 'STRING');
		$count   = '';
		$model   = $this->getModel('cartcheckout');
		$applicablePromotions   = $model->getPromoCoupon($coupon_code);

		if (!empty($applicablePromotions))
		{
			$couponList[0]                 = array("code" => $coupon_code);
			$session->set('coupon', $couponList);
			$session->set('one_pg_ckout_tab_state', 'qtc_cart');

			echo json_encode($couponList);
		}
		else
		{
			echo 0;
		}

		jexit();
	}

	/**
	 * Function used to get coupon
	 *
	 * @return  json
	 *
	 * @since  1.0.0
	 */
	public function getcoupon()
	{
		$jinput  = JFactory::getApplication()->input;
		$session = JFactory::getSession();
		$user    = JFactory::getUser();
		$db      = JFactory::getDBO();
		$c_code  = $jinput->get('coupon_code', '', 'STRING');
		$count   = '';
		$model   = $this->getModel('cartcheckout');

		// $count = '1';
		$count   = $model->getcoupon($c_code);

		if (!empty($count))
		{
			$copItems            = isset($count[0]->item_id) ? $count[0]->item_id : '';
			$c[]                 = array(
				"code" => $c_code,
				"value" => $count[0]->value,
				"val_type" => $count[0]->val_type,
				"item_id" => $copItems
			);

			$cop                 = $session->get('coupon');
			$cop_flag            = 0;
			$Quick2cartModelcart = new Quick2cartModelcart;
			$cart_itemIds        = $Quick2cartModelcart->getCartItemIds();

			if (!empty($cop))
			{
				foreach ($cop as $key => $copn)
				{
					// Avoid duplicate coupon
					// If($copn['value'] == $count[0]->value && $copn['code']==$c[0]['code']){
					if ($copn['code'] == $c[0]['code'])
					{
						// $cop_flag= 1;
						// Unset first coupon and keep it as latest (change order of coupon code)
						unset($cop[$key]);
						break;
					}

					// Apply only latest coupon on single item
					if (!empty($copn['item_id']))
					{
						$copitemsCart = array_intersect($copn['item_id'], $cart_itemIds);

						foreach ($copitemsCart as $item)
						{
							if (in_array($item, $c[0]['item_id']))
							{
								// If($copn['value'] < $c[0]['value'] )
								unset($cop[$key]);
							}
						}
					}
				}
			}

			// If($cop_flag== 0)
			$cop[0] = $c[0];
			$session->set('coupon', $cop);

			// For setting current tab status one page chkout::
			$session = JFactory::getSession();
			$session->set('one_pg_ckout_tab_state', 'qtc_cart');

			echo json_encode($c);
		}
		else
		{
			echo 0;
		}

		jexit();
	}

	/**
	 * Function used to save
	 *
	 * @return  Array
	 *
	 * @since  1.0.0
	 */
	public function save()
	{
		$model               = $this->getModel('cartcheckout');
		$jinput              = JFactory::getApplication()->input;
		$post                = $jinput->post;
		$orderDetail             = $model->store();
		$orderid = $orderDetail['order_id'];
		$data                = array();
		$comquick2cartHelper = new comquick2cartHelper;
		$itemid              = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=cartcheckout');

		// If($orderid && $orderid!=-1 && $orderid!=-2 )
		if ($orderid > 0)
		{
			// Vm: SESSION ORDERID USED in payment process
			$this->setOrderINsession($orderid);

			// Vm: REMOVING SESSIION VARIABLE WHICH IS USED FOR 1-PAGE-CKOUT TAB
			$session = JFactory::getSession();
			$session->set('one_pg_ckout_tab_state', '');
			$session->set('one_pg_ckoutMethod', '');

			// $tmp =$this->getorderHTML($orderid,$data,$post['gateways']);
			$orderDetail['success_msg'] = JText::_('CONFIG_SAV');
			$orderDetail['success']     = 1;
			$orderDetail['order_id']    = $orderid;
			$orderDetail                = $this->getorderHTML($orderid, $orderDetail);

			// $this->setRedirect( 'index.php?option=com_quick2cart&view=cartcheckout&layout=payment&Itemid='.$itemid, $msg );
		}
		else
		{
			$orderDetail['success']      = 0;
			$orderDetail['redirect_uri'] = 'index.php?option=com_quick2cart&view=cartcheckout&Itemid=' . $itemid;
		}

		return $orderDetail;
	}

	/**
	 * Function used to redirect on cancel
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function cancel()
	{
		$msg    = JText::_('Operation Cancelled');
		$jinput = JFactory::getApplication()->input;
		$itemid = $jinput->get('Itemid');
		$this->setRedirect('index.php', $msg);
	}

	/**
	 * Function used to load states
	 *
	 * @return  json
	 *
	 * @since  1.0.0
	 */
	public function loadState()
	{
		$app       = JFactory::getApplication();
		$db        = JFactory::getDBO();
		$jinput    = JFactory::getApplication()->input;
		$country   = $jinput->get('country', '', 'STRING');
		$model     = $this->getModel('cartcheckout');
		$stateList = $model->getuserState($country);
		echo json_encode($stateList);
		jexit();
	}

	/**
	 *Function used to set currency session
	 *
	 * @param   STRING  $cur  Currency
	 *
	 * @return  json
	 *
	 * @since  1.0.0
	 */
	public function setCurrencySession($cur = null)
	{
		$jinput = JFactory::getApplication()->input;

		if ($cur == null || !$curr)
		{
			$curr = $jinput->get('currency');
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$oldcurrency         = $comquick2cartHelper->getCurrencySession();
		$model               = $this->getModel('cart');
		$return              = $model->syncCartCurrency($oldcurrency, $curr);
		/*if($return==true)                // synchronise with new currency
		setcookie('qtc_currency',$curr,time() + (86400 * 7)); // 86400 = 1 day
		*/
		echo $return;
		jexit();
	}

	/**
	 * Function used to calculate final price
	 *
	 * @param   Obj  $ipdata  idata should in formats tdClass Object ( [totalprice] => 174.375 [country] => Bangladesh [region] => Dhaka [city] => punt )
	 *
	 * @return  json
	 *
	 * @since  1.0.0
	 */
	public function calFinalShipPrice($ipdata = '')
	{
		$jinput = JFactory::getApplication()->input;

		// If called from Ajax
		if (empty($ipdata))
		{
			$postdata = $jinput->post();
			$jsondata = $postdata->get('data', '', 'STRING');
			$data     = json_decode($jsondata);
		}
		else
		{
			$data = $ipdata;
		}

		// Call model
		$model         = $this->getModel('cartcheckout');
		$finalshipdata = $model->getFinalShipPrice($data);

		// If not called from Ajax
		if (!empty($ipdata))
		{
			return $finalshipdata;
		}

		$comquick2cartHelper       = new comquick2cartHelper;
		$finalshipdata['charges']  = $comquick2cartHelper->getFromattedPrice(number_format($finalshipdata['charges'], 2));
		$finalshipdata['totalamt'] = $comquick2cartHelper->getFromattedPrice(number_format($finalshipdata['totalamt'], 2));
		echo $json_ship_data = json_encode($finalshipdata);
		jexit();
	}

	/**
	 * Function used to check bill mail
	 *
	 * @return  json
	 *
	 * @since  1.0.0
	 */
	public function chkbillmail()
	{
		$jinput = JFactory::getApplication()->input;
		$email  = $jinput->get('email', '', 'STRING');
		$model  = $this->getModel('cartcheckout');
		$status = $model->checkbillMailExists($email);

		$e[]    = $status;

		if ($status == 1)
		{
			$e[] = JText::_('QTC_BILLMAIL_EXISTS');
		}

		echo json_encode($e);
		jexit();
	}

	/**
	 *Function used to get Oder HTML
	 *
	 * @param   INT  $order_id  Order ID
	 * @param   INT  $data      Order data
	 *
	 * @return  Array
	 *
	 * @since  1.0.0
	 */
	public function getorderHTML($order_id, $data)
	{
		$comquick2cartHelper = new comquick2cartHelper;
		$order               = $orderWholeDetail = $comquick2cartHelper->getOrderInfo($order_id);
		$this->payhtml       = "";

		// Paymodel is uesed in included layout
		JLoader::import('payment', JPATH_SITE . '/components/com_quick2cart/models');
		$paymodel = new Quick2cartModelpayment;

		if (!empty($order))
		{
			$this->order_authorized = 1;

			if (is_array($order))
			{
				$this->orderinfo  = $order['order_info'];
				$this->orderitems = $order['items'];

				/* $payhtml = $model->getpayHTML($order['order_info'][0]->processor,$order_id);
				if($this->orderinfo[0]->processor=="FreeCheckout")
				{
				$plgname = JText::_('COM_QUICK2CART_FREE_CHCKOUT');
				$this->orderinfo[0]->processor = JText::_('COM_QUICK2CART_FREE_CHCKOUT');
				@$this->payhtml=$this->getFreeOrderHtml($order_id);
				}
				else
				{
				$payhtml = $paymodel->getHTML($this->orderinfo[0]->processor,$order_id);
				@$this->payhtml=$payhtml[0];
				}*/
				$data['payhtml']  = $this->payhtml; // used in payment.php
			}
			elseif ($order == 0)
			{
				$this->undefined_orderid_msg = 1;
			}
		}
		else
		{
			$this->noOrderDetails = 1;
		}

		$orders_site           = '1';
		$this->orders_site     = $orders_site;
		$user                  = JFactory::getUser();
		$this->qtcSystemEmails = 1;

		$app  = JFactory::getApplication();
		$view = $comquick2cartHelper->getViewpath('cartcheckout', 'ordersummary');
		ob_start();
		include $view;
		$html = ob_get_contents();
		ob_end_clean();
		$data['orderHTML'] = $html;

		return $data;
	}

	/**
	 * Function used to set session
	 *
	 * @param   INT  $orderid  Order ID
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function setOrderINsession($orderid)
	{
		$session = JFactory::getSession();

		// $session->set('final_amt',$data['final_amt_pay_inputbox']);
		$session->set('order_id', $orderid);
	}

	/**
	 * Function used to set check out method
	 *
	 * @return  json
	 *
	 * @since  1.0.0
	 */
	public function setCheckoutMethod()
	{
		$jinput  = JFactory::getApplication()->input;
		$regType = $jinput->get("regType", '', "RAW");

		// For setting current tab status one page chkout::
		$session = JFactory::getSession();
		$session->set('one_pg_ckoutMethod', $regType);

		echo 1;
		jexit();
	}

	/**
	 * Function used to get Free irder HTML
	 *
	 * @param   INT  $order_id  Order ID
	 *
	 * @return  STRING
	 *
	 * @since  1.0.0
	 */
	public function getFreeOrderHtml($order_id)
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);

		// $path = JPATH_SITE.DS.'components'.DS.'com_quick2cart'.DS.'views'.DS.'cartcheckout'.DS.'tmpl'.DS.'freeorder.php';

		// CHECK for view override
		$comquick2cartHelper = new comquick2cartHelper;
		$path                = $comquick2cartHelper->getViewpath('cartcheckout', 'freeorder', 'SITE', 'SITE');

		ob_start();
		include $path;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Function used to process free orders
	 *
	 * @return  void
	 *
	 * @since  1.0.0
	 */
	public function processFreeOrder()
	{
		$jinput              = JFactory::getApplication()->input;
		$comquick2cartHelper = new comquick2cartHelper;
		$db                  = JFactory::getDBO();
		$user                = JFactory::getUser();
		$post                = $jinput->post;
		$orderid             = $post->get('orderid', '', 'STRING');
		$guest_email         = '';

		if (!empty($orderid))
		{
			$query = "SELECT `amount`,`email` FROM `#__kart_orders` where `id`=" . $orderid;
			$db->setQuery($query);
			$orderDetail = $db->loadAssoc();
			$orderPrice  = (int) $orderDetail['amount'];

			if (empty($orderPrice))
			{
				if (empty($user->id) && $orderDetail['email'])
				{
					$guest_email = "&email=" . md5($orderDetail['email']);
				}
				// CONFORM ONLY 0 PRICE ORDER
				$comquick2cartHelper->updatestatus($orderid, 'C', $comment = '', $send_mail = 1, $store_id = 0);
			}
		}

		global $mainframe;
		$mainframe = JFactory::getApplication();

		$orderItemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=orders');
		$orderLink = 'index.php?option=com_quick2cart&view=orders&layout=order&orderid=' . $orderid . '&Itemid=' . $orderItemid . $guest_email;
		$link        = JUri::base() . substr(JRoute::_($orderLink, false), strlen(JUri::base(true)) + 1);

		$mainframe->redirect($link);
	}

	/**
	 * This function save checkout data
	 *
	 * @return  json
	 *
	 * @since  1.0.0
	 */
	public function qtc_autoSave()
	{
		$params                        = JComponentHelper::getParams('com_quick2cart');
		$isShippingEnabled             = $params->get('shipping', 0);
		$shippingMode = $params->get('shippingMode', 'itemLevel');
		$mainframe                     = JFactory::getApplication();
		$input                         = JFactory::getApplication()->input;
		$session                       = JFactory::getSession();
		$post                          = $input->post;
		$model                         = $this->getModel('cartcheckout');
		$stepId                        = $input->get('stepId', '', 'STRING');
		$retdata                       = array();
		$retdata['stepId']             = $stepId;
		$retdata['payAndReviewHtml']   = '';
		$retdata['camp_id']            = '';
		$retdata['sa_sentApproveMail'] = '';
		$retdata['Itemid']             = '';
		$comquick2cartHelper           = new comquick2cartHelper;

		// Trigger: this trigger is called while changing the steps from checkout page
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin("system");
		$result = $dispatcher->trigger("OnAfterQ2cStepChange");

		$Quick2cartControllercartcheckout = new Quick2cartControllercartcheckout;
		$nextstep = '';

		switch ($stepId)
		{
			case "qtc_cartDetails":
				$nextstep = "fetchBillData";
			break;

			case "qtc_billing":

				if ($isShippingEnabled == 1)
				{
					// If order level shippin mode then place order. (No ned to fetch ship detail)
					if ($shippingMode == "orderLeval")
					{
						$nextstep = "fetchPayNdReviewData";
					}
					else
					{
						$nextstep = "fetchShipData";
					}
				}
				else
				{
					$nextstep = "fetchPayNdReviewData";
				}

			break;

			case "qtc_shippingStep":
				$nextstep = "fetchPayNdReviewData";
			break;
		}

		if ($nextstep == 'fetchBillData')
		{
			// Already fetched and rendered on form
		}

		// Clicked on billing
		if ($nextstep == 'fetchShipData')
		{
			$qtcshiphelper              = new qtcshiphelper;
			$modelsPath = JPATH_SITE . '/components/com_quick2cart/models/customer_addressform.php';
			$customer_addressform_model = $comquick2cartHelper->loadqtcClass($modelsPath, "Quick2cartModelCustomer_AddressForm");
			$helperPath = JPATH_SITE . '/components/com_quick2cart/helpers/createorder.php';
			$createOrderHelper = $comquick2cartHelper->loadqtcClass($helperPath, "CreateOrderHelper");

			$shippingDetails = new stdclass;
			$shipping = $input->get('shipping_address', '', 'INT');
			$billing = $input->get('billing_address', '', 'INT');

			if (!empty($shipping))
			{
				$shippingDetails->ship = $customer_addressform_model->getAddress($shipping);
				$shippingDetails->ship = $createOrderHelper->mapUserAddress($shippingDetails->ship);
			}
			else
			{
				// For guest checkout
				$shippingDetails->ship = $post->get("ship", '', "ARRAY");
			}

			if (!empty($billing))
			{
				$shippingDetails->bill = $customer_addressform_model->getAddress($billing);
				$shippingDetails->bill = $createOrderHelper->mapUserAddress($shippingDetails->bill);
			}
			else
			{
				// For guest checkout
				$shippingDetails->bill = $post->get("bill", '', "ARRAY");
			}

			$itemWiseShipDetail = $qtcshiphelper->getCartItemsShiphDetail($shippingDetails);
			$shippingHtml = $qtcshiphelper->getShipMethodHtml($itemWiseShipDetail);

			$retdata['shipMethoDetail'] = $shippingHtml;
		}

		// Save ad qtc_billing data
		if ($nextstep == 'fetchPayNdReviewData')
		{
			$response                    = $Quick2cartControllercartcheckout->save();

			if ($response['success'] == 0)
			{
				$retdata['shippingNotAvailable'] = $response['success_msg'];
			}
			else
			{
				$retdata['payAndReviewHtml'] = !empty($response['orderHTML']) ? $response['orderHTML'] : '';
				$retdata['order_id']         = !empty($response['order_id']) ? $response['order_id'] : 0;
			}
		}

		echo json_encode($retdata);
		jexit();
	}

	/**
	 * Function used to get gateway HTML
	 *
	 * @return  json
	 *
	 * @since  1.0.0
	 */
	public function qtc_gatewayHtml()
	{
		// TODO :- Remove this function if not used anywhere
		$db     = JFactory::getDBO();
		$jinput = JFactory::getApplication()->input;

		$model           = $this->getModel('payment');
		$selectedGateway = $jinput->get('gateway', '');
		$order_id        = $jinput->get('order_id', '');
		$return          = '';

		if (!empty($selectedGateway) && !empty($order_id))
		{
			// Add selected payment gateway name against order
			$status = $model->updateOrderGateway($selectedGateway, $order_id);

			if ($status)
			{
				$payhtml = $model->getHTML($selectedGateway, $order_id);
				$return  = !empty($payhtml[0]) ? $payhtml[0] : '';
			}
		}

		echo $return;
		jexit();
	}

	/**
	 * Function used to get gateway HTML
	 *
	 * @param   STRING  $selectedGateway  selected gateway name
	 *
	 * @param   STRING  $order_id         order id
	 *
	 * @return  json
	 *
	 * @since  1.0.0
	 */
	public function qtc_singleGatewayHtml($selectedGateway, $order_id)
	{
		$model           = $this->getModel('payment');
		$status = $model->updateOrderGateway($selectedGateway, $order_id);

		if ($status)
		{
			$payhtml = $model->getHTML($selectedGateway, $order_id);
			$return  = !empty ($payhtml[0]) ? $payhtml[0] : '';
		}

		return $return;
	}

	/**
	 * Function: updatecart updates the cart and also calculates the tax and shipping charges
	 *
	 * @return  json
	 *
	 * @since  1.0.0
	 */
	public function update_cart_item()
	{
		$comquick2cartHelper           = new comquick2cartHelper;
		$input = JFactory::getApplication()->input;
		$post = $input->post;

		$cart_item_id = $post->get('cart_item_id', '', 'INT');
		$item_id = $post->get('item_id', '', 'INT');

		// Get parsed form data
		parse_str($post->get('formData', '', 'STRING'), $formData);

		/* Load cart model
		$item = Array
					(
						[id] => 17
						[parent] => com_quick2cart
						[item_id] => 8 if item_id present then no need of id and parent fields
						[count] => 1
						[options] => 24,23,22,19
					)
					<pre>$userdata = Array
									(
										[23] => Array
											(
												[itemattributeoption_id] => 23
												[type] => Textbox
												[value] => qqq
											)

										[24] => Array
											(
												[itemattributeoption_id] => 24
												[type] => Textbox
												[value] => www
											)

									)
					* */

		$itemFromattedDet = array();
		$itemFromattedDet['item_id'] = $item_id;
		$userdata = array();

		if (!empty($formData['cartDetail']) && !empty($cart_item_id))
		{
			$newItemDetails = $formData['cartDetail'][$cart_item_id];
			$itemFromattedDet['count'] = $newItemDetails['cart_count'];
			$itemFromattedDet['options'] = '';

			// If not empty attribute details
			if (!empty($newItemDetails['attrDetail']))
			{
				$attrDetail = $newItemDetails['attrDetail'];

				foreach ($attrDetail as $key => $attr)
				{
					if ($attr['type'] == 'Textbox' && !empty($attr['value']))
					{
						$userkey = $attr['itemattributeoption_id'];
						$userdata[$userkey] = $attr;
						$itemFromattedDet['options'] .= $attr['itemattributeoption_id'] . ',';
					}
					else
					{
						$itemFromattedDet['options'] .= $attr['value'] . ',';
					}
				}
			}
		}

		$path                = JPATH_SITE . "/components/com_quick2cart/models/cart.php";
		$comquick2cartHelper->loadqtcClass($path, 'Quick2cartModelcart');
		$Quick2cartModelcart = new Quick2cartModelcart;

		// Remove last comma
		if (!empty($itemFromattedDet['options']))
		{
			$tempArray = explode(',', $itemFromattedDet['options']);
			$tempArray = array_filter($tempArray, "strlen");
			$itemFromattedDet['options'] = implode(',', $tempArray);
		}

		// Get formated product details  (internal)
		$prod_details = $Quick2cartModelcart->getProd($itemFromattedDet);

		// If option present
		if (!empty($prod_details[1]))
		{
			// Add user field detail to options
			$AttrOptions     = $prod_details[1];
			$prod_details[1] = $comquick2cartHelper->AddUserFieldDetail($AttrOptions, $userdata);
		}

		// Update the cart
		$result = $Quick2cartModelcart->putCartitem('', $prod_details, $cart_item_id);

		// Validate Result. If added successfully.
		if (is_numeric($result) && $result == 1)
		{
			$msg['status']      = true;
			$msg['successCode'] = 1;

			// $msg['message'] = JText::sprintf('COM_QUICK2CART_COMPACT_MSG', $prod_details[0]['name'], $item_count);
		}
		else
		{
			$msg['status']     = false;
			$msg['successCode'] = 0;
			$msg['message']     = $result;
		}

		echo json_encode($msg);
		jexit();
	}
}
