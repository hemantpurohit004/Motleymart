<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();
jimport('joomla.plugin.plugin');

if (!defined('DS'))
{
	define('DS', '/');
}

/**
 * System plugin for zoo integration.
 *
 * @package     Plgshare_For_Discounts
 * @subpackage  site
 * @since       1.0
 */
class PlgSystemQtc_Zoo extends JPlugin
{
	/**
	 * onAfterInitialise handler
	 *
	 * Adds ZOO event listeners
	 *
	 * @access public
	 * @return null
	 */
	public function onAfterInitialise ()
	{
		// Make sure ZOO exists
		jimport('joomla.filesystem.file');

		if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php'))
		{
			return;
		}

		// Load ZOO config
		if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php') )
		{
			if (!JComponentHelper::isEnabled('com_zoo', true))
			{
				return;
			}
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';

		// Make sure App class exists
		if (! class_exists('App'))
		{
			return;
		}

		// Get the ZOO App instance
		$zoo = App::getInstance('zoo');
		$zoo->path->register(JPATH_SITE . '/plugins/system/qtc_zoo/qtc_zoo/', 'fields');
		$zoo->event->register('plgSystemQtc_zoo');
		$zoo->event->dispatcher->connect('application:configparams', array('plgSystemQtc_zoo', 'configParams'));

		$zoo->event->register('ItemEvent');
		$zoo->event->dispatcher->connect('item:init', array('plgSystemQtc_zoo',	'init'));

		$zoo->event->dispatcher->connect('item:saved', array('plgSystemQtc_zoo', 'saved'));
	}

	/**
	 * [Placeholder for the configParams event]
	 *
	 * @param   [type]  $event  [The event triggered]
	 *
	 * @return  [type]          [description]
	 */
	public static function configParams ($event)
	{
		jimport('joomla.filesystem.file');

		if (! JFile::exists(JPATH_SITE . '/components/com_quick2cart/quick2cart.php'))
		{
			return true;
		}

		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);

		$application = $event->getSubject();

		// Set events ReturnValue after modifying $params
		$params = $event->getReturnValue();

		$params[] = '
		 <application>
			<params group="item-config">
				<param name="qtc_params" type="quick2cart" label="' . JText::_('QTC_OPTS') .
					'" description="' . JText::_('QTC_OPTS_DESC') . '" />
			</params>
		 </application>';
	}

	/**
	 * [init description]
	 *
	 * @param   [type]  $event  [description]
	 *
	 * @return  [type]          [description]
	 */
	public static function init ($event)
	{
		$subject = $event->getSubject();

		JRequest::setVar("qtc_article_name", $subject->name);
		$jinput = JFactory::getApplication()->input;
		$name = $jinput->get('qtc_article_name');
		if (empty($name))
			$jinput->set('qtc_article_name', $subject->name);
	}

	/**
	 * [saved description]
	 *
	 * @param   [type]  $event  [description]
	 *
	 * @return  [type]          [description]
	 */
	public static function saved ($event)
	{
		$app = JFactory::getApplication();

		// Getting zoo item id
		$item = $event->getSubject();
		$pid = $item->id;

		$path = JPATH_SITE . DS . 'components' . DS . 'com_quick2cart' . DS . 'helper.php';

		if (! class_exists('comquick2cartHelper'))
		{
			JLoader::register('comquick2cartHelper', $path);
			JLoader::load('comquick2cartHelper');
		}

		$input = JFactory::getApplication()->input;
		$post_data = $input->post;

		// Getting store id
		$store_id = $input->get('store_id', '0');

		if (! $pid || empty($store_id))
		{
			return;
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$client = $post_data->set('client', 'com_zoo');
		$pid = $post_data->set('pid', $pid);
		$comquick2cartHelper = $comquick2cartHelper->saveProduct($post_data);
	}
}
