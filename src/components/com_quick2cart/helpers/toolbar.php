<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

if (JVERSION < '3.0')
{
	define('TJTOOLBAR_ICON_ADDNEW', " icon-plus-sign icon-white");
	define('TJTOOLBAR_ICON_EDIT', " icon-edit icon-white");
	define('TJTOOLBAR_ICON_DELETE', " icon-trash icon-white");
	define('TJTOOLBAR_ICON_PUBLISH', " icon-ok-sign icon-white");
	define('TJTOOLBAR_ICON_UNPUBLISH', " icon-minus-sign icon-white");
}
else
{
	define('TJTOOLBAR_ICON_ADDNEW', " icon-plus-2");
	define('TJTOOLBAR_ICON_EDIT', " icon-apply");
	define('TJTOOLBAR_ICON_DELETE', " icon-trash");
	define('TJTOOLBAR_ICON_PUBLISH', " icon-checkmark");
	define('TJTOOLBAR_ICON_UNPUBLISH', " icon-unpublish");
}

/**
 * ToolBar handler
 *
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 * @since       1.5
 */
class TJToolbar
{
	/**
	 * Toolbar name
	 *
	 * @var    string
	 */
	protected $name = array();

	/**
	 * Toolbar cssClass
	 *
	 * @var    string
	 */
	protected $cssClass = array();

	/**
	 * Toolbar array
	 *
	 * @var    array
	 */
	protected $bar = array();

	/**
	 * Loaded buttons
	 *
	 * @var    array
	 */
	protected $buttons = array();

	/**
	 * Directories, where button types can be stored.
	 *
	 * @var    array
	 */
	protected $buttonPath = array();

	/**
	 * Stores the singleton instances of various toolbar.
	 *
	 * @var    TJToolbar
	 * @since  2.5
	 */
	protected static $instances = array();

	/**
	 * Constructor
	 *
	 * @param   string  $name      The toolbar name.
	 * @param   string  $cssClass  The name of the toolbar.
	 *
	 * @since   1.5
	 */
	public function __construct($name = 'toolbar', $cssClass = '')
	{
		$this->name = $name;
		$this->cssClass = $cssClass;

		// Set base path to find buttons.
		$this->buttonPath[] = __DIR__ . '/button';
	}

	/**
	 * Returns the global TJToolbar object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param   string  $name      The name of the toolbar.
	 * @param   string  $cssClass  The name of the toolbar.
	 *
	 * @return  TJToolbar  The TJToolbar object.
	 *
	 * @since   1.5
	 */
	public static function getInstance($name = 'tjtoolbar', $cssClass = '')
	{
		if (empty(self::$instances[$name]))
		{
			self::$instances[$name] = new TJToolbar($name, $cssClass);
		}

		return self::$instances[$name];
	}

	/**
	 * Set a value
	 *
	 * @return  string  The set value.
	 *
	 * @since   1.5
	 */
	public function appendButton()
	{
		// Push button onto the end of the toolbar array.
		$btn = func_get_args();
		array_push($this->bar, $btn);

		return true;
	}

	/**
	 * Get a value.
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public function prependButton()
	{
		// Insert button into the front of the toolbar array.
		$btn = func_get_args();
		array_unshift($this->bar, $btn);

		return true;
	}

	/**
	 * Render a tool bar.
	 *
	 * @return  string  HTML for the toolbar.
	 *
	 * @since   1.5
	 */
	public function render()
	{
		$html = array();

		// Start toolbar div.
		// $layout = new JLayoutFile('joomla.toolbar.containeropen');

		// $html[] = $layout->render(array('id' => $this->name));

		$html[] = '
		<style>
			.btn-toolbar .btn-wrapper {
				display: inline-block;
				margin: 0 0 5px 5px;
			}
		</style>';

		$html[] = '<div class="row-fluid">';
			$html[] = '<div class="span12">';
				$html[] = '<div class="btn-toolbar ' . $this->cssClass . '" id="' . $this->name . '">';

					// Render each button in the toolbar.
					foreach ($this->bar as $button)
					{
						$html[] = $this->renderButton($button);
					}

					// End toolbar div.
					// $layout = new JLayoutFile('joomla.toolbar.containerclose');

					// $html[] = $layout->render(array());

				$html[] = '</div>';
			$html[] = '</div>';
		$html[] = '</div>';

		return implode('', $html);
	}

	/**
	 * Render a button.
	 *
	 * @param   object  &$node  A toolbar node.
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public function renderButton(&$node)
	{
		$task     = $node[0];
		$text     = $node[1];
		$class    = $node[2];
		$btnClass = $node[3];

		// Add button 'onclick' Javascript
		$spiltTask = explode('.', $task);

		switch ($spiltTask[1])
		{
			default:
			case 'addNew':
				$task = "Joomla.submitbutton('" . $task . "')";

				if (empty($class))
				{
					$class = TJTOOLBAR_ICON_ADDNEW;
				}
			break;

			case 'edit':
				$task = "if (document.adminForm.boxchecked.value==0){alert('" .
				JText::_('TJTOOLBAR_NO_SELECT_MSG') . "'); } else{Joomla.submitbutton('" . $task . "')}";

				if (empty($class))
				{
					$class = TJTOOLBAR_ICON_EDIT;
				}
			break;

			case 'publish':
				$task = "if (document.adminForm.boxchecked.value==0) { alert('" .
				JText::_('TJTOOLBAR_NO_SELECT_MSG') . "'); } else { Joomla.submitbutton('" . $task . "') }";

				if (empty($class))
				{
					$class = TJTOOLBAR_ICON_PUBLISH;
				}
			break;

			case 'unpublish':
				$task = "if (document.adminForm.boxchecked.value==0) { alert('" .
				JText::_('TJTOOLBAR_NO_SELECT_MSG') . "'); } else { Joomla.submitbutton('" . $task . "') }";

				if (empty($class))
				{
					$class = TJTOOLBAR_ICON_UNPUBLISH;
				}
			break;

			case 'delete':
				$task = "if (document.adminForm.boxchecked.value==0) { alert('" .
				JText::_('TJTOOLBAR_NO_SELECT_MSG') . "'); } else { Joomla.submitbutton('" . $task . "')}";

				if (empty($class))
				{
					$class = TJTOOLBAR_ICON_DELETE;
				}
			break;
		}

		// Apply JText
		$text = JText::_($text);

		// Generate button HTML
		$btnHtml = ' <div class="btn-wrapper" id="tjtoolbar-' . $spiltTask[1] . '"> <button type="button" onclick="' . $task .
		'" class="' . $btnClass . '"> <span class="' . trim($class) . '"></span> ' . $text . ' </button> </div>';

		return $btnHtml;
	}
}
