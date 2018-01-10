<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.controllerform');

/**
 * Zone form controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerZone extends JControllerForm
{
	/**
	 * Class constructor.
	 *
	 * @since   1.6
	 */
	public function __construct()
	{
		$this->view_list = 'zones';
		parent::__construct();
	}

	/**
	 * This function give state/region select box.
	 *
	 * @return  null
	 *
	 * @since	2.2
	 */
	public function getStateSelectList()
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');
		$country_id = isset($data['country_id']) ? $data['country_id'] : 0;
		$default_option = $data['default_option'];
		$field_name = $data['field_name'];
		$field_id = $data['field_id'];

		// Based on the country, get state and generate a select box
		if (!empty($country_id))
		{
			$model = $this->getModel();
			$stateList = $model->getRegionList($country_id);

			$options = array();
			$options[] = JHtml::_('select.option', 0, JTEXT::_('COM_QUICK2CART_ZONE_ALL_STATES'));

			if ($stateList)
			{
				foreach ($stateList as $state)
				{
					// This is only to generate the <option> tag inside select tag
					$options[] = JHtml::_('select.option', $state['id'], $state['region']);
				}
			}

			// Now generate the select list and echo that
			$stateList = JHtml::_(
			'select.genericlist', $options, $field_name, ' class="qtc_regionListTopMargin"', 'value', 'text', $default_option, $field_id
			);
			echo $stateList;
		}

		$app->close();
	}

	/**
	 * This function add country/region in perticular zone.
	 *
	 * @return  null
	 *
	 * @since	2.2
	 */
	public function addZoneRule()
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');

		$model = $this->getModel();

		$response = array();
		$response['error'] = 0;

		if (!$model->saveZoneRule())
		{
			$response['error'] = 1;
			$response['errorMessage'] = $model->getError();
		}

		if ($response['error'] == 0)
		{
			$response['zonerule_id'] = $app->input->get('zonerule_id');
		}

		echo json_encode($response);

		$app->close();
	}

	/**
	 * This function Update country/region in perticular zone.
	 *
	 * @return  null
	 *
	 * @since	2.2
	 */
	public function updateZoneRule()
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');
		$model = $this->getModel();

		$response = array();
		$response['error'] = 0;

		if (!$model->saveZoneRule(1))
		{
			$response['error'] = 1;
			$response['errorMessage'] = $model->getError();
		}

		if ($response['error'] == 0)
		{
			$response['zonerule_id'] = $app->input->get('zonerule_id');
		}

		echo json_encode($response);
		$app->close();
	}

	/**
	 * This function deletes the rule form perticular zone.
	 *
	 * @return  null
	 *
	 * @since	2.2
	 */
	public function deleteZoneRule()
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');

		$model = $this->getModel();
		$zoneRuleTable = $model->getTable('Zonerule');

		$response = array();
		$response['error'] = 0;

		if (!$zoneRuleTable->delete(array($data['zonerule_id'])))
		{
			$response['error'] = 1;
			$response['errorMessage'] = $zoneRuleTable->getError();
		}

		echo json_encode($response);
		$app->close();
	}
}
