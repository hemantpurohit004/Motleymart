<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;

// Add Table Path
JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_quick2cart/tables');

/**
 * Routing class from com_quick2cart
 *
 * @subpackage  com_quick2cart
 *
 * @since       1.0.0
 */
class Quick2CartRouter extends JComponentRouterBase
{
	private  $views = array('adduserform', 'createorder', 'productpage', 'shipprofileform', 'vendor',
			'attributes', 'customer_addressform', 'promotion', 'shipprofiles', 'zoneform', 'cart',
			'downloads', 'promotions', 'stores', 'zones', 'cartcheckout', 'taxprofileform', 'category',
			'orders', 'taxprofiles', 'payouts', 'registration', 'taxrateform',
			'product', 'shipping', 'taxrates');

	private  $special_views = array('productpage', 'vendor', 'category');

	private  $menu_views = array('createorder', 'vendor', 'shipprofiles', 'cart',
			'downloads', 'promotions', 'stores', 'zones', 'cartcheckout',
			'orders', 'taxprofiles', 'payouts',
			'product', 'shipping', 'taxrates');

	private  $views_needing_tmpl = array('adduserform', 'customer_addressform');

	/**
	 * Build the route for the com_quick2cart component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   1.0.0
	 */
	public function build(&$query)
	{
		$segments = array();

		// Get a menu item based on Itemid or currently active
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$db = JFactory::getDbo();

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $menu->getActive();
			$menuItemGiven = false;
		}
		else
		{
			$menuItem = $menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_quick2cart')
		{
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		// Are we dealing with an view for which menu is already created
		if (($menuItem instanceof stdClass)
			&& isset($menuItem->query['view']) && isset($query['view']))
		{
			if ($menuItem->query['view'] == $query['view'] && in_array($query['view'], $this->menu_views))
			{
				unset($query['view']);

				if (isset($query['layout']))
				{
					unset($query['layout']);
				}

				return $segments;
			}
		}

		// Check if view is set.
		if (isset($query['view']))
		{
			$view = $query['view'];

			if (isset($query['layout']))
			{
				$layout = $query['layout'];
			}
		}
		else
		{
			// We need to have a view in the query or it is an invalid URL
			return $segments;
		}

		// Add the view only for normal views, for special its just the slug
		if (isset($query['view']) && !in_array($query['view'], $this->special_views))
		{
			$segments[] = $view;

			unset($query['view']);
		}

		/* Handle the special views */
		if ($view == 'category' && isset($query['prod_cat']))
		{
			$catId = (int) $query['prod_cat'];

			if (isset($catId))
			{
				$category = JTable::getInstance('Category', 'JTable', array('dbo', $db));
				$category->load(array('id' => $catId, 'extension' => 'com_quick2cart'));
				$menuItemParams = $menuItem->params;

				if (!empty($menuItemParams))
				{
					$menuWithCategory = $menuItemParams->get('defaultCatId', '');

					if ($menuWithCategory != $catId)
					{
						$segments[] = $category->alias;
					}
				}
				else
				{
					$segments[] = $category->alias;
				}

				if (!empty($query['prod_cat']))
				{
					unset($query['prod_cat']);
				}

				unset($query['view']);
			}
		}
		elseif ($view == 'category' && isset($query['qtcStoreOwner']))
		{
			$segments[] = 'storeproducts';
			$segments[] = $query['layout'];

			unset($query['view']);
			unset($query['qtcStoreOwner']);
			unset($query['layout']);
		}

		if ($view == 'productpage')
		{
			if (isset($query['item_id']))
			{
				$productTable = $this->_getProductRow($query['item_id'], 'item_id');

				$category = JTable::getInstance('Category', 'JTable', array('dbo', $db));
				$category->load(array('id' => $productTable->category, 'extension' => 'com_quick2cart'));
				$menuItemParams = $menuItem->params;

				if (!empty($menuItemParams))
				{
					$menuWithCategory = $menuItemParams->get('defaultCatId', '');

					if ($menuWithCategory != $productTable->category)
					{
						$segments[] = $category->alias;
					}
				}
				else
				{
					$segments[] = $category->alias;
				}

				$segments[] = $productTable->alias;
				unset($query['item_id']);
				unset($query['view']);
			}
		}

		if ($view == 'vendor')
		{
			if (($query['layout'] == 'store' || $query['layout'] == 'createstore' || $query['layout'] == 'storeinfo'))
			{
				if (isset($query['store_id']))
				{
					$storeTable = $this->_getStoreRow($query['store_id'], 'id');

					$segments[] = $storeTable->vanityurl;
					unset($query['store_id']);
					unset($query['view']);

					if (isset($query['store_cat']))
					{
						$category = JTable::getInstance('Category', 'JTable', array('dbo', $db));
						$category->load(array('id' => $query['store_cat'], 'extension' => 'com_quick2cart'));

						$segments[] = $category->alias;
						unset($query['store_cat']);
					}
				}
				else
				{
					$segments[] = $view;
					unset($query['view']);
				}
			}
		}
		/* End Handle the special views */

		/* Handle layouts*/
		if (isset($query['layout']) && $query['layout'] == 'default')
		{
			unset($query['layout']);
		}

		if (isset($query['layout']) && $query['layout'] == 'store' && $view == 'vendor')
		{
			unset($query['layout']);
		}
		else
		{
			if (isset($query['layout']))
			{
				$segments[] = $query['layout'];
				unset($query['layout']);
			}
		}
		/* Handle layouts*/

		/* Handle normal views */
		if ($view == 'promotion' || $view == 'taxrateform' || $view == 'taxprofileform' || $view == 'shipprofileform')
		{
			if (isset($query['id']))
			{
				$segments[] = (INT) $query['id'];
				unset($query['id']);
			}
		}

		if ($view == 'orders')
		{
			if ($layout == 'order')
			{
				if (isset($query['orderid']))
				{
					$segments[] = $query['orderid'];
					unset($query['orderid']);
					unset($query['layout']);
					unset($query['processor']);

					if (isset($query['email']))
					{
						$segments[] = $query['email'];
						unset($query['email']);
					}
				}
			}
		}
		/* End Handle normal views */

		if (in_array($view, $this->views_needing_tmpl))
		{
			unset($query['tmpl']);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$item = $this->menu->getActive();
		$vars = array();
		$db = JFactory::getDbo();

		// Count route segments
		$count = count($segments);

		/*
		 * count = 1 : Courses / Course or non-querystring needing views
		 */
		if ($count == 1)
		{
			$categoryTable = JTable::getInstance('Category', 'JTable', array('dbo', $db));
			$categoryTable->load(array('alias' => $segments[0], 'extension' => 'com_quick2cart'));

			if ($categoryTable->id)
			{
				$vars['view'] = 'category';
				$vars['prod_cat'] = $categoryTable->id;
			}
			elseif ($productId = $this->_getProductRow($segments[0])->item_id)
			{
					$vars['view'] = 'productpage';
					$vars['item_id'] = $productId;
			}
			elseif ($storeId = $this->_getStoreRow($segments[0])->id)
			{
				$vars['view'] = 'vendor';
				$vars['layout'] = 'store';
				$vars['store_id'] = $storeId;
			}
			else
			{
				$vars['view'] = $segments[0];
			}
		}
		else
		{
			$categoryTable = JTable::getInstance('Category', 'JTable', array('dbo', $db));
			$categoryTable->load(array('alias' => $segments[0], 'extension' => 'com_quick2cart'));

			if ($categoryTable->id)
			{
				if ($productId = $this->_getProductRow($segments[1])->item_id)
				{
					$vars['view'] = 'productpage';
					$vars['item_id'] = $productId;
				}
			}
			elseif (($storeId = $this->_getStoreRow($segments[0])->id) && ($segments[1] == 'createstore' || $segments[1] == 'storeinfo'))
			{
					$vars['view'] = 'vendor';
					$vars['store_id'] = $storeId;
					$vars['layout'] = $segments[1];
			}
			elseif (($storeId = $this->_getStoreRow($segments[0])->id)
				&& ($categoryTable->load(array('alias' => $segments[1], 'extension' => 'com_quick2cart'))))
			{
					$vars['view'] = 'vendor';
					$vars['store_id'] = $storeId;
					$vars['layout'] = 'store';
					$vars['store_cat'] = $categoryTable->id;
			}
			else
			{
				$vars['view'] = $segments[0];

				if (in_array($segments[0], $this->views))
				{
					$vars['layout'] = $segments[1];
				}

				switch ($segments[0])
				{
					case 'orders':
					if (isset($segments[1]))
					{
						$vars['layout'] = $segments[1];

						if (isset($segments[2]) && is_numeric($segments[2]))
						{
							$vars['orderid'] = $segments[2];
						}

						if (isset($segments[3]))
						{
							$vars['email'] = $segments[3];
						}
					}
					break;
					case 'promotion':
						if (isset($segments[2]))
						{
							$vars['id'] = (INT) $segments[2];
						}
					break;
					case 'taxrateform':
						$vars['layout'] = 'default';

						if (isset($segments[1]))
						{
							$vars['id'] = (INT) $segments[1];
						}
					break;
					case 'taxprofileform':
						$vars['layout'] = 'default';

						if (isset($segments[1]))
						{
							$vars['id'] = (INT) $segments[1];
						}
					break;
					case 'shipprofileform':
						$vars['layout'] = 'default';

						if (isset($segments[1]))
						{
							$vars['id'] = (INT) $segments[1];
						}
					break;
					case 'storeproducts':
						$vars['view'] = 'category';
						$vars['layout'] = $segments[1];
						$vars['qtcStoreOwner'] = '1';
					break;
					default:
					break;
				}

				if (in_array($segments[0], $this->views_needing_tmpl))
				{
					$vars['tmpl'] = 'component';
				}
			}
		}

		return $vars;
	}

	/**
	 * Get a product row based on alias or id
	 *
	 * @param   mixed   $product  The id or alias of the product to be loaded
	 * @param   string  $input    The field to match to load the product
	 *
	 * @return  object  The product JTable object
	 */
	private function _getProductRow($product, $input = 'alias')
	{
		$db = JFactory::getDbo();
		$table = JTable::getInstance('Product', 'Quick2cartTable', array('dbo', $db));
		$table->load(array($input => $product));

		return $table;
	}

	/**
	 * Get a store row based on alias or id
	 *
	 * @param   mixed   $store  The id or alias of the store to be loaded
	 * @param   string  $input  The field to match to load the store
	 *
	 * @return  object  The store JTable object
	 */
	private function _getStoreRow($store, $input = 'vanityurl')
	{
		$db = JFactory::getDbo();
		$table = JTable::getInstance('Store', 'Quick2cartTable', array('dbo', $db));
		$table->load(array($input => $store));

		return $table;
	}
}
