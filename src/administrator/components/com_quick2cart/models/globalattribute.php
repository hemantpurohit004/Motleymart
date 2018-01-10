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

jimport('joomla.application.component.modeladmin');

/**
 * Model for global attribute.
 *
 * @since  2.5
 */
class Quick2cartModelglobalAttribute extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_QUICK2CART';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 *
	 * @since   2.5
	 */
	public function getTable($type = 'Attribute', $prefix = 'Quick2cartTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm   A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_quick2cart.attribute', 'attribute', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed   The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_quick2cart.edit.attribute.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   1.6
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
	 * @param   Object  $table  table object
	 *
	 * @return  null
	 *
	 * @since	2.5
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
				$db->setQuery('SELECT MAX(ordering) FROM #__kart_global_attribute');
				$max = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Function to get optionlist for global attribute
	 *
	 * @param   INT  $id  global attribute id
	 *
	 * @return  option list
	 *
	 * @since  2.5
	 *
	 * */
	public function getOptionList($id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, option_name, ordering');
		$query->from('#__kart_global_attribute_option');
		$query->where('attribute_id' . ' = ' . $id);
		$query->order('ordering');
		$db->setQuery($query);
		$optionList = $db->loadObjectList();

		return $optionList;
	}

	/**
	 * Function to attribute id of option
	 *
	 * @param   INT  $optionId  option id
	 *
	 * @return  option list
	 *
	 * @since  2.5
	 *
	 * */
	public function getOptionsAttributeId($optionId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('attribute_id');
		$query->from('#__kart_global_attribute_option');
		$query->where('id' . ' = ' . $optionId);
		$db->setQuery($query);
		$attributeId = $db->loadResult();

		return $attributeId;
	}

	/**
	 * Function to save global attributes and related options
	 *
	 * @param   ARRAY  $data  data to be saved
	 *
	 * @return true/false
	 *
	 * @since  2.5
	 *
	 * */
	public function save($data)
	{
		$input = JFactory::getApplication()->input;
		$data['renderer'] = $input->post->get('renderer', '', 'string');
		$options = $input->post->get('options', '', 'array');

		// Array of option ordering
		$ordering = array();

		// Array of option name
		$name = array();

		$i = 0;

		foreach ($options as $opt)
		{
			$ordering[$i] = $opt['ordering'];
			$name[$i] = $opt['option_name'];
			$i++;
		}

		// Code to check duplicate ordering
		for ($i = 0;$i < count($ordering);$i++)
		{
			for ($j = $i + 1;$j < count($ordering);$j++)
			{
				if ($ordering[$i] == $ordering[$j])
				{
					$this->setError(JText::_('COM_QUICK2CART_DUPLICATE_OPTION_ORDERING_ERROR'));

					return false;
				}

				if ($name[$i] == $name[$j])
				{
					$this->setError(JText::_('COM_QUICK2CART_DUPLICATE_OPTION_NAME_ERROR'));

					return false;
				}
			}
		}

		// Code to check duplicate ordering ends
		if (parent::save($data))
		{
			$gid = (int) $this->getState($this->getName() . '.id');
			$db = JFactory::getDbo();

			foreach ($options as $key => $option)
			{
				$record = new stdclass;
				$record->id = $option['id'];
				$record->attribute_id = $gid;
				$record->option_name = $option['option_name'];
				$record->ordering = $option['ordering'];

				// If option already exist then update the record else insert new record
				if ($record->id)
				{
					$db->updateObject('#__kart_global_attribute_option', $record, 'id', true);
				}
				else
				{
					$db->insertObject('#__kart_global_attribute_option', $record, 'id', true);
				}
			}

			return true;
		}
	}

	/**
	 * Function to dlete options
	 *
	 * @return  model object
	 *
	 * @since  2.5
	 */
	public function deleteoption()
	{
		$input = JFactory::getApplication()->input;
		$optionid = $input->get('optionid', '', 'int');

		// Check if option to be deleted is used or not
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('itemattributeoption_name');
		$query->from('#__kart_itemattributeoptions');
		$query->where('global_option_id' . ' = ' . $optionid);
		$db->setQuery($query);
		$optionList = $db->loadColumn();

		if (empty($optionList))
		{
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__kart_global_attribute_option'));
			$query->where($db->quoteName('id') . " = " . $optionid);
			$db->setQuery($query);
			$db->execute();

			return true;
		}

		return false;
	}

	/**
	 * Function to get attributes details
	 *
	 * @param   INT  $attribute_id  attribute id
	 *
	 * @return  model object
	 *
	 * @since  2.5
	 */
	public function getAttributeDetails($attribute_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__kart_global_attribute');
		$query->where('id' . ' = ' . $attribute_id);
		$db->setQuery($query);
		$result = $db->loadAssoc();

		return $result;
	}
}
