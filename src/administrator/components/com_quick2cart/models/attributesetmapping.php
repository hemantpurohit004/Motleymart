<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Model for category attribute set mapping
 *
 * @since  2.5
 *
 */
class Quick2cartModelAttributesetMapping extends JModelList
{
	/**
	 * Function to get attribute sets
	 *
	 * @return array of attribute sets
	 *
	 * @since  2.5
	 *
	 **/
	public function getattributesets()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('global_attribute_set_name'));
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__kart_global_attribute_set'));
		$db->setQuery($query);

		$select = new stdclass;
		$select->id = '0';
		$select->global_attribute_set_name = JText::_('QTC_PROD_SEL_ATTRIBUTE');

		$attributesetslist = array();

		array_push($attributesetslist, $select);

		$attributesets = $db->loadObjectlist();

		foreach ($attributesets as $attr)
		{
			array_push($attributesetslist, $attr);
		}

		return $attributesetslist;
	}

	/**
	 * Function to get attribute set for mapped category id
	 *
	 * @param   INT  $categoryId  attribute set id
	 *
	 * @return category id
	 *
	 * @since  2.5
	 *
	 **/
	public function getAttributeSet($categoryId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('attribute_set_id'));
		$query->from($db->quoteName('#__kart_category_attribute_set'));
		$query->where($db->quoteName('category_id') . ' = ' . $categoryId);
		$db->setQuery($query);
		$attributeSetId = $db->loadResult();

		return $attributeSetId;
	}

	/**
	 * Function to get category id for mapped attribute set
	 *
	 * @param   INT  $categoryId  attribute set id
	 *
	 * @return category id
	 *
	 * @since  2.5
	 *
	 **/
	public function getAttributeSetId($categoryId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('attribute_set_id'));
		$query->from($db->quoteName('#__kart_category_attribute_set'));
		$query->where($db->quoteName('category_id') . ' = ' . $categoryId);
		$db->setQuery($query);
		$attributeSetId = $db->loadResult();

		return $attributeSetId;
	}

	/**
	 * Function to save attribute set and category mapping
	 *
	 * @return null
	 *
	 * @since  2.5
	 *
	 **/
	public function save()
	{
		$application = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$category_map = array();
		$category_map = $input->get('cat', '', 'array');

		// Get all mapped category set list
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('category_id'));
		$query->from($db->quoteName('#__kart_category_attribute_set'));
		$db->setQuery($query);
		$categoryList = $db->loadColumn();

		foreach ($category_map as $key => $category)
		{
			$record = new stdclass;
			$record->attribute_set_id = $category[0];
			$record->category_id = $key;

			// If attribute set id already maped then update record else add new record
			if (in_array($record->category_id, $categoryList))
			{
				if ($record->attribute_set_id != 0)
				{
					$db->updateObject('#__kart_category_attribute_set', $record, 'category_id', true);
				}
				else
				{
					$query = $db->getQuery(true);
					$conditions = array(
						$db->quoteName('category_id') . ' = ' . $record->category_id
					);
					$query->delete($db->quoteName('#__kart_category_attribute_set'));
					$query->where($conditions);
					$db->setQuery($query);
					$db->execute();
				}
			}
			else
			{
				if ($record->attribute_set_id != 0)
				{
					$db->insertObject('#__kart_category_attribute_set', $record, 'category_id', true);
				}
			}
		}

		$application->enqueueMessage(JText::_('COM_QUICK2CART_MAPPING_SAVED'));
	}

	/**
	 * Function to check if there are products in mapped category
	 *
	 * @param   INT  $categoryId  category id
	 *
	 * @return null
	 *
	 * @since  2.5
	 *
	 **/
	public function checkForProductsInCategory($categoryId)
	{
		// Fetch mapped attribute set from category id
		$attributeSetId = $this->getAttributeSet($categoryId);
		$attributeSetModel = JModelLegacy::getInstance('Attributeset', 'Quick2cartModel');

		if (!empty($attributeSetId))
		{
			$attributeList = $attributeSetModel->getAttributeListInAttributeSet($attributeSetId);
		}

		$count = 0;
		$globalAttributeList = array();

		if (!empty($attributeList))
		{
			foreach ($attributeList as $attribute)
			{
				if ($attribute['id'] != 0)
				{
					$globalAttributeList[] = $attribute['id'];
				}
			}
		}

		if (!empty($globalAttributeList) && (!empty($categoryId)))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('COUNT(a.item_id)');
			$query->from($db->quoteName('#__kart_items', 'a'));
			$query->join('INNER', $db->quoteName('#__kart_itemattributes', 'ia') . 'ON' . $db->quoteName('ia.item_id') . '=' . $db->quoteName('a.item_id'));
			$query->where($db->quoteName('ia.global_attribute_id') . ' IN (' . implode(',', $globalAttributeList) . ')');
			$query->where($db->quoteName('a.category') . '=' . $categoryId);
			$db->setQuery($query);
			$count = $db->loadResult();
		}

		return $count;
	}
}
