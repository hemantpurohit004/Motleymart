<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

jimport('joomla.application.component.controllerform');

/**
 * Taxprofile form controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerTaxprofile extends JControllerForm
{
	/**
	 * Class constructor.
	 *
	 * @since   1.6
	 */
	public function __construct()
	{
		$this->view_list = 'taxprofiles';
		parent::__construct();
	}

	/**
	 * Method to add tax rule against tax profile.
	 *
	 * @since   2.2
	 * @return   null Json response object.
	 */
	public function addTaxRule()
	{
		$app = JFactory::getApplication();
		$model = $this->getModel();

		$response = array();
		$response['error'] = 0;

		if (!$model->saveTaxRule())
		{
			$response['error'] = 1;
			$response['errorMessage'] = $model->getError();
		}

		if ($response['error'] == 0)
		{
			$response['taxrule_id'] = $app->input->get('taxrule_id');
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * This function delete the tax rule form perticular profile.
	 *
	 * @since	2.2
	 *
	 * @return null
	 */
	public function deleteProfileRule()
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');

		$model = $this->getModel();
		$ruleTable = $model->getTable('Taxrules');
		$response = array();
		$response['error'] = 0;

		if (!$ruleTable->delete(array($data['taxrule_id'])))
		{
			$response['error'] = 1;
			$response['errorMessage'] = $ruleTable->getError();
		}

		echo json_encode($response);
		$app->close();
	}

	/**
	 * This function Update tax rule associated with taxprofile.
	 *
	 * @since	2.2
	 *
	 * @return null
	 */
	public function updateTaxRule()
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');
		$model = $this->getModel();

		$response = array();
		$response['error'] = 0;

		if (!$model->saveTaxRule(1))
		{
			$response['error'] = 1;
			$response['errorMessage'] = $model->getError();
		}

		if ($response['error'] == 0)
		{
			$response['taxrule_id'] = $app->input->get('taxrule_id');
		}

		echo json_encode($response);
		$app->close();
	}
}
