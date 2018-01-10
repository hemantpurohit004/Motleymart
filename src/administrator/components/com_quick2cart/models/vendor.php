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

jimport('joomla.application.component.model');

// Added by Sneha
require_once JPATH_SITE . '/components/com_quick2cart/helper.php';

/**
 * Quick2cart vendor model.
 *
 * @since  2.2
 */
class Quick2cartModelVendor extends JModelLegacy
{
	protected $protected_data;

	protected $protected_total = null;

	protected $protected_pagination = null;

	/**
	 * Constructor that retrieves the ID from the request
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$mainframe        = JFactory::getApplication();
		$jinput           = $mainframe->input;

		// Get the pagination request variables
		$limit            = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart       = $jinput->get('limitstart', 0, '', 'int');
		$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', '', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Fucntion to build query
	 *
	 * @return  query
	 */
	public function _buildQuery()
	{
		// Added by Sneha
		$query = "SELECT DISTINCT(a.`id`), u.`username` , a.`owner` , a.`title` , a.`description` , a.`address` , a.`phone`
		, a.`store_email` , a.`store_avatar` , a.`fee` , a.`live` AS published, a.`cdate` , a.`mdate` ,
		a.`extra` , a.`company_name`, a.`payment_mode`, a.`pay_detail`, a.`vanityurl`
		 FROM #__kart_store AS a
		 LEFT JOIN #__users AS u ON a.owner = u.id";

		$query .= $this->_buildContentWhere();

		return $query;
	}

	/**
	 * Function to delete vendor
	 *
	 * @param   INT  $id  id
	 *
	 * @return  boolean
	 */
	public function deletevendor($id)
	{
		if (!empty($id))
		{
			$id    = implode(',', $id);
			$db    = JFactory::getDbo();

			$query = "delete FROM `#__kart_role`,`#__kart_store`
			USING `#__kart_store` INNER JOIN `#__kart_role`
			WHERE `#__kart_store`.id = `#__kart_role`.store_id
			AND `#__kart_store`.id IN (" . $id . ")";

			$db->setQuery($query);

			if (!$db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}
	}

	/**
	 * Function to build content where
	 *
	 * @param   integer  $mystores  store id
	 *
	 * @return  query
	 */
	public function _buildContentWhere($mystores = 0)
	{
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$db        = JFactory::getDbo();

		$search = $mainframe->getUserStateFromRequest($option . 'search', 'search', '', 'string');
		$where  = array();

		if (!empty($mystores))
		{
			$user    = JFactory::getUser();
			$where[] = "  owner=" . $user->id . " ";
		}

		if (trim($search) != '')
		{
			// Added by Sneha
			$where[] = "a.title LIKE '%" . $search . "%' OR u.username LIKE '%" . $search . "%' ";
		}

		return $where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
	}

	/**
	 * Function to get vendors
	 *
	 * @param   integer  $mystores  store id
	 *
	 * @return  array
	 */
	public function getVendors($mystores = 0)
	{
		$db = JFactory::getDbo();
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$jinput    = $mainframe->input;
		$option    = $jinput->get('option');
		$query     = $this->_buildQuery();

		// Commented by Sneha, To get this result in buildQuery function for csv export
		$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', '', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		if ($filter_order)
		{
			$qry = "SHOW COLUMNS FROM #__kart_store";
			$db->setQuery($qry);
			$exists = $db->loadobjectlist();

			foreach ($exists as $key => $value)
			{
				$allowed_fields[] = $value->Field;
			}

			if (in_array($filter_order, $allowed_fields))
			{
				$query .= " ORDER BY $filter_order $filter_order_Dir";
			}
		}

		$this->protected_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

		return $this->protected_data;
	}

	/**
	 * Function to edit list
	 *
	 * @param   INT  $zoneid  zone id
	 *
	 * @return  null
	 */
	public function Editlist($zoneid)
	{
		unset($this->protected_data);
		$query       = "SELECT * from #__kart_store where id=$zoneid";
		$this->protected_data = $this->_getList($query);

		return $this->protected_data;
	}

	/**
	 * Function to get total
	 *
	 * @return  int
	 */
	public function getTotal()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->protected_total))
		{
			$query        = $this->_buildQuery();
			$this->protected_total = $this->_getListCount($query);
		}

		return $this->protected_total;
	}

	/**
	 * Function to get pagination
	 *
	 * @return  null
	 */
	public function getPagination()
	{
		if (empty($this->protected_pagination))
		{
			jimport('joomla.html.pagination');
			$this->protected_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->protected_pagination;
	}

	/**
	 * This function save the store detail
	 *
	 * @param   ARRAY  $post    post
	 * @param   INT    $userid  user id
	 *
	 * @return  boolean
	 */
	public function store($post, $userid = '')
	{
		$params = JComponentHelper::getParams('com_quick2cart');
		$email  = $post->get('email', '', 'RAW');

		if (!empty($email))
		{
			$db      = JFactory::getDbo();
			$user    = JFactory::getUser();
			$oldData = '';

			// Get old data if exists
			$id = $post->get('id');

			if (!empty($id))
			{
				$query = "SELECT `id`, store_avatar, header
				 FROM #__kart_store
				 WHERE `id`=" . $id;
				$db->setQuery($query);

				$oldData = $db->loadAssoc($query);
			}

			$oldAvtarPath  = !empty($oldData) ? $oldData['store_avatar'] : '';
			$oldHeaderPath = !empty($oldData) ? $oldData['header'] : '';

			$row        = new stdClass;
			$row->owner = $user->id;

			if ($userid != '')
			{
				$row->owner = $userid;
			}

			$row->description  = $post->get('description', '', 'RAW');
			$row->company_name = $post->get('companyname', '', 'RAW');
			$row->address      = $post->get('address', '', 'RAW');
			$row->phone        = $post->get('phone');
			$row->store_email  = $post->get('email', '', 'RAW');
			$row->city  = $post->get('city', '', 'RAW');
			$row->land_mark  = $post->get('land_mark', '', 'RAW');
			$row->country  = $post->get('storecountry', '', 'RAW');
			$row->pincode  = $post->get('pincode', '', 'RAW');
			$row->region  = $post->get('qtcstorestate', '', 'RAW');
			$row->length_id      = $post->get('qtc_length_class', '', 'INTEGER');
			$row->weight_id      = $post->get('qtc_weight_class', '', 'INTEGER');
			$row->taxprofile_id  = $post->get('taxprofile_id', '', 'INTEGER');
			$row->shipprofile_id = $post->get('qtc_shipProfile', '', 'INTEGER');

			// Added by vbmundhe Dont remove as it is require on install script
			$helper_path = JPATH_SITE . '/components/com_quick2cart/helper.php';

			if (!class_exists('comquick2cartHelper'))
			{
				JLoader::register('comquick2cartHelper', $helper_path);
				JLoader::load('comquick2cartHelper');
			}

			$comquick2cartHelper = new comquick2cartHelper;

			// STORE LOGO IMGE
			$avatar = $post->get('avatar', '', 'RAW');

			if (!empty($avatar))
			{
				$avtar_path = $avatar;
			}
			else
			{
				$img_dimensions   = array();
				$img_dimensions[] = 'storeavatar';

				//  upload avtar

				//  name of file field
				$file_field = "avatar";
				$avtar_path = $comquick2cartHelper->imageupload($file_field, $img_dimensions, 0);
			}

			if (!empty($avtar_path))
			{
				// AVOID  IMAGE OVERWRITE TO NULL WHILE UPDATE
				$row->store_avatar = $avtar_path;
			}

			$header_path = '';
			$row->header = $header_path;
			$extra       = $post->get('extra', '', 'RAW');

			if (!empty($extra))
			{
				// While update , AVOID DONT MAKE EMPTY
				$row->extra = $extra;
			}

			$row->payment_mode = $post->get('paymentMode');

			if (empty($row->payment_mode))
			{
				$row->pay_detail = $post->get('paypalemail', '', 'RAW');
			}
			else
			{
				$row->pay_detail = $post->get('otherPayMethod', '', 'RAW');
			}

			$quick2cartModelVendor = new quick2cartModelVendor;

			$id             = "";
			$title          = $post->get('title', '', 'RAW');
			$storeVanityUrl = $post->get('storeVanityUrl', '', 'RAW');
			$id             = $post->get('id', '', 'RAW');

			// If already present then update
			if (!empty($oldData))
			{
				$row->title     = $title;
				$row->vanityurl = $quick2cartModelVendor->formatttedVanityURL($storeVanityUrl, $title, $id);
				$row->id        = $id = $oldData['id'];
				$row->mdate     = date("Y-m-d");

				try
				{
					$db->updateObject('#__kart_store', $row, 'id');
				}
				catch (RuntimeException $e)
				{
					$this->setError($e->getMessage());

					return 0;
				}

				$mail_on_store_edit = (int) $params->get('mail_on_store_edit');

				if ($mail_on_store_edit === 1)
				{
					// Send store edited email to admin
					$this->SendMailAdminOnStoreEdit($row);
				}

				$role = 1;
				$quick2cartModelVendor->addRoleEntry($id, $role, $row->owner);

				return $id;
			}
			else
			{
				// Insert
				$row->title           = $title;

				$row->vanityurl       = $quick2cartModelVendor->formatttedVanityURL($storeVanityUrl, $title);
				$row->cdate           = date("Y-m-d");
				$row->mdate           = date("Y-m-d");
				$admin_approval = (int) $params->get('admin_approval_stores');

				$mail_on_store_create = (int) $params->get('mail_on_store_create');

				if ($admin_approval == 1)
				{
					$row->live = 0;
				}

				if (!$db->insertObject('#__kart_store', $row, 'id'))
				{
					echo $db->stderr();

					return 0;
				}

				$id = $db->insertid();
				$quick2cartModelVendor->addRoleEntry($row->id, $role = 1, $row->owner);

				if ($mail_on_store_create === 1)
				{
					// Send Approval mail to admin
					$this->SendMailAdminOnCreateStore($row);
					$this->SendMailOwnerOnCreateStore($row);
				}

				global $mainframe;
				$mainframe          = JFactory::getApplication();
				$socialintegration  = $params->get('integrate_with', 'none');
				$streamOnCeateStore = $params->get('streamCeateStore', 1);

				// If (!$mainframe->isAdmin() && $streamOnCeateStore && $socialintegration != 'none')
				if ($socialintegration != 'none')
				{
					$user     = JFactory::getUser();
					$libclass = $comquick2cartHelper->getQtcSocialLibObj();

					// Add in activity.
					if ($streamOnCeateStore)
					{
						$action    = 'addstore';
						$slink = "index.php?option=com_quick2cart&view=vendor&layout=store&store_id=";
						$storeLink = '<a class="" href="' . JUri::root()
						. substr(JRoute::_($slink . $id), strlen(JUri::base(true)) + 1) . '">' . $title . '</a>';

						$originalMsg = JText::sprintf('QTC_ACTIVITY_ADD_STORE', $storeLink);
						$libclass->pushActivity($user->id, $act_type = '', $act_subtype = '', $originalMsg, $act_link = '', $title = '', $act_access = 0);
					}

					// Add points
					$point_system         = $params->get('point_system');
					$options['extension'] = 'com_quick2cart';

					if ($socialintegration == "EasySocial")
					{
						$options['command'] = 'create_store';
						$libclass->addpoints($user, $options);
					}
					elseif ($socialintegration == "JomSocial")
					{
						$options['command'] = 'CreateStore.points';
						$libclass->addpoints($user, $options);
					}
				}
			}

			return $id;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Function to get code
	 *
	 * @param   STRING  $code  code
	 *
	 * @return  boolean
	 */
	public function getcode($code)
	{
		$db  = JFactory::getDbo();
		$qry = "SELECT `id` FROM #__kart_coupon WHERE `code` = " . $db->quote($db->escape(trim($code)));
		$db->setQuery($qry);
		$exists = $db->loadResult();

		if ($exists)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Function to get selected code
	 *
	 * @param   STRING  $code  code
	 * @param   INT     $id    id
	 *
	 * @return  boolean
	 */
	public function getselectcode($code, $id)
	{
		$db  = JFactory::getDbo();
		$qry = "SELECT `code` FROM #__kart_coupon WHERE id<>'{$id}' AND `code` = " . $db->quote($db->escape(trim($code)));
		$db->setQuery($qry);
		$exists = $db->loadResult();

		if ($exists)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Function to add roll entry
	 *
	 * @param   INT     $storeid  Store id
	 * @param   STRING  $role     role
	 * @param   INT     $userid   user id
	 *
	 * @return boolean
	 */
	public function addRoleEntry($storeid, $role, $userid)
	{
		// Get role table id having store id = $storeid
		$action = "insertObject";
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$query->select('id');
		$query->from('#__kart_role');
		$query->where('store_id=' . $storeid);

		$db->setQuery($query);
		$entry = $db->loadResult();

		$row           = new stdClass;
		$row->store_id = $storeid;
		$row->role     = $role;
		$row->user_id  = $userid;

		if ($entry)
		{
			$action  = "updateObject";
			$row->id = $entry;
		}

		if (!$db->$action('#__kart_role', $row, 'id'))
		{
			echo $db->stderr();

			return 0;
		}
	}

	/**
	 * This function provide data  which is require for line graph
	 *
	 * @param   INT     $storeid   Store id
	 * @param   string  $backdate  date
	 * @param   string  $currdate  date
	 *
	 * @return  boolean
	 */
	public function getPeriodicIncomeGrapthData($storeid, $backdate = '', $currdate = '')
	{
		$app      = JFactory::getApplication();
		$backdate = $app->getUserStateFromRequest('from', 'from', '', 'string');
		$currdate = $app->getUserStateFromRequest('to', 'to', '', 'string');

		// Get date for 30 days before, in Y-m-d H:i:s format
		$thirtyDaysBefore = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' - 30 days'));
		$backdate         = !empty($backdate) ? $backdate : JFactory::getDate($thirtyDaysBefore)->Format(JText::_('Y-m-d H:i:s'));

		// Get current date, in Y-m-d H:i:s format
		$currdate         = !empty($currdate) ? $currdate : JFactory::getDate(date('Y-m-d H:i:s'))->Format(JText::_('Y-m-d H:i:s'));

		// Getting current currency
		$comquick2cartHelper = new comquick2cartHelper;
		$currency            = $comquick2cartHelper->getCurrencySession();
		$db                  = JFactory::getDbo();
		$query               = 'SELECT SUM( i.product_final_price)  AS amount, DATE(i.cdate) as cdate, COUNT(o.id) AS orders_count
				FROM `#__kart_order_item` AS i
				LEFT JOIN #__kart_orders AS o ON i.`order_id` = o.id
				WHERE i.store_id=' . $storeid . ' AND DATE(i.cdate) >\'' . $backdate . '\' and DATE(i.cdate) <=\'' . $currdate . '\'
				AND (i.status=\'C\' OR i.status=\'S\')
				AND o.currency=\'' . $currency . '\'
				GROUP BY DAY( i.cdate )
				ORDER BY i.cdate';
		$db->setQuery($query);

		return $db->loadObjectList("cdate");
	}

	/**
	 * Function to get periodic income
	 *
	 * @param   INT     $storeid   Store id
	 * @param   string  $backdate  date
	 * @param   string  $currdate  date
	 *
	 * @return  array
	 */
	public function getPeriodicIncome($storeid, $backdate = '', $currdate = '')
	{
		$app      = JFactory::getApplication();
		$backdate = $app->getUserStateFromRequest('from', 'from', '', 'string');
		$currdate = $app->getUserStateFromRequest('to', 'to', '', 'string');

		// Getting current currency
		$comquick2cartHelper = new comquick2cartHelper;
		$currency            = $comquick2cartHelper->getCurrencySession();

		// Get date for 30 days before, in Y-m-d H:i:s format
		$thirtyDaysBefore = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' - 30 days'));
		$backdate         = !empty($backdate) ? $backdate : JFactory::getDate($thirtyDaysBefore)->Format(JText::_('Y-m-d H:i:s'));

		// Get current date, in Y-m-d H:i:s format
		$currdate         = !empty($currdate) ? $currdate : JFactory::getDate(date('Y-m-d H:i:s'))->Format(JText::_('Y-m-d H:i:s'));

		$db    = JFactory::getDbo();
		$query = 'SELECT SUM(i.product_final_price) AS amount, SUM(i.product_quantity) AS qty, COUNT( DISTINCT (i.order_id)) AS totorders
		 FROM `#__kart_order_item` AS i
		 LEFT JOIN #__kart_orders AS o ON i.`order_id`=o.id
		 WHERE i.store_id=' . $storeid . '
		 AND DATE(i.cdate) >"' . $backdate . '"
		 AND DATE(i.cdate) <="' . $currdate . '"
		 AND (i.status="C" OR i.status="S")
		 AND o.currency="' . $currency . '"';
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * Function to get total sales
	 *
	 * @param   INT  $storeid  Store id
	 *
	 * @return  INT
	 */
	public function getTotalSales($storeid)
	{
		$db                  = JFactory::getDbo();

		// Getting current currency
		$comquick2cartHelper = new comquick2cartHelper;
		$currency            = $comquick2cartHelper->getCurrencySession();
		$query               = 'SELECT SUM(i.product_final_price)
				FROM `#__kart_order_item` AS i
				LEFT JOIN #__kart_orders AS o ON i.`order_id` = o.id
				WHERE i.store_id=' . $storeid . ' AND (i.status=\'C\' OR i.status=\'S\') AND o.currency=\'' . $currency . '\'';
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Function to get last five orders
	 *
	 * @param   INT  $storeid  Store id
	 *
	 * @return  Array
	 */
	public function getLast5orders($storeid)
	{
		$db    = JFactory::getDbo();
		$query = 'SELECT o.name, i.`status`, SUM( i.`product_final_price` ) AS price, o.id, o.`currency`, o.cdate, o.prefix
		 FROM  `#__kart_order_item` AS i
		 LEFT JOIN #__kart_orders AS o ON i.`order_id` = o.id
		 WHERE store_id=' . $storeid . '
		 GROUP BY o.id ORDER BY o.id DESC
		 LIMIT 0,5';
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * Function to store product count
	 *
	 * @param   INT  $storeid  store id
	 *
	 * @return  boolean
	 */
	public function storeProductCount($storeid)
	{
		$db    = JFactory::getDbo();
		$query = 'SELECT count(*)
				FROM  `#__kart_items` where store_id=' . $storeid;
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * This fuction will send customer email to store owner
	 *
	 * @param   ARRAY  $post  post data
	 *
	 * @return  boolean
	 */
	public function sendcontactUsEmail($post)
	{
		jimport('joomla.utilities.utility');
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);
		global $mainframe;
		$mainframe = JFactory::getApplication();
		$comquick2cartHelper  = new comquick2cartHelper;

		// GETTING CONFIGURATION PROPERTIES
		$from     = $mainframe->getCfg('mailfrom');
		$fromname = $mainframe->getCfg('fromname');
		$sitename = $mainframe->getCfg('sitename');

		$store_id            = $post->get("store_id", '', "INTEGER");
		$item_id            = $post->get("item_id", '', "INTEGER");
		$cust_email            = $post->get("cust_email", '', "STRING");
		$store_info          = $comquick2cartHelper->getSoreInfo($store_id);
		$recipient           = $store_info['store_email'];

		// Get enquiry
		$enquiry = $post->get("message", '', "RAW");

		// Get prod detail
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('name')->from('#__kart_items AS i')->where("i.item_id= " . $item_id);
		$db->setQuery($query);
		$itemresult           = $db->loadAssoc();

		// Get Product link
		$prodLink = '<a class="" href="' . JUri::root() . $comquick2cartHelper->getProductLink($item_id) . '">' .
												$itemresult['name'] .
										'</a>';

		$body = JText::sprintf('COM_QUICK2CART_CONTACT_US_BODY', $cust_email, $prodLink, $enquiry);
		$body = str_replace('{sitename}', $sitename, $body);

		// $bcc = array('0'=>$mainframe->getCfg('mailfrom') );

		$cc  = null;
		$bcc = array();
		$attachment  = null;
		$replyto     = null;
		$replytoname = null;
		$subject     = JText::_('COM_QUICK2CART_CONTACT_US_SUBJECT');

		$status = JFactory::getMailer()->sendMail($from, $fromname, $recipient, $subject, $body, $mode = 1, $cc, $bcc, $attachment, $replyto, $replytoname);
	}

	/**
	 * FUnction to get total orders count
	 *
	 * @param   INT  $storeid  store id
	 *
	 * @return  INT
	 */
	public function getTotalOrdersCount($storeid)
	{
		$db                  = JFactory::getDbo();

		// Getting current currency
		$comquick2cartHelper = new comquick2cartHelper;
		$currency            = $comquick2cartHelper->getCurrencySession();

		$query = "SELECT COUNT( DISTINCT (o.`id`) )
							FROM  `#__kart_order_item` AS i, #__kart_orders AS o
							WHERE i.store_id =" . $storeid . "
							AND i.`order_id` = o.id
							AND (o.status =  'C' OR o.status =  'S' )
							AND o.currency =  '" . $currency . "'
							AND i.`order_id` = o.id ";
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Function to check vanity url
	 *
	 * @param   STRING  $vanity       vanity url
	 * @param   string  $oldstore_id  old store id
	 *
	 * @return  boolean
	 */
	public function ckUniqueVanityURL($vanity, $oldstore_id = '')
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__kart_store'));
		$query->where($db->quoteName('vanityurl') . '=' . $db->quote($vanity));

		if (!empty($oldstore_id))
		{
			$query->where($db->quoteName('id') . '!=' . $oldstore_id);
		}

		$db->setQuery($query);
		$id = $db->loadResult();

		if (!empty($id))
		{
			// Present vanity URL
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Function to get formmated vanity URL
	 *
	 * @param   STRING  $vanityurl    vanity URL
	 * @param   STRING  $storeTitle   store title
	 * @param   INT     $oldstore_id  old store id
	 *
	 * @return  STRING
	 */
	public function formatttedVanityURL($vanityurl, $storeTitle, $oldstore_id = '')
	{
		$quick2cartModelVendor = new quick2cartModelVendor;
		$user                  = JFactory::getUser();
		$storeTitle            = trim($storeTitle);

		if (trim($vanityurl) == '')
		{
			$vanityurl = $storeTitle;
		}

		$final_vanity = $vanityurl;

		// Remove all space, tab, new line

		/*$final_vanity=preg_replace("/\s+/", "", $final_vanity);
		$final_vanity = preg_replace("/[^A-Za-z0-9\-]+$/", "", $final_vanity );*/

		$final_vanity = JApplication::stringURLSafe($final_vanity);

		if (trim(str_replace('-', '', $final_vanity)) == '')
		{
			$final_vanity = $user->id . '-' . JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		$i = 1;

		do
		{
			if ($i == 1)
			{
				$status = $quick2cartModelVendor->ckUniqueVanityURL($vanityurl, $oldstore_id);
			}
			else
			{
				// Remove userid: from vanity url if exist AS WE R GOING TO APPEND NEXT
				$vanityurl    = preg_replace('/' . $user->id . '-' . '/', '', $vanityurl, 1);
				$final_vanity = $newvanity = $user->id . '-' . $vanityurl . $i;

				$status = $quick2cartModelVendor->ckUniqueVanityURL($newvanity);
			}

			$i++;
		}
		while ($status != 0);

		return $final_vanity;
	}

	/**
	 * Function to check if store title is qnique or not
	 *
	 * @param   STRING  $title        title
	 * @param   string  $oldstore_id  old store id
	 *
	 * @return  boolean
	 */
	public function ckUniqueStoretitle($title, $oldstore_id = '')
	{
		$db      = JFactory::getDbo();
		$where   = array();
		$title   = $db->quote($db->escape(trim($title), true));
		$where[] = '`title`="' . $title . '"';

		if (!empty($oldstore_id))
		{
			$where[] = ' `id`!=\'' . $oldstore_id . '\' ';
		}

		$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
		$query = 'SELECT `id` FROM `#__kart_store` ' . $where;
		$db->setQuery($query);
		$id = $db->loadResult();

		if (!empty($id))
		{
			// Present title URL
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * This function gives formatted vanity url
	 *
	 * @param   STRING  $title        title
	 * @param   STRING  $oldstore_id  old store id
	 *
	 * @return  boolean
	 */
	public function formatttedTitle($title, $oldstore_id = '')
	{
		$db                    = JFactory::getDbo();
		$quick2cartModelVendor = new quick2cartModelVendor;
		$user                  = JFactory::getUser();
		$i                     = 1;
		$final_title           = $title;

		do
		{
			if ($i == 1)
			{
				$status = $quick2cartModelVendor->ckUniqueStoretitle($title, $oldstore_id);
			}
			else
			{
				$final_title = $title . $i;
				$status      = $quick2cartModelVendor->ckUniqueStoretitle($final_title);
			}

			// Generate new vanity url
			$i++;
		}
		while ($status != 0);

		return $db->escape(trim($final_title), true);
	}

	/**
	 * Function added by Sneha Send email on editing store
	 *
	 * @param   INT  $post  post id
	 *
	 * @return  null
	 */
	public function SendMailAdminOnStoreEdit($post)
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);
		$db      = JFactory::getDbo();
		$loguser = JFactory::getUser();
		$app     = JFactory::getApplication();

		$fromname = $app->getCfg('fromname');
		$sitename = $app->getCfg('sitename');

		$params = JComponentHelper::getParams('com_quick2cart');
		$sendto = $params->get('sale_mail');

		$query = "SELECT store_avatar
		 FROM `#__kart_store`
		 WHERE id=" . $post->id;
		$db->setQuery($query);
		$image = $db->loadColumn();

		$path = JUri::root() . 'images/quick2cart/' . $image[0];

		$subject = JText::_('COM_QUICK2CART_STORE_EDIT_SUBJECT');
		$subject = str_replace('{storename}', $post->title, $subject);

		$body = JText::_('COM_QUICK2CART_STORE_EDIT_BODY');
		$body = str_replace('{storename}', $post->title, $body);
		$body = str_replace('{companyname}', $post->company_name, $body);
		$body = str_replace('{address}', $post->address, $body);
		$body = str_replace('{phone}', $post->phone, $body);
		$body = str_replace('{email}', $post->store_email, $body);
		$body = str_replace('{paypalemail}', $post->pay_detail, $body);
		$body = str_replace('{img}', $path, $body);

		$comquick2cartHelper = new comquick2cartHelper;
		$res                 = $comquick2cartHelper->sendmail($sendto, $subject, $body);
	}

	/**
	 * Function added by Sneha Send email to admin on store creation
	 *
	 * @param   INT  $store  store id
	 *
	 * @return  null
	 */
	public function SendMailAdminOnCreateStore($store)
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);

		$app                   = JFactory::getApplication();
		$sitename              = $app->getCfg('sitename');
		$params                = JComponentHelper::getParams('com_quick2cart');
		$sendto                = $params->get('sale_mail');
		$admin_approval_stores = $params->get("admin_approval_stores", 0);

		if ($admin_approval_stores == 1)
		{
			$subject      = JText::_('COM_QUICK2CART_STORE_APPROVAL_SUBJECT');
			$body         = JText::_('COM_QUICK2CART_STORE_APPROVAL_BODY');
			$allStoreLink = 'administrator/index.php?option=com_quick2cart&view=stores&filter_published=0';
		}
		else
		{
			$subject      = JText::_('COM_QUICK2CART_STORE_ADMIN_NORMAL_EMAIL_SUBJECT');
			$body         = JText::_('COM_QUICK2CART_STORE_ADMIN_NORMAL_EMAIL_SUBJECT_BODY');
			$allStoreLink = 'administrator/index.php?option=com_quick2cart&view=stores';
		}

		$subject             = str_replace('{sitename}', $sitename, $subject);
		$body                = str_replace('{title}', $store->title, $body);
		$body                = str_replace('{description}', $store->description, $body);
		$body                = str_replace('{link}', JUri::root() . $allStoreLink, $body);
		$body                = str_replace('{sitename}', $sitename, $body);
		$comquick2cartHelper = new comquick2cartHelper;
		$res                 = $comquick2cartHelper->sendmail($sendto, $subject, $body);
	}

	/**
	 * Function addded by Sneha to Send email to owner on store creation
	 *
	 * @param   INT  $store  Store id
	 *
	 * @return null
	 */
	public function SendMailOwnerOnCreateStore($store)
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);
		$app                   = JFactory::getApplication();
		$sitename              = $app->getCfg('sitename');
		$sendto                = $store->store_email;
		$params                = JComponentHelper::getParams('com_quick2cart');
		$admin_approval_stores = $params->get("admin_approval_stores", 0);

		if ($admin_approval_stores == 1)
		{
			$subject = JText::_('COM_QUICK2CART_STORE_APPROVAL_OWNER_SUBJECT');
			$body    = JText::_('COM_QUICK2CART_STORE_APPROVAL_OWNER_BODY');
		}
		else
		{
			$subject = JText::_('COM_QUICK2CART_STORE_OWNER_NORMAL_MAIL_SUB');
			$body    = JText::_('COM_QUICK2CART_STORE_OWNER_NORMAL_MAIL_BODY');
		}

		$subject = str_replace('{store_name}', $store->item_name, $subject);
		$subject = str_replace('{sitename}', $sitename, $subject);

		$body = str_replace('{title}', $store->title, $body);
		$body = str_replace('{description}', $store->description, $body);
		$body = str_replace('{sitename}', $sitename, $body);

		$comquick2cartHelper = new comquick2cartHelper;
		$res                 = $comquick2cartHelper->sendmail($sendto, $subject, $body);
	}

	/**
	 * Function to get result in csv
	 *
	 * @return  [type]  [description]
	 */
	public function getCsvexportData()
	{
		$query = $this->_buildQuery();
		$db    = JFactory::getDbo();
		$query = $db->setQuery($query);

		return $data = $db->loadAssocList();
	}

	/**
	 * [getStoreCustomersCount description]
	 *
	 * @param   [type]  $storeId  [description]
	 *
	 * @return  [type]            [description]
	 */
	public function getStoreCustomersCount($storeId)
	{
		$userCount = 0;

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('DISTINCT order_id');
		$query->from('#__kart_order_item AS a');
		$query->where('store_id=' . $storeId);
		$db->setQuery($query);
		$orderIds = $db->loadColumn();

		if ($orderIds)
		{
			$orderIds = implode(',', $orderIds);
			$query    = $db->getQuery(true);
			$query->select('DISTINCT u.user_id');
			$query->from('#__kart_orders AS o');
			$query->join('LEFT', '#__kart_users AS u ON u.user_email=o.email');
			$query->where("u.address_type='BT'");
			$query->where("o.id=u.order_id");
			$query->where("u.order_id IN (" . $orderIds . ")");
			$db->setQuery($query);
			$userIds   = $db->loadColumn();
			$userCount = count($userIds);
		}

		return $userCount;
	}
}
