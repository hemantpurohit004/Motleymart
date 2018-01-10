<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();
jimport('joomla.plugin.plugin');

/*load language file for plugin frontend*/
$lang = JFactory::getLanguage();
$lang->load('plg_system_qtc_sms', JPATH_ADMINISTRATOR);


/**
 * PlgSystemQtc_Sms plugin
 *
 * @package     Com_Quick2cart
 *
 * @subpackage  site
 *
 * @since       2.2
 */
class PlgSystemQtc_Sms extends JPlugin
{
	/**
	 * OnAfterq2cOrder Trigger
	 *
	 * @param   Array  $order_obj  Order Object
	 * @param   Array  $data       User Info
	 *
	 * @return  array
	 *
	 * @since   2.7
	 */

	/*public function OnAfterq2cOrder($order_obj, $data)
	{
		$bill            = $data->get('bill', array(), "ARRAY");
		$ship            = $data->get('ship', array(), "ARRAY");
		$qtc_guest_regis = $data->get('qtc_guest_regis', '', "STRING");
		$vars            = new StdClass;

		if ($ship['phon'])
		{
			$find = array('{FNAME}','{LNAME}');
			$replace = array($ship['fnam'], $ship['lnam']);
			$message = str_replace($find, $replace, JText::_('PLG_SYSTEM_QTC_SMS_ORDER_PLACED_IN_SHIPPING'));
			$mob_no  = $ship['phon'];
		}
		else
		{
			$find = array('{FNAME}','{LNAME}');
			$replace = array($bill['fnam'], $bill['lnam']);
			$message = str_replace($find, $replace, JText::_('PLG_SYSTEM_QTC_SMS_ORDER_PLACED_IN_BILLING'));
			$mob_no  = $bill['phon'];
		}

		$vars->mobile_no = trim($mob_no);

		$dispatcher = JDispatcher::getInstance();
		$plugin_name = $this->params['sms_options'];

		$selected_order_status = array();
		$selected_order_status = $this->params['order_status'];

		if (in_array("Pending", $selected_order_status))
		{
			JPluginHelper::importPlugin('sms');
			$smsresult = $dispatcher->trigger('onSmsSendMessage', array($message, $vars));
		}
	}*/

	/**
	 * Onq2corderUpdate Trigger
	 *
	 * @param   Array  $order_obj  Order Object
	 * @param   Array  $data       User Info
	 *
	 * @return  array
	 *
	 * @since   2.7
	 */
	public function onQuick2cartAfterOrderPlace($order_obj, $data)
	{
		$ship = $data['order_info'][0];
		$bill = $data['order_info'][1];

		$vars = new StdClass;
		$oreder_status_arr = array('C', 'RF', 'S', 'P');

		if ($ship->phone)
		{
			if (in_array($ship->status, $oreder_status_arr))
			{
				$current_order_status = $ship->status;
			}

			$order_id_before_prefix = $ship->order_id;
			$mob_no  = $ship->phone;
		}
		elseif ($bill->phone)
		{
			if (in_array($bill->status, $oreder_status_arr))
			{
				$current_order_status = $bill->status;
			}

			$order_id_before_prefix = $bill->order_id;
			$mob_no  = $bill->phone;
		}

		// Check Here
		switch ($current_order_status)
		{
			case 'C' :
				$whichever = JText::_('PLG_SYSTEM_QTC_SMS_ORDER_STATUS_CONFIRMED');
			break;

			case 'RF' :
				$whichever = JText::_('PLG_SYSTEM_QTC_SMS_ORDER_STATUS_REFUND');
			break;

			case 'S' :
				$whichever = JText::_('PLG_SYSTEM_QTC_SMS_ORDER_STATUS_SHIPPED');
			break;

			case 'P' :
				$whichever = JText::_('PLG_SYSTEM_QTC_SMS_ORDER_STATUS_PENDING');
			break;
		}

		$vars->mobile_no = trim($mob_no);

		if (!class_exists('Quick2cartModelpayment'))
		{
			JLoader::register('Quick2cartModelpayment', JPATH_SITE . '/components/com_quick2cart/models/payment.php');
			JLoader::load('Quick2cartModelpayment');
		}

		$Quick2cartModelpayment = new Quick2cartModelpayment;
		$order_id_prefix               = $Quick2cartModelpayment->generate_prefix($order_id_before_prefix);

		$order_id = $order_id_prefix . $order_id_before_prefix;

		$find = array('{ORDERNO}','{STATUS}');
		$replace = array($order_id, $whichever);
		$message = str_replace($find, $replace, JText::_('PLG_SYSTEM_QTC_SMS_ORDER_STATUS_MESSAGE'));

		$dispatcher = JDispatcher::getInstance();
		$plugin_name = $this->params['sms_options'];

		$selected_order_status = array();
		$selected_order_status = $this->params['order_status'];

		if (in_array($whichever, $selected_order_status))
		{
			JPluginHelper::importPlugin('sms');
			$smsresult = $dispatcher->trigger('onSmsSendMessage', array($message, $vars));
		}
	}
}
