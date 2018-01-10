<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/*if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}*/

jimport('joomla.form.formfield');

/**
 * JFormFieldSmsplg form custom element class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.6
 */
class JFormFieldSmsplg extends JFormField
{
	protected $type = 'smsplg';

	protected $_name = 'smsplg';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
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
	 * @param   string  &$node         Node of the field
	 * @param   string  $control_name  Field control name
	 *
	 * @return   string  Field HTML
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		$db = JFactory::getDBO();

		$condtion = array(0 => '\'sms\'');
		$condtionatype = join(',', $condtion);

		if (JVERSION >= '1.6.0')
		{
			$query = "SELECT extension_id as id,name,element,enabled as published FROM #__extensions WHERE folder in ($condtionatype) AND enabled=1";
		}
		else
		{
			$query = "SELECT id,name,element,published FROM #__plugins WHERE folder in ($condtionatype) AND published=1";
		}

		$db->setQuery($query);
		$smsplugin = $db->loadobjectList();

		$options = array();

		foreach ($smsplugin as $sms_opt)
		{
			$sms_opt_name = ucfirst(str_replace('plugsms', '', $sms_opt->element));
			$options[] = JHtml::_('select.option', $sms_opt->element, $sms_opt_name);
		}

		if (JVERSION >= 1.6)
		{
			$fieldName = $name;
		}
		else
		{
			$fieldName = $control_name . '[' . $name . ']';
		}

		$html = JHtml::_(
		'select.genericlist', $options, $fieldName, 'class="inputbox required"', 'value', 'text', $value,
		$control_name . $name
		);

		// Show link for payment plugins.
		$html .= '<a
			href="index.php?option=com_plugins&view=plugins&filter_folder=sms&filter_enabled="
			target="_blank"
			class="btn btn-small btn-primary">'
				. JText::_('PLG_SYSTEM_QTC_SMS_SETUP_SMS_PLUGINS') .
			'</a>';

		return $html;
	}

	/**
	 * Get field tooltip
	 *
	 * @param   string  $label         Label of the field
	 * @param   string  $description   Description of the field
	 * @param   string  &$node         Node of the field
	 * @param   string  $control_name  Field control name
	 * @param   string  $name          Field name
	 *
	 * @return   string  Field HTML
	 */
	public function fetchTooltip($label, $description, &$node, $control_name, $name)
	{
		return null;
	}
}
