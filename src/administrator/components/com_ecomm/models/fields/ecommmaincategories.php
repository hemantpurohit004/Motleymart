<?php
/**
 * @package    com_bills
 * @copyright  Copyright (C) 2017 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of files
 *
 * @since  11.1
 */
class JFormFieldEcommMainCategories extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'EcommMainCategories';

	/**
	 * Method to get the list of files for the field options.
	 * Specify the target directory with a directory attribute
	 * Attributes allow an exclude mask and stripping of extensions from file name.
	 * Default attribute may optionally be set to null (no file) or -1 (use a default).
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Require helper file
		JLoader::register('EcommService', JPATH_SITE. '/administrator/components/com_ecomm/services/ecomm.php');

		$service  = new EcommService;

		$options = array();

		try
		{
			$result = $service->ecommGetAllCategories();

			if($result['success'])
			{
				foreach($result['categories'] as $row)
				{
					$data = new stdClass;
					$data->value = $row['id'];
					$data->text = $row['title'];
					$options[] = $data;
				}
			}

			// Merge any additional options in the XML definition.
			$options = array_merge(parent::getOptions(), $options);
		}
		catch(Exception $e)
		{
		}

		return $options;
	}
}
