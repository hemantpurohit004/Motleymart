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
 * Methods supporting product records.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       1.0
 */
class ProductHelper
{
	/**
	 * get all featured products
	 *
	 * @param   integer  $store_id  store id
	 * @param   integer  $prod_cat  product category id
	 * @param   integer  $limit     limit
	 *
	 * @since   2.2.2
	 *
	 * @return   array product list
	 */
	public function getAllFeturedProducts($store_id = '', $prod_cat = '', $limit = '4')
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('i.item_id', 'i.featured', 'i.name', 'i.images', 'i.store_id')));
		$query->select($db->quoteName(array('i.slab', 'i.min_quantity', 'i.max_quantity', 'i.parent', 'i.product_id', 'i.stock')));
		$query->from($db->quoteName("#__kart_items", "i"));
		$query->where($db->quoteName('i.featured') . '=1');
		$query->where($db->quoteName('i.state') . '=1');
		$query->where($db->quoteName('i.display_in_product_catlog') . '=1');

		if (!empty($store_id))
		{
			$query->where($db->quoteName('i.store_id') . '=' . (INT) $store_id);
		}

		if (!empty($prod_cat))
		{
			$query->where($db->quoteName('i.category') . '=' . (INT) $prod_cat);
		}

		$db->setQuery($query);

		return $data = $db->loadAssocList();
	}

	/**
	 * This function gives top sellers
	 *
	 * @param   integer  $store_id  store id
	 * @param   integer  $prod_cat  product category id
	 * @param   integer  $limit     limit
	 * @param   integer  $parent    parent
	 *
	 * @since   2.2.2
	 *
	 * @return   array product list
	 */
	public function getTopSellerProducts($store_id = '', $prod_cat = '', $limit = 5, $parent = "")
	{
		// Payment completed, and shipped are considered
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('i.item_id', 'i.name', 'i.images', 'i.store_id', 'i.slab')));
		$query->select($db->quoteName(array('i.min_quantity', 'i.max_quantity', 'i.featured', 'i.parent', 'i.product_id', 'i.stock')));
		$query->select('SUM(' . $db->quoteName('oi.product_quantity') . ') AS qty');
		$query->from($db->quoteName("#__kart_order_item", "oi"));
		$query->JOIN('INNER', $db->quoteName("#__kart_items", "i") . " ON " . $db->quoteName('oi.item_id') . "=" . $db->quoteName('i.item_id'));
		$query->JOIN('INNER', $db->quoteName("#__kart_orders", "o") . " ON " . $db->quoteName('oi.order_id') . "=" . $db->quoteName('o.id'));
		$query->where($db->quoteName('o.status') . " IN('C', 'S')");
		$query->where($db->quoteName('i.state') . '=1');
		$query->where($db->quoteName('i.display_in_product_catlog') . '=1');

		if (!empty($store_id))
		{
			$query->where($db->quoteName('i.store_id') . '=' . (INT) $store_id);
		}

		if (!empty($prod_cat))
		{
			$query->where($db->quoteName('i.category') . '=' . (INT) $prod_cat);
		}

		if (!empty($parent))
		{
			$query->where($db->quoteName('i.parent') . '=' . (INT) $parent);
		}

		$query->group($db->quoteName("i.item_id"));
		$query->order("qty DESC");
		$query->setLimit($limit);

		$db->setQuery($query);

		return $data = $db->loadAssocList();
	}

	/**
	 * This function gives all featured prod
	 *
	 * @param   integer  $limit  limit
	 *
	 * @since   2.2.2
	 *
	 * @return   array product list
	 */
	public function getTopSellerStore($limit = '')
	{
		$db = JFactory::getDBO();

		$query = 'SELECT SUM( oi.`product_quantity` ) AS qty, s.id,s.store_avatar,s.title, s.live
		FROM `#__kart_order_item` AS oi
		INNER JOIN `#__kart_store` AS s ON oi.`store_id` = s.`id`
		INNER JOIN `#__kart_orders` AS o ON oi.`order_id` = o.`id`
		 ' . " where o.`status` =  'C'" . "
		GROUP BY s.`id`
		ORDER BY qty DESC ";

		if ($limit != '')
		{
			$query .= " LIMIT 0, " . $limit;
		}

		/*$query = $db->getQuery(true)->select('SUM( oi.`product_quantity` ) AS qty, s.id,s.store_avatar,s.title, s.live')
		->from('#__kart_order_item AS oi')
		->join('INNER', '#__kart_store AS s ON oi.`store_id` = s.`id`')
		->join('INNER', '#__kart_orders AS o ON oi.`order_id` = o.`id`');
		$query->where("o.`status` =  'C'");
		$query->group('s.`id`');
		$query->group('qty DESC');
		$query->where('s.live=1');

		if ($limit != '')
		{
		$query->setLimit($limit);
		}*/

		$db->setQuery($query);

		return $data = $db->loadAssocList();
	}

	/**
	 * This function Products from same category
	 *
	 * @param   integer  $cat      category id from which we hv to select.
	 * @param   integer  $item_id  item_id id.
	 * @param   integer  $parent   products client
	 * @param   integer  $limit    Limit to fetch
	 *
	 * @since   2.2.2
	 *
	 * @return   array product list
	 */
	public function getSimilarProdFromCat($cat, $item_id, $parent = '', $limit = 4)
	{
		$where = array();

		if (empty($cat))
		{
			return;
		}

		if (!empty($item_id))
		{
			$where[] = ' `item_id`!=' . $item_id;
		}

		if (!empty($parent))
		{
			$where[] = ' i.`parent`=\'' . $parent . '\' ';
		}

		$where[] = ' `category`=\'' . $cat . '\' ';
		$where[] = " `state` =  '1'";
		$where[] = " `display_in_product_catlog` =  '1'";

		$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
		$db    = JFactory::getDbo();
		$query = "SELECT i.item_id,i.name,i.images,i.`store_id`,i.slab,i.`min_quantity`,i.`max_quantity`,i.`featured`,
		i.`parent`,i.`product_id`,i.`state`,i.stock
		FROM #__kart_items as i " . $where . ' LIMIT 0 , ' . $limit;
		$db->setQuery($query);

		return $data = $db->loadAssocList();
	}

	/**
	 * This function Products from same store
	 *
	 * @param   integer  $store_id  store id.
	 * @param   integer  $item_id   Product count to fetch
	 * @param   integer  $parent    Product count to fetch
	 * @param   integer  $limit     Limit to fetch
	 *
	 * @since   2.2.2
	 *
	 * @return   array product list
	 */
	public function prodFromSameStore($store_id, $item_id, $parent = '', $limit = 4)
	{
		$where = array();

		if (empty($store_id))
		{
			return;
		}

		$where[] = " `state` =  '1'";
		$where[] = ' `store_id`=\'' . $store_id . '\' ';

		if (!empty($item_id))
		{
			$where[] = ' `item_id`!=' . $item_id;
		}

		if (!empty($parent))
		{
			$where[] = ' i.`parent`=\'' . $parent . '\' ';
		}

		$where[] = " `display_in_product_catlog` =  '1'";

		$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
		$db    = JFactory::getDBO();
		$query = "SELECT i.item_id,i.name,i.images,i.`store_id`,i.`min_quantity`,i.slab,i.`max_quantity`,i.`featured`,
		i.`parent`,i.`product_id`,i.`state`,i.`stock`
		FROM #__kart_items as i " . $where . ' LIMIT 0 ,' . $limit;
		$db->setQuery($query);

		return $data = $db->loadAssocList();
	}

	/*SELECT `order_id` , count( `order_item_id` ) AS qtc
	FROM #__kart_order_item
	GROUP BY `order_id`
	LIMIT 0 , 30*/

	/**
	 * This function Products - people who bought this product also bought
	 *
	 * @param   integer  $item_id    item_id id.
	 * @param   integer  $datacount  Product count to fetch
	 *
	 * @since   2.2.2
	 *
	 * @return   array product list
	 */
	public function peopleAlsoBought($item_id, $datacount = 4)
	{
		$client = "com_quick2cart";
		$where = array();
		$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("oi.`order_id`")
		->from('`#__kart_order_item` AS oi')
		->where(" oi.item_id =" . $item_id);
		$query->order("oi.`order_id` DESC");
		$db->setQuery($query);
		$data = $db->loadColumn();

		//   MAINTAIN LIST OF item_id
		$item_rec = array();

		//  MAINTAIN item_id details
		$item_details = array();

		foreach ($data as $orderid)
		{
			$query = 'SELECT i.item_id,i.name,i.images ,i.`store_id`,i.slab,i.`min_quantity`,
			i.`max_quantity`,i.`parent`,i.`product_id` ,i.`state`,i.`featured`,i.`stock`
			FROM `#__kart_order_item` AS oi
			INNER JOIN `#__kart_items` AS i
			ON oi.item_id=i.item_id
			WHERE oi.order_id=' . $orderid . ' AND i.parent="' . $client . '" AND oi.`item_id` !=' . $item_id
			. ' AND i.state=1 AND i.display_in_product_catlog = 1 ';
			$db->setQuery($query);
			$otherProducts = $db->loadAssocList();

			//  If fetched data (order contain) more than one prod
			if (!empty($otherProducts))
			{
				foreach ($otherProducts as $detail)
				{
					// IF NOT PRESENT THEN ADD
					if (!in_array($detail['item_id'], $item_rec))
					{
						$item_rec[]     = $detail['item_id'];
						$item_details[] = $detail;
					}
				}

				//  CHECKING COUNT TO  SHOW
				if (count($item_details) >= $datacount)
				{
					break;
				}
			}
		}

		return $item_details;
	}

	/**
	 * This function gives Pepole who bought this prouduct
	 *
	 * @param   string  $item_id    status of store 0/1.
	 * @param   string  $datacount  Limit to fetch data.
	 *
	 * @since   2.2
	 *
	 * @return   Objectlist of store info.
	 */
	public function peopleWhoBought($item_id, $datacount = 4)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("DISTINCT(o.`user_info_id`) as id, o.id as order_id ,o.`email`,o.name")
				->from('`#__kart_orders` as o')
				->join('INNER', '`#__kart_order_item` as oi ON oi.order_id=o.id')
				->where(" oi.`status`= 'C'")
				->where(" oi.`item_id`= " . $item_id)
				->group(" o.`user_info_id`")
				->order("o.id DESC");
		$query->setLimit($datacount);
		$db->setQuery($query);

		return $data = $db->loadObjectlist();
	}

	/**
	 * This function gives latest store.
	 *
	 * @param   string  $status  status of store 0/1.
	 * @param   string  $limit   Limit to fetch data.
	 *
	 * @since   2.2
	 * @return   Objectlist of store info.
	 */
	public function getLatestStore($status = '', $limit = '')
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('s.id,s.store_avatar,s.title, s.live');
		$query->from('#__kart_store AS s');

		if ($status != '')
		{
			$query->where("s.live= " . $status);
		}

		if ($limit != '')
		{
			$query->setLimit($limit);
		}

		$query->order("`cdate` DESC");
		$db->setQuery($query);

		return $result = $db->loadAssocList();
	}

	/**
	 * function to fetch recntly added products
	 *
	 * @param   INT  $limit  imit
	 *
	 * @return array
	 */
	public function getNewlyAdded_products($limit = '2')
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('i.item_id','i.name','i.images','i.store_id','i.slab','i.min_quantity')));
		$query->select($db->quoteName(array('i.max_quantity','i.featured','i.parent','i.product_id','i.stock')));
		$query->from($db->quoteName('#__kart_items', 'i'));

		$query->where($db->quoteName('i.state') . "=1");
		$query->where($db->quoteName('i.display_in_product_catlog') . "=1");

		$query->setLimit($limit);
		$query->order("i.item_id DESC");
		$db->setQuery($query);

		return $productinfo = $db->loadAssocList();
	}

	/**
	 * function to fetch recntly bought products
	 *
	 * @param   INT  $limit  imit
	 *
	 * @return array
	 */
	public function getRecentlyBoughtproducts($limit = '2')
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT(' . $db->quoteName('oi.item_id') . ')');
		$query->select($db->quoteName(array('i.name', 'i.images', 'i.featured', 'i.stock')));
		$query->from($db->quoteName("#__kart_items", "i"));
		$query->where($db->quoteName('i.state') . "=1");
		$query->where($db->quoteName('i.display_in_product_catlog') . "=1");
		$query->JOIN('INNER', $db->quoteName("#__kart_order_item", "oi") . " ON " . $db->quoteName('oi.item_id') . "=" . $db->quoteName('i.item_id'));

		$query->group($db->quoteName('oi.item_id'));
		$query->order('oi.cdate DESC');
		$query->limit($limit);
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * function to fetch recntly bought products
	 *
	 * @param   INT  $uid    item user id
	 * @param   INT  $limit  imit
	 *
	 * @return array
	 */
	public function getUserRecentlyBoughtproducts($uid, $limit = '2')
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT DISTINCT(oi.item_id),i.item_id, i.product_id, i.parent, i.`store_id`,i.slab,
		i.`min_quantity`,i.`max_quantity`, i.name,i.images,i.featured, i.stock
					FROM `#__kart_order_item` AS oi , `#__kart_items` AS i, `#__kart_orders` as o
					WHERE i.state = 1
					AND i.display_in_product_catlog = 1
					AND oi.order_id = o.id
					AND oi.item_id = i.item_id
					AND o.user_info_id = "' . $uid . '"
					GROUP BY oi.item_id
					ORDER BY oi.order_id DESC
					LIMIT 0 , ' . $limit;
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * This function gives user stores
	 *
	 * @param   INT  $userid  item user id
	 * @param   INT  $limit   limit
	 *
	 * @return array
	 */
	public function getUserStores($userid, $limit = '2')
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT id,title,store_avatar
		From `#__kart_store`
		WHERE owner=' . (int) $userid . '
		ORDER BY `cdate` DESC
		LIMIT 0 , ' . (int) $limit;
		$db->setQuery($query);

		return $data = $db->loadAssocList();
	}

	/**
	 * This function attibute optios with all  prices
	 *
	 * @param   INT     $userid  item user id
	 * @param   INT     $limit   limit
	 * @param   STRING  $client  client
	 *
	 * @return array
	 */
	public function getUserProducts($userid, $limit = '2', $client = "com_quick2cart")
	{
		//  @TODO : SHOW MODULES INDEPENDANT OF ccks.
		$db    = JFactory::getDBO();
		$query = 'SELECT i.item_id,name,i.images,i.store_id,i.state,i.featured,i.`stock`
		From `#__kart_items` as i
		INNER JOIN `#__kart_store` AS s ON i.`store_id` = s.`id`
		WHERE s.owner=' . (int) $userid . '
		 AND i.display_in_product_catlog = 1 ORDER BY i.cdate DESC
		LIMIT 0 , ' . (int) $limit;
		$db->setQuery($query);

		return $data = $db->loadAssocList();
	}

	/**
	 * This function attibute optios with all  prices
	 *
	 * @param   INT  $itemattributeoption_id  item attribute option id
	 *
	 * @return null
	 */
	public function delWholeAttributeOptionPrices($itemattributeoption_id)
	{
		if (!empty($itemattributeoption_id))
		{
			$db    = JFactory::getDBO();
			$query = "DELETE FROM `#__kart_option_currency`  WHERE itemattributeoption_id=" . (int) $itemattributeoption_id;
			$db->setQuery($query);

			return $db->execute();
		}
	}

	/**
	 * This function delete option with all  prices
	 *
	 * @param   INT  $itemattributeoption_id  item attribute option id
	 *
	 * @return null
	 */
	public function delWholeAttributeOption($itemattributeoption_id)
	{
		$db = JFactory::getDBO();

		if (!empty($itemattributeoption_id))
		{
			$productHelper             = new productHelper;

			// LOAD ATTRIBUTE MODEL FILE
			$quick2cartModelAttributes = $productHelper->loadAttribModel();

			//  DELETE OPTION PRICES
			$productHelper->delWholeAttributeOptionPrices($itemattributeoption_id);

			//  DELETE OPTION
			$query = "DELETE FROM #__kart_itemattributeoptions  WHERE itemattributeoption_id=" . (int) $itemattributeoption_id;
			$db->setQuery($query);

			return $db->execute();
		}
	}

	/**
	 * This function delete whole attibute
	 *
	 * @param   INT  $itemattribute_id  item id
	 *
	 * @return null
	 */
	public function delWholeAttribute($itemattribute_id)
	{
		$db                        = JFactory::getDBO();
		$productHelper             = new productHelper;

		//  LOAD ATTRIBUTE MODEL FILE
		$quick2cartModelAttributes = $productHelper->loadAttribModel();

		//  GET OPTIONS
		$options                   = $quick2cartModelAttributes->getAttributeoption($itemattribute_id);

		foreach ($options as $op)
		{
			//  DEL OPTIONS
			$productHelper->delWholeAttributeOption($op->itemattributeoption_id);
		}

		//  DEL ATTRIBUTE
		$query = "DELETE FROM `#__kart_itemattributes`  WHERE itemattribute_id=" . (int) $itemattribute_id;
		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * This function delete whole product (its attribute, attribute option and prices )
	 *
	 * @param   INT  $item_id  item id
	 *
	 * @return null
	 */
	public function deleteWholeProduct($item_id)
	{
		$status = $this->isAllowedToDelProduct($item_id);

		if ($status === false)
		{
			return false;
		}

		$productHelper = new productHelper;

		//  LOAD ATTRIBUTE MODEL FILE
		$quick2cartModelAttributes = $productHelper->loadAttribModel();

		//  TASK1: FETCH ALL ATTRIBUTE ID
		$attributes = $quick2cartModelAttributes->getItemAttributes($item_id);

		if (!empty($attributes))
		{
			//  IF ATTRIBURE
			foreach ($attributes as $att)
			{
				$productHelper->delWholeAttribute($att->itemattribute_id);
			}
		}

		//  AT LAST, DELETE PRODUCT
		$productHelper->delProdBasePrices($item_id);

		// All entries from $kart_cart_items
		// Load model file
		$path = JPATH_SITE . "/components/com_quick2cart/models/cart.php";

		if (!class_exists("Quick2cartModelcart"))
		{
			JLoader::register("Quick2cartModelcart", $path);
			JLoader::load("Quick2cartModelcart");
		}

		// Delete product data from TJ - Fields srart
		if (!class_exists('Quick2cartModelProduct'))
		{
			JLoader::register('Quick2cartModelProduct', JPATH_SITE . '/components/com_quick2cart/models/product.php');
			JLoader::load('Quick2cartModelProduct');
		}

		$Quick2cartModelProduct = new Quick2cartModelProduct;
		$Quick2cartModelProduct->deleteExtraFieldsData($item_id, 'com_quick2cart.product');

		// Delete product data from TJ - Fields end

		$Quick2cartModelcart = new Quick2cartModelcart;
		$Quick2cartModelcart->remove_cartItem($cart_item_id = 0, $item_id);

		return $status = $quick2cartModelAttributes->deleteItem($item_id);
	}

	/**
	 * This function attibute optios with all  prices
	 *
	 * @param   INT  $item_id  item id
	 *
	 * @return null
	 */
	public function delProdBasePrices($item_id)
	{
		if (!empty($item_id))
		{
			$db    = JFactory::getDBO();
			$query = "DELETE FROM `#__kart_base_currency`  WHERE `item_id`=" . (int) $item_id;
			$db->setQuery($query);

			return $db->execute();
		}
	}

	/**
	 * This functions returns attribute model
	 *
	 * @return attribute object model
	 */
	public function loadAttribModel()
	{
		if (!class_exists('quick2cartModelAttributes'))
		{
			JLoader::register('quick2cartModelAttributes', JPATH_SITE . '/components' . '/com_quick2cart' . '/models' . '/attributes.php');
			JLoader::load('quick2cartModelAttributes');
		}

		return $quick2cartModelAttributes = new quick2cartModelAttributes;
	}

	/**
	 * This function deleted product extra images on EDIT OR DELETE product
	 *
	 * @param   INT     $item_id     item id
	 * @param   STRING  $image_path  image path
	 *
	 * @return null
	 */
	public function deleteNotReqProdImages($item_id, $image_path)
	{
		if (!empty($item_id))
		{
			$db    = JFactory::getDBO();
			$query = 'SELECT `images` from `#__kart_items` where `item_id`=' . $item_id;
			$db->setQuery($query);
			$dbimages      = $db->loadResult();
			$productHelper = new productHelper;

			//  IF NOT EMPTY
			if (!empty($dbimages))
			{
				$dbImg = array();
				$dbImg = json_decode($dbimages, true);

				$image_path = json_decode($image_path);

				if (empty($image_path))
				{
					//  DELETE ALL IMAGES IF IIMAGE_PATH PARAM IS EMPTY
					$productHelper = new productHelper;
					$productHelper->deletImg($dbImg);
				}
				else
				{
					//  FIND OUT EXTRA IMAGE
					$delImg = array();

					foreach ($dbImg as $img)
					{
						if (is_array($image_path) && !in_array($img, $image_path))
						{
							$delImg[] = $img;
						}

						$productHelper->deletImg($delImg);
					}
				}
			}
		}
	}

	/**
	 * FUnction to delete image
	 *
	 * @param   ARRAY  $imgarray  path of files to be deleted
	 *
	 * @return null
	 */
	public function deletImg($imgarray)
	{
		$productHelper = new productHelper;
		require_once JPATH_SITE . '/components/com_quick2cart/helpers/media.php';
		$media = new qtc_mediaHelper;

		if (!empty($imgarray))
		{
			foreach ($imgarray as $img)
			{
				$file_name_without_extension = $media->get_media_file_name_without_extension($img);
				$media_extension             = $media->get_media_extension($img);
				$imgpath_withoutExtention    = JPATH_SITE . '/images/quick2cart/' . $file_name_without_extension;

				//  DELETE ORIGINAL IMG
				$imgfilepath                 = JPATH_SITE . '/images/quick2cart/' . $img;
				$productHelper->deleteFile($imgfilepath);

				//  DELETE SMALL, M ,L IMAGES
				$imgfilepath = $imgpath_withoutExtention . '_S.' . $media_extension;
				$productHelper->deleteFile($imgfilepath);
				$imgfilepath = $imgpath_withoutExtention . '_M.' . $media_extension;
				$productHelper->deleteFile($imgfilepath);
				$imgfilepath = $imgpath_withoutExtention . '_L.' . $media_extension;
				$productHelper->deleteFile($imgfilepath);
			}
		}
	}

	/**
	 * THIS FUNCTION DELETE any file
	 *
	 * @param   STRING  $dfinepath  path of file to be deleted
	 *
	 * @return null
	 */
	public function deleteFile($dfinepath)
	{
		if (JFile::exists($dfinepath))
		{
			$status2 = JFile::delete($dfinepath);
		}
	}

	/**
	 * This function return attribute list of perticular item
	 *
	 * @param   INT  $item_id  item id
	 *
	 * @return attribute list
	 */
	public function getAttributeList($item_id)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT `itemattribute_id` FROM `#__kart_itemattributes`  WHERE `item_id`=" . (int) $item_id;
		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * THIS FUNCTION returns list of options of attribute
	 *
	 * @param   INT  $att_id  attribute id
	 *
	 * @return array
	 */
	public function getAttributeOptionsList($att_id)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT `itemattributeoption_id` FROM `#__kart_itemattributeoptions`  WHERE `itemattribute_id`=" . (int) $att_id;
		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * THIS FUNCTION DELETE db attributes which is not present used
	 *
	 * @param   INT  $item_id    item id
	 * @param   INT  $attIdList  attribute Id List
	 *
	 * @return status
	 */
	public function deleteExtaAttribute($item_id, $attIdList)
	{
		$productHelper = new productHelper;
		$DbAtt_list    = $productHelper->getAttributeList($item_id);

		//  ITEM HAS ATTRIBUTES
		if (!empty($DbAtt_list))
		{
			if (empty($attIdList))
			//  IF REMOVED ALL ATTRI THEN DEL ALL ATTRIB
			{
				foreach ($DbAtt_list as $dbid)
				{
					$productHelper->delWholeAttribute($dbid);
				}
			}
			else
			{
				foreach ($DbAtt_list as $dbid)
				{
					if (!in_array($dbid, $attIdList))
					{
						$productHelper->delWholeAttribute($dbid);
					}
				}
			}
		}
	}

	/**
	 * THIS FUNCTION DELETE db attributes OPTION which is not presently we not going to used/ delted
	 *
	 * @param   INT  $att_id     attributes id
	 * @param   INT  $optionids  option id
	 *
	 * @return status
	 */
	public function deleteExtraAttributeOptions($att_id, $optionids)
	{
		$productHelper    = new productHelper;
		$DbAttoption_list = $productHelper->getAttributeOptionsList($att_id);

		//  ATTRIBUTES HS OPTIONS
		if (!empty($DbAttoption_list))
		{
			//  CURRENTLY NO OPIONS IF REMOVED ALL OPTION THEN DELETE WHOLE ATTRIBUTE
			if (empty($optionids))
			{
				$productHelper->delWholeAttribute($att_id);
			}
			else
			{
				foreach ($DbAttoption_list as $dbid)
				{
					if (!in_array($dbid, $optionids))
					{
						$productHelper->delWholeAttributeOption($dbid);
					}
				}
			}
		}
	}

	/*
	function pushToEasySocialActivity($actor_id,$act_type='',$act_subtype='',$act_description='',$act_link='',$act_title='',$act_access='')
	{
	require_once( JPATH_ROOT . '/administrator/components/com_easysocial/includes/foundry.php' );
	$myUser = Foundry::user( $actor_id );
	$stream = Foundry::stream();
	$template = $stream->getTemplate();
	$template->setActor( $actor_id, SOCIAL_TYPE_USER );
	$template->setContext( $actor_id, SOCIAL_TYPE_USERS );
	$template->setVerb( 'invite' );
	$template->setType( SOCIAL_STREAM_DISPLAY_MINI );

	$userProfileLink = '<a href="'. $myUser->getPermalink() .'">' . $myUser->getName() . '</a>';
	$title      = ($userProfileLink." ".$act_description);

	$template->setTitle( $title );
	$template->setAggregate( false );

	$template->setPublicStream( 'core.view' );
	$stream->add( $template );
	return true;
	}
	*/

	/**
	 * function to get attribute Name
	 *
	 * @param   INT  $itemattribute_id  attributes id
	 *
	 * @return details
	 */
	public function getAttributeName($itemattribute_id)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT `itemattribute_name` FROM #__kart_itemattributes WHERE itemattribute_id=" . $itemattribute_id;
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * function to return product price depending upon default atribute price
	 *
	 * @param   INT  $item_id  item id
	 *
	 * @return details
	 */
	public function getProdPriceWithDefltAttributePrice($item_id)
	{
		$return               = array();
		$return['itemdetail'] = array();
		$helperobj            = new comquick2cartHelper;
		$curr                 = $helperobj->getCurrencySession();

		$path = JPATH_SITE . '/components/com_quick2cart/models/attributes.php';

		if (!class_exists('quick2cartModelAttributes'))
		{
			JLoader::register('quick2cartModelAttributes', $path);
			JLoader::load('quick2cartModelAttributes');
		}

		$quick2cartModelAttributes = new quick2cartModelAttributes;

		// GETTING ITEM_DETAILS
		$item_details = $quick2cartModelAttributes->getCurrenciesvalue('0', $curr, 'com_quick2cart', $item_id);

		if (!empty($item_details[0]))
		{
			$return['itemdetail'] = $item_details[0];
		}

		// GETTING ATTRIBUTE DETAILS
		$attribure_option_ids = array();
		$tot_att_price        = 0;
		$allAttr              = $quick2cartModelAttributes->getItemAttributes($item_id);

		if (!empty($allAttr))
		{
			// ATTRIBUTES
			foreach ($allAttr as $attr)
			{
				// If cumpulsory then only consider price ( i.e attribute_compulsary=0)
				if (!empty($attr->attribute_compulsary))
				{
					$attr_details = $helperobj->getAttributeDetails($attr->itemattribute_id);

					foreach ($attr_details as $options)
					{
						if ($options->ordering == 1 && !empty($options->$curr)) //  consider first order option
						{
							$attribure_option_ids[] = $options->itemattributeoption_id;
							$tot_att_price += $options->$curr;
							break;
						}

						// Consider prefix also
					}
				}
			}
		}

		$attrDetails                  = array();
		$attrDetails['tot_att_price'] = $tot_att_price;
		$attrDetails['attrOptionIds'] = $attribure_option_ids;

		$return['attrDetail'] = $attrDetails;

		return $return;
	}

	/**
	 * This function returns attribute field type html
	 *
	 * @param   ARRAY  $data  attributes data
	 *
	 * @return details
	 */
	public function getAttrFieldTypeHtml($data)
	{
		$parent = '';

		if (isset($data['parent']))
		{
			$parent = $data['parent'];
		}

		$product_id          = $data['product_id'];
		$comquick2cartHelper = new comquick2cartHelper;

		// $atri_options = comquick2cartHelper::getAttributeOption($attribute->itemattribute_id);//  commented during multicurrency
		$atri_options = $comquick2cartHelper->getAttributeOptionCurrPrice($data['itemattribute_id']);
		$select_opt   = array();
		$userData     = array();
		$userData[]   = 'Textbox';

		if (!$data['attribute_compulsary'] && !in_array($data['fieldType'], $userData))
		//  if checked
		{
			$select_opt[] = JHtml::_('select.option', "", "");
		}

		$returnHtml = '';

		foreach ($atri_options as $atri_option)
		{
			$attOp_price = (int) $atri_option->itemattributeoption_currency_price;

			// IF 0 ATT PRIVE THEN DONT ADD +0 USD
			if (!empty($attOp_price))
			{
				$priceText = $comquick2cartHelper->getFromattedPrice($atri_option->itemattributeoption_currency_price, null, 0);
				$opt_str   = $atri_option->itemattributeoption_name . ": " . $atri_option->itemattributeoption_prefix . " " . $priceText;
			}
			else
			{
				//  If no price than dont append like  +00.0 USD
				$opt_str = $atri_option->itemattributeoption_name;
			}

			//  Generate op according to datatype

			if (in_array($data['fieldType'], $userData))
			{
				$returnHtml = "<input type='text' name='qtcUserField_" .
				$atri_option->itemattributeoption_id
				. "' class='input input-small " . $parent . '-' . $product_id . '_UserField' . "' >";
			}
			else
			{
				/* Amol change*/
				$default_value = '';

				if (isset($data['default_value']))
				{
					$default_value = $data['default_value'];
				}

				$field_name = 'attri_option';

				if (isset($data['field_name']))
				{
					$field_name = $data['field_name'];
				}

				// User data
				$select_opt[] = JHtml::_('select.option', $atri_option->itemattributeoption_id, $opt_str);
				$returnHtml   = JHtml::_(
				'select.genericlist', $select_opt, $field_name, "  class='q2c_AttoptionsMaxWidth {$parent}-{$product_id}_options'",
				'value', 'text', $default_value, false
				);
			}
		}

		return $returnHtml;
		/*    if (!in_array($data['fieldType'],$userData)) {
		echo JHtml::_('select.genericlist', $select_opt, 'attri_option', "  class='span2 {$parent}-{$product_id}_options'", 'value', 'text', '',false);
		}*/
	}

	/**
	 * This function gets attribute field options
	 *
	 * @return details
	 */
	public function getAttributeFieldsOption()
	{
		$fields[] = JHtml::_('select.option', 'Select', JText::_('QTC_ADDATTRI_SELECT_FIELD'));
		$fields[] = JHtml::_('select.option', 'Textbox', JText::_('QTC_ADDATTRI_TEXT_FIELD'));

		// $fields[] = JHtml::_('select.option','Checkbox', JText::_('QTC_ADDATTRI_CHECKBOX_FIELD'));

		return $fields;
	}

	/**
	 * This function save product media details
	 *
	 * @param   ARRAY  $media_detail  array  - Media detail
	 * @param   INT    $item_id       array  - Media detail
	 * @param   INT    $deleteOldRec  Flag to delete old file or not
	 *
	 * @return details
	 */
	public function saveProdMediaDetails($media_detail, $item_id, $deleteOldRec = 1)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$db = JFactory::getDBO();

		// Get privious  file ids
		$query = 'SELECT  `file_id` FROM `#__kart_itemfiles` where `item_id`=' . $item_id;
		$db->setQuery($query);
		$oldMedia = $db->loadColumn();

		$comquick2cartHelper = new comquick2cartHelper;
		$params              = JComponentHelper::getParams('com_quick2cart');
		$destinationPath     = $params->get('eProdUploadDir', 'media/com_quick2cart/productfiles');
		$eProdExpFormat      = $params->get('eProdExpFormat', 'epMonthExp');

		// Down count/ date limit / both
		$eProdUExpiryMode    = $params->get('eProdUExpiryMode', 'epMaxDownload');

		foreach ($media_detail as $media)
		{
			if (empty($media['mediaFilePath']))
			{
				continue;
			}

			$file                    = new stdClass;
			$file->file_id           = $media['file_id'];
			$file->file_display_name = !empty($media['name']) ? $media['name'] : JText::_('COM_QUICK2CRT_DEFAULT_DOWNLODABLE_FILE');
			$file->item_id           = $item_id;
			$file->state             = isset($media['status']) ? true : false;
			$file->purchase_required = isset($media['purchaseReq']) ? true : false;

			//  use download count as well as data expiration mode
			// if ($eProdUExpiryMode == "epboth")
			{
				// @TODO : if date expiry mode, keep this value as default according to config
				$file->download_limit = !empty($media['downCount'])?$media['downCount']
				:"-1";
				$file->expiry_mode    = ($eProdExpFormat == 'epMonthExp') ? 1 : 0;

				// @TODO : if download limit mode, keep this value as default according to config
				$file->expiry_in = !empty($media['expirary']) ? $media['expirary'] : 2;
			}

			/*elseif ($eProdUExpiryMode == "epDateExpiry")
			{
			  use data expiration ( not download count )
			  null - field not considered, -1 - unlimited , other for req count
				$file->download_limit = -1;  //  for edit and changed config  ( NULL DOESN'T INSERT IN DB BZ DATATYPE MISMATCH)
			  IN DAY  OR MONTHS
			$file->expiry_mode = ($eProdExpFormat == 'epMonthExp') ? 1:0;
			$file->expiry_in = $media['expirary'];
			}
			elseif ($eProdUExpiryMode == "epMaxDownload")
			{
			$file->download_limit = $media['downCount'];
			 $file->expiry_mode = -1;  -1 for dont consider field
			 $file->expiry_in = 0;
			}*/

			if (empty($media['file_id']))
			{
				$file->cdate = date('Y-m-d');
			}
			else
			{
				// If media editing then
				$medKey = array_search($media['file_id'], $oldMedia);

				if (isset($medKey))
				{
					// Present data (not deleted media file)
					unset($oldMedia[$medKey]);
				}
			}

			$file->mdate = date('Y-m-d H:i:s');

			// For new file, check for file save path
			if (empty($media['file_id']) && !empty($media['mediaFilePath']))
			{
				// Get temparary uploaded path
				$tempSource   = JPATH_SITE . '/tmp/' . $media['mediaFilePath'];
				$uploadedFile = explode('/', $tempSource);

				$filNameIndex = count($uploadedFile) - 1;
				$fileName     = $uploadedFile[$filNameIndex];

				// New destination path
				$newDestination = JPATH_ROOT . "/" . $destinationPath;

				// If folder path not exist
				if (!JFolder::exists($newDestination))
				{
					$status = JFolder::create($newDestination);
				}

				// Move a file from temporary location to newlocation
				$newDestination = $newDestination . "/" . $fileName;
				$uploadSuccess  = JFile::move($tempSource, $newDestination);
			}

			$file->filePath = $media['mediaFilePath'];
			$action         = empty($media['file_id']) ? 'insertObject' : 'updateObject';

			if (!$db->$action('#__kart_itemfiles', $file, 'file_id'))
			{
				echo $db->stderr();

				return 0;
			}
		}

		$productHelper = new productHelper;

		if ($deleteOldRec == 1)
		{
			$productHelper->deleteProductMediaFile($oldMedia);
		}

		return 1;
	}

	/**
	 * This function delete media file
	 *
	 * @param   array  $mediaIds  id's and filePath array
	 *
	 * @return  status
	 */
	public function deleteProductMediaFile($mediaIds)
	{
		$productHelper = new productHelper;

		// Remove empty elements

		// $mediaIds = array_filter($mediaIds,'strlen');

		// $ids = implode(',',$mediaIds);
		$db            = JFactory::getDBO();
		$params        = JComponentHelper::getParams('com_quick2cart');
		$delFromDir    = JPATH_ROOT . "/" . $params->get('eProdUploadDir', 'media/com_quick2cart/productfiles');

		foreach ($mediaIds as $media)
		{
			// Get file name
			$query = 'SELECT  `filePath` FROM `#__kart_itemfiles` where `file_id`=' . $media;
			$db->setQuery($query);
			$filename = $db->loadResult();

			// Delete file record
			$query = 'DELETE FROM `#__kart_itemfiles`  WHERE `file_id` = ' . $media;
			$db->setQuery($query);
			$status = $db->execute();

			//  If file rec is deleted and file exist then delete physical media file
			if ($status && !empty($filename))
			{
				$productHelper->deleteFile($delFromDir . "/" . $filename);
			}
		}

		return 1;
	}

	/** This function give media files detail
	 *
	 * @param   INT     $item_id  integer if provoide item's media files
	 *
	 * @param   STRING  $file_id  give media detail for given file
	 *
	 * @param   INT     $state    state = '' means fetched published as well as unpublished recorder
	 *
	 * @return array media detail array
	 */
	public function getMediaDetail($item_id = 0, $file_id = '', $state = '')
	{
		$db    = JFactory::getDBO();
		$where = array();

		//  state = '' means published as well as unpublished recorder
		if ($state != '')
		{
			$where[] = " `state`=" . $state;
		}

		if (!empty($item_id))
		{
			$where[] = " `item_id`=" . $item_id;
		}

		if ($file_id != '')
		{
			$where[] = " `file_id`=" . $file_id;
		}

		$where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');
		$query = "SELECT `file_id`,`file_display_name`,`item_id`,`purchase_required`,`state`";

		$query = $query . ",`download_limit`,`filePath`,`expiry_mode`,`expiry_in`,`cdate` FROM `#__kart_itemfiles`" . $where;

		$db->setQuery($query);

		$res = $db->loadAssocList();

		return $res;
	}

	/**
	 * This function gives physical file name and display name from db
	 *
	 * @param   INT  $file_id  file id
	 *
	 * @return  file path
	 */
	public function getFileName($file_id)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT `filePath`,`file_display_name` FROM `#__kart_itemfiles` WHERE `file_id`=' . $file_id;
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * This function returns file path to downloads
	 *
	 * @param   INT  $file_id  file id
	 *
	 * @return  file path
	 */
	public function getFilePathToDownload($file_id)
	{
		$productHelper = new productHelper;

		//  GET FILE NAME
		$fileDetail = $productHelper->getFileName($file_id);
		$filePath   = $fileDetail['filePath'];

		$params     = JComponentHelper::getParams('com_quick2cart');
		$folderPath = $params->get('eProdUploadDir', 'media/com_quick2cart/productfiles');

		return JPATH_SITE . '/' . $folderPath . '/' . $filePath;
	}

	/**
	 * This function show download link of product
	 *
	 * @param   INT  $linkData  link data
	 *
	 * @return  VOID
	 */
	public function showMediaDownloadLink($linkData)
	{
		$linkData['href'];
		$event = $linkData['event'] . '= ' . $linkData['functionName'] . '(' . $linkData['fnParam'] . ')';
		$link  = "<a href=\"" . $linkData['href']
		. "\" $event title=\""
		. JText::_("COM_QUICK2CART_PROD_PG_DOWNLOADLINK_TITLE") . "\">" . ucfirst($linkData['linkName']) . "</a> ";

		return $link;
	}

	/**
	 * This function provide href field contains. Specified where to go(which task), onclick of download link
	 *
	 * @param   INT  $fileid         fileid item_id of prod
	 * @param   INT  $extraUrlPrams  integer if set if 1, return all files else only free file are
	 *
	 * @return  VOID
	 */
	public function getMediaDownloadLinkHref($fileid, $extraUrlPrams = '')
	{
		$helperobj = new comquick2cartHelper;

		//  If url extra param is present
		if (!empty($extraUrlPrams))
		{
			$extraUrlPrams = '&' . $extraUrlPrams;
		}

		$storecp_Itemid = $helperobj->getitemid('index.php?option=com_quick2cart&view=downloads');
		$b_lk = 'index.php?option=com_quick2cart&task=product.downStart&fid=';
		$link = JUri::root() . substr(JRoute::_($b_lk . $fileid . $extraUrlPrams . '&Itemid=' . $storecp_Itemid), strlen(JUri::base(true)) + 1);

		return $link;
	}

	/**
	 * This function gives  product files
	 *
	 * @param   INT  $item_id            item_id of prod
	 * @param   INT  $purchase_required  if set if 1, return all files else only free file are
	 *
	 * @return  VOID
	 */
	public function getProdmediaFiles($item_id, $purchase_required = 0)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT `file_id`,`file_display_name`,`filePath`
				FROM `#__kart_itemfiles` WHERE `item_id`=' . $item_id . ' AND `purchase_required`=' . $purchase_required;
		$db->setQuery($query);

		return $res = $db->loadAssocList();
	}

	/**
	 * On order confirm, add product media file in orderitemFIlesTable
	 *
	 * @param   INT  $order_id  order_id
	 *
	 * @return  VOID
	 */
	public function addEntryInOrderItemFiles($order_id)
	{
		$productHelper       = new productHelper;
		$comquick2cartHelper = new comquick2cartHelper;

		$params = JComponentHelper::getParams('com_quick2cart');

		$eProdExpFormat = $params->get('eProdExpFormat', 'epMonthExp');

		// Down count/ date limit / both
		$eProdUExpiryMode = $params->get('eProdUExpiryMode', 'epMaxDownload');

		$db = JFactory::getDBO();
		$q  = "SELECT `item_id`,`order_item_id`,`product_quantity`
				FROM  `#__kart_order_item`
				WHERE `order_id` =" . (int) $order_id;
		$db->setQuery($q);
		$result = $db->loadAssocList();

		foreach ($result as $res)
		{
			$item_id     = $res['item_id'];
			$mediaDetail = $productHelper->getMediaDetail($item_id);

			//  Copy all media
			foreach ($mediaDetail as $media)
			{
				$pfile                  = new stdClass;
				$pfile->product_file_id = $media['file_id'];
				$pfile->order_item_id   = $res['order_item_id'];
				$pfile->expirary_date   = '';
				$que                    = 'SELECT of.id FROM `#__kart_orderItemFiles` AS of
					where of.order_item_id=' . $pfile->order_item_id . ' AND of.`product_file_id`=' . $pfile->product_file_id . ' ORDER BY of.id';
				$db->setQuery($que);
				$fileid = $db->loadResult();

				if (!empty($fileid))
				{
					//  Actually will not come inside. still for worse case
					$action    = "updateObject";
					$pfile->id = $fileid;
				}
				else
				{
					$action       = "insertObject";
					$pfile->cdate = date('Y-m-d H:i:s');
				}

				//  if purchase require not req
				if ($media['purchase_required'] == 0)
				{
					$pfile->download_limit = -1;
				}
				else
				{
					//  DATE EXPIRATION
					if ($eProdUExpiryMode == 'epDateExpiry' || $eProdUExpiryMode == 'epboth')
					{
						//  expirary in months
						if ($media['expiry_mode'] == 1)
						{
							//  months
							$exdate = $comquick2cartHelper->add_date($pfile->cdate, 0, $media['expiry_in']);
						}
						else
						{
							//  in days
							$exdate = $comquick2cartHelper->add_date($pfile->cdate, $media['expiry_in']);
						}

						$pfile->expirary_date = $exdate;
					}

					if ($eProdUExpiryMode == 'epMaxDownload' || $eProdUExpiryMode == 'epboth')
					{
						if ($media['download_limit'] > 0)
						{
							//  not for unlimited
							$pfile->download_limit = $media['download_limit'] * $res['product_quantity'];
						}
						else
						{
							$pfile->download_limit = $media['download_limit'];
						}
					}
				}

				$pfile->expiration_mode = $eProdUExpiryMode;

				if (!$db->$action('#__kart_orderItemFiles', $pfile, 'id'))
				{
					echo JText::_("COM_QUICK2CART_ERROR_WHILE_ASSIGNING_MEIDA_FILE_TO_ORDER") . $db->stderr();

					return false;
				}
			}
		}
	}

	/* Code added by sanjivani*/

	/**
	 * After Order Confirm Add point to buyer
	 * count of order item X point allocate to action perform
	 *
	 * @param   INT  $oid  oid
	 *
	 * @return  VOID
	 */
	public function addPoint($oid)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT sum( `product_quantity` )  as count FROM `#__kart_order_item` WHERE `order_id`="' . $oid . '"';
		$db->setQuery($query);
		$count_item = $db->loadResult();

		//  Add point to Community extension when product added into Quick2cart
		$params         = JComponentHelper::getParams('com_quick2cart');
		$point_system   = $params->get('point_system');
		$integrate_with = $params->get('integrate_with');
		$user           = JFactory::getUser();

		if ($point_system === '1')
		{
			//  According to integration create social lib class obj.
			$comquick2cartHelper  = new comquick2cartHelper;
			$libclass             = $comquick2cartHelper->getQtcSocialLibObj();
			$options['extension'] = 'com_quick2cart';

			if ($integrate_with === 'JomSocial')
			{
				$options['command'] = 'BuyProduct';

				for ($i = 0; $i < $count_item; $i++)
				{
					$libclass->addpoints($user, $options);
				}
			}
			elseif ($integrate_with === 'EasySocial')
			{
				$options['command'] = 'buy_product';

				for ($i = 0; $i < $count_item; $i++)
				{
					$libclass->addpoints($user, $options);
				}
			}
		}
	}

	/**
	 * This function check whether order item has any media or not
	 *
	 * @param   INT  $primaryKey  primaryKey
	 *
	 * @return  VOID
	 */
	public function updateFileDownloadCount($primaryKey)
	{
		$pfile                  = new stdClass;
		$pfile->id              = $primaryKey;
		$pfile->product_file_id = $media['file_id'];

		$db    = JFactory::getDBO();
		$query = 'UPDATE  `#__kart_orderItemFiles` SET `download_count` = (`download_count` + 1) WHERE `id`=' . $primaryKey;

		//  . ' AND download_count != -1';
		$db->setQuery($query);
		$db->execute();

		return;
	}

	/**
	 * This function check whether order item has any media or not
	 *
	 * @param   INT  $order_item_id  integer
	 *
	 * @return  array of all mediafile ids
	 */
	public function isMediaForPresent($order_item_id)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT `id` FROM `#__kart_orderItemFiles` WHERE `order_item_id`=' . $order_item_id;
		$db->setQuery($query);

		return $res = $db->loadAssocList();
	}

	/**
	 * mediaFileAuthorise
	 *
	 * @param   STRING  $file_id        file_id
	 * @param   STRING  $strorecall     strorecall
	 * @param   STRING  $guest_email    guest_email
	 * @param   STRING  $order_item_id  order_item_id
	 *
	 * @return  html
	 */
	public function mediaFileAuthorise($file_id, $strorecall, $guest_email, $order_item_id)
	{
		// Get store id associatd with file id
		$db    = JFactory::getDBO();
		$query = "SELECT i.`store_id`,`file_id`,`file_display_name`,`filePath`,`purchase_required` FROM `#__kart_itemfiles` AS f
		LEFT JOIN `#__kart_items` AS i ON f.item_id=i.item_id
		where f.`file_id`=" . $file_id;
		$db->setQuery($query);
		$fileDetail = $db->loadAssoc();

		$comquick2cartHelper    = new comquick2cartHelper;
		$ret['validDownload']   = 0;
		$ret['orderItemFileId'] = 0;

		// FOR STORE AUTHORIZED PERSONS
		if (!empty($strorecall))
		{
			$ret['validDownload'] = $comquick2cartHelper->store_authorize('product_default', $fileDetail['store_id']);

			return $ret;
		}

		// Vm: for authorization we are only user detail from orderfiles table
		elseif (empty($order_item_id) && $fileDetail['purchase_required'] == 0)
		{
			// Called from product detail page
			// For free media
			$ret['validDownload'] = 1;

			return $ret;
		}
		else
		{
			// Is authorized guest chekout
			if (!empty($guest_email))
			{
				$orderid         = $comquick2cartHelper->getOrderId($order_item_id);
				$guest_email_chk = $comquick2cartHelper->checkmailhash($orderid, $guest_email);

				if (empty($guest_email_chk))
				{
					//  Not matched
					$ret['validDownload'] = 0;

					return $ret;
				}
			}

			// Is expired date/download conunt or not
			$productHelper = new productHelper;
			$ret           = $productHelper->validDownload($file_id, $order_item_id);

			return $ret;
		}
	}

	/**
	 * This functionw check whether download is expired date/download conunt or not
	 *
	 * @param   STRING  $file_id        file_id
	 * @param   STRING  $order_item_id  order_item_id
	 *
	 * @return  html
	 */
	public function validDownload($file_id, $order_item_id)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT of.* FROM `#__kart_orderItemFiles` AS of
		where of.order_item_id=' . $order_item_id . ' AND of.`product_file_id`=' . $file_id . ' ORDER BY id';
		$db->setQuery($query);
		$fileDetail             = $db->loadAssoc();
		$ret['validDownload']   = 0;
		$ret['orderItemFileId'] = $fileDetail['id'];
		$params                 = JComponentHelper::getParams('com_quick2cart');

		//  Down count/ date limit / both
		$eProdUExpiryMode = $params->get('eProdUExpiryMode', 'epMaxDownload');

		if (!empty($fileDetail))
		{
			//  ALL DOWNLOAD COUNT done. NULL for while placing order, only expiry date is considered
			//  DATE EXPIRATION
			if ($eProdUExpiryMode == 'epDateExpiry')
			{
				// Check of date expirary
				if (!empty($fileDetail['expirary_date'])
					&& $fileDetail['expirary_date'] != "0000-00-00 00:00:00"
					&& date('Y-m-d H:i:s') < $fileDetail['expirary_date'])
				{
					$ret['validDownload'] = 1;

					return $ret;
				}
			}

			if ($eProdUExpiryMode == 'epboth')
			{
				if (!empty($fileDetail['expirary_date'])
					&& $fileDetail['expirary_date'] != "0000-00-00 00:00:00"
					&& date('Y-m-d H:i:s') < $fileDetail['expirary_date']
					&& $fileDetail['download_limit'] != null)
				{
					if ($fileDetail['download_limit'] == -1)
					{
						$ret['validDownload'] = 1;
					}
					elseif ($fileDetail['download_limit'] != null
					&& $fileDetail['download_count'] < $fileDetail['download_limit'])
					{
						$ret['validDownload'] = 1;
					}

					return $ret;
				}
			}

			if ($eProdUExpiryMode == 'epMaxDownload')
			{
				if ($fileDetail['download_limit'] == -1)
				{
					$ret['validDownload'] = 1;
				}
				elseif ($fileDetail['download_limit'] != null
					&& $fileDetail['download_count'] < $fileDetail['download_limit'])
				{
					$ret['validDownload'] = 1;
				}

				return $ret;
			}
		}
	}

	/**
	 * download the file
	 *
	 * @param   STRING  $file             - file path eg /var/www/j30/media/com_quick2cart/qtc_pack.zip
	 * @param   STRING  $filename_direct  - for direct download it will be file path like http://
	 * localhost/j30/media/com_quick2cart/qtc_pack.zip  -- for FUTURE SCOPE
	 * @param   STRING  $extern           - for direct download it will be file path like http://
	 * @param   STRING  $exitHere         - for direct download it will be file path like http://
	 *
	 * @return  html
	 */
	public function download($file, $filename_direct = '', $extern = '', $exitHere = 1)
	{
		$productHelper = new productHelper;
		global $jlistConfig, $mainframe;
		$app = JFactory::getApplication();

		$view_types = array();

		//  ALLOWED  FILE EXTENTION
		$view_types = explode(',', $jlistConfig['file.types.view']);

		clearstatcache();

		//  Existiert file - wenn nicht error
		if (!$extern)
		{
			if (!file_exists($file))
			{
				return 2;
			}
			else
			{
				$len = filesize($file);
			}
		}
		else
		{
			$len = urlfilesize($file);
		}

		// If url go to other website - open it in a new browser window

		/*   if ($extern_site){
		echo "<script>document.location.href='$file';</script>\n";
		exit;
		}*/

		// If set the option for direct link to the file
		// If (0 || !$jlistConfig['use.php.script.for.download']){
		if (0)
		{
			if (empty($filename_direct))
			{
				$app->redirect($file);
			}
			else
			{
				$app->redirect($filename_direct);
			}
		}
		else
		{
			$filename       = basename($file);
			$file_extension = strtolower(substr(strrchr($filename, "."), 1));
			$ctype          = $productHelper->datei_mime($file_extension);

			ob_end_clean();

			//  Needed for MS IE - otherwise content disposition is not used?
			if (ini_get('zlib.output_compression'))
			{
				ini_set('zlib.output_compression', 'Off');
			}

			header("Cache-Control: public, must-revalidate");
			header('Cache-Control: pre-check=0, post-check=0, max-age=0');
			header("Expires: 0");
			header("Content-Description: File Transfer");

			// Header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
			header("Content-Type: " . $ctype);
			header("Content-Length: " . (string) $len);

			//  If valid extention
			//  If (!in_array($file_extension, $view_types)){

			header('Content-Disposition: attachment; filename="' . $filename . '"');

			/* } else {
			view file in browser
			header('Content-Disposition: inline; filename="'.$filename.'"');
			}*/

			// Header("Content-Transfer-Encoding: binary\n");

			//  redirect to category when it is set the time

			/* if (intval($jlistConfig['redirect.after.download']) > 0){
			header( "refresh:".$jlistConfig['redirect.after.download']."; url=".$redirect_to );
			}*/

			//  set_time_limit doesn't work in safe mode
			if (!ini_get('safe_mode'))
			{
				@set_time_limit(0);
			}

			@readfile($file);

			// Problems with MS IE
			// header("Expires: 0");
		}

		if ($exitHere == 1)
		{
			exit;
		}
	}

	/**
	 * datei_mime
	 *
	 * @param   STRING  $filetype  filetype
	 *
	 * @return  html
	 */
	public function datei_mime($filetype)
	{
		switch ($filetype)
		{
			case "ez":
				$mime = "application/andrew-inset";
				break;
			case "hqx":
				$mime = "application/mac-binhex40";
				break;
			case "cpt":
				$mime = "application/mac-compactpro";
				break;
			case "doc":
				$mime = "application/msword";
				break;
			case "bin":
				$mime = "application/octet-stream";
				break;
			case "dms":
				$mime = "application/octet-stream";
				break;
			case "lha":
				$mime = "application/octet-stream";
				break;
			case "lzh":
				$mime = "application/octet-stream";
				break;
			case "exe":
				$mime = "application/octet-stream";
				break;
			case "class":
				$mime = "application/octet-stream";
				break;
			case "dll":
				$mime = "application/octet-stream";
				break;
			case "oda":
				$mime = "application/oda";
				break;
			case "pdf":
				$mime = "application/pdf";
				break;
			case "ai":
				$mime = "application/postscript";
				break;
			case "eps":
				$mime = "application/postscript";
				break;
			case "ps":
				$mime = "application/postscript";
				break;
			case "xls":
				$mime = "application/vnd.ms-excel";
				break;
			case "ppt":
				$mime = "application/vnd.ms-powerpoint";
				break;
			case "wbxml":
				$mime = "application/vnd.wap.wbxml";
				break;
			case "wmlc":
				$mime = "application/vnd.wap.wmlc";
				break;
			case "wmlsc":
				$mime = "application/vnd.wap.wmlscriptc";
				break;
			case "vcd":
				$mime = "application/x-cdlink";
				break;
			case "pgn":
				$mime = "application/x-chess-pgn";
				break;
			case "csh":
				$mime = "application/x-csh";
				break;
			case "dvi":
				$mime = "application/x-dvi";
				break;
			case "spl":
				$mime = "application/x-futuresplash";
				break;
			case "gtar":
				$mime = "application/x-gtar";
				break;
			case "hdf":
				$mime = "application/x-hdf";
				break;
			case "js":
				$mime = "application/x-javascript";
				break;
			case "nc":
				$mime = "application/x-netcdf";
				break;
			case "cdf":
				$mime = "application/x-netcdf";
				break;
			case "swf":
				$mime = "application/x-shockwave-flash";
				break;
			case "tar":
				$mime = "application/x-tar";
				break;
			case "tcl":
				$mime = "application/x-tcl";
				break;
			case "tex":
				$mime = "application/x-tex";
				break;
			case "texinfo":
				$mime = "application/x-texinfo";
				break;
			case "texi":
				$mime = "application/x-texinfo";
				break;
			case "t":
				$mime = "application/x-troff";
				break;
			case "tr":
				$mime = "application/x-troff";
				break;
			case "roff":
				$mime = "application/x-troff";
				break;
			case "man":
				$mime = "application/x-troff-man";
				break;
			case "me":
				$mime = "application/x-troff-me";
				break;
			case "ms":
				$mime = "application/x-troff-ms";
				break;
			case "ustar":
				$mime = "application/x-ustar";
				break;
			case "src":
				$mime = "application/x-wais-source";
				break;
			case "zip":
				$mime = "application/x-zip";
				break;
			case "au":
				$mime = "audio/basic";
				break;
			case "snd":
				$mime = "audio/basic";
				break;
			case "mid":
				$mime = "audio/midi";
				break;
			case "midi":
				$mime = "audio/midi";
				break;
			case "kar":
				$mime = "audio/midi";
				break;
			case "mpga":
				$mime = "audio/mpeg";
				break;
			case "mp2":
				$mime = "audio/mpeg";
				break;
			case "mp3":
				$mime = "audio/mpeg";
				break;
			case "aif":
				$mime = "audio/x-aiff";
				break;
			case "aiff":
				$mime = "audio/x-aiff";
				break;
			case "aifc":
				$mime = "audio/x-aiff";
				break;
			case "m3u":
				$mime = "audio/x-mpegurl";
				break;
			case "ram":
				$mime = "audio/x-pn-realaudio";
				break;
			case "rm":
				$mime = "audio/x-pn-realaudio";
				break;
			case "rpm":
				$mime = "audio/x-pn-realaudio-plugin";
				break;
			case "ra":
				$mime = "audio/x-realaudio";
				break;
			case "wav":
				$mime = "audio/x-wav";
				break;
			case "pdb":
				$mime = "chemical/x-pdb";
				break;
			case "xyz":
				$mime = "chemical/x-xyz";
				break;
			case "bmp":
				$mime = "image/bmp";
				break;
			case "gif":
				$mime = "image/gif";
				break;
			case "ief":
				$mime = "image/ief";
				break;
			case "jpeg":
				$mime = "image/jpeg";
				break;
			case "jpg":
				$mime = "image/jpeg";
				break;
			case "jpe":
				$mime = "image/jpeg";
				break;
			case "png":
				$mime = "image/png";
				break;
			case "tiff":
				$mime = "image/tiff";
				break;
			case "tif":
				$mime = "image/tiff";
				break;
			case "wbmp":
				$mime = "image/vnd.wap.wbmp";
				break;
			case "ras":
				$mime = "image/x-cmu-raster";
				break;
			case "pnm":
				$mime = "image/x-portable-anymap";
				break;
			case "pbm":
				$mime = "image/x-portable-bitmap";
				break;
			case "pgm":
				$mime = "image/x-portable-graymap";
				break;
			case "ppm":
				$mime = "image/x-portable-pixmap";
				break;
			case "rgb":
				$mime = "image/x-rgb";
				break;
			case "xbm":
				$mime = "image/x-xbitmap";
				break;
			case "xpm":
				$mime = "image/x-xpixmap";
				break;
			case "xwd":
				$mime = "image/x-xwindowdump";
				break;
			case "msh":
				$mime = "model/mesh";
				break;
			case "mesh":
				$mime = "model/mesh";
				break;
			case "silo":
				$mime = "model/mesh";
				break;
			case "wrl":
				$mime = "model/vrml";
				break;
			case "vrml":
				$mime = "model/vrml";
				break;
			case "css":
				$mime = "text/css";
				break;
			case "asc":
				$mime = "text/plain";
				break;
			case "txt":
				$mime = "text/plain";
				break;
			case "gpg":
				$mime = "text/plain";
				break;
			case "rtx":
				$mime = "text/richtext";
				break;
			case "rtf":
				$mime = "text/rtf";
				break;
			case "wml":
				$mime = "text/vnd.wap.wml";
				break;
			case "wmls":
				$mime = "text/vnd.wap.wmlscript";
				break;
			case "etx":
				$mime = "text/x-setext";
				break;
			case "xsl":
				$mime = "text/xml";
				break;
			case "flv":
				$mime = "video/x-flv";
				break;
			case "mpeg":
				$mime = "video/mpeg";
				break;
			case "mpg":
				$mime = "video/mpeg";
				break;
			case "mpe":
				$mime = "video/mpeg";
				break;
			case "qt":
				$mime = "video/quicktime";
				break;
			case "mov":
				$mime = "video/quicktime";
				break;
			case "mxu":
				$mime = "video/vnd.mpegurl";
				break;
			case "avi":
				$mime = "video/x-msvideo";
				break;
			case "movie":
				$mime = "video/x-sgi-movie";
				break;
			case "asf":
				$mime = "video/x-ms-asf";
				break;
			case "asx":
				$mime = "video/x-ms-asf";
				break;
			case "wm":
				$mime = "video/x-ms-wm";
				break;
			case "wmv":
				$mime = "video/x-ms-wmv";
				break;
			case "wvx":
				$mime = "video/x-ms-wvx";
				break;
			case "ice":
				$mime = "x-conference/x-cooltalk";
				break;
			case "rar":
				$mime = "application/x-rar";
				break;
			default:
				$mime = "application/octet-stream";
				break;
		}

		return $mime;
	}

	/**
	 * Collecting data & called jLike trigger.
	 * Added by komal
	 *
	 * @param   string   $product_url  product_url.
	 * @param   integer  $id           id.
	 * @param   string   $title        title.
	 *
	 * @since   2.2
	 * @return   html
	 */
	public function DisplayAvarageRating($product_url, $id, $title)
	{
		$jlikeparams               = array();
		$jlikeparams['url']        = $product_url;
		$jlikeparams['product_id'] = $id;
		$jlikeparams['title']      = $title;

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('content', 'jlike_quick2cart');

		$grt_response = $dispatcher->trigger('onDisplayRatingAvarage', array('com_quick2cart.productpage', $jlikeparams)
			);

		if (!empty($grt_response['0']))
		{
			return $grt_response['0'];
		}
		else
		{
			return '';
		}
	}

	/**
	 * Collecting data & called jLike trigger
	 *
	 * @param   STRING   $product_url  product_url
	 * @param   INTEGER  $id           id
	 * @param   STRING   $title        STRING
	 *
	 * @return  html
	 */
	public function DisplayjlikeButton($product_url, $id, $title)
	{
		$jlikeparams               = array();
		$jlikeparams['url']        = $product_url;
		$jlikeparams['product_id'] = $id;
		$jlikeparams['title']      = $title;

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('content', 'jlike_quick2cart');
		$grt_response = $dispatcher->trigger('onBeforeDisplaylike', array('com_quick2cart.productpage', $jlikeparams)
		);

		if (!empty($grt_response['0']))
		{
			return $grt_response['0'];
		}
		else
		{
			return '';
		}
	}

	/**
	 * Method to get getMultipleCurrFields.
	 *
	 * @param   string  $name             name
	 * @param   string  $currFieldValues  currFieldValues
	 * @param   string  $currFieldClass   currFieldClass
	 *
	 * @since   2.2
	 *
	 * @return   null
	 */
	public function getMultipleCurrFields($name, $currFieldValues = array(), $currFieldClass = "")
	{
		$html             = '';
		$entered_numerics = "'" . JText::_('QTC_ENTER_NUMERICS') . "'";
		$comParams        = JComponentHelper::getParams('com_quick2cart');
		$currentBSViews   = $comParams->get('currentBSViews', "bs3");
		$app              = JFactory::getApplication();

		// Get Currencies
		$currencies = $comParams->get('addcurrency');
		$curr       = explode(',', $currencies);

		// Get Curr sysbols
		$currencies_sym = $comParams->get('addcurrency_sym');

		if (!empty($currencies_sym))
		{
			$curr_syms = explode(',', $currencies_sym);
		}

		//  Key contain 0,1,2...value contain INR...
		foreach ($curr as $currKey => $value)
		{
			$storevalue = "";

			if (!empty($curr_syms[$currKey]))
			{
				$currtext = $curr_syms[$currKey];
			}
			else
			{
				$currtext = $value;
			}

			$currentFieldValue = ((isset($currFieldValues[$value])) ? $currFieldValues[$value] : 0);

			if ($app->isAdmin() || $currentBSViews == "bs2")
			{
				$html .= '<div class="input-append  curr_margin " >' . '<input type="number" name="' . $name . '[' . $value . ']"
							 value="' . number_format($currentFieldValue, 2) . '"
							class=" input-mini ' . $currFieldClass . '">' . '<span class="add-on ">' . $currtext . '</span>' . '</div>';
			}
			else
			{
				$html .= '<div class="input-group curr_margin " >' . '<input type="number" name="' . $name . '[' . $value . ']"
							size="" id="" value="' . number_format($currentFieldValue, 2) . '"
							class=" form-control ' . $currFieldClass . '">' . '<div class="add-on input-group-addon">' . $currtext . '</div>' . '</div>';
			}
		}

		return $html;
	}

	/**
	 * This method give shipping related fields details.
	 *
	 * @param   INTEGER  $item_id  product's item id.
	 *
	 * @since   1.0
	 *
	 * @return  Shipping related fields.
	 */
	public function getProductsShipRelFields($item_id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("i.`item_id`,i.`item_length`,i.`item_width`,i.`item_height`");
		$query->select("i.`item_length_class_id`,i.`item_weight`,i.`item_weight_class_id`,i.`shipProfileId`");
		$query->from('#__kart_items AS i');
		$query->where('i.item_id=' . $item_id);
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * Method cCheck whether product is allowed to delete or not.  If not the enqueue error message accordingly..
	 *
	 * @param   string  $item_id  item_id.
	 *
	 * @since   2.2
	 *
	 * @return   boolean true or false.
	 */
	public function isAllowedToDelProduct($item_id)
	{
		// 1. Check order with this item id
		$app   = JFactory::getApplication();
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Check in tax related table
		$query->select('oi.order_id');
		$query->from('#__kart_order_item AS oi');
		$query->where('oi.item_id	=' . $item_id);
		$db->setQuery($query);
		$entryList = $db->loadColumn();

		if (!empty($entryList))
		{
			// Order list
			$entryListStr = '(' . JText::_('COM_QUICK2CART_IDS') . implode(',', $entryList) . ')';

			$errMsg = JText::sprintf('COM_QUICK2CART_DEL_PROD_FOUND_ORDER_AGAINST', $item_id, $entryListStr);
			$app->enqueueMessage($errMsg, 'error');

			return false;
		}

		//  @TODO have to check for shipping related and etc table.
		return true;
	}

	/**
	 * Method Check whether product is allowd to buy or not according to the options eg "allowed out of stock" option..
	 * Called from (1.while displaying the product page 2. While adding product in cart 3. While updating the cart item's attribute)
	 *
	 * @param   string   $itemdetail  itemdetail.
	 * @param   integer  $userBuyQty  No of user want to buy the quantity.
	 * Eg when user x no of product add to cart view. At that time, check whether that product quantity is available or not.
	 *
	 * @since   2.2
	 *
	 * @return   boolean true or false.
	 */
	public function isInStockProduct($itemdetail, $userBuyQty = "")
	{
		$params               = JFactory::getApplication()->getParams('com_quick2cart');
		$usestock             = $params->get('usestock');
		$outofstock_allowship = $params->get('outofstock_allowship');

		$min_qty          = (!empty($itemdetail->min_quantity)) ? $itemdetail->min_quantity : 1;
		$max_qty          = (!empty($itemdetail->max_quantity)) ? $itemdetail->max_quantity : 999;
		$buybutton_status = 1;

		if ($usestock == 1 && $outofstock_allowship == 0)
		{
			$stock = $itemdetail->stock;

			// If product has attributes then  Check attribute based stock details
			if (!empty($itemdetail->itemAttributes))
			{
				$statusDetail = $this->checkStockForAttributeOptions($itemdetail->itemAttributes);

				if (!empty($statusDetail['inStock']))
				{
					if (!empty($statusDetail['isAttrBasedStockKeepingProduct']))
					{
						return 1;
					}
				}
				else
				{
					return 0;
				}
			}

			// Check main stock
			if ($stock != null || $stock != '')
			{
				$max_qty = min($stock, $max_qty);
			}

			// 0  and not equal to NULL
			if ($stock <= 0 && $stock != "")
			{
				$buybutton_status = 0;
			}
			elseif ($stock == null)
			{
				// STOCK=NULL mean not entered or not require of stock (e-artical)
				$buybutton_status = 1;
			}
			else
			{
				// Something stock is there, and we hv to check whether use entered qty is present or no
				if (!empty($userBuyQty))
				{
					$buybutton_status = ($userBuyQty <= min($stock, $max_qty)) ? 1 : 0;
				}
			}
		}

		return $buybutton_status;
	}

	/**
	 * Get no of products in cart with same item id
	 * */
	/**
	 * Get no of products in cart with same item id
	 *
	 * @param   integer  $cartId   cart id
	 * @param   integer  $item_id  item_id of product
	 *
	 * @since   2.2
	 *
	 * @return   item_id count
	 */
	public function getCartItemQuantity($cartId, $item_id)
	{
		$db = JFactory::getDbo();

		// Get total cart item with same item id
		$query = $db->getQuery(true);
		$query->select("count(*)");
		$query->from("#__kart_cartitems");

		$conditions = array(
			$db->quoteName('cart_id') . ' =' . (int) $cartId,
			$db->quoteName('item_id') . ' =' . (int) $item_id
		);

		$query->where($conditions);
		$db->setQuery($query);
		$item_idCount = $db->loadResult();

		return !empty($item_idCount)?$item_idCount:0;
	}

	/**
	 * This function check whether product maintaining the  attribute bases stock or not.
	 * If yes then retrn in stock status detail
	 *
	 * @param   array  $itemAttributes  array of attributes with complete attribute details (getAttributeDetails())
	 *
	 * @return  array status detail array.
	 *
	 * @since	2.5
	 */
	public function checkStockForAttributeOptions($itemAttributes)
	{
		$status                                   = array();
		$status['isAttrBasedStockKeepingProduct'] = 0;
		$status['inStock']                        = 1;

		if (empty($itemAttributes))
		{
			return 0;
		}

		// Check is attribute bases stock keeping prodcut
		$stockableAttr = new stdClass;

		foreach ($itemAttributes as $attr)
		{
			if ($attr->is_stock_keeping == 1)
			{
				$stockableAttr = $attr;
				break;
			}
		}

		if (!empty($stockableAttr->optionDetails))
		{
			$status['isAttrBasedStockKeepingProduct'] = 1;
			$status['inStock']                        = 0;

			// Check atleast one of option is in stock
			foreach ($stockableAttr->optionDetails as $option)
			{
				// Stock for atleast one option
				if ($option->state == 1 && !empty($option->child_product_detail->stock) && $option->child_product_detail->stock > 1)
				{
					$status['inStock'] = 1;
					break;
				}
			}
		}
		else
		{
			$status['isAttrBasedStockKeepingProduct'] = 0;
			$status['inStock']                        = 1;
		}

		return $status;
	}

	/**
	 * +manoj
	 * Used for category view => categorylist layout
	 * Returns arry of [catid] => no. of products
	 * Array ( [26] => 1 [28] => 3 )
	 *
	 * @param   INTEGER  $store_id       if store id is zero then consider products from all stores else from specified store
	 * @param   INTEGER  $considerChild  if this is set to 1 then consider subcategory proucts in count
	 *
	 * @return  boolean true or false.
	 */
	public function getCategoryProductsCount($store_id, $considerChild = 0)
	{
		jimport('joomla.application.categories');
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('c.id,c.title');
		$query->from('`#__categories` AS c');
		$query->where("extension='com_quick2cart'");
		$query->where('c.published = 1');
		$db->setQuery($query);
		$qtcCategories = $db->loadAssocList('id');

		// Get  category object instance
		$options               = array();
		$options['extension']  = 'com_quick2cart';
		$options['table']      = '#__kart_items';
		$options['field']      = 'category';
		$options['key']        = 'item_id';
		$options['statefield'] = 'state';
		$options['countItems'] = 1;

		// Get object
		if ($store_id)
		{
			$options['store_id'] = $store_id;
		}

		if (!empty($qtcCategories))
		{
			foreach ($qtcCategories as $key => $cat)
			{
				if (empty($store_id))
				{
					$qtcCategories[$key]['count'] = $this->getProdCountInCategory($cat['id'], $considerChild);
				}
				else
				{
					$qtcCategories[$key]['count'] = $this->getProdCountInCategory($cat['id'], $considerChild, $store_id);
				}
			}
		}

		/*		else
		{
		$qtcJCategoriesObj = new JCategories($options);
		}*/

		/*require_once JPATH_SITE . '/components/com_quick2cart/helpers/qtccategories.php';
		$qtcJCategoriesObj = new qtccategories($options);

		if (!empty($qtcCategories))
		{
		foreach ($qtcCategories as $key => $cat)
		{
		$JCategoryNode = $qtcJCategoriesObj->get($cat['id']);

		if ($JCategoryNode)
		{
		$qtcCategories[$key]['count'] = $JCategoryNode->getNumItems(true);
		}
		}
		}*/

		return $qtcCategories;

		/*  Create a new query object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		Select the required fields from the table.
		$query->select('COUNT(ki.item_id) AS count');
		$query->select('c.id AS cid');
		$query->from('`#__kart_items` AS ki');
		$query->join('LEFT', '`#__categories` AS c ON c.id=ki.category');
		$query->where('ki.state = 1');
		$query->group('c.id');

		$db->setQuery($query);
		$productCount = $db->loadAssocList();

		$pcArray = array();

		if (count($productCount))
		{
		foreach ($productCount as $pc)
		{
		$pcArray[$pc['cid']] = $pc['count'];
		}
		}
		return $pcArray;*/
	}

	/**
	 * This function return item's complete detail.
	 *
	 * @param   string  $item_id  item_id .
	 *
	 * @since   2.2.2
	 *
	 * @return   boolean true or false.
	 */
	public function getItemCompleteDetail($item_id)
	{
		if (empty($item_id))
		{
			return false;
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$db                  = JFactory::getDbo();
		$query               = $db->getQuery(true);

		// 1. Get products basic details
		$query->select("i.`item_id`,i.`parent`,i.`product_id`,i.`store_id`,i.`name`,i.`stock`,i.`min_quantity`,
		i.`max_quantity`,i.`category`,i.`sku`,i.`images`,i.`description`,i.`video_link`,i.`cdate`,
		i.`mdate`,i.`state`,i.`featured`,i.`metakey`,i.`metadesc`,i.`item_length`,i.`item_width`,
		i.`item_height`,i.`item_length_class_id`,i.`item_weight`,i.`item_weight_class_id`,i.`taxprofile_id`,i.`shipProfileId`");
		$query->from('`#__kart_items` AS i');
		$query->where('item_id=' . $item_id);

		$db->setQuery($query);
		$prodBasicDetails = $db->loadObject();

		// 2.Get product all currencies prices irrespective of  session currency.
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('`#__kart_base_currency` AS bc');
		$query->where('item_id=' . $item_id);
		$db->setQuery($query);
		$prodBasicDetails->prodPriceDetails = $db->loadObjectList('currency');

		// 3. Get Attribute Details.
		$prodBasicDetails->prodAttributeDetails = $this->getItemCompleteAttrDetail($item_id);

		// 4. Get media File detail.
		$prodBasicDetails->prodMediaDetails = $this->getMediaDetail($item_id);

		return $prodBasicDetails;
	}

	/**
	 * This function  provides all info about item attributes ( id,att_name,options details, price ).
	 *
	 * @param   integer  $item_id  item_id.
	 *
	 * @since   2.2.2
	 *
	 * @return   boolean true or false.
	 */
	public function getItemCompleteAttrDetail($item_id)
	{
		if (empty($item_id))
		{
			return;
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$attributes          = $this->getAttributes($item_id);

		foreach ($attributes as $key => $att)
		{
			$att->optionDetails = $comquick2cartHelper->getAttributeDetails($att->itemattribute_id);
		}

		return $attributes;
	}

	/**
	 * This function  give item attributes.
	 *
	 * @param   integer  $item_id  item_id
	 *
	 * @since   2.2.2
	 *
	 * @return   Object list.
	 */
	public function getAttributes($item_id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__kart_itemattributes');
		$query->where('item_id=' . $item_id);

		$db->setQuery($query);

		return $db->loadobjectList();
	}

	/**
	 * This function check whether user bought the product or not.
	 *
	 * @param   integer  $user_id  user_id.
	 * @param   integer  $item_id  item_id.
	 *
	 * @since   2.2.2
	 *
	 * @return   boolean true or false.
	 */
	public function isUserBoughtproduct($user_id = 0, $item_id = 0)
	{
	}

	/**
	 * Insert/update the selected attribute option. This function is called while updatating the order.
	 *
	 * @param   ARRAY  $optionDetail  array of option details
	 *
	 * @return  boolean
	 */
	public function insertOptionToOrderItems($optionDetail)
	{
		if (!empty($optionDetail))
		{
			$db        = JFactory::getDbo();
			$items_opt = new stdClass;
			$dbAction  = 'insertObject';

			if (isset($optionDetail['orderitemattribute_id']))
			{
				$dbAction                         = 'updateObject';
				$items_opt->orderitemattribute_id = $optionDetail['orderitemattribute_id'];
			}

			if (isset($optionDetail['order_item_id']))
			{
				$items_opt->order_item_id = $optionDetail['order_item_id'];
			}

			if (isset($optionDetail['itemattributeoption_id']))
			{
				$items_opt->itemattributeoption_id = $optionDetail['itemattributeoption_id'];
			}

			if (isset($optionDetail['orderitemattribute_name']))
			{
				$items_opt->orderitemattribute_name = $optionDetail['orderitemattribute_name'];
			}

			if (isset($optionDetail['orderitemattribute_prefix']))
			{
				$items_opt->orderitemattribute_prefix = $optionDetail['orderitemattribute_prefix'];
			}

			// Load model file
			$path = JPATH_SITE . "/components/com_quick2cart/models/cartcheckout.php";

			if (!class_exists("Quick2cartModelcartcheckout"))
			{
				JLoader::register("Quick2cartModelcartcheckout", $path);
				JLoader::load("Quick2cartModelcartcheckout");
			}

			$Quick2cartModelcartcheckout = new Quick2cartModelcartcheckout;

			// Get option price [currency is referred in this function]
			$items_opt->orderitemattribute_price = $Quick2cartModelcartcheckout->getAttrOptionPrice($optionDetail['itemattributeoption_id']);

			try
			{
				if (!$db->$dbAction('#__kart_order_itemattributes', $items_opt, 'orderitemattribute_id'))
				{
					echo $this->_db->stderr();

					return 0;
				}

				// Return $items_opt->orderitemattribute_id;
				return (array) $items_opt;
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());
			}
		}
	}

	/**
	 * This function gives you option details from DB.
	 *
	 * @param   integer  $option_id  option id.
	 *
	 * @since   2.2.2
	 *
	 * @return   boolean true or false.
	 */
	public function getAttributeOptionDetails($option_id)
	{
		// Get orderitemattribute_ids to update

		if ($option_id)
		{
			try
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select("*")->from('#__kart_itemattributeoptions')->where(" itemattributeoption_id = " . $option_id);
				$db->setQuery($query);

				return $db->loadAssoc('itemattributeoption_id');
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return array();
			}
		}
	}

	/**
	 * This function gives you option details from DB.
	 *
	 * @param   integer  $order_item_id           order item id.
	 * @param   integer  $itemattributeoption_id  attributeoption_id.
	 *
	 * @since   2.2.2
	 *
	 * @return   boolean true or false.
	 */
	public function getOrderAttributeOptionDetails($order_item_id, $itemattributeoption_id)
	{
		// Get orderitemattribute_ids to update

		if ($itemattributeoption_id && $order_item_id)
		{
			try
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select("`orderitemattribute_id`,order_item_id,itemattributeoption_id,orderitemattribute_name,
				orderitemattribute_price,orderitemattribute_prefix")
				->from('#__kart_order_itemattributes ')
				->where(" order_item_id = " . $order_item_id)
				->where(" itemattributeoption_id = " . $itemattributeoption_id);
				$db->setQuery($query);

				return $db->loadAssoc('itemattributeoption_id');
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return array();
			}
		}
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $store_list  The list of the store id.
	 *
	 * @param   integer  $limit       The limit is the number of product to show.
	 *
	 * @return  Object list.
	 *
	 * @since	1.7
	 */
	public function getNewlyAddedProductsWithStoreId($store_list, $limit = '2')
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('i.item_id,i.name,i.images,i.`store_id`,i.slab,i.`min_quantity`,
		i.`max_quantity`,i.`featured`,i.`parent`,i.`product_id`  ,i.`stock`');
		$query->from('#__kart_items AS i');
		$query->where('i.state=1');

		if (!empty($store_list))
		{
			$storesStr = implode(",", $store_list);
			$query->where('i.store_id  IN ( ' . $storesStr . ')');
		}

		$query->setLimit($limit);
		$query->order("i.item_id DESC");
		$db->setQuery($query);

		return $productinfo = $db->loadObjectList();
	}

	/**
	 * Method to globle attribute set for product
	 *
	 * @param   ARRAY  $itemDetail  product detail
	 *
	 * @return  Object list.
	 *
	 * @since	2.5
	 */
	public function getProductGlobalAttributeSet($itemDetail)
	{
		$attributeIds = $this->getCategoryGlobalAttriIds($itemDetail['category']);

		if (!empty($attributeIds))
		{
			$globalAttrIds = implode(",", $attributeIds);

			try
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select("ga.id, ga.attribute_name")
				->from('#__kart_global_attribute AS ga')
				->where('ga.id  IN ( ' . $globalAttrIds . ')')
				->where('ga.state=1');
				$db->setQuery($query);

				return $globalAttributes = $db->loadObjectList('id');
			}
			catch (Exception $e)
			{
				echo $e->getMessage();

				return array();
			}
		}
	}

	/**
	 * Method to get globle attribtues for provided category
	 *
	 * @param   integer  $cat_id  Category Id
	 *
	 * @return  Object list.
	 *
	 * @since	2.5
	 */
	public function getCategoryGlobalAttriIds($cat_id)
	{
		if ($cat_id)
		{
			try
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select("gas.global_attribute_ids")
				->from('#__kart_category_attribute_set AS cas')
				->join('INNER', '#__kart_global_attribute_set AS gas ON cas.attribute_set_id=gas.id')
				->where(" cas.category_id = " . $cat_id);
				$db->setQuery($query);

				$result = $db->loadObject();

				if ($result)
				{
					return $global_attribute_idsArray = json_decode($result->global_attribute_ids);
				}
				else
				{
					return array();
				}
			}
			catch (Exception $e)
			{
				echo $e->getMessage();

				return array();
			}
		}
	}

	/**
	 * Method to get globle attribtue's option detail
	 *
	 * @param   ARRAY  $gAttriId  product detail
	 *
	 * @return  Object list.
	 *
	 * @since	2.5
	 */
	public function getGlobalAttriOptions($gAttriId)
	{
		if ($gAttriId)
		{
			try
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select("ga.*")->from('#__kart_global_attribute_option AS ga')->where(" ga.attribute_id = " . $gAttriId);
				$db->setQuery($query);

				return $db->loadObjectList();
			}
			catch (Exception $e)
			{
				echo $e->getMessage();

				return array();
			}
		}
		else
		{
			return array();
		}
	}

	/**
	 * Method return product's stock according to STOCKABLE attribute's option
	 *
	 * @param   ARRAY  $formattedAttriDetails  product detail
	 *
	 * @return  Object list.
	 *
	 * @since	2.5
	 */
	public function getAttriBasedStock($formattedAttriDetails)
	{
		$child_product_item_id            = 0;
		$retData                          = array();
		$retData['child_product_item_id'] = '';
		$retData['stock']                 = '';

		if (!empty($formattedAttriDetails))
		{
			foreach ($formattedAttriDetails as $attrib)
			{
				// Check for stock keeping attribute and get the global child product id
				if (!empty($attrib['is_stock_keeping']) && $attrib['selectedOptionDetail']['child_product_item_id'])
				{
					$child_product_item_id = $attrib['selectedOptionDetail']['child_product_item_id'];
				}
			}
		}

		if (!empty($child_product_item_id))
		{
			$retData['child_product_item_id'] = $child_product_item_id;

			try
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select("stock")->from('#__kart_items AS i')->where(" i.item_id = " . $child_product_item_id);
				$db->setQuery($query);
				$retData['stock'] = $db->loadResult();

				return $retData;
			}
			catch (Exception $e)
			{
				echo $e->getMessage();

				return $retData;
			}
		}
	}

	/**
	 * Method to get sku for item_id
	 *
	 * @param   ARRAY  $item_id  item id.
	 *
	 * @return  Object list.
	 *
	 * @since	2.5
	 */
	public function getSku($item_id)
	{
		if ($item_id)
		{
			try
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select("i.sku")->from('#__kart_items AS i')->where(" i.item_id = " . $item_id);
				$db->setQuery($query);

				return $db->loadResult();
			}
			catch (Exception $e)
			{
				echo $e->getMessage();

				return array();
			}
		}
	}

	/**
	 * This function check whether category is allowed to change
	 *
	 * @param   integer  $item_id  item id.
	 *
	 * @return  Object list.
	 *
	 * @since	2.5
	 */
	public function isAllowedtoChangeProdCategory($item_id)
	{
		if ($item_id)
		{
			try
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select("ia.global_attribute_id")
				->from('#__kart_itemattributes AS ia')
				->where(" ia.item_id = " . $item_id)
				->where(" ia.global_attribute_id > 0");
				$db->setQuery($query);

				$data = $db->loadColumn();

				if (!empty($data))
				{
					return 1;
				}
				else
				{
					return 0;
				}
			}
			catch (Exception $e)
			{
				echo $e->getMessage();

				return 0;
			}
		}
	}

	/**
	 * This function returns count of products in category
	 *
	 * @param   integer  $cat_id              category id
	 *
	 * @param   integer  $consider_child_cat  flag
	 *
	 * @param   integer  $storeId             store Id
	 *
	 * @return  Object list.
	 *
	 * @since	2.5.1
	 */
	public function getProdCountInCategory($cat_id, $consider_child_cat = 0, $storeId = 0)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("COUNT(i.item_id)");
		$query->from('#__kart_items AS i');

		if (!empty($storeId))
		{
			$query->where("i.store_id = " . $storeId);
		}

		if ($consider_child_cat == 1)
		{
			// Load model file
			$path = JPATH_SITE . "/components/com_quick2cart/models/category.php";

			if (!class_exists("Quick2cartModelCategory"))
			{
				JLoader::register("Quick2cartModelCategory", $path);
				JLoader::load("Quick2cartModelCategory");
			}

			$CategoryModel     = new Quick2cartModelCategory;
			$categoryCondition = $CategoryModel->getWhereCategory($cat_id);
			$query->join('LEFT', '`#__categories` AS c ON c.id=i.category');
			$query->where($categoryCondition);
		}
		else
		{
			$query->where("i.category = " . $cat_id);
		}

		$query->where("i.display_in_product_catlog = 1");

		$db->setQuery($query);

		$count = $db->loadresult();

		return $count;
	}

	/**
	 * This function return add/update in query result data and return the modified data to calling function.
	 *
	 * @param   array   $data   data to be store in param field
	 * @param   STRING  $query  to get param field data from DB
	 * @param   string  $key    Key of array in which data have to store
	 *
	 * @return json string  :: json_encoded extra field data
	 */
	public function updateJsonData($data, $query, $key)
	{
		$db = JFactory::getDBO();

		// $q="SELECT  `extra` FROM  `#__kart_orders` WHERE `id` =".$order_id;
		$db->setQuery($query);
		$oldres = $db->loadResult();

		if (empty($oldres))
		{
			$olddata = array();
		}
		else
		{
			// Take already exist extra data
			$olddata = json_decode($oldres, true);
		}

		$olddata[$key] = $data;

		return json_encode($olddata);
	}
}
