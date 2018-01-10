<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Order controller class.
 *
 * @since  1.6
 */
class Quick2cartControllerOrders extends quick2cartController
{
	/**
	 * Constructor
	 *
	 * @since    1.6
	 */
	public function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask('add', 'edit');
		$this->siteMainHelper = new comquick2cartHelper;
		$this->vdashboardItemid = $this->siteMainHelper->getitemid('index.php?option=com_quick2cart&view=vendor');

		// Buyer view
		$this->myOrdersItemId = $this->siteMainHelper->getitemid('index.php?option=com_quick2cart&view=orders');
	}

	/**
	 * Method to save/update the order status.
	 *
	 * @return  void
	 *
	 * @since    1.0
	 */
	public function save()
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);
		$model  = $this->getModel('orders');
		$jinput = JFactory::getApplication()->input;
		$layout = $jinput->get('layout', '', "STRING");
		$orderid = $jinput->get('orderid');

		$post     = $jinput->post;

		// For list view
		$store_id = $post->get('store_id');

		if (empty($store_id))
		{
			// For order detail view
			$store_id = $jinput->get('store_id', '', "INTEGER");
		}

		$model->setState('request', $post);
		$result = $model->store($store_id);

		if ($result == 1)
		{
			$msg = JText::_('QTC_FIELD_SAVING_MSG');
		}
		elseif ($result == 3)
		{
			$msg = JText::_('QTC_REFUND_SAVING_MSG');
		}
		else
		{
			$msg = JText::_('QTC_FIELD_ERROR_SAVING_MSG');
		}

		if ($layout == "storeorder")
		{
			$link = 'index.php?option=com_quick2cart&view=orders&layout=storeorder';
		}
		elseif ($layout == "customerdetails")
		{
			$link = 'index.php?option=com_quick2cart&view=orders&layout=customerdetails&orderid=' . $orderid . '&store_id=' . $store_id;
		}
		else
		{
			$link = 'index.php?option=com_quick2cart&view=orders';
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * Method to update the order status and comment from **order detail page**.
	 *
	 * @return  void
	 *
	 * @since    1.0
	 */
	public function updateStoreItemStatus()
	{
		$jinput = JFactory::getApplication()->input;
		$layout = $jinput->get('layout', '', "STRING");
		$post     = $jinput->post;
		$store_id = $jinput->get('store_id', '', "INTEGER");
		$orderid = $jinput->get("orderid", '', "INTEGER");

		// $comment             = $data->get('comment', '', "STRING");
		$add_note_chk = $post->get('add_note_chk');
		$note         = '';
		$note = $post->get('order_note', '', "STRING");
		$status = $jinput->get('status', '', "STRING");
		$notify_chk = $post->get('notify_chk');

		if (!empty($notify_chk))
		{
			$notify_chk = 1;
		}
		else
		{
			$notify_chk = 0;
		}

		if ($orderid && $store_id)
		{
			// Update item status
			$this->siteMainHelper->updatestatus($orderid, $status, $note, $notify_chk, $store_id);

			// Save order history
			$orderItemsStr = $post->get("orderItemsStr", '', "STRING");
			$orderItems = explode("||", $orderItemsStr);

			foreach ($orderItems as $oitemId)
			{
				// Save order item status history
				$this->siteMainHelper->saveOrderStatusHistory($orderid, $oitemId, $status, $note, $notify_chk);
			}
		}

		// $layout == "order"
		$rLink = "index.php?option=com_quick2cart&view=orders&layout=order&orderid=";
		$link = JRoute::_($rLink . $orderid . '&store_id=' . $store_id . '&calledStoreview=1&Itemid' . $this->vdashboardItemid, false);

		$this->setRedirect($link, $msg);
	}

	/**
	 * Used to change store order status
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	public function changeStoreOrderStatus()
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);
		$model  = $this->getModel('orders');
		$jinput = JFactory::getApplication()->input;
		$post   = $jinput->post;
		$model->setState('request', $post);
		$store_id = $post->get('current_store', '', 'STRING');

		// Call model function
		//  1 for change store product status
		$result = $model->store($store_id);

		if ($result == 1)
		{
			$msg = JText::_('QTC_FIELD_SAVING_MSG', true);
		}
		elseif ($result == 3)
		{
			$msg = JText::_('QTC_REFUND_SAVING_MSG', true);
		}
		else
		{
			$msg = JText::_('QTC_FIELD_ERROR_SAVING_MSG', true);
		}

		if (!empty($store_id))
		{
			$link = "index.php?option=com_quick2cart&view=orders&layout=storeorder&change_store=" . $store_id;
		}
		else
		{
			$link = 'index.php?option=com_quick2cart&view=orders';
		}

		$this->setRedirect($link, $msg);
	}

	/**
	 * Called on cancel button
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	public function cancel()
	{
		$msg = JText::_('CCK_FIELD_CANCEL_MSG', true);
		$this->setRedirect('index.php?option=com_quick2cart', $msg);
	}

	/**
	 * Function for Resending invoice to buyer
	 *
	 * @since   2.2.2
	 * @return   null.
	 */
	public function resendInvoice()
	{
		$params             = JComponentHelper::getParams('com_quick2cart');
		$multivendor_enable = $params->get('multivendor');
		$app     = JFactory::getApplication();
		$jinput  = $app->input;
		$orderid = $jinput->get('orderid', '', 'INT');
		$store_id = $jinput->get('store_id', '', 'INT');
		$comquick2cartHelper = new comquick2cartHelper;

		if (empty($multivendor_enable))
		{
			$order = $comquick2cartHelper->getorderinfo($orderid);
			$store_id = $order['items'][0]->store_id;
			$jinput->set('store_id', $store_id);
		}

		$model  = $this->getModel('Orders');
		$result = $model->resendInvoice();

		$msg = '';

		if ($result)
		{
			$msg = JText::_("COM_QUICK2CART_INVOICE_SEND");
		}

		echo $msg;

		jexit();

		// IF not multi-vendor then redirect to my order list layout

		/*if (empty($multivendor_enable))
		{
			$redirectUrl = JRoute::_('index.php?option=com_quick2cart&view=orders&layout=default&Itemid=' . $this->myOrdersItemId, false);
		}
		else
		{
			$calledStoreview = $jinput->get('calledStoreview', '', 'INT');

			 MD5	EMAIL
			$email = $jinput->get('email', '', 'RAW');
			$streLinkPrarm = "";

			if (!empty($calledStoreview))
			{
				$streLinkPrarm = "&calledStoreview=1";
			}

			if (!empty($email))
			{
				$streLinkPrarm = "&email=" . $email;
			}

			$redirectUrl =  JUri::base() .
			"index.php?option=com_quick2cart&view=orders&layout=order&orderid=" . $orderid . "&store_id=" . $store_id . $streLinkPrarm;
			For multivendor ON, redirect to myorders->detail page or store's->order detial page

			$this->setRedirect($redirectUrl, $msg);
		}*/
	}

	/**
	 * This function is to generate, store wise invoice PDF
	 *
	 * @since   2.5
	 * @return   json.
	 */
	public function generateInvoicePDF()
	{
		$params             = JComponentHelper::getParams('com_quick2cart');
		$multivendor_enable = $params->get('multivendor');
		$app     = JFactory::getApplication();
		$jinput  = $app->input;

		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);
		$storeHelper = new storeHelper;
		$storeHelper->generateInvoicePDF();

		$orderid = $jinput->get("orderid");

		// IF not multi-vendor then redirect to my order list layout
		if (empty($multivendor_enable))
		{
			$redirectUrl = JRoute::_('index.php?option=com_quick2cart&view=orders&layout=defaul&Itemid=' . $this->myOrdersItemId, false);
		}
		else
		{
			$redirectUrl = JUri::base() . "index.php?option=com_quick2cart&view=orders&layout=order&orderid=" . $orderid . "&Itemid=" . $this->myOrdersItemId;
		}

		$this->setRedirect($redirectUrl);
	}
}
