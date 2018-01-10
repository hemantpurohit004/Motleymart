<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of files
 *
 * @since  11.1
 */
class JFormFieldSubscriptionsList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'subscriptionslist';

	/**
	 * Method to get the list of files for the field options.
	 * Specify the target directory with a directory attribute
	 * Attributes allow an exclude mask and stripping of extensions from file name.
	 * Default attribute may optionally be set to null (no file) or -1 (use a default).
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();

		try
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id as value, title as text')
				  ->from($db->quoteName('#__ecomm_subscriptions'))
				  ->where($db->quoteName('state') . ' = ' . $db->quote('1'));

			$db->setQuery($query);
			$rows = $db->loadObjectlist();

			foreach($rows as $row)
			{
				$options[] = $row;
			}

			// Merge any additional options in the XML definition.
			$options = array_merge(parent::getOptions(), $options);
		}
		catch(Exception $e)
		{
		}

		return $options;
	}
}
