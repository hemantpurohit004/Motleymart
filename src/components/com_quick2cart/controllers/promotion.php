<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Promotion controller class.
 *
 * @since  1.6
 */
class Quick2cartControllerPromotion extends JControllerForm
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'promotions';
		parent::__construct();
	}

	/**
	 * Function to delete promotion code
	 *
	 * @return true/false
	 *
	 * @since  2.5
	 *
	 * */
	public function qtc_delete_promotion_condition()
	{
		$promotionModel = $this->getModel('promotion');
		$input = JFactory::getApplication()->input;
		$conditionId = $input->get('cid', '', 'int');

		if (!empty($conditionId))
		{
			$result = $promotionModel->qtc_delete_promotion_condition($conditionId);

			if ($result == false)
			{
				$message = JText::_("COM_QUICK2CART_PROMOTION_CONDITION_REMOVE_ERROR");
				$status[] = array("error" => $message);
			}
			else
			{
				$status[] = array("success" => 'ok');
			}

			echo json_encode($status);
		}

		jexit();
	}

	/**
	 * Function to save promotion rule
	 *
	 * @param   INT     $key     key
	 * @param   STRING  $urlVar  url var
	 *
	 * @return true/false
	 *
	 * @since  2.8
	 *
	 * */
	public function save($key = null, $urlVar = null)
	{
		if (!(parent::save()))
		{
			$this->setMessage(JText::_("COM_QUICK2CART_PROMOTION_CONDITION_MSG"), 'error');

			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return void
	 *
	 * @since    2.9
	 */
	public function add()
	{
		$input = JFactory::getApplication()->input;
		$input->set('view', 'promotion');
		$input->set('layout', 'edit');

		parent::add();
	}
}
