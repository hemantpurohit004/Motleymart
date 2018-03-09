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

jimport('joomla.plugin.plugin');

JLoader::import('ecomm', JPATH_ADMINISTRATOR . '/components/com_ecomm/services');

/*load language file for plugin frontend*/
$lang = JFactory::getLanguage();
$lang->load('plg_system_ecomm_qtc_sms', JPATH_ADMINISTRATOR);
$lang->load('plg_system_ecomm_qtc_sys', JPATH_ADMINISTRATOR);

/**
 * System plguin
 *
 * @package     Plgshare_For_Discounts
 * @subpackage  site
 * @since       1.0
 */
class PlgSystemEcomm_Qtc_Sys extends JPlugin
{
	/**
	 * [ecommOnQuick2cartAfterOrderPlace ]
	 *
	 * @param   [type]  $orderDetails  Order details array
	 *
	 * @return  [type]              [description]
	 */

	public function ecommOnQuick2cartAfterOrderPlace($orderDetails)
	{ 
		$mobileNo = trim($orderDetails->userAddressDetails->mobileNo);

		$order_status_arr = array('C', 'P');

		if ($mobileNo)
		{
			if (in_array($orderDetails->status, $order_status_arr))
			{
				$current_order_status = $orderDetails->status;
			}

			$order_id = $orderDetails->prefix . $orderDetails->orderId;
		}

		// Check Here
		switch ($current_order_status)
		{
			case 'C' :
				$whichever = JText::_('PLG_SYSTEM_QTC_SMS_ORDER_STATUS_CONFIRMED');
			break;

			case 'P' :
				$whichever = JText::_('PLG_SYSTEM_QTC_SMS_ORDER_STATUS_PENDING');
			break;
		}

		$amount = $orderDetails->amount;

		$find = array('{ORDERNO}','{STATUS}','{AMOUNT}');
		$replace = array($order_id, $whichever, $amount);
		$message = str_replace($find, $replace, JText::_('PLG_SYSTEM_ECOMM_QTC_SYS_ORDER_STATUS_MESSAGE_PENDING'));

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('sms');
		$smsresult = $dispatcher->trigger('onSmsSendMessage', array($mobileNo, $message));
		
		return true;
	}

	/**
	 * [ecommApplyCouponCode ]
	 *
	 * @param   String  $couponCode  Coupon Code to be applied
	 *
	 * @return  [type]              [description]
	 */

	public function ecommApplyCouponCode($couponCode)
	{ 
        $return = 'false';

		// Get the table instance
		$db  = JFactory::getDbo();
		$userId = JFactory::getUser()->id;

        $userCouponMapTable = JTable::getInstance('UserCouponMap', 'EcommTable', array('dbo', $db));

		$userCouponMapTable->load(array('userId' => $userId));
		// Build the data to be stored
        $data = array(
            'userId' => $userId,
            'couponCode' => $couponCode
        );

		if(empty($userCouponMapTable->id))
		{
			// coupon code not exists
            // Save the data in the table
            if ($userCouponMapTable->save($data)) {
            	$return = 'true';
            }
		}
		else
		{
			// Coupon code exists so update coupon code
			$data['id'] = $userCouponMapTable->id;
            // Save the data in the table
            if ($userCouponMapTable->save($data)) {
            	$return = 'true';
            }
		}

		return $return;
	}

	/**
	 * [getUserAppliedCouponCode ]
	 *
	 * @return  [type]              [description]
	 */

	public function getUserAppliedCouponCode()
	{
		// Get the table instance
		$db  = JFactory::getDbo();
		$userId = JFactory::getUser()->id;

		$userCouponMapTable = JTable::getInstance('UserCouponMap', 'EcommTable', array('dbo', $db));

		$userCouponMapTable->load(array('userId' => $userId));
		
		if(!empty($userCouponMapTable->id))
		{
			return $userCouponMapTable->couponCode;
		}

		return '';
	}
}