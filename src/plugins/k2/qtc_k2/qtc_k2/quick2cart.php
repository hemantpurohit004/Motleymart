<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_k2/elements/base.php';

/**
 * Form field for Quick2cart
 *
 * @package     Com_Quick2cart
 *
 * @subpackage  site
 *
 * @since       2.2
 */
class K2ElementQuick2cart extends K2Element
{
	/**
	 * [fetchElement description]
	 *
	 * @param   [type]  $name          [description]
	 * @param   [type]  $value         [description]
	 * @param   [type]  $node          [description]
	 * @param   [type]  $control_name  [description]
	 *
	 * @return  [type]                 [description]
	 */
	function fetchElement ($name, $value, $node, $control_name)
	{
		$input = JFactory::getApplication()->input;
		$option = $input->get('option', '');

		if ($option != 'com_k2')
		{
			return;
		}

		jimport('joomla.filesystem.file');

		if (! JFile::exists(JPATH_SITE . '/components/com_quick2cart/quick2cart.php'))
		{
			return true;
		}

		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);
		JHtml::_('behavior.modal', 'a.modal');
		$html = '';
		$client = "com_k2";
		$pid = JRequest::getInt('cid');

		/* prefill k2 title */
		$db = JFactory::getDBO();
		$q = "SELECT `title` FROM `#__k2_items` WHERE `id` =" . (int) $pid;
		$db->setQuery($q);
		$k2item = $db->loadResult();
		$jinput = JFactory::getApplication()->input;
		$jinput->set('qtc_article_name', $k2item);
		/* prefill k2 title */

		if (!class_exists('comquick2cartHelper'))
		{
			// Require_once $path;
			$path = JPATH_SITE . '/components/com_quick2cart/helper.php';
			JLoader::register('comquick2cartHelper', $path);
			JLoader::load('comquick2cartHelper');
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$isAdmin = JFactory::getApplication()->isAdmin();

		if ($isAdmin)
		{
			$path = $comquick2cartHelper->getViewpath('attributes', '', 'JPATH_ADMINISTRATOR', 'JPATH_ADMINISTRATOR');
		}
		else
		{
			$path = $comquick2cartHelper->getViewpath('attributes', '', 'SITE', 'SITE');
		}

		ob_start();
		include $path;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
}

/**
 * Form field for Quick2cart
 *
 * @package     Com_Quick2cart
 *
 * @subpackage  site
 *
 * @since       2.2
 */
class JFormFieldQuick2cart extends K2ElementQuick2cart
{
	var $type = 'Quick2cart';
}

/**
 * Form field for Quick2cart
 *
 * @package     Com_Quick2cart
 *
 * @subpackage  site
 *
 * @since       2.2
 */
class JElementQuick2cart extends K2ElementQuick2cart
{
	var $_name = 'Quick2cart';
}
