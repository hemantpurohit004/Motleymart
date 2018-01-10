<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of stores records.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       1.0
 */
class Quick2cartModelCategory extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   2.2
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.item_id',
				'name', 'a.name',
				'state', 'a.state',
				'featured', 'a.featured',
				'parent', 'a.parent',
				'category', 'a.category',
				'store_id', 'a.store_id',
				'cdate', 'a.cdate',
				'item_id', 'a.item_id',
				'published', 'a.state',
				'store', 'a.store_id'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		global $mainframe;
		$mainframe = JFactory::getApplication();
		$jinput = JFactory::getApplication()->input;
		$layout = $jinput->get('layout', 'default', 'STRING');

		// List state information.
		parent::populateState('a.item_id', 'desc');

		// Initialise variables.
		$app = JFactory::getApplication('site');

		// Set pagination limit according from menu settings
		$itemid    = $mainframe->input->get('Itemid', 0, 'int');
		$capplimit = $mainframe->getParams()->get('cat_all_prod_pagination_limit');
		$limit     = $app->getUserStateFromRequest('com_quick2cart.category.list' . $itemid . '.limit', 'limit', $capplimit, 'uint');
		$this->setState('list.limit', $limit);

		$limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);

		if ($limit == 0)
		{
			$this->setState('list.start', 0);
		}
		else
		{
			$this->setState('list.start', $limitstart);
		}

		// Set ordering.
		$orderCol = $app->getUserStateFromRequest($this->context . 'filter_order', 'filter_order');

		if (!in_array($orderCol, $this->filter_fields))
		{
			/* @Deepa Changes */
			/*$orderCol = 'a.item_id';*/
			$orderCol = 'a.ordering';
		}

		$this->setState('list.ordering', $orderCol);

		// Set ordering direction.
		$listOrder = $app->getUserStateFromRequest($this->context . 'filter_order_Dir', 'filter_order_Dir');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'DESC';
		}

		$this->setState('list.direction', $listOrder);

		/* Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
		$this->setState('filter.search', $search);*/

		$prod_sorting = $app->getUserStateFromRequest($this->context . '.sort_products', 'sort_products', '', 'string');
		$this->setState('sort_products', $prod_sorting);

		$published = $app->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $published);

		if ($layout == "my" || $layout == "select_product")
		{
			// Filter category.
			$category = $app->getUserStateFromRequest($this->context . '.filter.category', 'filter_category', '', 'string');
			$this->setState('filter.category', $category);

			// Load the filter state.
			$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
			$this->setState('filter.search', $search);
		}
		else
		{
			// Category filter 2
			/*$prod_cat = $app->getUserStateFromRequest($this->context . 'prod_cat', 'prod_cat', 0, 'INTEGER');*/
			$prod_cat = $jinput->get('prod_cat', 0, 'INTEGER');
			$this->setState('filter.category', $prod_cat);

			// Category menu
			$menu_category = $app->getParams()->get('defaultCatId');
			$this->setState('filter.menu_category', $menu_category);

			// Category search
			$menu_category_search = $app->getParams()->get('qtcCategorySearch');
			$this->setState('filter.qtcCategorySearch', $menu_category_search);

			// Show subcategory prodcuts
			$show_subcat_products = $app->getParams()->get('show_subcat_products');
			$this->setState('filter.show_subcat_products', $show_subcat_products);
		}

		// Filter store.
		$store = $app->getUserStateFromRequest($this->context . '.filter.store', 'current_store', '', 'string');
		$this->setState('filter.store', $store);

		// Load the parameters. Merge Global and Menu Item params into new object
		$params = $app->getParams();
		$menuParams = new JRegistry;

		if ($menu = $app->getMenu()->getActive())
		{
			$menuParams->loadString($menu->params);
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);

		$this->setState('params', $mergedParams);

		// Load the parameters.
		// $params = JComponentHelper::getParams('com_quick2cart');

		// $this->setState('params', $params);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$jinput  = JFactory::getApplication()->input;
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$TjfieldsHelperPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$TjfieldsHelper = new TjfieldsHelper;
		$tjfieldItem_ids = $TjfieldsHelper->getFilterResults();

		$client = JFactory::getApplication()->input->get('client', '', 'string');

		if (!empty($client))
		{
			if ($tjfieldItem_ids != '-2')
			{
				$query->where(" a.item_id IN (" . $tjfieldItem_ids . ") ");
			}
		}

		$user  = JFactory::getUser();
		$jinput = JFactory::getApplication()->input;
		$attributeFilterOptions = explode(',', $jinput->get('attributeoption', '', 'string'));

		// To remove null values from array
		$attributeFilterOptions = array_filter($attributeFilterOptions, 'strlen');

		$layout = $jinput->get('layout', 'default', 'STRING');

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'a.*'));
		$query->select('CASE WHEN bc.discount_price IS NOT NULL THEN bc.discount_price
						ELSE a.price
						END as fprice');
		$query->from('`#__kart_items` AS a');
		$query->JOIN('LEFT', '`#__categories` AS c ON c.id=a.category');
		$query->JOIN('INNER', '`#__kart_base_currency` AS bc ON bc.item_id=a.item_id');

		// Added now
		if ($layout == 'default')
		{
			$path = JPATH_ADMINISTRATOR . '/components/com_quick2cart/models/globalattribute.php';

			if (!class_exists('Quick2cartModelglobalAttribute'))
			{
				JLoader::register('Quick2cartModelglobalAttribute', $path);
				JLoader::load('Quick2cartModelglobalAttribute');
			}

			$globalAttributeModel = new Quick2cartModelglobalAttribute;
			$filtersArray = array();

			foreach ($attributeFilterOptions as $attributeFilterOption)
			{
				$attributeId = $globalAttributeModel->getOptionsAttributeId($attributeFilterOption);

				if (array_key_exists($attributeId, $filtersArray))
				{
					$filtersArray[$attributeId][] = $attributeFilterOption;
				}
				else
				{
					$filtersArray[$attributeId][] = $attributeFilterOption;
				}
			}

			$i = 1;

			foreach ($filtersArray as $attribute => $option)
			{
				if (!empty($option))
				{
					$query->JOIN('INNER', '`#__kart_itemattributes` AS ia' . $i . ' ON ia' . $i . '.item_id=a.item_id');
					$query->JOIN('INNER', '`#__kart_itemattributeoptions` AS iao' . $i .
					' ON iao' . $i . '.itemattribute_id=ia' . $i . '.itemattribute_id');
					$query->where('iao' . $i . '.global_option_id IN (' . implode(',', $option) . ')');
					$i++;
				}
			}
		}

		// Show product only if display_in_product_catlog set to 1
		$query->where('a.display_in_product_catlog = 1');

		/* TODO : Use between clause instead of for loop
		Price range for products
		if (!empty($min_limit) && !empty($max_limit))
		{
		$query->where('a.price BETWEEN ' . $min_limit . ' AND ' . $max_limit);
		}*/

		// Added now end

		// Filter by search in title.
		$search = $jinput->get('filter_search', '', 'STRING');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.item_id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.name LIKE ' . $search . ' OR a.description LIKE ' . $search . ' OR a.metakey LIKE ' . $search . ')');
			}
		}

		if ($layout == 'default')
		{
			/*$query->where('(a.state = 1)');*/

			// Show only the native products and published category,
			$query->where('(a.state = 1)');
			$query->where(" c.published = 1");
			$query->where(" a.parent = 'com_quick2cart'");
			$storeHelper = new storeHelper;
			$storeIds = $storeHelper->getStoreIds(1);

			if (!empty($storeIds))
			{
				$storeidStr = implode(',', $storeIds);
				$query->where(" a.store_id IN (" . $storeidStr . ')');
			}
			else
			{
				// If all stores are unpublished then dont show
				$query->where(" a.store_id = -1");
			}

			// When prod_cat is found in URL
			$filter_category = $this->state->get("filter.category");

			if ($filter_category)
			{
				// Show from decedor category
				$catWhere = $this->getWhereCategory($filter_category);

				if ($catWhere)
				{
					foreach ($catWhere as $cw)
					{
						$query->where($cw);
					}
				}
			}
			else
			{
				$filter_menu_category = $this->state->get("filter.menu_category");
				$filter_menu_category_search = $this->state->get("filter.qtcCategorySearch");

				if ($filter_menu_category)
				{
					$filter_show_subcat_products = $this->state->get("filter.show_subcat_products");

					if ($filter_show_subcat_products)
					{
						$catWhere = $this->getWhereCategory($filter_menu_category);

						if ($catWhere)
						{
							foreach ($catWhere as $cw)
							{
								$query->where($cw);
							}
						}
					}
					else
					{
						$query->where("a.category = '" . $db->escape($filter_menu_category) . "'");
					}
				}

				// If menu with search keyword created
				if (!empty($filter_menu_category_search))
				{
					$filter_menu_category_search = $db->Quote('%' . $db->escape($filter_menu_category_search, true) . '%');
					$query->where('( a.name LIKE ' . $filter_menu_category_search
					. ' OR a.description LIKE ' . $filter_menu_category_search . ' OR a.metakey LIKE ' . $filter_menu_category_search . ')');
				}
			}

			$productSorting = $this->getState('sort_products');

			// For sorting products according to price
			if ($productSorting != '')
			{
				if ($productSorting == 'PRICE_DESC')
				{
					$query->order('fprice DESC');
				}
				elseif ($productSorting == 'PRICE_ASC')
				{
					$query->order('fprice ASC');
				}
				elseif ($productSorting == 'FEATURED')
				{
					$query->order('a.featured DESC');
				}
				elseif ($productSorting == 'CREATED_DESC')
				{
					$query->order('a.cdate DESC');
				}
				elseif ($productSorting == 'CREATED_ASC')
				{
					$query->order('a.cdate ASC');
				}
			}
			else
			{
				// TODO: move this code below for ordering
				$currentLayout = $jinput->get('layout', '', 'string');

				if ($currentLayout = "my")
				{
					$query->order('a.cdate ASC');
				}
			}
		}
		else
		{
			$filter_category = $this->state->get("filter.category");

			if ($filter_category)
			{
				$catWhere = $this->getWhereCategory($filter_category);

				if ($catWhere)
				{
					foreach ($catWhere as $cw)
					{
						$query->where($cw);
					}
				}
			}

			// Filter by published state.
			$published = $this->getState('filter.published');

			if (is_numeric($published))
			{
				$query->where('a.state = ' . (int) $published);
			}
			elseif ($published === '')
			{
				if ($layout == 'my')
				{
					$query->where('(a.state IN (0, 1))');
				}
				else
				{
					$query->where('(a.state = 1)');
				}
			}

			// My stores view.
			// Filter by store.
			$filter_store = $this->state->get("filter.store");

			// Get all published stores by logged in user
			$comquick2cartHelper = new comquick2cartHelper;

			if ($layout != 'select_product')
			{
				$my_stores = $comquick2cartHelper->getStoreIds($user->id);

				if (count($my_stores))
				{
					$stores = array();

					// Get all store ids
					foreach ($my_stores as $key => $value)
					{
						$stores[] = $value["store_id"];
					}

					// If store filter is selected, check it in my stores array
					if ($filter_store)
					{
						if (in_array($filter_store, $stores))
						{
							$query->where("a.store_id = '" . $db->escape($filter_store) . "'");
						}
					}
					else
					{
						// If selected store filter is not found in my stores array, show products from all stores for logged in user
						$stores = implode(',', $stores);

						if (!empty($stores))
						{
							$query->where(" a.store_id IN (" . $stores . ")");
						}
					}
				}
				else
				{
					// Unauthorized access
					$query->where(" a.store_id=0");
				}
			}
			else
			{
				$store_id = $jinput->get('store_id', '0', 'INT');
				$query->where(" a.store_id=" . $store_id);
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		$query->order($db->qn('a.ordering') . 'ASC');

		$query->group('a.item_id');

		return $query;
	}

	/**
	 * Method to get a list of products.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		$jinput = JFactory::getApplication()->input;

		// Variable to store prices for price filter
		$min_limit = $jinput->get('min_price', '', 'int');
		$max_limit = $jinput->get('max_price', '', 'int');

		if ($min_limit > $max_limit)
		{
			$temp = $min_limit;
			$min_limit = $max_limit;
			$max_limit = $temp;
		}

		// Show products from price range
		if (!empty($min_limit) && !empty($max_limit))
		{
			$i = 0;

			foreach ($items as $item)
			{
				$price = (int) $item->fprice;

				if ($price < $min_limit || $price > $max_limit)
				{
					unset($items[$i]);
				}

				$i++;
			}
		}

		return $items;
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   array    $items  The array of record ids.
	 * @param   integer  $state  The value of the property to set or null.
	 *
	 * @return  integer  The number of records updated.
	 *
	 * @since   2.2
	 */
	public function setItemState($items, $state)
	{
		$db = $this->getDbo();
		$app = JFactory::getApplication();

		if ($state === 1)
		{
			$params = JComponentHelper::getParams('com_quick2cart');
			$admin_approval = (int) $params->get('admin_approval');

			// If admin approval is on for stores
			if ($admin_approval === 1)
			{
				$app->enqueueMessage(JText::_('COM_QUICK2CART_ERR_MSG_ADMIN_APPROVAL_NEEDED_PRODUCTS'), 'error');

				return 0;
			}
		}

		$count = 0;

		if (is_array($items))
		{
			foreach ($items as $id)
			{
				$query = $db->getQuery(true);

				// Update the reset flag
				$query->update($db->quoteName('#__kart_items'))
					->set($db->quoteName('state') . ' = ' . $state)
					->where($db->quoteName('item_id') . ' = ' . $id);

				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					$this->setError($e->getMessage());

					return 0;
				}

				$count++;
			}
		}

		return $count;
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   array  $items  An array of primary key value to delete.
	 *
	 * @return  int  Returns count of success
	 */
	public function delete($items)
	{
		$db = $this->getDbo();
		$app = JFactory::getApplication();

		$count = 0;

		if (is_array($items))
		{
			foreach ($items as $id)
			{
				$productHelper = new productHelper;
				$res = $productHelper->deleteWholeProduct($id);
				$productHelper->deleteNotReqProdImages($id, '');

				if (!empty($res))
				{
					$count++;
				}
				else
				{
					$app->enqueueMessage(JText::_('COM_QUICK2CART_MSG_ERROR_DELETE_PRODUCT'), 'error');

					return 0;
				}
			}
		}

		return $count;
	}

	/**
	 * Get sub cateogry.
	 *
	 * @param   integer  $categoryId  category Id.
	 *
	 * @return  array    Store ids.
	 */
	public function getWhereCategory($categoryId)
	{
		$db = JFactory::getDBO();
		$where = array();

		if (JVERSION >= '3.0')
		{
			$cat_tbl = JTable::getInstance('Category', 'JTable');
			$cat_tbl->load($categoryId);
			$rgt = $cat_tbl->rgt;
			$lft = $cat_tbl->lft;
			$baselevel = (int) $cat_tbl->level;
			$where[] = 'c.lft >= ' . (int) $lft;
			$where[] = 'c.rgt <= ' . (int) $rgt;
		}
		else
		{
			// Create a subquery for the subcategory list
			$subQuery = $db->getQuery(true);
			$subQuery->select('sub.id');
			$subQuery->from('#__categories as sub');
			$subQuery->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt');
			$subQuery->where('this.id = ' . (int) $categoryId);

			/*if ($levels >= 0)
			{
			$subQuery->where('sub.level <= this.level + '.$levels);
			}*/

			$db->setQuery($subQuery);
			$result = $db->loadColumn();

			if ($result)
			{
				$result = implode(',', $result);
				$where[] = ' c.id IN (' . $result . ',' . $categoryId . ')';
			}
			else
			{
				$where[] = ' c.id = ' . $categoryId;
			}
		}

		return $where;
	}
}
