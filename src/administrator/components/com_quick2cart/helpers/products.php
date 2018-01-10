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

/**
 * Products helper for quick2cart backend.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartBackendProductsHelper
{
	/**
	 * Returns edit or details link for given product as per cck or native parent.
	 *
	 * @param   int     $item_id   The product id
	 * @param   string  $linkType  Type of link
	 *
	 * @return  string
	 *
	 * @since   2.2
	 */
	public function getProductLink($item_id, $linkType = 'detailsLink')
	{
		$helperobj = new comquick2cartHelper;
		$db        = JFactory::getDBO();
		$query     = "select `product_id`,`parent` from `#__kart_items` where item_id=" . $item_id;
		$db->setQuery($query);
		$res = $db->loadAssoc();

		$link = "";

		$uri = JUri::getInstance();

		switch ($res["parent"])
		{
			case "com_content":
				if ($linkType == 'detailsLink')
				{
					require_once JPATH_SITE . '/components/com_content/helpers/route.php';

					$query = 'SELECT a.id, ' . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,' .
					' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug' . ' FROM #__content AS a ' .
					' INNER JOIN #__categories AS cc ON cc.id = a.catid' . ' WHERE a.id=' . $res["product_id"];

					$db->setQuery($query);
					$article = $db->loadObject();

					$link = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catslug), false);
				}
				elseif ($linkType == 'editLink')
				{
					$link = JUri::base() . 'index.php?option=com_content&task=article.edit&id=' . $res["product_id"];
				}
				break;

			default:
			case "com_quick2cart":
				if ($linkType == 'detailsLink')
				{
					$catpage_Itemid = $helperobj->getitemid('index.php?option=com_quick2cart&view=category');
					$q2cLink = 'index.php?option=com_quick2cart&view=productpage&layout=default&item_id=' . $res["product_id"] . "&Itemid=" . $catpage_Itemid;
					$link           = JRoute::_($q2cLink, false);
				}
				elseif ($linkType == 'editLink')
				{
					$link = JUri::base() . 'index.php?option=com_quick2cart&view=product&layout=new&item_id=' . $res["product_id"];
				}
				break;

			case "com_zoo":
				if ($linkType == 'detailsLink')
				{
					$Itemid = $helperobj->getitemid('index.php?option=com_zoo&task=item');
					$link = JRoute::_("index.php?option=com_zoo&task=item&item_id=" . $res["product_id"] . "&Itemid=" . $Itemid, false);
				}
				elseif ($linkType == 'editLink')
				{
					if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php'))
					{
						require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';
					}

					$zooApp = App::getInstance('zoo');
					$appId = $zoo_item->application_id;

					$zoo_item = $zooApp->table->item->get($res["product_id"]);
					$link = JUri::base() . 'index.php?option=com_zoo&controller=item&changeapp=' . $appId . '&task=edit&cid[]=' . $res["product_id"];
				}
				break;

			case "com_k2":
				require_once JPATH_SITE . '/components/com_k2/helpers/route.php';
				$Itemid = $helperobj->getitemid('index.php?option=com_k2&view=item');

				if ($linkType == 'detailsLink')
				{
					$query = "SELECT a.id, a.alias, a.catid,
					 b.alias as categoryalias
					 FROM #__k2_items as a
					 LEFT JOIN #__k2_categories AS b ON b.id = a.catid
					 WHERE a.id = " . $res["product_id"];

					$db->setQuery($query);
					$k2item = $db->loadObject();

					$k2link = K2HelperRoute::getItemRoute($k2item->id . ':' . urlencode($k2item->alias), $k2item->catid . ':' . urlencode($k2item->categoryalias));
					$link = JRoute::_($k2link, false);
				}
				elseif ($linkType == 'editLink')
				{
					$link = JUri::base() . 'index.php?option=com_k2&view=item&cid=' . $res["product_id"];
				}
				break;

			case "com_flexicontent":
				if ($linkType == 'detailsLink')
				{
					$link = JUri::base() . 'index.php?option=com_flexicontent&task=items.edit&cid[]=' . $res["product_id"];
				}
				elseif ($linkType == 'editLink')
				{
					$link = JUri::base() . 'index.php?option=com_flexicontent&task=items.edit&cid[]=' . $res["product_id"];
				}
				break;

			case "com_cobalt":

				include_once JPATH_ROOT . '/components/com_cobalt/api.php';

				if ($linkType == 'detailsLink')
				{
					$link = JRoute::_(Url::record($res["product_id"]), true, -1);
				}
				elseif ($linkType == 'editLink')
				{
					$link = JRoute::_(Url::edit($res["product_id"]), true, -1);
				}

				$link = str_replace('administrator/', '', $link);
				break;
		}

		return $link;
	}

	/**
	 * Returns parent CCK name for gien product.
	 *
	 * @param   int  $item_id  The product id
	 *
	 * @return  string
	 *
	 * @since   2.2
	 */
	public function getProductParentName($item_id)
	{
		$helperobj = new comquick2cartHelper;
		$db        = JFactory::getDBO();
		$query     = "select `product_id`,`parent` from `#__kart_items` where item_id=" . $item_id;
		$db->setQuery($query);
		$res = $db->loadAssoc();

		$parent = "";

		switch ($res["parent"])
		{
			default:
			case 'com_quick2cart':
				$parent = JText::_('COM_QUICK2CART_NATIVE');
				break;

			case 'com_content':
				$parent = JText::_('COM_QUICK2CART_CONTENT_ARTICLES');
				break;

			case 'com_flexicontent':
				$parent = JText::_('COM_QUICK2CART_FLEXICONTENT');
				break;

			case 'com_k2':
				$parent = JText::_('COM_QUICK2CART_K2');
				break;

			case 'com_zoo':
				$parent = JText::_('COM_QUICK2CART_ZOO');
				break;

			case 'com_cobalt':
			$parent = JText::_('COM_QUICK2CART_COBALT');
			break;
		}

		return $parent;
	}
}
