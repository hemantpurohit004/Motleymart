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

jimport('joomla.form.formfield');

/**
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class JFormFieldCreatedby extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'createdby';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 *
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();

		// Load user
		$user_id = $this->value;

		if ($user_id)
		{
			$user = JFactory::getUser($user_id);
		}
		else
		{
			$user = JFactory::getUser();
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $user->id . '" />';
		}

		$html[] = "<div>" . $user->name . " (" . $user->username . ")</div>";

		return implode($html);
	}
}
