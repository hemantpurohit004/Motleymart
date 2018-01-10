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
 * This Class supports checkout process.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class JFormFieldZonestorelist extends JFormField
{
	protected	$type = 'zonestorelist';

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
		$comquick2cartHelper = new comquick2cartHelper;

		// Getting user accessible store ids
		$storeList = $comquick2cartHelper->getStoreIds();
		$options = array();

		$app = JFactory::getApplication();
		$jinput = $app->input;
		$zone_id = $jinput->get('id');
		$defaultSstore_id = 0;

		if ($zone_id)
		{
			// Load Zone helper.
			$path = JPATH_SITE . "/components/com_quick2cart/helpers/zoneHelper.php";

			if (!class_exists('zoneHelper'))
			{
				JLoader::register('zoneHelper', $path);
				JLoader::load('zoneHelper');
			}

			$zoneHelper = new zoneHelper;
			$defaultSstore_id = $zoneHelper->getZoneStoreId($zone_id);
		}

		foreach ($storeList as $store)
		{
			$storename = ucfirst($store['title']);
			$options[] = JHtml::_('select.option', $store['store_id'], $storename);
		}

		return JHtml::_('select.genericlist', $options, $name, 'class="inputbox required"  size="1"', 'value', 'text', $defaultSstore_id, $control_name);
	}
}
