<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');

if (!defined('DS'))
{
	define('DS', '/');
}

jimport('joomla.plugin.plugin');

$lang = JFactory::getLanguage();
$lang->load('plg_content_content_quick2cart', JPATH_ADMINISTRATOR);

$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

if (! class_exists('comquick2cartHelper'))
{
	// Require_once $path;
	JLoader::register('comquick2cartHelper', $path);
	JLoader::load('comquick2cartHelper');
}

/**
 * Content plugin for Quick2cart Product option for article type product.
 *
 * @package     Com_Quick2cart
 *
 * @subpackage  site
 *
 * @since       2.2
 */
class PlgContentcontent_Quick2cart extends JPlugin
{
	/**
	 * [__construct description]
	 *
	 * @param   [type]  &$subject  [subject]
	 * @param   [type]  $config    [config]
	 */
	public function __construct (&$subject, $config)
	{
		parent::__construct($subject, $config);

		if ($this->params === false)
		{
			$this->_plugin = JPluginHelper::getPlugin('content', 'content_quick2cart');
			$this->params = new JParameter($jPlugin->params);
		}
	}

	/**
	 * This trigger add Quick2cart's product detail .
	 */

	/**
	 * This is a request for information that should be placed immediately after the generated content for more detail visit - https://docs.joomla.org/Plugin/Events/Content#onContentAfterDisplay
	 *
	 * @param   string  $context  The context of the content being passed to the plugin - this is the component name and view - or name of module (e.g. com_content.article). Use this to check whether you are in the desired context for the plugin.
	 * @param   string  $article  The article that is being rendered by the view
	 * @param   string  $params   A JRegistry object of merged article and menu item params.
	 *
	 * @since   2.2
	 *
	 * @return   null
	 */
	public function onContentAfterDisplay ($context, $article, $params)
	{
		$btn_pos_method = $this->params->get('btn_pos_method');

		if ($btn_pos_method == 'normal')
		{
			$clientdetails = explode(".", $context);
			$client = $clientdetails[0];

			if ($client != "com_content")
			{
				return;
			}

			jimport('joomla.filesystem.file');

			if (JFile::exists(JPATH_SITE . '/components/com_quick2cart/quick2cart.php'))
			{
				$mainframe = JFactory::getApplication();
				$lang = JFactory::getLanguage();
				$lang->load('com_quick2cart');
				$comquick2cartHelper = new comquick2cartHelper;
				$output = $comquick2cartHelper->getBuynow($article->id, "com_content", array());

				return $output;
			}
		}
	}

	/**
	 * Trigger the onBeforeRender event. Add tab to article form in frontend
	 *
	 * @return   null
	 */
	public function onBeforeRender ()
	{
	}

	/**
	 * J1.7
	 * This is an event that is called after the content is saved into the database. Even though article object is passed by reference, changes will not be saved since storing data into database phase is past. An example use case would be redirecting user to the appropriate place after saving.
	 *
	 * @param   string  $context  The context of the content being passed to the plugin - this is the component name and view - or name of module (e.g. com_content.article). Use this to check whether you are in the desired context for the plugin.
	 * @param   string  $article  A reference to the JTableContent object that is being saved which holds the article data
	 * @param   string  $isNew    A boolean which is set to true if the content is about to be created.
	 *
	 * @return  mixed  The escaped value.
	 *
	 * @since   1.0
	 */
	function onContentAfterSave ($context, $article, $isNew)
	{
		$jinput = JFactory::getApplication()->input;
		$post_data = $jinput->post;
		$client = $post_data->get('client', 'com_content');

		// On after onAfterK2Save and flexi item save this function is runs. And client,pid is overriding and two entry place in kart_item table
		if ($client != 'com_content')
		{
			return;
		}

		$pid = $article->id;
		$client = $jinput->post->set('client', 'com_content');
		$pid = $jinput->post->set('pid', $pid);
		$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

		if (class_exists('comquick2cartHelper'))
		{
			JLoader::register('comquick2cartHelper', $path);
			JLoader::load('comquick2cartHelper');
		}

		$comquick2cartHelper = new comquick2cartHelper;
		$comquick2cartHelper = $comquick2cartHelper->saveProduct($post_data);
	}

	/**
	 * J1.7
	 * Called before a JForm is rendered. For more detail visit - https://docs.joomla.org/Plugin/Events/Content#onContentAfterDisplay
	 *
	 * @param   object  $form  The JForm object to be displayed
	 * @param   object  $data  An object containing the data for the form.
	 *
	 * @return  True if method succeeds.
	 *
	 * @since   12.2
	 */
	function onContentPrepareForm ($form, $data)
	{
		$input = JFactory::getApplication()->input;

		$option = $input->get('option', '');

		if ($option != 'com_content')
		{
			return;
		}

		// Add Language file.
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);

		$isAdmin = JFactory::getApplication()->isAdmin();
		$document = JFactory::getDocument();

		if (! $isAdmin)
		{
			if (JVERSION > '3.0')
			{
				$document->addScriptDeclaration(
						'
				(function($){
					$(document).ready(function(){
						var tab = $(\'<li class=""><a href="#attrib-qtc" data-toggle="tab">' . JText::_('PLG_QTC_SLIDER_LABEL') . '</a></li>\');
						$(\'ul.nav-tabs\').append(tab);
						$(\'#attrib-qtc\').appendTo($(\'div.tab-content\', \'#adminForm\'));
						});
					})(jQuery);
			');
			}
			else
			{
				$document->addScriptDeclaration(
						'
                (function($){
                    $(document).ready(function(){
		            	var tab = $(\'<fieldset><legend>' . JText::_('PLG_QTC_SLIDER_LABEL') . '</legend>\');
		            	$(\'#adminForm\').append(tab);
		            	$(\'#attrib-qtc\').appendTo(tab);
		            });
		        })(jQuery);
		    ');
			}
		}

		if (! ($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		jimport('joomla.filesystem.file');

		if (! JFile::exists(JPATH_SITE . '/components/com_quick2cart/quick2cart.php'))
		{
			return true;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();

		if (isset($data->title))
		{
			JRequest::setVar("qtc_article_name", $data->title);
		}

		if (!in_array($name, array('com_content.article','com_categories.categorycom_content')))
		{
			return true;
		}

		$doc = JFactory::getDocument();
		$doc->addStyleSheet(JUri::base() . 'components/com_quick2cart/assets/css/quick2cart.css');

		// Add the registration fields to the form.
		JForm::addFormPath(dirname(__FILE__) . '/content_quick2cart');
		JForm::addFieldPath(dirname(__FILE__) . '/content_quick2cart/fields');
		$form->loadFile('quick2cart', false);

		$isAdmin = JFactory::getApplication()->isAdmin();

		if (! $isAdmin)
		{
			$jinput = JFactory::getApplication()->input;

			require_once dirname(__FILE__) . '/content_quick2cart/fields/quick2cart.php';
			$fromfields = new JFormFieldQuick2cart;
			$form_data = $fromfields->getInput();

				$html = '<div class="tab-pane" id="attrib-qtc">' . $form_data . '</div>';

			echo $html;
		}

		$articleId = isset($data->id) ? $data->id : 0;

		return true;
	}

	/**
	 * [onContentAfterDelete description]
	 *
	 * @param   [type]  $context  [description]
	 * @param   [type]  $data     [description]
	 *
	 * @return  [type]            [description]
	 */
	function onContentAfterDelete ($context, $data)
	{
		/*$articleId = isset($data->id) ? $data->id : 0;

		if ($articleId)
		{
			$db = JFactory::getDbo();
			$db->setQuery("DELETE FROM #__kart_items WHERE product_id = $articleId AND  parent = 'com_content'");
			if (! $db->execute())
			{
				$messagetype = 'notice';
				$message = JText::_('QTC_PARAMS_DEL_FAIL') . " - " . $db->stderr();
			}
		}
		*/

		return true;
	}

	/**
	 * J1.7
	 * Plugin that loads module positions within content
	 *
	 * @param
	 *        	string	The context of the content being passed to the plugin.
	 * @param
	 *        	object	The article object. Note $article->text is also
	 *        	available
	 * @param
	 *        	object	The article params
	 * @param
	 *        	int		The 'page' number
	 */

	/**
	 * [onContentPrepare description]
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  object	The article object. Note $article->text is also available
	 * @param   object   &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  [type]              [description]
	 */
	public function onContentPrepare ($context, &$article, &$params, $page = 0)
	{
		$clientdetails = explode(".", $context);
		$client = $clientdetails[0];

		if ($client != "com_content")
		{
			return;
		}

		$mainframe = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart');
		$btn_pos_method = $this->params->get('btn_pos_method');

		if ($btn_pos_method == 'data_tag')
		{
			jimport('joomla.filesystem.file');

			if (! class_exists('JFolder'))
			{
				jimport('joomla.filesystem.folder');
			}

			if (JFolder::exists(JPATH_ROOT . '/components/com_quick2cart'))
			{
				// Don't run this plugin when the content is being indexed
				if ($context == 'com_finder.indexer')
				{
					return true;
				}

				// Simple performance check to determine whether bot should
				// Process further
				if (strpos($article->text, 'loadquick2cart') === false)
				{
					return true;
				}

				// Expression to search for (datatag) eg. {loadquick2cart id=3}
				// $regex = '/{loadquick2cart/i';
				$regex = '/{loadquick2cart\s+(.*?)}/i';

				/* Find all instances of plugin and put in $matches for
				 loadposition
				 $matches[0] is full pattern match, $matches[1] is the
				 position
				 */
				preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

				// No matches, skip this
				if ($matches)
				{
					foreach ($matches as $match)
					{
						$vars = new stdClass;

						$pcs = explode('=', $match[1]);
						$vars->product_id = $pcs[1];
						$comquick2cartHelper = new comquick2cartHelper;
						$output = $comquick2cartHelper->getBuynow($vars->product_id, "com_content");

						/* We should replace only first occurrence in order to
						 allow positions with the same name to regenerate
						 their content:
						 */
						$article->text = preg_replace("{" . $match[0] . "}", addcslashes($output, '\\$'), $article->text, 1);
					}
				}
			}
		}

		// End data_tag if

		return true;
	}

	/**
	 * [qtc_explode description]
	 *
	 * @param   [type]  $match  [description]
	 * @param   [type]  $body   [description]
	 *
	 * @return  [type]          [description]
	 */
	function qtc_explode ($match, $body)
	{
		$data = explode($match, $body);

		return $data[1];
	}
}
