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
class Quick2cartControllerAttributeset extends JControllerForm
{
	/**
	 * Constructor.
	 *
	 * @since   2.5
	 */
	public function __construct()
	{
		$this->view_list = 'attributesets';
		parent::__construct();
	}

	/**
	 * Method to add attribute.
	 *
	 * @return null
	 *
	 * @since  2.5
	 */
	public function addAttribute()
	{
		$attributeSetModel = $this->getModel('attributeset');
		$attributeSetModel->addAttribute();
	}

	/**
	 * Method to remove attribute
	 *
	 * @return null
	 *
	 * @since  2.5
	 */
	public function removeAttribute()
	{
		$attributeSetModel = $this->getModel('attributeset');
		$count = $attributeSetModel->removeAttribute();

		if (!empty($count))
		{
			$message = sprintf(JText::_("COM_QUICK2CART_ATTRIBUTE_REMOVE_ERROR"), implode(',', $count));
			$c[] = array("error" => $message);
		}
		else
		{
			$c[] = array("success" => 'ok');
		}

		echo json_encode($c);

		jexit();
	}

	/**
	 * Function to save global_attribute_ids in order in table kart_global_attribute_set
	 *
	 * @param   INT  $key     key
	 *
	 * @param   INT  $urlVar  url
	 *
	 * @return  null
	 *
	 * @since 2.5
	 *
	 * */
	public function save($key = null, $urlVar = null)
	{
		$attributeSetModel = $this->getModel('attributeset');
		$input = JFactory::getApplication()->input;
		$attId = $input->get('id', '', 'int');
		$attributeData = $input->get('attributes', '', 'array');

		if (!empty($attributeData))
		{
			$result = $attributeSetModel->saveOrdering($attributeData);
		}

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_quick2cart&view=attributeset&layout=edit&id=' . $attId);

			return false;
		}

		parent::save($key = null, $urlVar = null);
	}
}
