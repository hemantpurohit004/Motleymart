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

		$order_status_arr = array('C', 'RF', 'S', 'P');

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

		$find = array('{ORDERNO}','{STATUS}');
		$replace = array($order_id, $whichever);
		$message = str_replace($find, $replace, JText::_('PLG_SYSTEM_QTC_SMS_ORDER_STATUS_MESSAGE'));

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
		/*
		CREATE TABLE `db_motleymart`.`ichal_ecomm_user_coupon_map` ( `id` INT NOT NULL AUTO_INCREMENT , `userId` INT NOT NULL , `couponCode` VARCHAR(50) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

        // Load the table and save the data
        $mobileOtpMapTable = JTable::getInstance('MobileOtpMap', 'EcommTable', array('dbo', $this->db));
        $mobileOtpMapTable->load(array('mobile_no' => $mobileNo));

		*/

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