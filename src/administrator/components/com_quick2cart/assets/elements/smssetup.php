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
 * JFormFieldSmssetup form custom element class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.6
 */
class JFormFieldSmssetup extends JFormField
{
	protected $type = 'smssetup';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   2.6
	 */
	public function getInput()
	{
		return $this->fetchElement($this->name, $this->value, $this->element, $this->options['control']);
	}

	/**
	 * Get needed field data
	 *
	 * @param   string  $name          Name of the field
	 * @param   string  $value         Value of the field
	 * @param   string  $node          Node of the field
	 * @param   string  $control_name  Field control name
	 *
	 * @return   string  Field HTML
	 */
	public function fetchElement($name, $value, $node, $control_name)
	{
		$html = '<a
			href="index.php?option=com_plugins&view=plugins&filter_folder=system"
			target="_blank"
			class="btn btn-small btn-primary">'
				. JText::_('COM_QUICK2CART_SETUP_SMS_PLUGINS') .
			'</a>';

		return $html;
	}
}
