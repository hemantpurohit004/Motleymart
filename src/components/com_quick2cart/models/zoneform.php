<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

/**
 * Item Model for a zone.
 *
 * @since  2.2
 */
class Quick2cartModelZoneForm extends JModelForm
{
	/*$_item = null;*/

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_QUICK2CART';

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
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('com_quick2cart');

		// Load state from the request userState on edit or from the passed variable on default
		if (JFactory::getApplication()->input->get('layout') == 'edit')
		{
			$id = JFactory::getApplication()->getUserState('com_quick2cart.edit.zone.id');
		}
		else
		{
			$id = JFactory::getApplication()->input->get('id');
			JFactory::getApplication()->setUserState('com_quick2cart.edit.zone.id', $id);
		}

		$this->setState('zone.id', $id);

		// Load the parameters.
		$params = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('zone.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function &getData($id = null)
	{
		if (empty($this->_item) || $this->_item === null)
		{
			$this->_item = false;
			$id = JFactory::getApplication()->input->get('id');

			if (empty($id))
			{
				$id = $this->getState('zone.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table->load($id))
			{
				$user = JFactory::getUser();
				$id = $table->id;
				$canEdit = $user->authorise('core.edit', 'com_quick2cart') || $user->authorise('core.create', 'com_quick2cart');

				if (!$canEdit && $user->authorise('core.edit.own', 'com_quick2cart'))
				{
					$canEdit = $user->id == $table->created_by;
				}

				if (!$canEdit)
				{
					JError::raiseError('500', JText::_('JERROR_ALERTNOAUTHOR'));
				}

				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published)
					{
						return $this->_item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties = $table->getProperties(1);
				$this->_item = JArrayHelper::toObject($properties, 'JObject');
			}
			elseif ($error = $table->getError())
			{
				$this->setError($error);
			}
		}

		return $this->_item;
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable    A database object
	 */
	public function getTable($type = 'Zone', $prefix = 'quick2cartTable', $config = array())
	{
		$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to checkin a row.
	 *
	 * @param   integer  $id  The numeric id of the primary key.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   12.2
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('zone.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin'))
			{
				if (!$table->checkin($id))
				{
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer  $id  The numeric id of the primary key.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   12.2
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('zone.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = JFactory::getUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   2.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_quick2cart.zone', 'zoneform', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 *
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_quick2cart.edit.zone.data', array());

		if (empty($data))
		{
			$data = $this->getData();
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('zone.id');
		$state = (!empty($data['state'])) ? 1 : 0;
		$user = JFactory::getUser();

		if ($id)
		{
			// Check the user can edit this item
			$authorised = $user->authorise('core.edit.own', 'com_quick2cart');
			/*if($user->authorise('core.edit.state', 'com_quick2cart') !== true && $state == 1){ //The user cannot edit the state of the item.
				$data['state'] = 0;
			}*/
		}
		else
		{
			// Check the user can create new items in this section
			$authorised = $user->authorise('core.create', 'com_quick2cart');
			/*if($user->authorise('core.edit.state', 'com_quick2cart') !== true && $state == 1){ //The user cannot edit the state of the item.
				$data['state'] = 0;
			}*/
		}

		// $authorised = true;

		if ($authorised !== true)
		{
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$table = $this->getTable();

		if ($table->save($data) === true)
		{
			return $table->id;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed  $data  An array of record id to be deleted
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($data)
	{
		$id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('zone.id');

		if (JFactory::getUser()->authorise('core.delete', 'com_quick2cart') !== true)
		{
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$table = $this->getTable();

		if ($table->delete($data['id']) === true)
		{
			return $id;
		}
		else
		{
			return false;
		}

		return true;
	}

	/**
	 * Gives country list.
	 *
	 * @return  array
	 *
	 * @since   2.2
	 */
	public function getCountry()
	{
		require_once JPATH_SITE . '/components/com_tjfields/helpers/geo.php';
		$tjGeoHelper = TjGeoHelper::getInstance('TjGeoHelper');
		$rows = (array) $tjGeoHelper->getCountryList('com_quick2cart');

		return $rows;
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

		if ($zone_id == 0)
		{
			$zone_id = (int) $app->getUserState('com_quick2cart.edit.zone.id');
		}

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
	 * Get Zone Rule Details
	 *
	 * @param   int  $rule_id  Rule id
	 *
	 * @return  object
	 */
	public function getZoneRuleDetail($rule_id)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('zr.zonerule_id as id, c.country as country,c.id as country_id, reg.region,reg.id as region_id');
		$query->from('#__kart_zonerules AS zr');
		$query->join('LEFT', '#__tj_country AS c ON c.id=zr.country_id');
		$query->join('LEFT', '#__tj_region AS reg ON reg.id=zr.region_id');
		$query->where('zr.zonerule_id="' . $rule_id . '"');
		$query->order('zr.ordering');
		$db->setQuery((string) $query);

		return $db->loadObject();
	}

	/**
	 * Get list of regions from given country
	 *
	 * @param   int  $country_id  Country id
	 *
	 * @return  array
	 */
	public function getRegionList($country_id)
	{
		require_once JPATH_SITE . '/components/com_tjfields/helpers/geo.php';
		$tjGeoHelper = TjGeoHelper::getInstance('TjGeoHelper');
		$rows = $tjGeoHelper->getRegionList($country_id, 'com_quick2cart');

		return $rows;
	}

	/**
	 * This function save country and region/state aginst zone.
	 *
	 * @param   int  $update  Update
	 *
	 * @return   true or false.
	 *
	 * @since	2.2
	 **/
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

		if (!$ZoneRule->store($data))
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
