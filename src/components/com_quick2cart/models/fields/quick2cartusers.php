<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2Cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Supports an HTML select list of categories
 *
 * @package  Quick2cart
 * @since    2.7
 */
class JFormFieldquick2cartusers extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'quick2cartusers';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since  1.6
	 */
	public function getInput()
	{
		// Initialize variables.
		$options = array();
		$db	= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('id, name, username');
		$query->from('#__users AS ac');

		// Get the options.
		$db->setQuery($query);

		$users = array();
		$user[] = array('id' => '0', 'name' => JText::_('COM_QUICK2CART_SELECT_CUSTOMER'), 'username' => JText::_('COM_QUICK2CART_SELECT_CUSTOMER'));

		$users = array_merge($user, $db->loadAssocList());

		return $users;
	}
}
