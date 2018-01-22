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
	 * [onQuick2cartAfterOrderPlace ]
	 *
	 * @param   [type]  $order_obj  [description]
	 * @param   [type]  $data       [description]
	 *
	 * @return  [type]              [description]
	 */
	public function onQuick2cartAfterOrderPlace($order_obj, $data)
	{	
		$ecommService = new EcommService;

		$products = $order_obj['items'];
		$totalAmount = 0;

		foreach($products as $item) 
		{
			$totalAmount += $item->original_price;
		}

		$taxAmount = $ecommService->getTaxAmount($totalAmount);
		$shipAmount = $ecommService->getDeliveryAmount($totalAmount);
		$amount = $order_obj['order']->amount + $shipAmount + $taxAmount;

		// Create the insert query
		$db = JFactory::getDbo();
		$query   = $db->getQuery(true);

        // Fields to update.
        $fields = array(
            $db->quoteName('order_tax') . ' = ' . $db->quote($taxAmount),
            $db->quoteName('order_shipping') . ' = ' . $db->quote($shipAmount),
            $db->quoteName('amount') . ' = ' . $db->quote($amount),
            $db->quoteName('original_amount') . ' = ' . $db->quote($amount),
        );

        // Conditions for which records should be updated.
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($order_obj['order']->id)
        );

        $query->update($db->quoteName('#__kart_orders'))
            ->set($fields)
            ->where($conditions);

        $db->setQuery($query);
        
    	return $db->execute();
	}
}