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
 * This help to fetch users available zones across all store.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class JFormFieldZonelist extends JFormField
{
	protected $type = 'zonelist';

	/**
	 * Fetch Element view.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getInput()
	{
		return $this->fetchElement($this->name, $this->value, $this->element, $this->options['control']);
	}

	/**
	 * Fetch custom Element view.
	 *
	 * @param   string  $name          Field Name.
	 * @param   mixed   $value         Field value.
	 * @param   mixed   $node          Field node.
	 * @param   mixed   $control_name  Field control_name/Id.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function fetchElement($name, $value, $node, $control_name)
	{
		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		// Load Zone helper.
		$path = JPATH_SITE . "/components/com_quick2cart/helpers/zoneHelper.php";

		//  if (!class_exists('zoneHelper'))
		{
			JLoader::register('zoneHelper', $path);
			JLoader::load('zoneHelper');
		}
		$zoneHelper = new zoneHelper;

		// Get user's accessible zone list
		$zoneList = $zoneHelper->getUserZoneList('', array(1));
		$options = array();
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$taxrate_id = $jinput->get('id');
		$defaultZoneid = "";

		if ($taxrate_id)
		{
			$defaultZoneid = $zoneHelper->getZoneFromTaxRateId($taxrate_id);
		}

		foreach ($zoneList as $zone)
		{
			$zoneName = ucfirst($zone['name']);
			$options[] = JHtml::_('select.option', $zone['id'], $zoneName);
		}

		return JHtml::_('select.genericlist', $options, $name, 'class="inputbox required"  size="1"', 'value', 'text', $defaultZoneid, $control_name);
	}
}
