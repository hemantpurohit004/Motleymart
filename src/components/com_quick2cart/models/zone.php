<?php
/**
 * @package    Quick2Cart
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */

// No direct access.
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Zone Model.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelZone extends JModelAdmin
{
	protected $text_prefix = 'COM_QUICK2CART';

	/**
	 * Render view.
	 *
	 * @param   string  $type    An optional associative array of configuration settings.
	 * @param   string  $prefix  An optional associative array of configuration settings.
	 * @param   array   $config  An optional associative array of configuration settings.
	 *
	 * @since   2.2
	 * @return   null
	 */

	public function getTable($type = 'Zone', $prefix = 'Quick2cartTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_quick2cart.zone', 'zone', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the record form.
	 *
	 * @since   2.2
	 * @return   null
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_quick2cart.edit.zone.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $pk  Private key.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Do any procesing on fields here if needed
		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   object  $table  Table object.
	 *
	 * @since   2.2
	 * @return   null
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '')
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__kart_zone');
				$max = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Gives country list.
	 *
	 * @since   2.2
	 * @return   countryList
	 */
	public function getCountry()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("`id` AS country_id ,  `country`")
		->from('#__tj_country')
		->where('com_quick2cart=1');
		$db->setQuery((string) $query);

		return $db->loadAssocList();
	}

	/**
	 * Gives zone rules list.
	 *
	 * @since   2.2
	 * @return   rulelist.
	 */
	public function getZoneRules ()
	{
		$app = JFactory::getApplication();
		$zone_id = $app->input->get('id', 0);

		if (!empty($zone_id))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('zr.zonerule_id as id, c.country as country, reg.region,reg.id AS region_id ');
			$query->from('#__kart_zonerules AS zr');
			$query->join('LEFT', '#__tj_country AS c ON c.id=zr.country_id');
			$query->join('LEFT', '#__tj_region AS reg ON reg.id=zr.region_id');
			$query->where('zr.zone_id=' . $zone_id);
			$query->order('zr.ordering');
			$db->setQuery((string) $query);

			return $db->loadObjectList();
		}
	}

	/**
	 * Function getZoneRuleDetail.
	 *
	 * @param   Integer  $rule_id  Rule_id
	 *
	 * @return  String
	 *
	 * @since  1.0.0
	 */
	public function getZoneRuleDetail($rule_id)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('zr.zonerule_id as id, c.country as country,c.country_id, reg.region,reg.region_id');
		$query->from('#__kart_zonerules AS zr');
		$query->join('LEFT', '#__tj_country AS c ON c.country_id=zr.country_id');
		$query->join('LEFT', '#__tj_region AS reg ON reg.region_id=zr.region_id');
		$query->where('zr.zonerule_id=' . $rule_id);
		$query->order('zr.ordering');
		$db->setQuery((string) $query);

		return $db->loadObject();
	}

	/**
	 * Function getRegionList.
	 *
	 * @param   Integer  $country_id  Country_id
	 *
	 * @return  String
	 *
	 * @since  1.0.0
	 */
	public function getRegionList($country_id)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id as region_id, region');
		$query->from('#__tj_region');
		$query->where('country_id=' . $db->quote($country_id));
		$query->where('com_quick2cart=1');
		$query->order('region ASC');
		$db->setQuery((string) $query);

		return $db->loadObjectList();
	}

	/**
	 * This function save country and region/state aginst zone.
	 *
	 * @param   Integer  $update  Update
	 *
	 * @return  String
	 *
	 * @since  1.0.0
	 */
	public function saveZoneRule($update = 0)
	{
		jimport('joomla.database.table');
		$app = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');

		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__kart_zonerules AS gr');

		if ($update === 1)
		{
			// Getting zone id from rule id.
			$zone_id = $this->getZoneId($data['zonerule_id']);
			$data['zone_id'] = $zone_id;
		}

		$query->where('gr.zone_id=' . $db->escape($data['zone_id']));
		$query->where('gr.country_id=' . $db->quote($data['country_id']));
		$query->where('gr.region_id=' . $db->escape($data['region_id']));
		$db->setQuery($query);
		$result = $db->loadResult();

		if ($result == 1)
		{
			$this->setError(JText::_('COM_QUICK2CART_ZONERULE_ALREADY_EXISTS'));

			return false;
		}

		$ZoneRule = $this->getTable('Zonerule');

		if (!$ZoneRule->bind($data))
		{
			$this->setError($ZoneRule->getError());

			return false;
		}

		if (!$ZoneRule->check())
		{
			$this->setError($ZoneRule->getError());

			return false;
		}

		if (!$ZoneRule->store())
		{
			$this->setError($ZoneRule->getError());

			return false;
		}

		$app->input->set('zonerule_id', $ZoneRule->zonerule_id);

		return true;
	}

	/**
	 * This function save country and region/state aginst zone.
	 *
	 * @param   object  $ruleId  zone rule id.
	 *
	 * @since	2.2
	 * @return   true or false.
	 */
	public function getZoneId($ruleId)
	{
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('zone_id');
		$query->from('#__kart_zonerules AS zr');
		$query->where('zr.zonerule_id=' . $db->escape($ruleId));
		$db->setQuery($query);

		return $db->loadResult();
	}
}
