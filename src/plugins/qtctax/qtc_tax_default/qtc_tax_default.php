<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

jimport('joomla.plugin.plugin');
$lang = JFactory::getLanguage();
$lang->load('plug_qtc_tax_default', JPATH_ADMINISTRATOR);

/**
 * PlgQtctaxqtc_tax_default
 *
 * @package     Com_Quick2cart
 * @subpackage  site
 * @since       2.2
 */
class PlgQtctaxqtc_Tax_Default extends JPlugin
{
	/**
	 * Gives applicable tax charges.
	 *
	 * @param   integer  $amt   cart subtotal (after discounted amount )
	 * @param   object   $vars  object with cartdetail,billing and shipping details.
	 *
	 * @since   2.2
	 * @return   it should return array that contain [charges]=>charges [DetailMsg]=>Detail message
	 * 				or return empty array
	 */
	public function addTax($amt, $vars='')
	{
		$tax_per = $this->params->get('tax_per');
		$tax_value = ($tax_per * $amt) / 100;

		$return["DetailMsg"] = $tax_per . "%";
		$return["charges"] = $tax_value;

		return $return;
	}
}
