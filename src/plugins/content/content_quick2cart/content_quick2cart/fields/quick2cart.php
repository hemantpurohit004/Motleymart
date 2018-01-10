<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();

// Import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form field for Quick2cart
 *
 * @package     Com_Quick2cart
 *
 * @subpackage  site
 *
 * @since       2.2
 */
class JFormFieldQuick2cart extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var string
	 */
	protected $type = 'Quick2cart';

	/**
	 * [getInput description]
	 *
	 * @return  [type]  [description]
	 */
	public function getInput()
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);

		$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

		if (! class_exists('comquick2cartHelper'))
		{
			// Require_once $path;
			JLoader::register('comquick2cartHelper', $path);
			JLoader::load('comquick2cartHelper');
		}

		$comquick2cartHelper = new comquick2cartHelper;

		$fieldName = $this->fieldname;
		JHtml::_('behavior.modal', 'a.modal');
		$html = '';
		$client = "com_content";
		$jinput = JFactory::getApplication()->input;

		// $pid=$jinput->get('id');
		$isAdmin = JFactory::getApplication()->isAdmin();

		if (! $isAdmin)
		{
			$pid = $jinput->get('a_id');
		}
		else
		{
			$pid = $jinput->get('id');
		}

		// CHECK for view override

		// For admin, no need of bs-3 layout. Check override in admin template if not present then take from site->com_quick2cart->layout
		if ($isAdmin)
		{
			$path = $comquick2cartHelper->getViewpath('attributes', '', 'JPATH_ADMINISTRATOR', 'JPATH_ADMINISTRATOR');
		}
		else
		{
			$path = $comquick2cartHelper->getViewpath('attributes', '', 'SITE', 'SITE');
		}

		// Load component models
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_quick2cart/models');

		$Quick2cartModelWeights = JModelLegacy::getInstance('Weights', 'Quick2cartModel');
		$Quick2cartModelLengths = JModelLegacy::getInstance('Lengths', 'Quick2cartModel');

		$this->weightClasses = $Quick2cartModelWeights->getItems();
		$this->lengthClasses = $Quick2cartModelLengths->getItems();

		ob_start();
		include $path;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
}
