<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
jimport('joomla.database.table.user');

/**
 * Reports Model.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelReports extends JModelLegacy
{
	// Changed by Deepa

	/*protected  $_data;
	protected $_total = null;
	protected $_pagination = null;*/

	protected  $data;

	protected $total = null;

	protected $pagination = null;

	/**
	 * Constructor.
	 *
	 * @since   1.6
	 * @see     JController
	 */
	public function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$mainframe  = JFactory::getApplication();
		$jinput     = $mainframe->input;
		$option     = $jinput->get('option');

		// Get pagination request variables
		$limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $jinput->get('limitstart', 0, '', 'int');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Method get private payouts
	 *
	 * @return	query
	 *
	 * @since	1.6
	 */
	public function getPayouts()
	{
		if (empty($this->_data))
		{
			$query       = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

	/*
	function getCampaignWiseDonations()
	{
	if (empty($this->_data))
	{
	$query=$this->_buildQuery();
	$this->_data=$this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
	}

	foreach ($this->_data as $d)
	{
		$d->exclude_amount=reportsHelper::getTotalAmount2BExcluded($d->cid);
	}

	global $mainframe,$option;
	$mainframe=JFactory::getApplication();
	$filter_type=$mainframe->getUserStateFromRequest($option.'filter_order','filter_order','goal_amount','cmd');
	$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jgive.filter_order_Dir','filter_order_Dir','desc','word');

	if ($filter_type=='donations_count' || $filter_type=='total_amount' || $filter_type=='total_commission' || $filter_type=='exclude_amount'){
	$this->_data=jgiveHelper::multi_d_sort($this->_data,$filter_type,$filter_order_Dir);
	}

	return $this->_data;
	}
	*/

	/**
	 * Method BuildQuery.
	 *
	 * @return	query
	 *
	 * @since	1.6
	 */
	public function _buildQuery()
	{
		$db = JFactory::getDBO();
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$jinput    = $mainframe->input;
		$option    = $jinput->get('option');
		$layout    = $jinput->get('layout', 'payouts');

		// Get the WHERE and ORDER BY clauses for the query
		$where = '';

		$me      = JFactory::getuser();
		$user_id = $me->id;
		$where   = " where user_id=" . $user_id;
		$where .= " AND a.status=1";

		// Payouts report when called from front end
		if ($layout == 'mypayouts')
		{
			$query            = "SELECT a.id, a.user_id, a.payee_name, a.transaction_id, a.date, a.email_id, a.amount, a.status, u.username
			FROM #__kart_payouts AS a
			LEFT JOIN `#__users` AS u ON u.id=a.user_id
			" . $where;
			$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'id', 'cmd');
			$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

			/*$me=JFactory::getuser();
			$user_id = $me->id;
			$query = "SELECT * FROM #__kart_commission WHERE user_id = ".$user_id;*/
			if ($filter_order)
			{
				$qry1 = "SHOW COLUMNS FROM #__kart_payouts";
				$db->setQuery($qry1);
				$exists1 = $db->loadobjectlist();

				foreach ($exists1 as $key1 => $value1)
				{
					$allowed_fields[] = $value1->Field;
				}

				if (in_array($filter_order, $allowed_fields))
				{
					$query .= " ORDER BY $filter_order $filter_order_Dir";
				}
			}
		}

		if ($layout == 'payouts')
		{
			$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'id', 'int');
			$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'string');
			$query            = "SELECT * FROM #__kart_payouts ";

			if ($filter_order)
			{
				$qry1 = "SHOW COLUMNS FROM #__kart_payouts";
				$db->setQuery($qry1);
				$exists1 = $db->loadobjectlist();

				foreach ($exists1 as $key1 => $value1)
				{
					$allowed_fields[] = $value1->Field;
				}

				if (in_array($filter_order, $allowed_fields))
				{
					$query .= " ORDER BY $filter_order $filter_order_Dir";
				}
			}
		}

		return $query;
	}

	/**
	 * Method Gettotal.
	 *
	 * @return	link
	 *
	 * @since	1.6
	 */
	public function getTotal()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->_total))
		{
			$query        = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method Get Pagination.
	 *
	 * @return	link
	 *
	 * @since	1.6
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Method Get Payout form data.
	 *
	 * @return	result
	 *
	 * @since	1.6
	 */
	public function getPayoutFormData()
	{
		// $query="select s.id,s.fee,s.owner as user_id,s.title as name,s.store_email as email from `#__kart_store` as s";
		// GETTING ALL COMMISSION should be deducted from user including all store
		$query = 'SELECT SUM( fee ) AS fee,  `owner` as user_id  FROM  `#__kart_store` GROUP BY  `owner`';
		$this->_db->setQuery($query);
		$payouts = $this->_db->loadObjectList();

		foreach ($payouts as $index => $key)
		{
			// Calculate toatl total sales
			$query1 = "SELECT SUM( i.product_final_price ) AS total_amount,o.`email`
				FROM  `#__kart_orders` AS o
				LEFT JOIN  `#__kart_order_item` AS i ON o.id = i.order_id
				LEFT JOIN  `#__kart_store` AS s ON s.id = i.store_id
				WHERE o.status='C' AND s.owner =" . $key->user_id;
			$this->_db->setQuery($query1);
			$userPriceDetail = $this->_db->loadAssoc();

			//  Calculate old payout given
			if (!empty($userPriceDetail['total_amount']))
			{
				$query = "SELECT SUM(amount)
						FROM #__kart_payouts
						WHERE user_id = " . $key->user_id . "  AND status = 1";
				$this->_db->setQuery($query);
				$paid_amount       = $this->_db->loadresult();

				/*$key->paid_amount = $paid_amount;*/
				$key->total_amount = $userPriceDetail['total_amount'] - $paid_amount;
				$key->email        = $userPriceDetail['email'];
			}
			else
			{
				unset($payouts[$index]);
			}
		}

		return $payouts;
	}

	/**
	 * Method Getsingle Payout data.
	 *
	 * @return	result
	 *
	 * @since	1.6
	 */
	public function getSinglePayoutData()
	{
		$jinput    = JFactory::getApplication()->input;
		$payout_id = $jinput->get('payout_id', '', 'INT');

		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select("id, user_id, payee_name, transaction_id, date, email_id, amount,status, comment");
		$query->from("#__kart_payouts");
		$query->where("id=" . $payout_id);

		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}

	/**
	 * Method Save Payout.
	 *
	 * @param   Array  $post  Post Array
	 *
	 * @return	Boolean
	 *
	 * @since	1.6
	 */
	public function savePayout($post = "")
	{
		if (empty($post))
		{
			$post = $this->getReportPostData();
		}

		$repId  = '';
		$action = 'insertObject';

		if (!empty($post['id']))
		{
			$repId  = $post['id'];
			$action = 'updateObject';
		}

		$obj                 = new stdClass;
		$obj->id             = $repId;
		$obj->user_id        = $post['user_id'];
		$obj->payee_name     = $post['payee_name'];
		$obj->email_id       = $post['paypal_email'];
		$obj->transaction_id = $post['transaction_id'];
		$obj->amount         = $post['payment_amount'];
		$obj->date           = $post['payout_date'];
		$obj->status         = $post['status'];
		$obj->comment        = $post['payment_comment'];

		// Insert object
		if (!$this->_db->$action('#__kart_payouts', $obj, 'id'))
		{
			echo $this->_db->stderr();

			return false;
		}

		$this->sendmail($obj->payee_name, $obj->email_id, $obj->transaction_id, $obj->amount, $obj->date, $obj->status, $obj->comment);

		return true;
	}

	/**
	 * Method Get ReportPostData.
	 *
	 * @return	Array
	 *
	 * @since	1.6
	 */
	public function getReportPostData()
	{
		$jinput                = JFactory::getApplication()->input;
		$postobj               = $jinput->post;

		/*$postobj2 = $postobj->get('data','','ARRAY');*/
		$data                  = array();
		$data['id']            = $postobj->get('id');
		$data['payee_name']    = $postobj->get('payee_name');
		$data['user_id']       = $postobj->get('user_id');
		$data['payee_options'] = $postobj->get('payee_options');
		$data['paypal_email']  = $postobj->get('paypal_email', '', 'RAW');

		$data['transaction_id']  = $postobj->get('transaction_id', '', 'RAW');
		$data['payout_date']     = $postobj->get('payout_date', '', 'RAW');
		$data['payment_amount']  = $postobj->get('payment_amount');
		$data['payment_comment'] = $postobj->get('payment_comment', '', 'RAW');
		$data['status']          = $postobj->get('status');

		$data['option']     = $postobj->get('option');
		$data['controller'] = $postobj->get('controller');
		$data['task']       = $postobj->get('task');

		return $data;
	}

	/**
	 * Method Edit Payouts.
	 *
	 * @return	boolean
	 *
	 * @since	1.6
	 */
	public function editPayout()
	{
		$jinput = JFactory::getApplication()->input;
		$post   = $jinput->post;

		$obj                 = new stdClass;
		$obj->id             = $post->get('edit_id');
		$obj->user_id        = $post->get('user_id');
		$obj->payee_name     = $post->get('payee_name', '' . 'RAW');
		$obj->email_id       = $post->get('paypal_email', '' . 'RAW');
		$obj->transaction_id = $post->get('transaction_id', '' . 'RAW');
		$obj->amount         = $post->get('payment_amount');
		$obj->date           = $post->get('payout_date');
		$obj->status         = $post->get('status');
		$obj->comment        = $post->get('payment_comment', '' . 'RAW');

		// Insert object
		if (!$this->_db->updateObject('#__kart_payouts', $obj, 'id'))
		{
			echo $this->_db->stderr();

			return false;
		}

		$this->sendmail($obj->payee_name, $obj->email_id, $obj->transaction_id, $obj->amount, $obj->date, $obj->status, $obj->comment);

		return true;
	}

	/**
	 * Method Delete Payouts.
	 *
	 * @param   Intger  $id  Id
	 *
	 * @return	mixed	$data  The data for the form.
	 *
	 * @since	1.6
	 */
	public function deletePayouts($id)
	{
		$payee_id = implode(',', $id);
		$db       = JFactory::getDBO();
		$query    = "delete FROM #__kart_payouts where id IN(" . $payee_id . ")";

		$db->setQuery($query);

		if (!$db->execute())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		return true;
	}

	/**
	 * Method SendMail.
	 *
	 * @param   String   $payee_name      Payee Name
	 * @param   String   $email_id        Email  Id
	 * @param   Integer  $transaction_id  Transaction Id
	 * @param   Float    $amount          Amount
	 * @param   Date     $date            Date
	 * @param   String   $status          Status
	 * @param   String   $comment         Comment
	 *
	 * @return	mixed	$data  The data for the form.
	 *
	 * @since	1.6
	 */
	public function sendmail($payee_name, $email_id, $transaction_id, $amount, $date, $status, $comment)
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);

		if ($status == 0)
		{
			$status_msg = 'Unpaid';
		}
		else
		{
			$status_msg = 'Paid';
		}

		$mainframe = JFactory::getApplication();
		$body      = JText::_('BALANCERL');
		$subject   = JText::_('PAYOUT_DETAILS_CHANGED');

		$body = str_replace('{username}', $payee_name, $body);
		$html = '<br/><div>' . JText::_("CHANGED") . '<br/><table>
		<tr><td>' . JText::_("COM_QUICK2CART_TRANSACTION_ID") . '</td><td> ' . $transaction_id . '</td></tr>
		<tr><td>' . JText::_("EMAIL_CASHBACK_AMOUNT") . '</td><td>' . $amount . ' USD</td></tr>
		<tr><td>' . JText::_("EMAILS_PAYOUT_DATE") . '</td><td>' . $date . '</td></tr>
		<tr><td>' . JText::_("EMAIL_STATUS") . ' </td><td>' . $status_msg . '</td></tr>
		<tr><td>' . JText::_("EMAIL_PAYOUT_COMMENT") . ' </td><td>' . $comment . '</td></tr></div>';

		/*$ad_title=($result->ad_title!= '')?
		JText::_("PERIDIC_STATS_ADTIT").' <b>"'.$result->ad_title.'"</b>' :
		JText::_("PERIDIC_STATS_ADID").' : <b>'.$adid.'</b>';
		$body	= str_replace('{title}', $ad_title, $body);
		$body	= str_replace('{sitename}', JUri::base(), $body);
		$body	= str_replace('{adsite}', JUri::base(), $body);*/
		$body .= $html;
		$from        = $mainframe->getCfg('mailfrom');
		$fromname    = $mainframe->getCfg('fromname');
		$recipient[] = $email_id;
		$body        = nl2br($body);

		$mode        = 1;
		$cc          = null;
		$bcc         = null;
		$bcc         = null;
		$attachment  = null;
		$replyto     = null;
		$replytoname = null;

		/* in j3.0 JUtility ::sendMail  to JMail ::sendMail*/
		JFactory::getMailer()->sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
	}

	/**
	 * Method to get CSV export report
	 *
	 * @return	data
	 *
	 * @since	1.6
	 */
	public function getCsvexportData()
	{
		$query = $this->_buildQuery();
		$db    = JFactory::getDBO();
		$query = $db->setQuery($query);

		return $data = $db->loadAssocList();
	}
}
