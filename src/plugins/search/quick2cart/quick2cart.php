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

require_once JPATH_SITE . '/components/com_quick2cart/router.php';
require_once JPATH_SITE . '/components/com_quick2cart/helper.php';

/**
 * Quick2cart search plugin.
 *
 * @package     Quick2cart
 * @subpackage  Search.Quick2cart
 * @since       2.2
 */
class PlgSearchQuick2cart extends JPlugin
{
	/**
	 * Determine areas searchable by this plugin.
	 *
	 * @return  array  An array of search areas.
	 *
	 * @since   1.6
	 */
	public function onContentSearchAreas()
	{
		JPlugin::loadLanguage('plg_search_quick2cart', JPATH_ADMINISTRATOR);

		static $areas = array(
			'quick2cart' => 'PLG_SEARCH_QUICK2CART_PRODUCTS'
		);

		return $areas;
	}

	/**
	 * Search Quick2cart (Products).
	 * The SQL must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav.
	 *
	 * @param   string  $text      Target search string.
	 * @param   string  $phrase    Matching option (possible values: exact|any|all).  Default is "any".
	 * @param   string  $ordering  Ordering option (possible values: newest|oldest|popular|alpha|category).  Default is "newest".
	 * @param   mixed   $areas     An array if the search it to be restricted to areas or null to search all areas.
	 *
	 * @return  array  Search results.
	 *
	 * @since   1.6
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$tag = JFactory::getLanguage()->getTag();

		require_once JPATH_ADMINISTRATOR . '/components/com_search/helpers/search.php';

		$searchText = $text;

		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return array();
			}
		}

		$sContent = $this->params->get('search_products', 1);
		$limit = $this->params->def('search_limit', 50);

		$nullDate = $db->getNullDate();
		$date = JFactory::getDate();
		$now = $date->toSql();

		$text = trim($text);

		if ($text == '')
		{
			return array();
		}

		switch ($phrase)
		{
			case 'exact':
				$text = $db->quote('%' . $db->escape($text, true) . '%', false);
				$wheres2 = array();
				$wheres2[] = 'a.name LIKE ' . $text;
				$wheres2[] = 'a.description LIKE ' . $text;
				$wheres2[] = 'a.metakey LIKE ' . $text;
				$wheres2[] = 'a.metadesc LIKE ' . $text;
				$where = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode(' ', $text);
				$wheres = array();

				foreach ($words as $word)
				{
					$word = $db->quote('%' . $db->escape($word, true) . '%', false);
					$wheres2 = array();
					$wheres2[] = 'a.name LIKE ' . $word;
					$wheres2[] = 'a.description LIKE ' . $word;
					$wheres2[] = 'a.metakey LIKE ' . $word;
					$wheres2[] = 'a.metadesc LIKE ' . $word;
					$wheres[] = implode(' OR ', $wheres2);
				}

				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.cdate ASC';
				break;

			/*case 'popular':
				$order = 'a.hits DESC';
				break;*/

			case 'alpha':
				$order = 'a.name ASC';
				break;

			case 'category':
				$order = 'c.title ASC, a.title ASC';
				break;

			case 'newest':
			default:
				$order = 'a.cdate DESC';
				break;
		}

		$rows = array();
		$query = $db->getQuery(true);

		// Search products.
		if ($sContent && $limit > 0)
		{
			$query->clear();

			$query->select('a.item_id, a.name AS title, a.metadesc, a.metakey, a.cdate AS created')
				->select('a.description AS text')
				->select('c.title AS section')

				->from('#__kart_items AS a')
				->join('INNER', '#__categories AS c ON c.id=a.category')
				->where('(' . $where . ') AND a.state=1 AND c.published = 1')
				->group('a.item_id, a.name, a.metadesc, a.metakey, a.cdate, a.description, c.title, c.id')
				->order($order);

			$db->setQuery($query, 0, $limit);
			$list = $db->loadObjectList();
			$limit -= count($list);

			if (isset($list))
			{
				$comquick2cartHelper = new comquick2cartHelper;
				$itemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=productpage&layout=default');

				foreach ($list as $key => $item)
				{
					$link = 'index.php?option=com_quick2cart&view=productpage&layout=default&item_id=' . $item->item_id . '&Itemid=' . $itemid;
					$list[$key]->href = JRoute::_($link, false);
				}
			}

			$rows[] = $list;
		}

		$results = array();

		if (count($rows))
		{
			foreach ($rows as $row)
			{
				$new_row = array();

				foreach ($row as $article)
				{
					$article->browsernav = '';

					if (SearchHelper::checkNoHTML($article, $searchText, array('text', 'title', 'metadesc', 'metakey')))
					{
						$new_row[] = $article;
					}
				}

				$results = array_merge($results, (array) $new_row);
			}
		}

		return $results;
	}
}
