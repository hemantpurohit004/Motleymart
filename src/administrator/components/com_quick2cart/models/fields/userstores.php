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
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class JFormFieldUserstores extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'userstores';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		require_once JPATH_SITE . '/components/com_quick2cart/helpers/storeHelper.php';

		$storeHelper = new storeHelper;

		// Get all stores.
		$user = JFactory::getUser();

		$stores = $storeHelper->getUserStore($user->id);

		$options   = array();

		foreach ($stores as $key => $value)
		{
			$options[] = JHtml::_('select.option', $value['id'], $value['title']);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
