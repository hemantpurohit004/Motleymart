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
/*

Array
(
    [order] => stdClass Object
        (
            [id] => 272
            [prefix] => OID-2VHU4-00000
            [user_info_id] => 965
            [created_by] => 
            [name] => 
            [email] => 8446363349@mailinator.com
            [cdate] => 2018-01-22 12:58:15
            [mdate] => 2018-01-22 12:58:15
            [transaction_id] => 
            [payee_id] => 965
            [original_amount] => 216.00
            [amount] => 216.00
            [coupon_discount] => 0.00000
            [coupon_code] => 
            [couponDetails] => 
            [payment_note] => 
            [order_tax] => 0.00
            [order_tax_details] => 
            [order_shipping] => 
            [order_shipping_details] => 
            [orderRuleDetails] => 
            [fee] => 
            [customer_note] => 
            [status] => P
            [processor] => 
            [ip_address] => 127.0.0.1
            [ticketscount] => 0
            [currency] => INR
            [extra] => 
            [itemTaxShipIncluded] => 0
            [migrateto28version] => 0
        )

    [items] => Array
        (
            [0] => stdClass Object
                (
                    [order_item_id] => 477
                    [store_id] => 3
                    [order_id] => 272
                    [user_info_id] => 
                    [item_id] => 43
                    [variant_item_id] => 0
                    [product_attributes] => 47
                    [product_attribute_names] => Available in (Units):1 KG
                    [order_item_name] => Tata samann [Testing] Do not delete 
                    [product_quantity] => 1
                    [product_item_price] => 100.00
                    [product_attributes_price] => 100
                    [product_final_price] => 90.00
                    [original_price] => 100.00
                    [discount] => 10.00000
                    [discount_detail] => {"id":"6","name":"10PERCENT","coupon_code":"10PERCENT"}
                    [coupon_code] => 10PERCENT
                    [originalBasePrice] => 0.00
                    [item_tax] => 0.00000
                    [item_tax_detail] => 
                    [item_shipcharges] => 0.00000
                    [item_shipDetail] => 
                    [cdate] => 2018-01-22 12:58:15
                    [mdate] => 2018-01-22 12:58:15
                    [params] => 
                    [status] => P
                )

            [1] => stdClass Object
                (
                    [order_item_id] => 478
                    [store_id] => 3
                    [order_id] => 272
                    [user_info_id] => 
                    [item_id] => 43
                    [variant_item_id] => 0
                    [product_attributes] => 48
                    [product_attribute_names] => Available in (Units):500 GRAMS
                    [order_item_name] => Tata samann [Testing] Do not delete 
                    [product_quantity] => 2
                    [product_item_price] => 100.00
                    [product_attributes_price] => 70
                    [product_final_price] => 126.00
                    [original_price] => 140.00
                    [discount] => 14.00000
                    [discount_detail] => {"id":"6","name":"10PERCENT","coupon_code":"10PERCENT"}
                    [coupon_code] => 10PERCENT
                    [originalBasePrice] => 0.00
                    [item_tax] => 0.00000
                    [item_tax_detail] => 
                    [item_shipcharges] => 0.00000
                    [item_shipDetail] => 
                    [cdate] => 2018-01-22 12:58:15
                    [mdate] => 2018-01-22 12:58:15
                    [params] => 
                    [status] => P
                )

        )

)
stdClass Object
(
    [address] => stdClass Object
        (
            [billing] => stdClass Object
                (
                    [id] => 88
                    [user_id] => 965
                    [firstname] => 
                    [middlename] => 
                    [lastname] => 
                    [vat_number] => 
                    [phone] => 8446363349
                    [address_title] => 
                    [user_email] => 8446363349@mailinator.com
                    [address] => Dattachhaya Tejas Society,Londhe Wada,Chaitanya Nagar
                    [land_mark] => Kothrud
                    [zipcode] => 411058
                    [country_code] => 99
                    [state_code] => 1344
                    [city] => Pune
                    [last_used_for_billing] => 1
                    [last_used_for_shipping] => 1
                )

            [shipping] => stdClass Object
                (
                    [id] => 88
                    [user_id] => 965
                    [firstname] => 
                    [middlename] => 
                    [lastname] => 
                    [vat_number] => 
                    [phone] => 8446363349
                    [address_title] => 
                    [user_email] => 8446363349@mailinator.com
                    [address] => Dattachhaya Tejas Society,Londhe Wada,Chaitanya Nagar
                    [land_mark] => Kothrud
                    [zipcode] => 411058
                    [country_code] => 99
                    [state_code] => 1344
                    [city] => Pune
                    [last_used_for_billing] => 1
                    [last_used_for_shipping] => 1
                )

        )

    [userId] => 965
    [products_data] => Array
        (
            [0] => Array
                (
                    [store_id] => 3
                    [product_id] => 43
                    [product_quantity] => 1
                    [att_option] => Array
                        (
                            [3] => 47
                        )

                )

            [1] => Array
                (
                    [store_id] => 3
                    [product_id] => 43
                    [product_quantity] => 2
                    [att_option] => Array
                        (
                            [3] => 48
                        )

                )

        )

    [coupon_code] => 10PERCENT
)
*/