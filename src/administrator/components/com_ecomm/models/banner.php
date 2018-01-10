<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jticketing
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Subscription Model
 *
 * @since  0.0.1
 */
class EcommModelBanner extends JModelAdmin
{
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Banner', $prefix = 'EcommTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_ecomm.banner',
			'banner',
			array(
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
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState(
			'com_ecomm.edit.ecomm.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
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
		// Get the user input & files
		$jinput = JFactory::getApplication()->input;
		$files = $jinput->files->get('jform');
		unset($data['img_path']);
		if(parent::save($data))
		{
			$data['id'] = empty($data['id']) ? $this->getState($this->getName() . '.id') : $data['id'];

			if($data['id'])
			{
				if(!empty($files['img']['name']))
				{
					$data['img_path'] = (string) ($this->storeFile($data['id']));

					return parent::save($data);
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Method to save the uploaded image.
	 *
	 * @param   int  $id  The id.
	 *
	 * @return  string  File name.
	 *
	 * @since   0.0.1
	 */
	public function storeFile($id)
	{
		// Expiration time in seconds
		$params = JComponentHelper::getParams( 'com_ecomm' );
		$target_dir = $params->get ('banner_store_path');

		// Get the user input & files
		$jinput = JFactory::getApplication()->input;
		$files = $jinput->files->get('jform');

		if (!empty($files['img']['name']))
		{
			$attachmentImgUrl = JPATH_SITE . '/' . $target_dir . $id . '/' . $files['img']['name'];

			// Move uploaded files to destination
			JFile::upload($files['img']['tmp_name'], $attachmentImgUrl);
		}

		return $attachmentImgUrl;
	}

		/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  \JObject|boolean  Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		// Get the details
		$data = parent::getItem($pk);

		// If attachements are present get the attachment url
		if(!empty($data->img_path))
		{
			$params = JComponentHelper::getParams( 'com_ecomm' );
			$target_dir = $params->get ('banner_store_path');

			$data->img_path = str_replace(JPATH_SITE . '/' , JURI::root(), $data->img_path);
		}

		return $data;
	}
}
