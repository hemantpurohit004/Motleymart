<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');

/**
 * Reports Helper
 *
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 * @since       1.5
 */
class ReportsHelper
{
	/**
	 * GetTotalAmount2BPaidOut
	 *
	 * @param   integer  $user_id  user_id.
	 *
	 * @return  TJToolbar  The TJToolbar object.
	 *
	 * @since   1.5
	 */
	public function getTotalAmount2BPaidOut($user_id = 0)
	{
		$db = JFactory::getDBO();
		$q  = "select id from #__kart_store where owner=" . $user_id . " order by id";
		$db->setQuery($q);
		$store_ids = $db->loadColumn();
		$store_ids = implode(",", $store_ids);

		if (empty($store_ids))
		{
			return 0;
		}

		$query = "SELECT SUM( i.product_final_price )
				FROM  `#__kart_orders` AS o LEFT JOIN  `#__kart_order_item` AS i ON o.id = i.order_id
				LEFT JOIN  `#__kart_store` AS s ON s.id = i.store_id
				WHERE (o.status='C' OR o.status='S') AND  s.owner =" . $user_id;

		/*	$query="SELECT SUM(cashback) AS total_amount
		FROM `#__kart_commission` ".$where;*/
		/*ONLY consider payments which are directly transferred to admin's account*/
		$db->setQuery($query);
		$result = $db->loadresult();
		/*	$TotalAmount2BPaidOut=0;
		if($result){
		$TotalAmount2BPaidOut=$result->total_amount-$result->total_commission;
		}*/
		return $result;
	}

	/**
	 * GetTotalPaidOutAmount
	 * doesn't already exist.
	 *
	 * @return  TJToolbar  The TJToolbar object.
	 *
	 * @since   1.5
	 */
	public function getTotalPaidOutAmount()
	{
		$user    = JFactory::getUser();
		$user_id = $user->id;
		$db      = JFactory::getDBO();
		$where   = '';

		if ($user_id)
		{
			$where = " AND user_id=" . $user_id;
		}

		// $query="SELECT user_id,payee_name,transaction_id,date,email_id,amount
		$query = "SELECT amount
		FROM #__kart_payouts
		WHERE status=1 " . $where;

		$db->setQuery($query);
		$totalearn = 0;
		$result    = $db->loadObjectlist();
		$totalpaid = 0;

		if (!empty($result))
		{
			foreach ($result as $data)
			{
				$totalpaid = $totalpaid + $data->amount;
			}
		}

		return $totalpaid;
	}

	/**
	 * GetCommissionCut
	 *
	 * @param   integer  $user_id  user_id.
	 *
	 * @return  TJToolbar  The TJToolbar object.
	 *
	 * @since   1.5
	 */
	public function getCommissionCut($user_id)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT SUM( fee ) AS fee  FROM  `#__kart_store` where `owner`=' . $user_id . ' GROUP BY  `owner`';
		$db->setQuery($query);

		return $fee = $db->loadResult();
	}
}
