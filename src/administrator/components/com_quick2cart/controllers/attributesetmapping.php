<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Attributeset controller class.
 *
 * @since  2.5
 */
class Quick2cartControllerAttributeSetMapping extends JControllerForm
{
	/**
	 * Method to save mapping
	 *
	 * @param   STRING  $key     key
	 *
	 * @param   STRING  $urlVar  url var
	 *
	 * @return null
	 *
	 * @since  2.5
	 */
	public function save($key = null, $urlVar = null)
	{
		$input = JFactory::getApplication()->input;
		$task = $input->get('task', '', 'string');
		$this->model = $this->getModel('attributesetmapping');
		$this->model->save();

		if ($task == 'apply')
		{
			$this->setRedirect(JUri::base() . "?option=com_quick2cart&view=attributesetmapping");
		}
		else
		{
			$this->setRedirect(JUri::base() . "?option=com_quick2cart");
		}
	}

	/**
	 * Method to cancel changes
	 *
	 * @param   STRING  $key  key
	 *
	 * @return null
	 *
	 * @since  2.5
	 */
	public function cancel($key = null)
	{
		$this->setRedirect(JUri::base() . "?option=com_quick2cart");
	}
}
