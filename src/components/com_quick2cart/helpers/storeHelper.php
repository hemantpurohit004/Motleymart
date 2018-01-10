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

// Reference the Dompdf namespace(add it before your class start)
use Dompdf\Dompdf;

/**
 * StoreHelper
 *
 * @package     Com_Quick2cart
 * @subpackage  site
 * @since       2.2
 */
class StoreHelper
{
	/**
	 * Constructor
	 *
	 * @since   2.2
	 */
	public function __construct()
	{
		$this->comquick2cartHelper = new comquick2cartHelper;
	}

	/**
	 * Search in 2d array
	 *
	 * @param   integer  $needle     search key
	 * @param   array    $twoDArray  twoDArray
	 *
	 * @since   2.2
	 * @return   array () AUTHORIZED STORE ID LIST..
	 */
	public function array_search2d($needle, $twoDArray)
	{
		if (!empty($needle))
		{
			foreach ($twoDArray as $key => $value)
			{
				if ($value['store_id'] == $needle)
				{
					return $key;
				}
			}
		}

		return false;
	}

	/**
	 * Get user StoreList
	 *
	 * @param   integer  $store_id            store_id
	 * @param   integer  $catid               catid
	 * @param   integer  $onchangeSubmitForm  WHETHER WE HAVE Submit for or not 1:submit (default)  0:Dont submit
	 * @param   string   $name                name
	 * @param   string   $class               class
	 * @param   integer  $givedropdown        givedropdown
	 *
	 * @since   2.2
	 * @return   array () AUTHORIZED STORE ID LIST..
	 */
	public function getStoreCats($store_id, $catid = '', $onchangeSubmitForm = 1, $name = 'store_cat', $class = '', $givedropdown = 1)
	{
		$db      = JFactory::getDBO();
		$where   = array();
		$where[] = " c.extension='com_quick2cart' ";
		$where[] = " i.category=c.id ";
		$where[] = " i.`store_id`=" . $store_id;

		$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
		$query = "SELECT DISTINCT(id),title FROM `#__categories` AS c,`#__kart_items` AS i " . $where;
		$db->setQuery($query);
		$categories = $db->loadObjectList();

		// GENERATE OPTIONS
		$cats[] = JHtml::_('select.option', '', JText::_('QTC_PROD_SEL_CAT'));

		foreach ($categories as $cat_obj)
		{
			$cats[] = JHtml::_('select.option', $cat_obj->id, $cat_obj->title);
		}
		// If empty then return options
		if (empty($givedropdown))
		{
			return $cats;
		}

		$onchangeSubmitForm = !empty($onchangeSubmitForm) ? 'onchange="document.adminForm.submit();"' : '';
		$dropdown           = JHtml::_('select.genericlist', $cats, $name, $onchangeSubmitForm . ' ' . $class, 'value', 'text', $catid);

		return $dropdown;
	}

	/**
	 * Get user StoreList
	 *
	 * @param   integer  $user_id  user id.
	 *
	 * @since   2.2
	 * @return   array () AUTHORIZED STORE ID LIST..
	 */
	public function getuserStoreList($user_id = '')
	{
		$comquick2cartHelper = new comquick2cartHelper;
		$store_role_list     = $comquick2cartHelper->getStoreIds();
		$store_list          = array();

		foreach ($store_role_list as $store)
		{
			$store_list[] = $store['store_id'];
		}

		return $store_list;
	}

	/**
	 * This public function return array of parent hirarchey childcat-....>topCat
	 *
	 * @param   integer  $catid      cat id.
	 * @param   string   $extension  extension.
	 *
	 * @since   2.2
	 * @return   integer.
	 */
	public function getCatParents($catid, $extension = 'com_quick2cart')
	{
		$parentCats  = array();
		$db          = JFactory::getDBO();
		$storehelper = new storeHelper;

		do
		{
			$category = '';
			$category = $storehelper->getCatDetail($catid, $extension);

			if (!empty($category) && !empty($category['parent_id']))
			{
				$parentCats[] = $category;

				// Shift to parent catid
				$catid        = $category['parent_id'];
			}
			else
			{
				break;
			}
		}
		while (!empty($category));

		return $parentCats;
	}

	/**
	 * This public function return category detail
	 *
	 * @param   integer  $catid      cat id.
	 * @param   string   $extension  extension.
	 *
	 * @since   2.2
	 * @return   integer.
	 */
	public function getCatDetail($catid, $extension = 'com_quick2cart')
	{
		$db    = JFactory::getDBO();
		$query = "SELECT id,title,`parent_id`,`path` FROM #__categories WHERE extension='" . $extension . "' && id=" . $catid;
		$db->setQuery($query);

		return $category = $db->loadAssoc();
	}

	/**
	 * Get getCatHierarchyLink Alias
	 *
	 * @param   integer  $catid      cat id.
	 * @param   string   $extension  extension.
	 *
	 * @since   2.2
	 * @return   integer.
	 */
	public function getCatHierarchyLink($catid, $extension = 'com_quick2cart')
	{
		// GETTING PARENT CATS
		$storehelper    = new storeHelper;
		$parentCatArray = $storehelper->getCatParents($catid, $extension);
		$catcount       = (int) count($parentCatArray);

		// GETTING ITEM ID
		$comquick2cartHelper = new comquick2cartHelper;
		$catItemid           = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=category');

		$linkArray     = array();
		$linkHtmlArray = array();

		for ($i = ($catcount - 1); $i >= 0; $i--)
		{
			$link        = 'index.php?option=com_quick2cart&view=category&prod_cat=' . $parentCatArray[$i]["id"] . '&Itemid=' . $catItemid;

			// CAT LINKS HREF
			$linkArray[] = $link = JUri::root() . substr(JRoute::_($link), strlen(JUri::base(true)) + 1);

			// CAT LINKS html code
			$linkHtmlArray[] = '<a href="' . $link . '">' . $parentCatArray[$i]["title"] . '</a>';
		}

		return $CatHierarchyLink = implode(' >', $linkHtmlArray);
	}

	/**
	 * Get Store Alias
	 *
	 * @param   integer  $store_id  store id.
	 *
	 * @since   2.2
	 * @return   integer.
	 */
	public function getStoreAlias($store_id)
	{
		$db = JFactory::getDBO();
		$q  = "SELECT  vanityurl
				FROM  `#__kart_store`
				WHERE 	`id` = '" . $store_id . "'";
		$db->setQuery($q);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * GetStoreOwner
	 *
	 * @param   integer  $store_id  store id.
	 *
	 * @since   2.2
	 * @return   integer.
	 */
	public function getStoreOwner($store_id)
	{
		$db = JFactory::getDBO();
		$q  = "SELECT  owner
				FROM  `#__kart_store`
				WHERE 	`id` = '" . $store_id . "'";
		$db->setQuery($q);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * GetStoreLink
	 *
	 * @param   integer  $store_id  store id.
	 *
	 * @since   2.2
	 * @return   string.
	 */
	public function getStoreLink($store_id)
	{
		$helperobj = new comquick2cartHelper;
		$Itemid    = $helperobj->getitemid('index.php?option=com_quick2cart&view=category');
		$Itemid = $helperobj->getitemid('index.php?option=com_quick2cart&view=category');
		$mainlink      = 'index.php?option=com_quick2cart&view=vendor&layout=store&store_id=' . $store_id . '&Itemid=' . $Itemid;

		return JRoute::_($mainlink);
	}

	/**
	 * Sneha and vk change for plugin- create new store according to user
	 *
	 * @param   Object  $post    post    obj.
	 * @param   String  $userid  userid  userid.
	 *
	 * @since   2.2
	 * @return   post obj.
	 */
	public function saveVendorDetails($post, $userid = '')
	{
		// Modified for vk-sneha change
		if (empty($userid))
		{
			$user   = JFactory::getUser();
			$userid = $user->id;
		}

		// START Q2C Sample development
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$beforecart = '';
		$result     = $dispatcher->trigger('onQuick2cartBeforeStoreSave', array($post));

		if (!empty($result))
		{
			$post = $result[0];
		}

		// @DEPRICATED
		$result     = $dispatcher->trigger('qtcOnBeforeSaveStore', array($post));

		if (!empty($result))
		{
			$post = $result[0];
		}

		JLoader::import('vendor', JPATH_ADMINISTRATOR . '/components/com_quick2cart/models');
		$model  = new quick2cartModelVendor;
		$result = $model->store($post, $userid);

		if (!empty($result))
		{
			// START Q2C Sample development
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('system');
			$plgresult = $dispatcher->trigger('onQuick2cartAfterStoreSave', array($post, $result));

			// @DEPRICATED
			$plgresult = $dispatcher->trigger('qtcOnAfterSaveStore', array($post, $result));

			$msg = JText::_('COM_QUICK2CART_STOREC_SAVE_M_S');
		}
		else
		{
			$msg = JText::_('COM_QUICK2CART_STOREC_SAVE_M_NS');
		}

		$return             = array();
		$return['store_id'] = $result;
		$return['msg']      = $msg;

		return $return;
	}

	/**
	 * Gives all store's list accoding to the state param (0/1).
	 *
	 * @param   string  $state  0/1.
	 *
	 * @since   2.2
	 * @return   array of store ids.
	 */
	public function getStoreIds($state = '')
	{
		$db = JFactory::getDBO();
		$q  = 'SELECT  `id` FROM  `#__kart_store` ';

		if ($state != '')
		{
			$q .= " WHERE `live` = " . $state;
		}

		$db->setQuery($q);

		return $result = $db->loadColumn();
	}

	/**
	 * Gives basic store info.
	 *
	 * @param   string  $liveStatus  status of store 0/1.
	 * @param   string  $limit       Limit to fetch data.
	 *
	 * @since   2.2
	 * @return   Objectlist of store info.
	 */
	public function getStoreList($liveStatus = '', $limit = '')
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('`id`,`title`,`store_avatar`,`live`');
		$query->from('#__kart_store AS s');

		if ($liveStatus != '')
		{
			$query->where("s.live= " . $liveStatus);
		}

		if ($limit != '')
		{
			$query->setLimit($limit);
		}

		$query->order("cdate");

		$db->setQuery($query);

		return $result = $db->loadObjectList();
	}

	/**
	 * Get store Home Button
	 *
	 * @since   2.2
	 * @return  button html.
	 */
	public function getStoreHomeBtn()
	{
		$comquick2cartHelper = new comquick2cartHelper;
		$cp_Itemid           = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=vendor&layout=cp');

		return "<button type=\"button\"  title='" . JText::_('QTC_BACK_TO_DASH_TTITLE') .
		"' class=\"btn btn-primary  btn_margin btn-small qtc_float_right\" onclick=\"window.open('" .
		JRoute::_('index.php?option=com_quick2cart&view=vendor&layout=cp&Itemid=' . $cp_Itemid) .
		"','_self')\"  > <i class=\"icon-home icon-white\"></i> <?php echo JText::_( 'QTC_BACK_TO_DASH' ); ?>
			</button>";
	}

	/**
	 * This public function check no of store for each user
	 *
	 * @since   2.2
	 * @return  boolean.
	 */
	public function getUserStoreCount()
	{
		$user = JFactory::getUser();
		$db   = JFactory::getDBO();
		$q    = 'SELECT  count(*) FROM  `#__kart_store` where `owner`=' . $user->id;
		$db->setQuery($q);

		return $result = $db->loadResult();
	}

	/**
	 * This public function return store list again user id
	 *
	 * @param   integer  $uid  User Id.
	 *
	 * @since   2.2
	 * @return  boolean.
	 */
	public function getUserStore($uid)
	{
		$user = JFactory::getUser();
		$db   = JFactory::getDBO();
		$q    = 'SELECT  id,title FROM  `#__kart_store` where live=1 and `owner`=' . $uid;
		$db->setQuery($q);

		return $result = $db->loadAssocList();
	}

	/**
	 * This public function return products from user's store. Code done by sanjivani
	 *
	 * @param   integer  $userId   User Id.
	 * @param   integer  $limit    Limit.
	 * @param   integer  $storeid  store id.
	 *
	 * @since   2.2
	 * @return  boolean.
	 */
	public function getStoreProductAgainUsers($userId, $limit = 0, $storeid = '')
	{
		$user = JFactory::getUser();
		$db   = JFactory::getDBO();
		$sql  = 'SELECT SUM(i.item_id) as count, DISTINCT(i.item_id),i.name,i.images,i.featured FROM `#__kart_store` as s , `#__kart_items`  as i
				WHERE s.id = i.store_id AND i.state=1 AND `owner` = ' . $userId;

		if ($storeid)
		{
			$sql .= 'AND i.store_id = ' . $storeid;
		}

		$sql .= ' order by i.item_id desc limit ' . $limit;
		$db->setQuery($sql);

		return $result = $db->loadAssocList();
	}

	/**
	 * This public function check no of store for each user and allowed store limit from  comp param
	 * AND return status to create store
	 *
	 * @since   2.2
	 * @return  boolean.
	 */
	public function isAllowedToCreateNewStore()
	{
		$qtc_params        = JComponentHelper::getparams('com_quick2cart');
		$storeLimitPerUser = $qtc_params->get('storeLimitPerUser');

		$allowToCreateStore = 1;

		if (!empty($storeLimitPerUser))
		{
			$storeHelper = new storeHelper;
			$noOfStore   = $storeHelper->getUserStoreCount();

			if (!empty($noOfStore))
			{
				if ($noOfStore >= $storeLimitPerUser)
				{
					$allowToCreateStore = 0;
				}
			}
		}

		return $allowToCreateStore;
	}

	/**
	 * Check  whether allowed To View Store Order Detail View
	 *
	 * @param   object  $authDetail  object with authentication related details
	 *
	 * @since   2.2
	 * @return  boolean.
	 */
	public function allowToViewStoreOrderDetailView($authDetail)
	{
		// URL store id
		$store_id = $authDetail->store_id;
		$order_user_id = $authDetail->order_user_id;
		$guest_email = $authDetail->guest_email;
		$order_id = $authDetail->order_id;

		$user   = JFactory::getUser();
		$userid = $user->id;

		if ($guest_email)
		{
			// Guest email match then any one can view order detail
			$guest_email_chk = 0;
			$guest_email_chk = $this->comquick2cartHelper->checkmailhash($order_id, $guest_email);

			if (!$guest_email_chk)
			{
				// Zero then
				return $html = "
					<div class='" . Q2C_WRAPPER_CLASS . "'>
					<div class='well' >
						<div class='alert alert-error alert-danger'>
							<span >" . JText::_('QTC_GUEST_MAIL_UNMATCH') . "  </span>
						</div>
					</div>
				</div> ";
			}

			return 1;
		}
		elseif (!empty($userid))
		{
			// Logged in user and order user same
			if ($userid == $order_user_id)
			{
				return 1;
			}

			// Store owner can see the order detail
			$comquick2cartHelper = new comquick2cartHelper;
			$storeidsWithAccess  = $comquick2cartHelper->getStoreIds();

			if (!empty($storeidsWithAccess))
			{
				foreach ($storeidsWithAccess as $storeinfo)
				{
					if ($storeinfo['store_id'] == $store_id)
					{
						return 1;
					}
				}
			}

			return $html = "
						<div class='" . Q2C_WRAPPER_CLASS . "'>
						<div class='well' >
							<div class='alert alert-error alert-danger'>
								<span >" . JText::_('QTC_NOT_AUTHORIZED_USER_TO_VIEW_ORDER') . "  </span>
							</div>
						</div>
					</div> ";
		}
		else
		{
			return $html = "
						<div class='" . Q2C_WRAPPER_CLASS . "'>
						<div class='well' >
							<div class='alert alert-error alert-danger'>
								<span >" . JText::_('QTC_LOGIN') . "  </span>
							</div>
						</div>
					</div> ";
		}
	}

	/**
	 * Total Commission Applied
	 *
	 * @param   integer  $tprice  total price.
	 *
	 * @since   2.2
	 * @return  boolean.
	 */
	public function totalCommissionApplied($tprice)
	{
		$params     = JComponentHelper::getParams('com_quick2cart');
		$commission = $params->get('commission');

		return $commission_cutPrice = ($commission / 100) * ((float) $tprice);
	}

	/**
	 * FOR STORE OWNER :: ALL PRODUCT VIEW
	 * This public function checks whether current_store is releated to logged in user  (vendpor)
	 *
	 * @param   integer  $change_storeto  store id.
	 *
	 * @since   2.2
	 * @return  boolean.
	 */
	public function isVendorsStoreId($change_storeto)
	{
		$comquick2cartHelper = new comquick2cartHelper;
		$store_role_list     = $comquick2cartHelper->getStoreIds();
		$store_list          = array();

		if (empty($store_role_list))
		{
			return 0;
		}

		foreach ($store_role_list as $store)
		{
			$store_list[] = $store['store_id'];
		}

		// FOR STORE OWNER :: ALL PRODUCT VIEW, check change_storeto
		if (!in_array($change_storeto, $store_list))
		{
			// Change store does not beloning to store owners then set vendors first store id as..
			$change_storeto = $store_role_list[0]['store_id'];
		}
		elseif (empty($change_storeto))
		{
			// If current_store=0
			$change_storeto = $store_role_list[0]['store_id'];

			// Not previously set in main frame
		}

		return $change_storeto;
	}

	/**
	 * GetAdminDefaultStoreId
	 *
	 * @since   2.2
	 * @return  Store id.
	 */
	public function getAdminDefaultStoreId()
	{
		// Get default store id
		$db    = JFactory::getDBO();
		$query = "SELECT `id`,`extra` FROM `#__kart_store` WHERE `extra` IS NOT NULL";
		$db->setQuery($query);
		$storedata     = $db->loadAssocList();
		$default_store = 0;

		foreach ($storedata as $data)
		{
			$extraField = json_decode($data['extra'], 1);

			if (!empty($extraField['default']))
			{
				$default_store = $data['id'];
				break;
			}
		}

		return $default_store;
	}

	/**
	 * This public function this public function gives order item store info. [adaptive - used while adding paypout entry]
	 *
	 * @param   integer  $order_id  order id.
	 *
	 * @since   2.2
	 * @return  store info.
	 */
	public function getorderItemsStoreInfo($order_id)
	{
		$db    = JFactory::getDBO();

		// Get order item detail. group by BZ IF IN CART PRODUCT WITH DIFFERENT OPTION IS SELECTED (save item id more than 1 time)
		$query = "Select item_id,SUM(i.`product_final_price`) AS product_final_price,SUM(i.`item_tax`),SUM(i.`item_shipcharges`)
		 FROM `#__kart_order_item` as i
		 WHERE order_id='" . $order_id . "'
		 GROUP BY item_id";

		$db->setQuery($query);
		$order_items = $db->loadAssocList();

		/*$uniqueItem_id = array();
		// remove duplicate item_is( BZ of different option selected douplicate item id may found)
		foreach($order_items as $id) {

		if (!array_key_exists($id, $uniqueItem_id)){
		$uniqueItem_id[$id] = $id;
		}
		}
		*/
		$storeInfo           = array();
		$comquick2cartHelper = new comquick2cartHelper;
		$params              = JComponentHelper::getParams('com_quick2cart');
		$commission          = $params->get("commission", 0);

		foreach ($order_items as $item)
		{
			// Get store infor. Get payee detail having only paypal mode
			$db    = JFactory::getDBO();
			$query = "Select i.item_id as item_id,s.`id` as store_id,s.`owner`,s.payment_mode,s.`pay_detail` ,s.`store_email`
			 FROM `#__kart_order_item` as i JOIN  `#__kart_store` as s
			 ON i.`store_id` = s.`id`
			 WHERE i.`item_id` = '" . $item['item_id'] . "' AND s.payment_mode=0";

			$db->setQuery($query);
			$data = array();
			$data = $db->loadAssoc();

			if (empty($data))
			{
				continue;
			}

			$data['product_final_price'] = $item['product_final_price'];

			if (!empty($commission))
			{
				$data['commissonCutPrice'] = $data['product_final_price'] - (($data['product_final_price'] * $commission) / 100);
			}
			else
			{
				$data['commissonCutPrice'] = $data['product_final_price'];
			}

			$paypalEmail = trim($data['pay_detail']);

			if (!empty($storeInfo[$paypalEmail]))
			{
				// Already  store-item present then take sum of final amount only
				$storeInfo[$paypalEmail]['product_final_price'] = (float) $storeInfo[$paypalEmail]['product_final_price'] + $data['product_final_price'];

				if (!empty($commission))
				{
					$storeInfo[$paypalEmail]['commissonCutPrice'] = (float) $storeInfo[$paypalEmail]['commissonCutPrice'] + $data['commissonCutPrice'];
				}
			}
			else
			{
				// Add data
				$storeInfo[$paypalEmail] = $data;
			}
		}

		return $storeInfo;
	}

	/**
	 * Get Total Sale Per Store. added by Sneha
	 *
	 * @param   Integer  $store_id  store_id.
	 *
	 * @since   2.2
	 * @return  sale.
	 */
	public function getTotalSalePerStore($store_id)
	{
		$db    = JFactory::getDBO();
		$query = " SELECT SUM( product_final_price )
			FROM `#__kart_order_item`
			WHERE store_id = " . $store_id . " AND ( STATUS = 'C' OR STATUS = 'S') ";
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Get to delays in order
	 *
	 * @param   Integer  $order_id  order id.
	 *
	 * @since   2.2
	 * @return  array () Legth class agin store.
	 */
	public function GetDelaysInOrder($order_id)
	{
		$delay        = '';
		$confirm_date = '';
		$ship_date    = '';

		$db    = JFactory::getDBO();
		$query = " SELECT status, mdate FROM #__q2c_order_history WHERE order_id = " . $order_id;
		$db->setQuery($query);
		$result = $db->loadObjectList('status');

		if ((isset($result['S']->mdate)) && $result['S']->mdate)
		{
			$confirm_date = $result['C']->mdate;
			$ship_date    = $result['S']->mdate;
			$date1        = new DateTime($confirm_date);
			$date2        = new DateTime($ship_date);
			$delay        = round(abs($date2->format('U') - $date1->format('U')) / (60 * 60 * 24));

			// $delay = $daysDifference - 1;
		}

		elseif ((isset($result['E']->mdate)) && $result['E']->mdate)
		{
			$confirm_date = $result['C']->mdate;
			$cancel_date  = $result['E']->mdate;
			$date1        = new DateTime($confirm_date);
			$date2        = new DateTime($cancel_date);
			$delay        = round(abs($date2->format('U') - $date1->format('U')) / (60 * 60 * 24));

			// $delay = $daysDifference - 1;
		}
		elseif ((isset($result['C']->mdate)) && $result['C']->mdate)
		{
			$confirm_date = $result['C']->mdate;
			$curr_date    = date("Y-m-d H:i:s");
			$date1        = new DateTime($confirm_date);
			$date2        = new DateTime($curr_date);
			$delay        = round(abs($date2->format('U') - $date1->format('U')) / (60 * 60 * 24));

			// $delay = $daysDifference - 1;
		}

		return $delay;
	}

	/**
	 * Get order history
	 *
	 * @param   Integer  $order_id  order id.
	 *
	 * @since   2.2
	 * @return  array () Legth class agin store.
	 */
	public function getOrderHistory($order_id)
	{
		$db    = JFactory::getDBO();
		$query = " SELECT distinct(STATUS) , mdate FROM `#__q2c_order_history` WHERE order_id = " . $order_id . " GROUP BY order_id,status";
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Get getStoreShippingLegthClassList
	 *
	 * @param   Integer  $storeId  store id.
	 *
	 * @since   2.2
	 * @return  array () Legth class agin store.
	 */
	public function getStoreShippingLegthClassList($storeId = 0)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT * FROM `#__kart_lengths` WHERE `state`=1 and `store_id`='" . $storeId . "'";
		$db->setQuery($query);
		$legth_list = $db->loadObjectList();

		return $legth_list;
	}

	/**
	 * This public function return select box of length classes.
	 *
	 * @param   Integer  $storeId  store id.
	 *
	 * @since   2.2
	 * @return  array () Weigth class agin store,
	 */
	public function getStoreShippingWeigthClassList($storeId = 0)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT * FROM `#__kart_weights` WHERE `state`=1 and `store_id`='" . $storeId . "'";
		$db->setQuery($query);
		$legth_list = $db->loadObjectList();

		return $legth_list;
	}

	/**
	 * This public function return select box of length classes.
	 *
	 * @param   Integer  $storeId     store id.
	 * @param   Integer  $default     default id.
	 * @param   Integer  $fieldName   name of select field.
	 * @param   Integer  $fieldClass  name of select field class.
	 *
	 * @since   2.2
	 * @return  select,
	 */
	public function getLengthClassSelectList($storeId = 0, $default = 0, $fieldName = 'qtc_length_class', $fieldClass = 'qtc_putmargintop10px')
	{
		$units = $this->getStoreShippingLegthClassList($storeId);

		if (!empty($units))
		{
			for ($i = 0; $i < count($units); $i++)
			{
				$options_legth[] = JHtml::_('select.option', $units[$i]->id, $units[$i]->title);
			}

			$attr = 'class="' . $fieldClass . '" size="1"   ';

			return $this->dropdown = JHtml::_('select.genericlist', $options_legth, $fieldName, $attr, 'value', 'text', $default, 'qtc_length_class');
		}
	}

	/**
	 * This public function return select box of weight classes.
	 *
	 * @param   Integer  $storeId     store id.
	 * @param   Integer  $default     default id.
	 * @param   Integer  $fieldName   name of select field.
	 * @param   Integer  $fieldClass  name of select field class.
	 *
	 * @since   2.2
	 * @return  store profile detail.
	 */
	public function getWeightClassSelectList($storeId = 0, $default = 0, $fieldName = 'qtc_weight_class', $fieldClass = 'qtc_putmargintop10px')
	{
		$units = $this->getStoreShippingWeigthClassList($storeId);

		if (!empty($units))
		{
			for ($i = 0; $i < count($units); $i++)
			{
				$options_legth[] = JHtml::_('select.option', $units[$i]->id, $units[$i]->title);
			}

			$attr = 'class="' . $fieldClass . '" size="1"   ';

			return $this->dropdown = JHtml::_('select.genericlist', $options_legth, $fieldName, $attr, 'value', 'text', $default, 'qtc_weight_class');
		}
	}

	/**
	 * Get getStoreProfileDetails.
	 *
	 * @param   INTEGER  $storeId  storeId id.
	 *
	 * @since   1.0
	 * @return  store profile detail.
	 */
	public function getStoreProfileDetails($storeId = 0)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT * FROM `#__kart_taxprofiles` WHERE `state`=1";

		if (!empty($storeId))
		{
			$query .= " AND `store_id`='" . $storeId . "'";
		}

		$db->setQuery($query);
		$tax_list = $db->loadObjectList();

		return $tax_list;
	}

	/**
	 * This method give shipping related fields details.
	 *
	 * @param   INTEGER  $item_id  product's item id.
	 *
	 * @since   1.0
	 * @return  Shipping related fields.
	 */
	public function getProductsShipRelFields($item_id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$sel = "i.`item_id`,i.`item_length`,i.`item_width`,i.`item_height`," .
		"i.`item_length_class_id`,i.`item_weight`,i.`item_weight_class_id`,i.`shipProfileId`";
		$query->select($sel);
		$query->from('#__kart_items AS i');
		$query->where('i.item_id=' . $item_id);
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * Method cCheck whether Taxrate is allowed to delete or not.  If not the enqueue error message accordingly..
	 *
	 * @param   integer  $pk_id  pk_id .
	 *
	 * @since   2.2
	 * @return   boolean true or false.
	 */
	public function isAllowedToDelStore($pk_id)
	{
		// 1. Check entry in kart_order_item  table
		$app   = JFactory::getApplication();
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('DISTINCT(tb1.item_id)');
		$query->from('#__kart_items  AS tb1');
		$query->where('tb1.store_id	=' . $pk_id);
		$db->setQuery($query);
		$entryList = $db->loadColumn();

		if (!empty($entryList))
		{
			// Order list
			// @TODO if possible show product ids in error msg.

			$entryListStr = '(' . JText::_('COM_QUICK2CART_IDS') . implode(',', $entryList) . ')';

			$errMsg = JText::sprintf('COM_QUICK2CART_DEL_STORE_FOUND_ENTRY_IN_ORDERITEMS_TB', $pk_id);
			$app->enqueueMessage($errMsg, 'error');

			return false;
		}

		return true;
	}

	/**
	 * This public function return select box of store tax profile list.
	 *
	 * @param   Integer  $storeId     store id.
	 * @param   Integer  $def         default id.
	 * @param   Integer  $fieldName   name of select field.
	 * @param   Integer  $fieldClass  name of select field class.
	 * @param   Integer  $fieldId     name of select field class.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getStoreTaxProfilesSelectList($storeId = 0, $def = 0, $fieldName = 'taxprofile_id', $fieldClass = '', $fieldId = 'taxprofile_id')
	{
		$units = (array) $this->getStoreProfileDetails($storeId);

		if (!empty($units))
		{
			// Get option List
			$option[] = JHtml::_('select.option', '', JText::_("COM_QUICK2CART_SEL_TAX_PROFILE"));

			foreach ($units as $key => $unit)
			{
				$option[] = JHtml::_('select.option', $unit->id, $unit->name);
			}

			$attri = 'class="' . $fieldClass . '" size="1"   ';

			return $this->dropdown = JHtml::_('select.genericlist', $option, $fieldName, $attri, 'value', 'text', $def, $fieldId);
		}
	}

	/**
	 * This public function return store's default settings.
	 *
	 * @param   integer  $store_id  store_id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getStoreDefaultSettings($store_id)
	{
		if (!empty($store_id))
		{
			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('length_id,weight_id, shipprofile_id,taxprofile_id');
			$query->from('#__kart_store AS s');
			$query->where('s.id=' . $store_id);
			$db->setQuery($query);

			return $shipProfileList = $db->loadAssoc();
		}
	}

	/**
	 * Get getProductLengthDetail
	 *
	 * @param   integer  $lenthId   lenthId.
	 * @param   integer  $store_id  store_id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getProductLengthDetail($lenthId, $store_id = 0)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id,title,unit,value,state');
		$query->from('#__kart_lengths AS l');

		if ($lenthId)
		{
			$query->where('l.id=' . $lenthId);
		}
		else
		{
			// Get default length value (having value=1)
			$query->where('l.value=1');
			$query->where('l.state=1');
		}

		$query->where('l.store_id=' . $store_id);
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * Get getProductWeightDetail
	 *
	 * @param   integer  $weightId  weightId.
	 * @param   integer  $store_id  store_id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getProductWeightDetail($weightId, $store_id = 0)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id,title,unit,value,state');
		$query->from('#__kart_weights AS l');

		if ($weightId)
		{
			$query->where('l.id=' . $weightId);
		}
		else
		{
			// Get default length value (having value=1)
			$query->where('l.value=1');
			$query->where('l.state=1');
		}

		$query->where('l.store_id=' . $store_id);
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * Get Store details
	 *
	 * @param   integer  $storeId  Store id.
	 *
	 * @return   store info
	 *
	 * @since   2.5
	 */
	public function getStoreDetail($storeId)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("*")
		->from('#__kart_store')
		->WHERE('id = ' . $storeId);
		$db->setQuery($query);
		$data = $db->loadAssoc();

		return $data;
	}

	/**
	 * Get default store image. Check for custom image if exist then use other wise use default.
	 *
	 * @return   store info
	 *
	 * @since   2.5
	 */
	public function getDefaultStoreImage()
	{
		$customDefStoreImg_path = JPATH_SITE . '/components/com_quick2cart/assets/images/custom_default_store_image.png';

		if (JFile::exists($customDefStoreImg_path))
		{
			return JUri::root() . 'components/com_quick2cart/assets/images/custom_default_store_image.png';
		}
		else
		{
			return $img = JUri::root() . 'components/com_quick2cart/assets/images/default_store_image.png';
		}
	}

	/**
	 * This function is to generate, store wise invoice PDF
	 *
	 * @return  ''
	 *
	 * @since	2.6
	 */
	public function generateInvoicePDF()
	{
		$app   = JFactory::getApplication();
		$jinput    = JFactory::getApplication()->input;
		$post = $jinput->post;

		// Get order id and store id
		$orderid = $jinput->get('orderid', '', 'INT');
		$store_id = $jinput->get('store_id', '', 'INT');

		if (empty($orderid) || empty($store_id))
		{
			$app->enqueueMessage(JText::_('COM_QUICK2CART_INVOICE_MISSING_STORE_ID'), 'error');

			return 0;
		}

		if (!JFile::exists(JPATH_SITE . "/libraries/techjoomla/dompdf/autoload.inc.php"))
		{
			$app->enqueueMessage(JText::_('COM_QUICK2CART_PDF_LIBRARY_NOT_FOUND'), 'error');

			return 0;
		}

		// Get HTML for the invoice
		$comquick2cartHelper = new comquick2cartHelper;
		$this->orders = $comquick2cartHelper->getorderinfo($orderid, $store_id);
		$qtcfullorder_id = $this->orders['order_info'][0]->prefix . $orderid;
		$view = $comquick2cartHelper->getViewpath('orders', 'invoice');

		// Add Dompdf file path
		require_once  JPATH_SITE . "/libraries/techjoomla/dompdf/autoload.inc.php";

		if (isset($funcs))
		{
			// Import the library loader if necessary.
			if (!class_exists('JLoader'))
			{
				require_once JPATH_PLATFORM . '/loader.php';
			}

			class_exists('JLoader') or die('pdf generation failed');

			// Setup the autoloaders.
			JLoader::setup();

			// Import the cms loader if necessary.
			if (version_compare(JVERSION, '2.5.6', 'le'))
			{
				if (!class_exists('JCmsLoader'))
				{
					require_once JPATH_PLATFORM . '/cms/cmsloader.php';
					JCmsLoader::setup();
				}
			}
			else
			{
				if (!class_exists('JLoader'))
				{
					require_once JPATH_PLATFORM . '/cms.php';
					require_once JPATH_PLATFORM . '/loader.php';
					JLoader::setup();
				}
			}
		}

		require_once JPATH_PLATFORM . '/loader.php';

		ob_start();
		include $view;
		$invoicehtml = ob_get_contents();
		ob_end_clean();

		if (get_magic_quotes_gpc())
		{
			$invoicehtml = stripslashes($html);
		}

		$html      = utf8_decode($invoicehtml);

		$dompdf    = new Dompdf;
		$paper_orientation = 'landscape';
		$customPaper = array(0,0,950,950);
		$dompdf->set_paper($customPaper, $paper_orientation);

		/* $font      = Font_Metrics::get_font("Minim", "normal");
		$txtHeight = Font_Metrics::get_font_height($font, 8);
		*/

		$dompdf->loadHtml($invoicehtml);
		$dompdf->render();

		$output = $dompdf->output();

		// COM_QUICK2CART_INVOICE_PDF_DOWN_TITLE
		$dompdf->stream($qtcfullorder_id . ".pdf");

		// Jexit();
	}

	/**
	 * Get Store details
	 *
	 * @param   integer  $order_id  Store id.
	 *
	 * @return   store info
	 *
	 * @since   2.5
	 */
	public function getOrderUser($order_id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('payee_id');
		$query->from('#__kart_orders');
		$query->where('id=' . $order_id);
		$db->setQuery($query);

		return $db->loadResult();
	}
}
