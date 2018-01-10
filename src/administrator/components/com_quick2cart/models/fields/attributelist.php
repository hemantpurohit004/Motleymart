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

JFormHelper::loadFieldClass('list');

/**
 * This Class supports checkout process.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class JFormFieldattributelist extends JFormField
{
	public $type = 'attributelist';

	/**
	 * This function to get attribute list
	 *
	 * @return  null
	 *
	 * @since	2.2
	 */
	public function getInput()
	{
		// Initialize variables.
		$options = array();
		$db	= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('DISTINCT ga.id, ga.attribute_name');
		$query->from('#__kart_global_attribute AS ga');
		$query->join('INNER', "#__kart_global_attribute_option AS ao ON ao.attribute_id = ga.id");

		// Get the options.
		$db->setQuery($query);

		$globalattributes = $db->loadObjectList();

		$options = array();

		$options[] = JHtml::_('select.option', 0, JText::_('QTC_PRODUCT_ATTRIBUTE_SELECT'));

		foreach ($globalattributes as $attributes)
		{
			$options[] = JHtml::_('select.option', $attributes->id, $attributes->attribute_name);
		}

		return $options;
	}
}
