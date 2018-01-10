<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

/**
 * TaxprofileForm Model
 *
 * @package     Com_Quick2cart
 *
 * @subpackage  site
 *
 * @since       2.2
 */
class Quick2cartModelTaxprofileForm extends JModelForm
{
	protected $item = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('com_quick2cart');

		// Load state from the request userState on edit or from the passed variable on default
		if (JFactory::getApplication()->input->get('layout') == 'edit')
		{
			$id = JFactory::getApplication()->getUserState('com_quick2cart.edit.taxprofile.id');
		}
		else
		{
			$id = JFactory::getApplication()->input->get('id');
			JFactory::getApplication()->setUserState('com_quick2cart.edit.taxprofile.id', $id);
		}

		$this->setState('taxprofile.id', $id);

		// Load the parameters.
		$params = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('taxprofile.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param   integer  $id  Id
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
				$id = $this->getState('taxprofile.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table->load($id))
			{
				$user = JFactory::getUser();
				$id = $table->id;

				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published)
					{
						return $this->item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties = $table->getProperties(1);
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
	 * Method to get table.
	 *
	 * @param   integer  $type    type
	 * @param   integer  $prefix  prefix
	 * @param   integer  $config  config
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function getTable($type = 'taxprofile', $prefix = 'Quick2cartTable', $config = array())
	{
		$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   integer  $id  ID
	 *
	 * @return	boolean		True on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('taxprofile.id');

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
	 * Method to check out an item for editing.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return	boolean  True on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('taxprofile.id');

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
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_quick2cart.taxprofile', 'taxprofileform', array('control' => 'jform', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_quick2cart.edit.taxprofile.data', array());

		if (empty($data))
		{
			$data = $this->getData();
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  Data
	 *
	 * @return  mixed  The user id on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function save($data)
	{
		$id = (!empty($data['id'])) ? $data['id'] : '';
		$state = (!empty($data['state'])) ? 1 : 0;
	/*	$table = $this->getTable('taxprofile');
		print"<pre>"; print_r($data); die;

		if ($table->save($data) === true)
		{
			return $table->id;
		}
		else
		{
			return false;
		}
*/
		$db = JFactory::getDBO();
		$obj = new stdClass;
		$obj->id = $id;
		$obj->state = $state;
		$obj->name = $data['name'];
		$obj->store_id = $data['store_id'];
		$action = 'insertObject';

		if (!empty($obj->id))
		{
			$action = 'updateObject';
		}

		if (!$db->$action('#__kart_taxprofiles', $obj,'id'))
		{
			echo $db->stderr();

			return false;
		}

		return $obj->id;
	}

	/**
	 * Method to get profiles tax rule(s) detail.
	 *
	 * @param   string  $taxprofile_id  Tax Profile Id.
	 * @param   string  $taxRule_id     Tax rule id.
	 *
	 * @since   2.2
	 *
	 * @return   null object.
	 */
	public function getTaxRules($taxprofile_id='', $taxRule_id='')
	{
		// Load Zone helper.
		$path = JPATH_SITE . DS . "components" . DS . "com_quick2cart" . DS . 'helpers' . DS . "zoneHelper.php";

		if (!class_exists('zoneHelper'))
		{
			JLoader::register('zoneHelper', $path);
			JLoader::load('zoneHelper');
		}

		$zoneHelper = new zoneHelper;

		return  $zoneHelper->getTaxRules($taxprofile_id, $taxRule_id);
	}

	/**
	 * Method to get the users tax rule select box.
	 *
	 * @param   string  $store_id     Store ID
	 * @param   string  $default_val  default value of select box.
	 *
	 * @since   2.2
	 * @return   null object of tax rule select box.
	 */
	public function getTaxRateListSelect($store_id, $default_val='')
	{
		$zoneHelper = new zoneHelper;

		// Get tax rate list
		$taxrates = $zoneHelper->getUserTaxRateList($store_id);
		$taxrate_options = array();
		$taxrate_options[] = JHtml::_('select.option', '', JText::_('COM_QUICK2CART_SELECT_TAXRATE'));

		foreach ($taxrates as $item)
		{
			$name = $item->name . ' (' . floatval($item->percentage) . '%)';
			$taxrate_options[] = JHtml::_('select.option', $item->id, $name);
		}

		$taxrate_list = JHtml::_('select.genericlist', $taxrate_options, 'jform[taxrate_id]', '', 'value', 'text', $default_val);

		return $taxrate_list;
	}

	/**
	 * Method to get address list to be consider while appling the tax.
	 *
	 * @param   string  $default_val  default value of select box.
	 *
	 * @since   2.2
	 * @return   null object of address select box.
	 */
	public function getAddressList($default_val='')
	{
		$address_options = array();
		$address_options[] = JHtml::_('select.option', '', JText::_('COM_QUICK2CART_SELECT_ADDRESS'));
		$address_options[] = JHtml::_('select.option', 'shipping', JText::_('COM_QUICK2CART_SHIPPING_ADDRESS'));
		$address_options[] = JHtml::_('select.option', 'billing', JText::_('COM_QUICK2CART_BILLING_ADDRESS'));
		/*$address_options[] =  JHtml::_('select.option', 'store', JText::_('COM_QUICK2CART_STORE_ADDRESS'));*/
		$address_list = JHtml::_('select.genericlist', $address_options, 'jform[address]', '', 'value', 'text', $default_val);

		return $address_list;
	}

	/**
	 * Method to add tax rule against tax profile.
	 *
	 * @param   Integer  $update  Update
	 *
	 * @since   2.2
	 * @return   null object of address select box.
	 */
	public function saveTaxRule($update = 0)
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->get('jform', array(), 'array');

		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__kart_taxrules AS r');

		if ($update == 1)
		{
			// Getting profile id of tax rule id.
			$taxHelper = new taxHelper;
			$taxprofile_id = $taxHelper->getTaxProfileId($data['taxrule_id']);
			$data['taxprofile_id'] = $taxprofile_id;
			$query->where('r.taxrule_id !=' . $db->escape($data['taxrule_id']));
		}

		$query->where('r.taxprofile_id=' . $db->escape($data['taxprofile_id']));
		$query->where('r.taxrate_id=' . $db->escape($data['taxrate_id']));
		$query->where('r.address=' . $db->Quote($db->escape($data['address'])));

		$db->setQuery($query);
		$result = $db->loadResult();

		if (!empty($result))
		{
			$this->setError(JText::_('COM_QUICK2CART_TAXRULE_ALREADY_EXISTS'));

			return false;
		}

		$taxRule = $this->getTable('Taxrules');

		if (!$taxRule->bind($data))
		{
			$this->setError($taxRule->getError());

			return false;
		}

		if (!$taxRule->check())
		{
			$this->setError($taxRule->getError());

			return false;
		}

		if (!$taxRule->store())
		{
			$this->setError($taxRule->getError());

			return false;
		}

		$app->input->set('taxrule_id', $taxRule->taxrule_id);

		return true;
	}
}
