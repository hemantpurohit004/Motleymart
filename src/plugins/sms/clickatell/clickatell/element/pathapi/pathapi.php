<?php
/**
 * @version    SVN: <svn_id>
 * @package    Techjoomla_API
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

jimport("joomla.html.parameter.element");
jimport('joomla.html.html');
jimport('joomla.form.formfield');

$lang = JFactory::getLanguage();
$lang->load('plug_clickatell', JPATH_ADMINISTRATOR);

if (JVERSION >= 1.6)
{
		/**
		 * JFormFieldPathapi
		 *
		 * @since  1.0.1
		 */
		class JFormFieldPathapi extends JFormField
				{
					public $type = 'Pathapi';

					/**
					 * Method to get the field input markup.
					 *
					 * TODO: Add access check.
					 *
					 * @return  string  The field input markup.
					 *
					 * @since  1.6
					 */
					protected function getInput()
					{
						if ($this->id == 'jform_params_pathapi_clickatell')
						{
							$return = '<div class="instructions">
									Go to <a href="http://techjoomla.com/documentation-for-invitex/configuring-clickatell-api-plugin.html" target="_blank">
									How to configure Techjoomla-Click-a-tell API
									</a><br />
									</div>';

							return $return;
						}
					}
		}
}
else
{
	/**
	 * JElementPathapi
	 *
	 * @since  1.0.1
	 */
	class JElementPathapi extends JElement
				{
					protected $file_name = 'pathapi';

					/**
					 * Function fetchElement
					 *
					 * @param   String   $name          USER
					 * @param   Integer  $value         Password
					 * @param   Integer  &$node         API key
					 * @param   String   $control_name  TEXT in the SMS
					 *
					 * @return  void
					 *
					 * @since  1.0
					 */
					public function fetchElement($name, $value, &$node, $control_name)
					{
						if ($name == 'pathapi_clickatell')
						{
							$return = '<div class="instructions">
									Go to <a href="http://techjoomla.com/documentation-for-invitex/configuring-clickatell-api-plugin.html" target="_blank">
									How to configure Techjoomla-Click-a-tell API
									</a><br />
									</div>';

							return $return;
						}
					}
	}
}
