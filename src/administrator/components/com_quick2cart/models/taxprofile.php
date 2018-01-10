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

jimport('joomla.application.component.modeladmin');

/**
 * Quick2cart model.
 */
class Quick2cartModelTaxprofile extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_QUICK2CART';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Taxprofile', $prefix = 'Quick2cartTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_quick2cart.taxprofile', 'taxprofile', array('control' => 'jform', 'load_data' => $loadData));

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
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_quick2cart.edit.taxprofile.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			//Do any procesing on fields here if needed
		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
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
				$db->setQuery('SELECT MAX(ordering) FROM #__kart_taxprofiles');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}
		}
	}

	/**
	 * Method to get the users tax rule select box.
	 *
	 * @param   string  $default_val  default value of select box.
	 *
	 * @since   2.2
	 * @return   null object of tax rule select box.
	 */
	public function getTaxRateListSelect($store_id,$default_val='')
	{
		$zoneHelper = new zoneHelper;

		// Get tax rate list
		$taxrates = $zoneHelper->getUserTaxRateList($store_id);
		$taxrate_options = array();
		$taxrate_options[] = JHtml::_('select.option', '', JText::_('COM_QUICK2CART_SELECT_TAXRATE'));

		foreach($taxrates as $item)
		{
			$name =  $item->name . ' (' . floatval($item->percentage) . "%)";
			//[" . JText::_("COM_QUICK2CART_TAXPROFILES_STORE_NAME") . " : " . $item->storeName . " ]" ;
			$taxrate_options[] =  JHtml::_('select.option', $item->id, $name);
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
		$address_options[] =  JHtml::_('select.option', 'shipping', JText::_('COM_QUICK2CART_SHIPPING_ADDRESS'));
		$address_options[] =  JHtml::_('select.option', 'billing', JText::_('COM_QUICK2CART_BILLING_ADDRESS'));
		//$address_options[] =  JHtml::_('select.option', 'store', JText::_('COM_QUICK2CART_STORE_ADDRESS'));
		$address_list = JHtml::_('select.genericlist', $address_options, 'jform[address]', '', 'value', 'text', $default_val);

		return $address_list;
	}

	/**
	 * Method to add tax rule against tax profile.
	 *
	 * @since   2.2
	 * @return   null object of address select box.
	 */
	public function saveTaxRule($update = 0)
	{
		$app = JFactory::getApplication();
		$data = $app->input->post->get('jform',array(),'array');

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
			$query->where('r.taxrule_id !='.$db->escape($data['taxrule_id']));

		}

		$query->where('r.taxprofile_id='.$db->escape($data['taxprofile_id']));
		$query->where('r.taxrate_id='.$db->escape($data['taxrate_id']));
		$query->where('r.address='.$db->Quote($db->escape($data['address'])));

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

	/**
	 * Method to get profiles tax rule(s) detail.
	 *
	 * @param   string  $taxRule_id  Tax rule id.
	 *
	 * @since   2.2
	 * @return   null object.
	 */
	public function getTaxRules($taxprofile_id='', $taxRule_id='')
	{
		$zoneHelper = new zoneHelper;
		return  $zoneHelper->getTaxRules($taxprofile_id, $taxRule_id);
	}

	function updateTaxRule()
	{

	}
}
