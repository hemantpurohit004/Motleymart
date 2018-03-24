<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * Methods supporting for cart promotion.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       1.0
 */
class PromotionHelper
{
	/**
	 * [Get Coupon code from session]
	 *
	 * @return  [type]  [description]
	 */
	public function getSessionCoupon()
	{
		// Hack By Nitesh
		$dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin("system");
        $return = $dispatcher->trigger("getUserAppliedCouponCode", array());

        return $return[0];

		// Commented By Nitesh
		/*$session = JFactory::getSession();
		$cops = $session->get('coupon');

		$coupon_code = '';

		if (!empty($cops))
		{
			// For  now, we are considering the only last coupon will applicable. so Reverse the array.
			$cops = array_reverse($cops);

			foreach ($cops as $key => $coupon)
			{
				$coupon_code = $coupon['code'];

				break;
			}
		}

		return $coupon_code;*/
	}

	/**
	 * This function return the cart  promotion detail which includes coupon code, applicable all promotions, max discount promotion.
	 *
	 * @param   Array   $cart    Cart detail
	 * @param   string  $coupon  Coupon code
	 *
	 * @return  [type]           [promotion detail ]
	 */
	public function getCartPromotionDetail($cart, $coupon = '')
	{
		$data = new stdClass;
		$data->coupon_code = empty($coupon) ? $this->getSessionCoupon() : $coupon;
		$data->applicablePromotions = $this->getPromotionDiscount($cart, $data->coupon_code);
		$data->maxDisPromo = array();
		$applicableMaxDiscount = 0;

		if (!empty($data->applicablePromotions))
		{
			foreach ($data->applicablePromotions as $key => $promo)
			{
				if ($applicableMaxDiscount < $promo->applicableMaxDiscount)
				{
					$applicableMaxDiscount = $promo->applicableMaxDiscount;
					$data->maxDisPromo = $promo;
				}
			}
		}

		return $data;
	}

	/**
	 * [This function will return the promotion discount
	 *
	 * @param   Array   $cart    Cart details
	 * @param   String  $coupon  Coupon code
	 *
	 * @return  [type]           [description]
	 */
	public function getPromotionDiscount($cart, $coupon = '')
	{
		// Get valid and all rules for promotion (rules are not validted against cart detail)
		$data['coupon_code'] = empty($coupon) ? $this->getSessionCoupon() : $coupon;

		// 0 then get all promotion. If 1 then get only coupon based promotions
		$data['promoType'] = 0;

		$validPromotions = $this->getValidatePromotions($data);

		if (empty($validPromotions))
		{
			return;
		}

		// TO validatat, the promotions rules, Format the cart data according to promo
		$formattedCartData = $this->getFormattedCartDetailForPromotion($cart);

		// Rules are not validted against cart detail)
		$applicablePromotions = $this->getApplicablePromotions($validPromotions, $formattedCartData);

		// $this->getMaxDiscountPromotionDetail($applicablePromotions);

		return $applicablePromotions;
	}

	/**
	 * [Get valid promotions according to startdata,end data, coupon code, max use, max per user.]
	 *
	 * @param   Array  $data  User preferences like coupon code, promoTypepromotions]
	 *
	 * @return  [type]                   [description]
	 */
	public function getValidatePromotions($data)
	{
		$utcDateTime = JFactory::getDate('now');

		$coupon_code = !empty($data['coupon_code']) ? $data['coupon_code'] : '';

		// 0 then get all promotion. If 1 then get only coupon based promotions
		$fetchPromoType = !empty($data['promoType']) ? $data['promoType'] : 0;
		$userId = !empty($data['userId']) ? $data['userId'] : JFactory::getUser()->id;

		// @TODO select promotion where  store id in {cart item's store id list}
		$curr                 = Comquick2cartHelper::getCurrencySession();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("p.*, pd.discount,pd.max_discount, pd.currency, pd.discount_type")
			->from('#__kart_promotions AS p')
			->join('INNER', '`#__kart_promotion_discount` AS pd ON p.id=pd.promotion_id')
			->where(" p.`state`= 1")
			->where(" from_date <= '" . $utcDateTime . "'")
			->where(" ( exp_date >= '" . $utcDateTime . "' OR exp_date IS NULL OR exp_date='0000-00-00 00:00:00')")
			->where(" CASE @discount_type WHEN 'quanity_discount' THEN 1 ELSE currency ='$curr' END");

		$where = array();
		$OrderItemCopCondition = '';

		if (!empty($coupon_code))
		{
			// From 2.8 all coupons are applid on order item
			if ($fetchPromoType == 1)
			{
				$where[] = " ((p.coupon_required = 1 AND p.coupon_code = " .
				$db->quote($db->escape($coupon_code)) . ")) ";
			}
			else
			{
				$where[] = " ((p.coupon_required = 0 ) OR (p.coupon_required = 1 AND p.coupon_code = " .
				$db->quote($db->escape($coupon_code)) . ")) ";
			}

			$OrderItemCopCondition = " oi.`coupon_code` =  " . $db->quote($db->escape($coupon_code)) . " AND";

			// Max uses
			$maxUseSubQuery = "SELECT COUNT(*) FROM (" .
				"SELECT oi.coupon_code, oi.order_id FROM  `#__kart_order_item` AS oi " .
				"WHERE " . $OrderItemCopCondition . "  oi.status IN ('C', 'S') GROUP BY `order_id` " .
				") myNewTable ";

			$where[] = "(max_use  > (" . $maxUseSubQuery . ") OR max_use=0 )";

			// Max per user
			$maxPerUserSubQuery = "SELECT COUNT( * ) FROM (" .
			" SELECT o.id FROM  `#__kart_order_item` AS oi INNER JOIN  `#__kart_orders` AS o ON oi.order_id = o.id " .
			" WHERE o.user_info_id = " . $userId . " AND " . $OrderItemCopCondition . "  o.status IN ('C','S','P') " .
			"GROUP BY o.id " .
			")maxperuser ";

			$where[] = "(max_per_user  > (" . $maxPerUserSubQuery . ") OR max_per_user=0 )";
		}
		else
		{
			$where[] = " (p.coupon_required = 0 )";
		}

		foreach ($where as $key => $value)
		{
			$query->where($value);
		}

		// Coupon_required
		$db->setQuery($query);
		$promotions = $db->loadObjectList();

		// Get promotion rules
		return $this->getPromotionRules($promotions);
	}

	/* For getFormattedCartDetailForPromotion function
		$cart = [0] => Array
        (
            [id] =>    // Unique id which refer to curren item
            [store_id] => 1
            [qty] => 1
            [item_id] => 22
            [category] => 14
            [tamt] => 21  // final amout of cart item
         )
	 */
	/**
	 * Format the cart data and get store wise item detail.$cart data should be lik
	 *
	 * @param   Array  $cart  Cart detail
	 *
	 * @return  Array         [Formated cart detail
	 */
	public function getFormattedCartDetailForPromotion($cart)
	{
		if (empty($cart))
		{
			return array();
		}

		$formattedCData = array();

		foreach ($cart as $citem)
		{
			$temp = array();
			$temp['id'] = $citem['id'];
			$temp['store_id'] = $citem['store_id'];
			$temp['qty'] = $citem['qty'];
			$temp['item_id'] = $citem['item_id'];
			$temp['category'] = $citem['category'];
			$temp['tamt'] = $citem['tamt'];
			$formattedCData[] = $temp;
		}

		$cart = $formattedCData;
		$storeProducts = array();
		$cartStoreList = array();

		$formattedData = array();

		foreach ($cart as $cartItem)
		{
			$itemStoreId = $cartItem['store_id'];

			// If no entry for store then create init the data
			if (!array_key_exists($itemStoreId, $formattedData))
			{
				$formattedData[$itemStoreId] = new stdclass;
				$formattedData[$itemStoreId]->storeCartTotalAmt = $cartItem['tamt'];

				// Not For CCK there will be empty category
				if (!empty($cartItem['category']))
				{
					$catTotalPrice = array();
					$catTotalPrice['qty'] = $cartItem['qty'];
					$catTotalPrice['amount'] = $cartItem['tamt'];
					$formattedData[$itemStoreId]->catProdQtyList = array($cartItem['category'] => $catTotalPrice);
				}
				else
				{
					// $formattedData[$itemStoreId]->catProdQtyList = array();
				}

				// Key value pair of item and qty
				$prodTotalPrice = array();
				$prodTotalPrice['qty'] = $cartItem['qty'];
				$prodTotalPrice['amount'] = $cartItem['tamt'];

				$formattedData[$itemStoreId]->prodIdQtyList = array($cartItem['item_id'] => $prodTotalPrice);
				$formattedData[$itemStoreId]->cartItemEntry[] = $cartItem;
			}
			else
			{
				// Append/update data in respective array
				$formattedData[$itemStoreId]->storeCartTotalAmt = $formattedData[$itemStoreId]->storeCartTotalAmt + $cartItem['tamt'];

				// Key value pair of category and qty
				$cat = $cartItem['category'];

				// Not CCK product
				if (!empty($cartItem['category']))
				{
					if (!array_key_exists($cat, $formattedData[$itemStoreId]->catProdQtyList))
					{
						$formattedData[$itemStoreId]->catProdQtyList[$cat]['qty'] = $cartItem['qty'];
						$formattedData[$itemStoreId]->catProdQtyList[$cat]['amount'] = $cartItem['tamt'];
					}
					else
					{
						$oldCatProdQty = $formattedData[$itemStoreId]->catProdQtyList[$cat];

						$formattedData[$itemStoreId]->catProdQtyList[$cat]['qty'] = $oldCatProdQty['qty'] + $cartItem['qty'];
						$formattedData[$itemStoreId]->catProdQtyList[$cat]['amount'] = $oldCatProdQty['amount'] + $cartItem['tamt'];
					}
				}

				// Key value pair of item and qty
				$cartItemId = $cartItem['item_id'];

				if (!array_key_exists($cartItemId, $formattedData[$itemStoreId]->prodIdQtyList))
				{
					$formattedData[$itemStoreId]->prodIdQtyList[$cartItemId]['qty'] = $cartItem['qty'];
					$formattedData[$itemStoreId]->prodIdQtyList[$cartItemId]['amount'] = $cartItem['tamt'];
				}
				else
				{
					$oldQty = $formattedData[$itemStoreId]->prodIdQtyList[$cartItemId];
					$formattedData[$itemStoreId]->prodIdQtyList[$cartItemId]['qty'] = $oldQty['qty'] + $cartItem['qty'];
					$formattedData[$itemStoreId]->prodIdQtyList[$cartItemId]['amount'] = $oldQty['amount'] + $cartItem['tamt'];
				}

				$formattedData[$itemStoreId]->cartItemEntry[] = $cartItem;
			}
		}

		return $formattedData;
	}

	/**
	 * This function return the maximum discount detail
	 *
	 * @param   [OBJECT]  $applicablePromotions  All applicable discount list
	 *
	 * @return  [type]                         [description]
	 */
	private function getMaxDiscountPromotionDetail($applicablePromotions)
	{
		$max_discount = 0;
		$validPromos = array();

		foreach ($applicablePromotions as $promo)
		{
			if ($max_discount < $promo->applicableMaxDiscount)
			{
				$max_discount = $promo->applicableMaxDiscount;
			}

			$promoId = $promo->id;
			$validPromos[$promoId]['id'] = $promoId;
			$validPromos[$promoId]['name'] = $promo->name;
			$validPromos[$promoId]['description'] = $promo->description;
			$validPromos[$promoId]['max_discount'] = $promo->max_discount;
			$validPromos[$promoId]['applicableMaxDiscount'] = $promo->applicableMaxDiscount;
		}

		return $validPromos;
	}

	/**
	 * [getApplicablePromotions description]
	 *
	 * @param   [ARRAY]  $promotions         [promotions list]
	 * @param   [ARRAY]  $formattedCartData  [Formatted cart detail]
	 *
	 * @return  [type]                      [description]
	 */
	public function getApplicablePromotions($promotions, $formattedCartData)
	{
		// Unset the  promotion from list according cart detail
		foreach ($promotions as $promoIndex => $promo)
		{
			$store_id = $promo->store_id;

			// Check whether cart has product from this store
			if (!isset($formattedCartData[$store_id]))
			{
				unset($promotions[$promoIndex]);
			}
		}

		$validPromotions = array();

		foreach ($promotions as $promoIndex => $promo)
		{
			$storeCartDetail = $formattedCartData[$promo->store_id];

			if (!empty($promo->rules))
			{
				// Get status of all rules
				$allRuleStatus = array();

				// Operation (AND/OR)
				$operationBetweenRules = '';

				foreach ($promo->rules as $rule)
				{
					$rulesStatus = array("status" => 0, "discount" => 0, "applicableItemDetail" => array());
					$conditonOn = $rule->condition_on;
					$condition_on_attribute = $rule->condition_on_attribute;
					$condition_attribute_value = $rule->condition_attribute_value;
					$ruleQty = $rule->quantity;
					$ruleOperation = $rule->operation;

					// Data for promotion rules
					$data = new stdclass;
					$data->store_id = $promo->store_id;
					$data->promotion = $promo;
					$data->rule = $rule;
					$data->formattedCartData = $formattedCartData;

					// Every rule  wil have same condition
					$operationBetweenRules = $rule->is_compulsary;

					switch ($conditonOn)
					{
						case "product":
							// Check cart contain X qty "category ids" /"product ids"
							$pieces = 0;
							$conditionValues = explode(',', $condition_attribute_value);
							$conditionValues = array_filter($conditionValues, 'strlen');

							switch ($condition_on_attribute)
							{
								case "item_id":

									$rulesStatus = $this->validateItemIdRule($data);
									break;

								case "category":
									$rulesStatus = $this->validateCategoryRule($data);
								break;
							}

						break;

						case "cart":
							switch ($condition_on_attribute)
							{
								case "cart_amount":
									$rulesStatus = $this->validateCartAmountyRule($data);

								break;

								case "quantity_in_store_cart":
									$rulesStatus = $this->validateQtyInStoreCartRule($data);
								break;
							}
						break;
					}

					$allRuleStatus[$rule->id] = $rulesStatus;
				}

				if ($this->operationBetweenRules($operationBetweenRules, $allRuleStatus))
				{
					// Find out max applicable discunt. (Actually its minimum discount
					$detail = $this->getApplicableItemAcrossRules($allRuleStatus);
					$promo->applicableMaxDiscount  = 0;
					$promo->applicableItemDetail = array();

					if (!empty($detail['applicableItemTotalPrice']))
					{
						$promo->applicableMaxDiscount = $this->applyDiscount($detail['applicableItemTotalPrice'], $promo);
						$promo->applicableItemDetail = !empty($detail['applicableItemDetail']) ? $detail['applicableItemDetail'] : array();

					}

					// $promo->applicableMaxDiscount = !empty($detail["applicableMaxDiscount"]) ? $detail["applicableMaxDiscount"] : 0;

					// $promo->applicableItemDetail = !empty($detail["applicableItemDetail"]) ? $detail["applicableItemDetail"] : array();

					foreach ($allRuleStatus as $ruleStatus)
					{
						if ($ruleStatus['status'] == 1)
						{
						}

						/*if (empty($promo->applicableMaxDiscount))
						{
							// Assign first as minimum discount
							$promo->applicableMaxDiscount = $ruleStatus['discount'];
							$promo->applicableItemDetail = $ruleStatus['applicableItemDetail'];

						}*/

						// Find minimum
						if ($promo->applicableMaxDiscount < $ruleStatus['discount'])
						{
							$promo->applicableMaxDiscount = $ruleStatus['discount'];
							$promo->applicableItemDetail = $ruleStatus['applicableItemDetail'];
						}
					}

					$validPromotions[] = $promo;
				}
			}
		}

		return $validPromotions;
	}

	private function getApplicableItemAcrossRules($allRuleStatus)
	{
		$applicableMaxDiscount = 0;
		$applicableItemDetail = array();

		foreach ($allRuleStatus as $ruleStatus)
		{
			$itemsDetail = $ruleStatus['applicableItemDetail'];

			if (empty($itemsDetail))
			{
				// Process the next rule status
				continue;
			}

			foreach ($itemsDetail as $iDetail)
			{
				$uniqueItemId = $iDetail['id'];

				if (empty($applicableItemDetail[$uniqueItemId]))
				{
					$applicableItemDetail[$uniqueItemId] = $iDetail;
					$applicableMaxDiscount = $applicableMaxDiscount + $iDetail["tamt"];
				}
			}
		}

		$ret['applicableItemTotalPrice'] = $applicableMaxDiscount;
		$ret['applicableItemDetail'] = $applicableItemDetail;

		return $ret;
	}

	/**
	 * [This will return then all rules for the prommotions]
	 *
	 * @param   Array  $promotions  [Promotion detail]
	 *
	 * @return  [array]                 [Array for rules for promotion ids]
	 */
	public function getPromotionRules($promotions)
	{
		$rules = array();

		if (empty($promotions))
		{
			return array();
		}

		$promotionIds = array();
		$db    = JFactory::getDbo();

		// Get promotion rules
		foreach ($promotions as $key => $promo)
		{
			$query = $db->getQuery(true);
			$query->select("pr.*")
			->from('`#__kart_promotions_rules` AS pr')
			->where(" pr.promotion_id =" . $promo->id);
			$db->setQuery($query);
			$promotions[$key]->rules = $db->loadObjectList();
		}

		return $promotions;
	}

	/**
	 * [This function return max applicable discount amount. (max discount <= applicable discount)]
	 *
	 * @param   Integer  $applicableAmt  Amount on which discout have to calculate
	 * @param   Integer  $promotion      Promotion detail
	 *
	 * @return  [integer]                [Disount amount]
	 */
	private function applyDiscount($applicableAmt, $promotion)
	{
		$discount = 0;
		$discounType = $promotion->discount_type;

		switch ($discounType)
		{
			case "flat":
				// Return according to currency
				$discount = isset($promotion->discount) ? $promotion->discount : 0;
			break;

			case "percentage":
				$max_discount = isset($promotion->max_discount) ? $promotion->max_discount : 0;
				$dis = (($applicableAmt * $promotion->discount) / 100);

				if ($dis > $max_discount)
				{
					$discount = $max_discount;
				}
				else
				{
					$discount = $dis;
				}
			break;

			default:
				$discount = 0;
			break;
		}

		if ($discount > $applicableAmt)
		{
			$discount = $applicableAmt;
		}

		return $discount;
	}

	/**
	 * [This function validatate the "category" rule description]
	 *
	 * @param   Object  $data  Data which is required to validate the rule
	 *
	 * @return  [Array]         [status detail]
	 */
	private function validateCategoryRule($data)
	{
		// Find out all applicable cart items
		$status = array("status" => 0, "discount" => 0, "applicableItemDetail" => array());

		// Consider amount for applicable entities only
		$relativePrice = 0;
		$store_id = $data->store_id;
		$formattedCartData = $data->formattedCartData;
		$rule = $data->rule;
		$pieces = 0;

		// $rulesStatus[$rule->id] = 0;
		$conditonOn = $rule->condition_on;
		$condition_on_attribute = $rule->condition_on_attribute;
		$condition_attribute_value = $rule->condition_attribute_value;
		$ruleQty = $rule->quantity;
		$ruleOperation = $rule->operation;
		$pieces = 0;
		$catProdQtyList = $formattedCartData[$store_id]->catProdQtyList;

		// Empty for all category
		$applicableCats = array();

		// Consider all category if  conditionValues is empty
		if (!empty($condition_attribute_value))
		{
			$conditionValues = json_decode($condition_attribute_value, true);

			foreach ($conditionValues as $key => $catId)
			{
				if (isset($catProdQtyList[$catId]))
				{
					$applicableCats[] = $catId;
					$pieces = $pieces + $catProdQtyList[$catId]['qty'];

					// Found any item from catgory
					$status['status'] = 1;
					$relativePrice = $relativePrice + $catProdQtyList[$catId]['amount'];
				}
			}
		}
		else
		{
			// Consider all category
			$status['status'] = 1;

			foreach ($formattedCartData[$store_id]->catProdQtyList as $prodQty)
			{
				$pieces = $pieces + $prodQty['qty'];
				$relativePrice = $relativePrice + $catProdQtyList[$catId]['amount'];
			}
		}

		// If category condition is valid and Quantity condition is added in rule
		if ($status['status'] == 1 && !empty($ruleQty))
		{
			$ruleStatus = $this->performRuleOperation($pieces, $ruleOperation, $ruleQty);

			if ($ruleStatus == false)
			{
				$status['status'] = 0;
			}
		}

		if ($status['status'] == 1)
		{
			// $discounType = $data->promotion->discount_type;
			$status['discount'] = $this->applyDiscount($relativePrice, $data->promotion);

			foreach ($formattedCartData[$store_id]->cartItemEntry as $cartItem)
			{
				$cartItemId = $cartItem['id'];
				$item_id = $cartItem['item_id'];

				// Not cck product
				if ($cartItem['category'])
				{
					if (empty($applicableCats) || in_array($cartItem['category'], $applicableCats))
					{
						$status['applicableItemDetail'][$cartItemId] = $cartItem;
					}
				}
			}
		}

		return $status;
	}

	/**
	 * [This function validatate the "cartamount" rule description]
	 *
	 * @param   Object  $data  Data which is required to validate the rule
	 *
	 * @return  [Array]         [status detail]
	 */
	private function validateQtyInStoreCartRule($data)
	{
		$status = array("status" => 0, "discount" => 0, "applicableItemDetail" => array());

		// Consider amount for applicable entities only
		$relativePrice = 0;
		$store_id = $data->store_id;
		$formattedCartData = $data->formattedCartData;
		$rule = $data->rule;

		// $rulesStatus[$rule->id] = 0;
		$conditonOn = $rule->condition_on;
		$condition_on_attribute = $rule->condition_on_attribute;
		$condition_attribute_value = $rule->condition_attribute_value;
		$ruleOperation = $rule->operation;

		$conditionValues = json_decode($condition_attribute_value, true);

		if (!empty($conditionValues))
		{
			$storeItemQty = 0;
			$prodIdQtyList = $formattedCartData[$store_id]->prodIdQtyList;

			foreach ($prodIdQtyList as $key => $item)
			{
				$storeItemQty = $storeItemQty + $item['qty'];
			}

			$ruleStatus = $this->performRuleOperation($storeItemQty, $ruleOperation, $condition_attribute_value);

			if ($ruleStatus)
			{
				$status['status'] = 1;
			}
		}

		if ($status['status'] == 1)
		{
			$storeCartTotalAmt = $formattedCartData[$store_id]->storeCartTotalAmt;
			$status['discount'] = $this->applyDiscount($storeCartTotalAmt, $data->promotion);

			// If status is  1 then all store cart items are applicable
			foreach ($formattedCartData[$store_id]->cartItemEntry as $cartItem)
			{
				$cartItemId = $cartItem['id'];
				$item_id = $cartItem['item_id'];
				$status['applicableItemDetail'][$cartItemId] = $cartItem;
			}
		}

		return $status;
	}

	/**
	 * [This function validatate the "cartamount" rule description]
	 *
	 * @param   Object  $data  Data which is required to validate the rule
	 *
	 * @return  [Array]         [status detail]
	 */
	private function validateCartAmountyRule($data)
	{
		$status = array("status" => 0, "discount" => 0, "applicableItemDetail" => array());

		// Applicable cart item detail. cartItemId => item detail
		$applicableItemDetail = array();

		// Consider amount for applicable entities only
		$relativePrice = 0;
		$store_id = $data->store_id;
		$formattedCartData = $data->formattedCartData;
		$rule = $data->rule;

		// $rulesStatus[$rule->id] = 0;
		$conditonOn = $rule->condition_on;
		$condition_on_attribute = $rule->condition_on_attribute;
		$condition_attribute_value = $rule->condition_attribute_value;
		$ruleOperation = $rule->operation;

		$conditionValues = json_decode($condition_attribute_value, true);
		$curr                 = Comquick2cartHelper::getCurrencySession();

		if (isset($conditionValues[$curr]))
		{
			$cartAmountCheck = $conditionValues[$curr];
			$storeCartTotalAmt = $formattedCartData[$store_id]->storeCartTotalAmt;
			$ruleStatus = $this->performRuleOperation($storeCartTotalAmt, $ruleOperation, $cartAmountCheck);

			if ($ruleStatus === true)
			{
				$status['status'] = 1;
			}
		}

		if ($status['status'] == 1)
		{
			$storeCartTotalAmt = $formattedCartData[$store_id]->storeCartTotalAmt;
			$status['discount'] = $this->applyDiscount($storeCartTotalAmt, $data->promotion);

			foreach ($formattedCartData[$store_id]->cartItemEntry as $cartItem)
			{
				$cartItemId = $cartItem['id'];
				$applicableItemDetail[$cartItemId] = $cartItem;
			}
		}

		$status['applicableItemDetail'] = $applicableItemDetail;

		return $status;
	}

	/**
	 * [This function validatate the "item_id" rule description]
	 *
	 * @param   Object  $data  Data which is required to validate the rule
	 *
	 * @return  [Array]         [status detail]
	 */
	private function validateItemIdRule($data)
	{
		$status = array("status" => 0, "discount" => 0, "applicableItemDetail" => array());

		// Consider amount for applicable entities only
		$relativePrice = 0;
		$store_id = $data->store_id;
		$formattedCartData = $data->formattedCartData;
		$rule = $data->rule;
		$pieces = 0;

		// $rulesStatus[$rule->id] = 0;
		$conditonOn = $rule->condition_on;
		$condition_on_attribute = $rule->condition_on_attribute;
		$condition_attribute_value = $rule->condition_attribute_value;
		$ruleQty = $rule->quantity;
		$ruleOperation = $rule->operation;
		$pieces = 0;
		$prodIdQtyList = $formattedCartData[$store_id]->prodIdQtyList;
		$applicableItemIds = array();

		if (!empty($condition_attribute_value))
		{
			// Selected items are applicable
			$conditionValues = json_decode($condition_attribute_value, true);

			foreach ($conditionValues as $key => $item_id)
			{
				// If item_id is present in cart
				if (isset($prodIdQtyList[$item_id]))
				{
					$applicableItemIds[] = $item_id;
					$pieces = $pieces + $prodIdQtyList[$item_id]['qty'];

					// Found any item from catgory
					$status['status'] = 1;
					$relativePrice = $relativePrice + $prodIdQtyList[$item_id]['amount'];
				}
			}
		}
		else
		{
			// All store cart items are applicable
			$status['status'] = 1;

			foreach ($formattedCartData[$store_id]->prodIdQtyList as $item_id => $prod)
			{
				$applicableItemIds[] = $item_id;
				$pieces = $pieces + $prod['qty'];
				$relativePrice = $relativePrice + $prod['amount'];
			}
		}

		// If category condition is valid and Quantity condition is added in rule
		if ($status['status'] == 1 && !empty($ruleQty))
		{
			$ruleStatus = $this->performRuleOperation($pieces, $ruleOperation, $ruleQty);

			if ($ruleStatus == false)
			{
				$status['status'] = 0;
			}
		}

		if ($status['status'] == 1)
		{
			$status['discount'] = $this->applyDiscount($relativePrice, $data->promotion);

			if (!empty($applicableItemIds))
			{
				foreach ($formattedCartData[$store_id]->cartItemEntry as $cartItem)
				{
					$cartItemId = $cartItem['id'];
					$item_id = $cartItem['item_id'];

					if (in_array($item_id, $applicableItemIds))
					{
						$applicableItemDetail[$cartItemId] = $cartItem;
					}
				}
			}
		}

		if (!empty($applicableItemDetail))
		{
			$status['applicableItemDetail'] = $applicableItemDetail;
		}

		return $status;
	}

	/**
	 * [Perform the operation between rules]
	 *
	 * @param   String  $operation    Operation between rules
	 * @param   Array   $rulesStatus  Rules status
	 *
	 * @return  [Boolean]                status
	 */
	private function operationBetweenRules($operation, $rulesStatus)
	{
		switch ($operation)
		{
			case "AND":
				$status = 1;

				foreach ($rulesStatus as $rStatus)
				{
					if ($rStatus['status'] == 0)
					{
						$status = 0;
						break;
					}
				}

				return $status;
			break;

			case "OR":
				$status = 0;

				foreach ($rulesStatus as $rStatus)
				{
					if ($rStatus['status'] == 1)
					{
						$status = 1;
						break;
					}
				}

				return $status;
			break;

			default:
				return 0;
			break;
		}
	}

	/**
	 *  This function perform the operation between operand
	 *
	 * @param   Integer  $op1        Operand 1
	 * @param   String   $operation  operation
	 * @param   Integer  $op2        Operand 2
	 *
	 * @return  [type]        [return result of operation]
	 */
	private function performRuleOperation($op1, $operation, $op2)
	{
		switch ($operation)
		{
			case "=":
				return ($op1 == $op2);
			break;

			case "<=":
				return ($op1 <= $op2);
			break;

			case "<":
					return ($op1 < $op2);
			break;

			case ">=":
					return ($op1 >= $op2);
			break;

			case ">":
					return ($op1 > $op2);
			break;
		}
	}

	/**
	 * Function to get promotions related to product
	 *
	 * @param   INT  $productId  Product id
	 * @param   INT  $category   category id
	 * @param   INT  $storeId    store id
	 *
	 * @return  Array  promotions detail
	 */
	public function getApplicablePromotionsForProduct($productId, $category, $storeId)
	{
		// Get current UTC date
		$utcDateTime = JFactory::getDate('now');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("DISTINCT p.id, p.name, p.description, p.coupon_code, pr.condition_attribute_value, pr.condition_on, pr.condition_on_attribute");
		$query->from('#__kart_promotions AS p');
		$query->join('INNER', '`#__kart_promotions_rules` AS pr ON p.id=pr.promotion_id');
		$query->join('INNER', '`#__kart_promotion_discount` AS pd ON p.id=pr.promotion_id');
		$query->where("p.state = 1");
		$query->where("p.store_id" . "=" . $storeId);
		$query->where("pr.condition_on = 'product'");
		$query->where(" p.from_date <= '" . $utcDateTime . "'");
		$query->where(" (p.exp_date >= '" . $utcDateTime . "' OR p.exp_date='0000-00-00 00:00:00')");
		$db->setQuery($query);
		$promotions = $db->loadObjectList();
		$applicablePromotions = array();

		if (!empty($promotions))
		{
			foreach ($promotions as $promotion)
			{
				$condition_attribute_value = json_decode($promotion->condition_attribute_value);

				if (!empty($condition_attribute_value))
				{
					if (in_array($productId, $condition_attribute_value) || in_array($category, $condition_attribute_value))
					{
						$applicablePromotions[] = $promotion;
					}
				}
			}
		}

		// Remove duplicate offers
		for ($i = 0; $i < count($applicablePromotions); $i++)
		{
			for ($j = $i + 1; $j < count($applicablePromotions); $j++)
			{
				if ($applicablePromotions[$i]->id == $applicablePromotions[$j]->id)
				{
					unset($applicablePromotions[$j]);
				}
			}
		}

		return $applicablePromotions;
	}
}
