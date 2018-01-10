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

jimport('joomla.application.component.view');
require_once JPATH_ADMINISTRATOR . '/components/com_quick2cart/models/zone.php';

/**
 * View class for vendor.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewVendor extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$Quick2cartModelZone = new Quick2cartModelZone;
		$this->params        = JComponentHelper::getParams('com_quick2cart');
		$comquick2cartHelper = new comquick2cartHelper;
		$storeHelper         = new storeHelper;

		$model     = $this->getModel('vendor');
		$mainframe = JFactory::getApplication();
		$input     = $mainframe->input;

		$option          = $input->get('option');
		$task            = $input->get('task');
		$layout          = $input->get('layout', 'cp');
		$this->adminCall = $adminCall = $input->get('adminCall', 0, 'INTEGER');
		$store_id        = $input->get('store_id', '0');
		$this->storeinfo = '';

		if ($layout != "contactus")
		{
			$specialAccess = 0;

			if ($layout == "createstore")
			{
				$this->countrys = $Quick2cartModelZone->getCountry();
				$user = JFactory::getUser();

				if (!$user->id)
				{
					echo "<div class=\"techjoomla-bootstrap\" >
					<div class=\"well\" >
						<div class=\"alert alert-danger\">
							<span >" . JText::_('QTC_LOGIN') . " </span>
						</div>
					</div>
					</div>";

					return false;
				}

				if (!empty($adminCall))
				{
					$specialAccess = $comquick2cartHelper->isSpecialAccess();
				}
			}

			if ($layout == "createstore" || $layout == "managestore" || $layout == "cp")
			{
				// Check for multivender COMPONENT PARAM
				$isMultivenderOFFmsg = $comquick2cartHelper->isMultivenderOFF();
			}

			if (!empty($isMultivenderOFFmsg))
			{
				if (!empty($adminCall))
				{
					// CALLED FROM ADMIN
					if ($specialAccess == 0)
					{
						echo $this->specialAccessMsg();

						return false;
					}
				}
				else
				{
					print $isMultivenderOFFmsg;

					return false;
				}
			}
		}

		/*if($layout=="default")
		{
		$this->site=1;
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'desc',			'word' );
		$filter_type		= $mainframe->getUserStateFromRequest( "$option.filter_type",		'filter_type', 		0,			'string' );
		$filter_state = $mainframe->getUserStateFromRequest( $option.'search_list', 'search_list', '', 'string' );
		$search = $mainframe->getUserStateFromRequest( $option.'search', 'search','', 'string' );
		$search = JString::strtolower( $search );
		$limit = '';
		$limitstart = '';
		$cid[0]='';
		if($search==null)
		$search='';

		$model	= $this->getModel( 'vendor' );
		$task = $input->get('task');

		$total 		= $model->getTotal();
		$this->pagination = $pagination = $model->getPagination();

		$this->storeinfo = $storeinfo = $comquick2cartHelper->getStoreDetail();

		$lists['search_select']	= $search;
		$lists['search']		= $search;
		$lists['search_list']	= $filter_state;
		$lists['order']			= $filter_type;
		$lists['order_Dir']		= $filter_order_Dir;
		$lists['limit']			= $limit;
		$lists['limitstart']	= $limitstart;
		$this->lists = $lists;

		}// end of $layout=="mystores" if
		else */

		if ($layout == "createstore")
		{
			$this->orders_site        = 1;
			$store_id                 = $input->get('store_id', '0');

			// DEFAULT ALLOW TO CREAT STORE
			$this->allowToCreateStore = 1;

			// Means edit task
			if (!empty($store_id))
			{
				$this->store_authorize = $comquick2cartHelper->store_authorize("vendor_createstore", $store_id);
				$this->editview        = 1;
				$this->storeinfo       = $storeinfo = $comquick2cartHelper->editstore($store_id);

				// Get weight and length select box
				$this->legthList  = $storeHelper->getLengthClassSelectList($storeid = 0, $this->storeinfo[0]->length_id);
				$this->weigthList = $storeHelper->getWeightClassSelectList($storeid = 0, $this->storeinfo[0]->weight_id);
			}
			else
			{
				// NEW STORE TASK:: CK FOR WHETHER WE HV TO ALLOW OR NOT
				$storeHelper              = new storeHelper;
				$this->allowToCreateStore = $storeHelper->isAllowedToCreateNewStore();

				// Get weight and length select box
				$this->legthList          = $storeHelper->getLengthClassSelectList($storeid = 0, 0);
				$this->weigthList         = $storeHelper->getWeightClassSelectList($storeid = 0, 0);
			}

			// START Q2C Sample development
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('system');

			// @DEPRICATED
			$result              = $dispatcher->trigger('qtcOnBeforeEditStore', array($store_id));

			// Call the plugin and get the result
			$beforecart          = '';
			$OnBeforeCreateStore = '';

			if (!empty($result))
			{
				// If more than one plugin returns

				/* $OnBeforeCreateStore = $result[0];
				 $OnBeforeCreateStore = join('', $result);*/
				$OnBeforeCreateStore = trim(implode("\n", $result));
			}

			$result              = $dispatcher->trigger('onQuick2cartBeforeStoreEdit', array($store_id));

			if (!empty($result))
			{
				// If more than one plugin returns

				/* $OnBeforeCreateStore = $result[0];
				 $OnBeforeCreateStore = join('', $result);*/
				$OnBeforeCreateStore .= trim(implode("\n", $result));
			}

			$this->OnBeforeCreateStore = $OnBeforeCreateStore;
		}
		elseif ($layout == "managestore")
		{
			$this->storeDetailInfo = $comquick2cartHelper->getSoreInfoInDetail($store_id);
		}
		elseif ($layout == "cp")
		{
			$this->catpage_Itemid         = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=category');
			$this->orders_itemid          = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=orders');
			$this->store_customers_itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=orders&layout=mycustomer');
			$user                         = JFactory::getUser();

			if ($user->id)
			{
				// Chck whetere there is any product or not
				// Retrun store_id,role etc with order by role,store_id
				$this->store_role_list = $store_role_list = $comquick2cartHelper->getStoreIds();

				// Store_id is changed from manage storeorder view
				$change_storeto = $input->get('change_store', '');

				// When chage store,get latest storeid otherwise( on first load) set first storeid as default
				$firstStore = (!empty($store_role_list[0]['store_id']) ? $store_role_list[0]['store_id'] : '');
				$this->store_id = $store_id = (!empty($change_storeto)) ? $change_storeto : $firstStore;
			}

			if (!empty($this->store_id))
			{
				$this->prodcountprodCount = $model->storeProductCount($this->store_id);

				// $allincome = $this->get( 'AllOrderIncome');
				$this->getPeriodicIncomeGrapthData = $model->getPeriodicIncomeGrapthData($store_id);

				// Get revenue ,total order, and qty
				$this->getPeriodicIncome = $model->getPeriodicIncome($store_id);

				// GETTING TOATL SALES
				$this->totalSales = $model->getTotalSales($store_id);

				// GETTING TOtal orders
				$this->totalOrdersCount = $model->getTotalOrdersCount($store_id);

				// GETTING LAST 5 ORDERS
				$this->last5orders     = $model->getLast5orders($store_id);

				// Getting store detail
				$this->storeDetailInfo = $comquick2cartHelper->getSoreInfoInDetail($store_id);

				// Get customer count for store.
				$this->storeCustomersCount = $model->getStoreCustomersCount($store_id);

				// Get top seller products.
				$product_path = JPATH_SITE . '/components/com_quick2cart/helpers/product.php';

				if (!class_exists('productHelper'))
				{
					JLoader::register('productHelper', $product_path);
					JLoader::load('productHelper');
				}

				$productHelper           = new productHelper;
				$this->topSellerProducts = $productHelper->getTopSellerProducts($store_id, '', 5);
			}
		}
		elseif ($layout == "store")
		{
			global $mainframe;
			$mainframe = JFactory::getApplication();
			$jinput    = $mainframe->input;

			// Store_id is changed from  STORE view
			// $change_storeto= $mainframe->getUserStateFromRequest( 'current_store', 'current_store','', 'INTEGER' );
			$this->change_prod_cat = $jinput->get('store_cat', 0, 'INTEGER');

			// GET STORE ID
			$this->store_id = $store_id = $input->get('store_id');

			// RESET ENTITIES
			// $mainframe->setUserState('store_cat', '');

			if (!empty($this->store_id))
			{
				$this->storeDetailInfo = $comquick2cartHelper->getSoreInfoInDetail($store_id);

				require_once JPATH_SITE . '/components/com_tjfields/helpers/geo.php';
				$tjGeoHelper = TjGeoHelper::getInstance('TjGeoHelper');

				$this->storeDetailInfo = $comquick2cartHelper->getSoreInfoInDetail($this->store_id);

				if ($this->storeDetailInfo["country"])
				{
					$this->storeDetailInfo["country"] = $tjGeoHelper->getCountryNameFromId($this->storeDetailInfo["country"]);
				}

				if ($this->storeDetailInfo["region"])
				{
					$this->storeDetailInfo["region"] = $tjGeoHelper->getRegionNameFromId($this->storeDetailInfo["region"]);
				}

				// ALL FETCH ALL CATEGORIES
				$storeHelper           = new storeHelper;
				$this->cats            = $storeHelper->getStoreCats($this->store_id, $this->change_prod_cat, 1, 'store_cat');

				// FETCH ALL STORE PRODUCT
				JLoader::import('store', JPATH_SITE . '/components/com_quick2cart/models');
				$model              = new Quick2cartModelstore;

				$fetchFeatured = !empty($this->change_prod_cat) ? 1 : 0;
				$this->allStoreProd = $model->getAllStoreProducts('com_quick2cart', $this->store_id, $fetchFeatured);
				$pagination       = $model->getPagination('com_quick2cart', $this->store_id);

				// @allCCK

				/* $this->allStoreProd = $model->getAllStoreProducts('', $this->store_id);
				$pagination       = $model->getPagination('', $this->store_id);*/

				$this->pagination = $pagination;
			}
		}
		elseif ($layout == "contactus")
		{
			$this->store_id = $input->get('store_id', '0', 'INTEGER');
			$this->item_id = $input->get('item_id', '0', 'INTEGER');
		}
		elseif ($layout == "storeinfo")
		{
			$this->store_id = $input->get('store_id');

			if (!empty($this->store_id))
			{
				$this->storeDetailInfo = $comquick2cartHelper->getSoreInfoInDetail($this->store_id);
			}
		}

		$this->_setToolBar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function _setToolBar()
	{
		// Added by aniket for task #25690
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('QTC_VENDOR_PAGE'));
	}

	/**
	 * SpecialAccessMsg
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function specialAccessMsg()
	{
		return $msg = "<div class=\"techjoomla-bootstrap\" >
				<div class=\"well\" >
					<div class=\"alert alert-danger\">
						<span >" . JText::_('QTC_SPECAIL_ACCESS_MSG') . " </span>
					</div>
				</div>
				</div>";
	}
}
