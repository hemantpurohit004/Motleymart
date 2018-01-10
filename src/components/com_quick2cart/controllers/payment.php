<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2Cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
require_once JPATH_COMPONENT . '/controller.php';

jimport('joomla.application.component.controller');

/**
 * Payment model class.
 *
 * @package  Quick2cart
 * @since    2.7
 */
class Quick2cartControllerpayment extends JControllerLegacy
{
	/**
	 * THis method is used to return payment form
	 *
	 * @return  string  Payemnt form
	 *
	 * @since   2.0
	 */
	public function getHTML()
	{
		$model     = $this->getModel('payment');
		$jinput    = JFactory::getApplication()->input;
		$pg_plugin = $jinput->get('processor');
		$user      = JFactory::getUser();
		$session   = JFactory::getSession();
		$order_id  = $jinput->get('order');
		$html      = $model->getHTML($pg_plugin, $order_id);

		if (!empty($html[0]))
		{
			echo $html[0];
		}

		jexit();
	}

	/**
	 * THis method is to handle payment notification (generally onsite)
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function confirmpayment()
	{
		$model    = $this->getModel('payment');
		$session  = JFactory::getSession();
		$jinput   = JFactory::getApplication()->input;
		$order_id = $session->get('order_id');

		if (empty($order_id))
		{
			$order_id = $jinput->get('order_id', '', 'STRING');
		}

		if (empty($order_id))
		{
			$order_id = $jinput->get('orderid', '', 'STRING');
		}

		$pg_plugin = $jinput->get('processor');
		$response = $model->confirmpayment($pg_plugin, $order_id);
	}

	/**
	 * THis method is to handle payment notification (generally off site payment gateway)
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function processpayment()
	{
		$mainframe = JFactory::getApplication();
		$jinput    = JFactory::getApplication()->input;
		$session   = JFactory::getSession();

		if ($session->has('payment_submitpost'))
		{
			$post = $session->get('payment_submitpost');
			$session->clear('payment_submitpost');
		}
		else
		{
			$post = JRequest::get('post');
		}

		$pg_plugin = $jinput->get('processor');
		$model     = $this->getModel('payment');
		$order_id  = $jinput->get('orderid', '', 'STRING');

		if (empty($order_id))
		{
			$order_id = $jinput->get('order_id', '', 'STRING');
		}

		if (empty($post) || empty($pg_plugin))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('SOME_ERROR_OCCURRED'), 'error');

			return;
		}

		$response = $model->processpayment($post, $pg_plugin, $order_id);
		$mainframe->redirect($response['return'], $response['msg']);
	}
}
