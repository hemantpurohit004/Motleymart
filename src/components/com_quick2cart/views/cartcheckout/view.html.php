<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

jimport('joomla.application.component.view');


/**
 * This Class supports checkout process.
 *
 * @package     Joomla.Site
 * @subpackage  com_quick2cart
 * @since       1.0
 */
class Quick2cartViewcartcheckout extends JViewLegacy
{
	/**
	 * Render view.
	 *
	 * @param   array  $tpl  An optional associative array of configuration settings.
	 *
	 * @since   1.0
	 * @return   null
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();
		$mainframe = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$session = JFactory::getSession();

		$comquick2cartHelper = new comquick2cartHelper;
		$modelsPath = JPATH_SITE . '/components/com_quick2cart/models/customer_addressform.php';
		$customer_addressform_model = $comquick2cartHelper->loadqtcClass($modelsPath, "Quick2cartModelCustomer_AddressForm");

		if (!empty($user->id))
		{
			$this->addressesListHtml = $customer_addressform_model->getUserAddressList($user->id);
		}

		require_once JPATH_SITE . '/components/com_quick2cart/helpers/media.php';

		// Create object of media helper class
		$this->media = new qtc_mediaHelper;
		$model = $this->getModel('cartcheckout');
		$layout = $input->get('layout', '');

		// Send to joomla's registration of guest ckout is off
		if ($layout == 'cancel' || $layout == 'orderdetails')
		{
			$input->set('remote', 1);
			$sacontroller = new quick2cartController;
			$sacontroller->execute('clearcart');
		}
		else
		{
			$params = $this->params = JComponentHelper::getParams('com_quick2cart');
			$guestcheckout = $params->get('guest');

			if ($guestcheckout == 0 && !($user->id))
			{
				$itemid = $input->get('Itemid');

				// $uri=JRoute::_('index.php?option=com_quick2cart&view=cartcheckout&Itemid=' . $itemid,false);
				$rurl = 'index.php?option=com_quick2cart&view=cartcheckout&Itemid=' . $itemid;
				$returnurl = base64_encode($rurl);
				$mainframe->redirect(JRoute::_('index.php?option=com_users&return=' . $returnurl, false), $msg);
			}

			// GETTING CART ITEMS
			JLoader::import('cart', JPATH_SITE . '/components/com_quick2cart/models');
			$cartCheckoutModel = new Quick2cartModelcartcheckout;
			$cart = $cartCheckoutModel->getCheckoutCartitemsDetails();
			$this->cart = $cart;

			// Get promtion discount
			$path = JPATH_SITE . '/components/com_quick2cart/helpers/promotion.php';

			if (!class_exists('PromotionHelper'))
			{
				JLoader::register('PromotionHelper', $path);
				JLoader::load('PromotionHelper');
			}

			$promotionHelper = new PromotionHelper;
			$this->coupon = $promotionHelper->getSessionCoupon();
			$this->promotions = $promotionHelper->getCartPromotionDetail($this->cart, $this->coupon);

			if ($user->id != 0)
			{
				$userdata = $model->userdata();
				$this->userdata = $userdata;
			}

			if ($layout == 'payment')
			{
				$orders_site = '1';
				$orderid = $session->get('order_id');

				$order = $comquick2cartHelper->getorderinfo($orderid);

				if (!empty($order))
				{
					if (is_array($order))
					{
						$this->orderinfo = $order['order_info'];
						$this->orderitems = $order['items'];
					}
					elseif ($order == 0)
					{
						$this->undefined_orderid_msg = 1;
					}

					// $payhtml = $model->getpayHTML($order['order_info'][0]->processor,$orderid);
					JLoader::import('payment', JPATH_SITE . '/components/com_quick2cart/models');
					$paymodel = new Quick2cartModelpayment;
					$payhtml = $paymodel->getHTML($order['order_info'][0]->processor, $orderid);
					$this->payhtml = $payhtml[0];
				}
				else
				{
					$this->undefined_orderid_msg = 1;
				}

				$orders_site = '1';
				$this->orders_site = $orders_site;

				// Make cart empty
				JLoader::import('cart', JPATH_SITE . '/components/com_quick2cart/models');
				$Quick2cartModelcart = new Quick2cartModelcart;
				$Quick2cartModelcart->empty_cart();
			}
			else
			{
				// START Q2C Sample development
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('system');

				// Call the plugin and get the result
				$result = $dispatcher->trigger('onQuick2cartBeforeCheckoutCartDisplay');
				$beforecart = '';

				if (!empty($result))
				{
					$beforecart .= trim(implode(" \n ", $result));
				}

				// Depricated start
				$result = $dispatcher->trigger('OnBeforeq2cCheckoutCartDisplay');

				if (!empty($result))
				{
					$beforecart .= trim(implode(" \n ", $result));
				}

				// Depricated end  //////////////////////////////////////////////////////////////
				$this->beforecart = $beforecart;
				$result = $dispatcher->trigger('OnAfterq2cCheckoutCartDisplay');
				$aftercart = '';

				if (!empty($result))
				{
					$aftercart .= trim(implode(" \n ", $result));
				}

				$result = $dispatcher->trigger('OnAfterq2cCheckoutCartDisplay');

				// Depricated start

				if (!empty($result))
				{
					$aftercart .= trim(implode(" \n ", $result));
				}

				// Depricated end  //////////////////////////////////////////////////////////////

				$this->aftercart = $aftercart;

				// END Q2C Sample development
				// Q2C Sample development - ADD TAB in ckout page
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('system');
				$result = $dispatcher->trigger('qtcaddTabOnCheckoutPage', array($this->cart));
				$this->addTab = '';
				$this->addTabPlace = '';

				if (!empty($result))
				{
					$this->addTab = $result[0];
					$this->addTabPlace = !empty($result[0]['tabPlace']) ? $result[0]['tabPlace'] : '';
				}
				// END - Q2C Sample development - ADD TAB in ckout page
				// Trigger plg to add plg after shipping tab

				// GETTING country
				$country = $this->get("Country");
				$this->country = $country;
			}

			// Getting GETWAYS
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('payment');

			// $params->get( 'gateways' ) = array('0' => 'paypal','1'=>'Payu');

			if (!is_array($params->get('gateways')) )
			{
				$gateway_param[] = $params->get('gateways');
			}
			else
			{
				$gateway_param = $params->get('gateways');
			}

			if (!empty($gateway_param))
			{
				$gateways = $dispatcher->trigger('onTP_GetInfo', array($gateway_param));
			}

			$this->gateways = $gateways;

			// START Q2C Sample development
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('system');

			// Call the plugin and get the result
			$result = $dispatcher->trigger('OnSystemBeforeDisplayingPaymentList', array($this->gateways, $this->cart));

			if (!empty($result[0]))
			{
				$beforecart = trim(implode(" \n ", $result));
				$this->gateways = $result[0];
			}

			// START Q2C Sample development
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('system', "qtcamazon_easycheckout");

			/* Call the plugin and get the result
			$results = $dispatcher->trigger('onATP_processIOPN');
			*/
			$results = $dispatcher->trigger('onBeforeCheckoutViewDisplay');
			$this->onBeforeCheckoutViewDisplay = '';

			if (!empty($results))
			{
				$this->onBeforeCheckoutViewDisplay = trim(implode(" \n ", $results));
			}
		}

		$this->_setToolBar();
		parent::display($tpl);
	}

/**
 * Method Allow to set toolbar.
 *
 * @return  ''
 */
	private function _setToolBar()
	{
		// Added by aniket for task #25690
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('QTC_CARTCHECKOUT_PAGE'));
	}
}
