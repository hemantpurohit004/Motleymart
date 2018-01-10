<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');

/**
 * Quick2cartModelProductpage for Product Details page
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       1.6.7
 */
class Quick2cartModelProductpage extends JModelLegacy
{
	/**
	 * Method to get the extra fields information
	 *
	 * @param   array  $item_id  Id of the record
	 *
	 * @return	Extra field data
	 *
	 * @since	1.8.5
	 */
	public function getDataExtra($item_id = null)
	{
		if (empty($item_id))
		{
			$input = JFactory::getApplication()->input;
			$item_id = $input->get('item_id', '', 'INT');
		}

		if (empty($item_id))
		{
			return false;
		}

		$TjfieldsHelperPath = JPATH_SITE . DS . 'components' . DS . 'com_tjfields' . DS . 'helpers' . DS . 'tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$tjFieldsHelper = new TjfieldsHelper;
		$data               = array();
		$data['client']     = 'com_quick2cart.product';
		$data['content_id'] = $item_id;

		$extra_fields_data = $tjFieldsHelper->FetchDatavalue($data);

		return $extra_fields_data;
	}
}
