<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.event.dispatcher');
jimport('joomla.application.component.modeladmin');

/**
 * Shipprofile Model.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelShipprofile extends JModelAdmin
{
	// Changed by Deepa
	/*protected $_item = null;*/
	protected $item = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 *
	 * @return void
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('com_quick2cart');

		// Load state from the request userState on edit or from the passed variable on default
		if (JFactory::getApplication()->input->get('layout') == 'edit')
		{
			$id = JFactory::getApplication()->getUserState('com_quick2cart.edit.shipprofile.id');

			if (!isset($id))
			{
				$id = JFactory::getApplication()->input->get('id');
			}
		}
		else
		{
			$id = JFactory::getApplication()->input->get('id');
			JFactory::getApplication()->setUserState('com_quick2cart.edit.shipprofile.id', $id);
		}

		$this->setState('shipprofile.id', $id);

		// Load the parameters.
		$params       = JComponentHelper::getParams('com_quick2cart');
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('shipprofile.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function &getData($id = null)
	{
		if ($this->item === null)
		{
			$this->item = false;

			if (empty($id))
			{
				$id = $this->getState('shipprofile.id');
			}
			// Get a level row instance.
			$table = $this->getTable();
			/*$this->getTable('shipprofile');*/
			// Attempt to load the row.
			if ($table->load($id))
			{
				$user = JFactory::getUser();
				$id   = $table->id;

				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published)
					{
						return $this->item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties  = $table->getProperties(1);
				$this->item = JArrayHelper::toObject($properties, 'JObject');
			}
			elseif ($error = $table->getError())
			{
				$this->setError($error);
			}
		}

		return $this->item;
	}

	/**
	 * Method Get table.
	 *
	 * @param   String  $type    Type
	 * @param   String  $prefix  Prefix
	 * @param   String  $config  Config
	 *
	 * @return	Object
	 */
	public function getTable($type = 'shipprofile', $prefix = 'Quick2cartTable', $config = array())
	{
		$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return	boolean		True on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('shipprofile.id');

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
	 * Method to check out an item.
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return	boolean		True on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('shipprofile.id');

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
	 * Method to get the profile form The base form is loaded from XML.
	 *
	 * @param   Array    $data      An optional array of data for the form to interogate.
	 * @param   Boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_quick2cart.shipprofile', 'shipprofile', array(
																'control' => 'jform',
																'load_data' => $loadData
																)
		);

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
		$data = JFactory::getApplication()->getUserState('com_quick2cart.edit.shipprofile.data', array());

		if (empty($data))
		{
			$data = $this->getData();
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   Array  $data  Data
	 *
	 * @since	1.6
	 *
	 * @return id
	 */
	public function save($data)
	{
		jimport('joomla.database.table');
		$app = JFactory::getApplication();
		/*$data = $app->input->post->get('jform', array(), 'array');*/

		/*$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__kart_zonerules AS gr');

		if ($update === 1)
		{
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
		}*/

		$table = $this->getTable('shipprofile');

		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		$app->input->set('shipprofileId', $table->id);

		return $table->id;
		/*$id = (!empty($data['id'])) ? $data['id'] : '';
		$state = (!empty($data['state'])) ? 1 : 0;*/

		/*	$table = $this->getTable('shipprofile');

		if ($table->save($data) === true)
		{
		return $table->id;
		}
		else
		{
		return false;
		}
		*/
		/*		$db = JFactory::getDBO();
		$obj=new stdClass();
		$obj->id = $id;
		$obj->state = $state;
		$obj->name = $data['name'];
		$obj->store_id=  $data['store_id'];
		$action = 'insertObject';

		if(!empty($obj->id))
		{
		$action = 'updateObject';
		}

		if(!$db->$action('#__kart_shipprofile',$obj,'id'))
		{
		echo $db->stderr();
		return false;
		}

		return $obj->id;*/
	}

	/**
	 * Method to get ship profiles method(s) detail.
	 *
	 * @param   string  $shipprofile_id  ship profile id id.
	 * @param   string  $methodId        Tax rule id.
	 *
	 * @since   2.2
	 * @return   object list.
	 */
	public function getShipMethods($shipprofile_id = '', $methodId = '')
	{
		$qtcshiphelper = new qtcshiphelper;

		return $qtcshiphelper->getShipMethods($shipprofile_id, $methodId);
	}

	/**
	 * Method to get shipping plugin select box.
	 *
	 * @param   string  $default_val  default value of select box.
	 *
	 * @since   2.2
	 * @return   null object of shipping plugin select box.
	 */
	public function getShipPluginListSelect($default_val = '')
	{
		// Get tax rate list
		$plugins          = qtcshiphelper::getPlugins();
		$plugin_options   = array();
		$plugin_options[] = JHtml::_('select.option', '', JText::_("COM_QUICK2CART_SELECT_SHIPPLUGIN"));

		foreach ($plugins as $item)
		{
			$plugin_options[] = JHtml::_('select.option', $item->extension_id, $item->name);
		}

		$plugin_list = JHtml::_(
		'select.genericlist', $plugin_options, 'qtcShipPlugin', 'aria-invalid="false" size="1"
		autocomplete="off" data-chosen="qtc" onchange=\'qtcLoadPlgMethods()\'',
		'value', 'text', $default_val
		);

		return $plugin_list;
	}

	/**
	 * Method to add shipping method.
	 *
	 * @param   Integer  $update  Update
	 *
	 * @since   2.2
	 * @return   null object of shipping method select box.
	 */
	public function addShippingPlgMeth($update = 0)
	{
		$app  = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');

		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__kart_shipProfileMethods AS sm');

		if ($update == 1)
		{
			$data['id'] = $data['qtc_shipProfileMethodId'];
			$query->where('sm.id !=' . $db->escape($data['qtc_shipProfileMethodId']));
		}

		$qtcshiphelper  = new qtcshiphelper;
		$data['client'] = $qtcshiphelper->getPluginDetail($data['qtcShipPluginId']);

		$query->where('sm.shipprofile_id=' . $db->escape($data['shipprofile_id']));
		$query->where('sm.client=' . $db->Quote($db->escape($data['client'])));
		$query->where('sm.methodId=' . $db->Quote($db->escape($data['methodId'])));
		$db->setQuery($query);

		$result = $db->loadResult();

		if (!empty($result))
		{
			$this->setError(JText::_("COM_QUICK2CART_SHIPMETHOD_ALREADY_EXISTS"));

			return false;
		}

		$table = $this->getTable('Shipmethods');

		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		$app->input->set('shipMethodId', $table->id);

		return true;
	}

	/**
	 * Method Delete
	 *
	 * @param   String  &$items  Reference Address of items
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function delete(&$items)
	{
		$user  = JFactory::getUser();
		$db    = JFactory::getDBO();
		$count = 0;
		$ids   = '';
		$app   = JFactory::getApplication();

		if (is_array($items) && !empty($items))
		{
			foreach ($items as $id)
			{
				try
				{
					$table  = $this->getTable();
					$status = $table->delete($id);
				}
				catch (RuntimeException $e)
				{
					$this->setError($e->getMessage());

					return 0;
				}

				if ($status)
				{
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * This function manage items published or unpublished state
	 *
	 * @param   Array   $items  Items
	 * @param   String  $state  1 for publish and 0 for unpublish.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function setItemState($items, $state)
	{
		$db = JFactory::getDBO();

		if (is_array($items))
		{
			$taxreate_ids = implode(',', $items);
			$db           = JFactory::getDbo();
			$query        = $db->getQuery(true);

			// Fields to update.
			$fields = array(
				$db->quoteName('state') . ' =' . $state
			);

			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('id') . '  IN (' . $taxreate_ids . ')'
			);

			$query->update($db->quoteName('#__kart_shipprofile'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();

			if (!$result)
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		return true;
	}
}
