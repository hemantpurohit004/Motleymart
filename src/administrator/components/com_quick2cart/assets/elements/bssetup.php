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
 * Supports an HTML select list of gateways
 *
 * @since  1.6
 */
class JFormFieldBssetup extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Bssetup';

	/**
	 * Function to fetch elements
	 *
	 * @return  STRING  html
	 */
	public function getInput()
	{
		return $this->fetchElement($this->name, $this->value, $this->element, $this->options['control']);
	}

	/**
	 * Function to fetch elements
	 *
	 * @param   STRING  $name          name
	 * @param   STRING  $value         value
	 * @param   STRING  $node          node
	 * @param   STRING  $control_name  control_name
	 *
	 * @return  STRING  html
	 */
	public function fetchElement($name, $value, $node, $control_name)
	{
		$actionLink = JURI::base() . "index.php?option=com_quick2cart&view=dashboard&layout=setup";

		// Show link for payment plugins.
		$html = '<a
			href="' . $actionLink . '" target="_blank"
			class="btn btn-small btn-primary ">'
				. JText::_('COM_QUICK2CART_CLICK_BS_SETUP_INSTRUCTION') .
			'</a>';

		return $html;
	}
}
