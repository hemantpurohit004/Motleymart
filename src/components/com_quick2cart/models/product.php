<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2Cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
require_once JPATH_SITE . "/components/com_tjfields/filterFields.php";
/**
 * Product model class.
 *
 * @package  Quick2cart
 * @since    1.0
 */
class Quick2cartModelProduct extends JModelForm
{
	use TjfieldsFilterField;

	/**
	 * Method to get the profile form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since  1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm(
			'com_quick2cart.product', 'product',
			array('control' => 'jform',
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
	 * This function store attributes
	 *
	 * @param   integer  $item_id    item_id
	 * @param   array    $allAttrib  All attribute details
	 * @param   string   $sku        Sku
	 * @param   string   $client     client
	 *
	 * @since   1.0
	 * @return   null
	 */
	public function StoreAllAttribute($item_id, $allAttrib, $sku, $client)
	{
		// Get  attributeid list FROM POST
		$attIdList = array();

		foreach ($allAttrib as $attributes)
		{
			if (!empty($attributes['attri_id']))
			{
				$attIdList[] = $attributes['attri_id'];
			}
		}

		// DEL EXTRA ATTRIBUTES
		if (!class_exists('productHelper'))
		{
			// Require while called from backend
			JLoader::register('productHelper', JPATH_SITE . '/components/com_quick2cart/helpers/product.php');
			JLoader::load('productHelper');
		}

		// THIS  DELETE db attributes which is not present now or removed
		$productHelper = new productHelper;
		$productHelper->deleteExtaAttribute($item_id, $attIdList);

		if (!class_exists('quick2cartModelAttributes'))
		{
			// Require while called from backend
			JLoader::register('quick2cartModelAttributes', JPATH_SITE . '/components/com_quick2cart/models/attributes.php');
			JLoader::load('quick2cartModelAttributes');
		}

		$quick2cartModelAttributes = new quick2cartModelAttributes;

		foreach ($allAttrib as $key => $attr)
		{
			$attr['sku']     = $sku;
			$attr['client']  = $client;
			$attr['item_id'] = $item_id;

			// Dont consider empty attributes
			if (!empty($attr['attri_name']))
			{
				$quick2cartModelAttributes->store($attr);
			}
		}
	}

	/**
	 * This function store attributes
	 *
	 * @param   string  $sku  sku
	 *
	 * @since   1.0
	 * @return   null
	 */
	public function getItemidFromSku($sku)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT `item_id` from `#__kart_items` where `sku`="' . $sku . '"';
		$db->setQuery($query);

		return $ietmid = $db->loadResult();
	}

	/**
	 * This model function manage items published or unpublished state
	 *
	 * @param   array   $items  items
	 * @param   integr  $state  state
	 *
	 * @since   1.0
	 * @return   null
	 */
	public function setItemState($items, $state)
	{
		$db = JFactory::getDBO();

		if (is_array($items))
		{
			foreach ($items as $id)
			{
				$db    = JFactory::getDBO();
				$query = "UPDATE #__kart_items SET state=" . $state . " WHERE item_id=" . $id;
				$db->setQuery($query);

				if (!$db->execute())
				{
					$this->setError($this->_db->getErrorMsg());

					return false;
				}
			}
		}
	}

	/**
	 * Send mail to owner
	 *
	 * @param   array  $values  values
	 *
	 * @since   1.0
	 * @return   null
	 */
	public function SendMailToOwner($values)
	{
		$app                 = JFactory::getApplication();
		$mailfrom            = $app->getCfg('mailfrom');
		$fromname            = $app->getCfg('fromname');
		$sitename            = $app->getCfg('sitename');
		$loguser             = JFactory::getUser();
		$sendto              = $loguser->email;
		$subject             = JText::_('COM_Q2C_PRODUCT_AAPROVAL_OWNER_SUBJECT');
		$subject             = str_replace('{sellername}', $loguser->name, $subject);
		$comquick2cartHelper = new comquick2cartHelper;
		$itemid              = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=category&layout=default');
		$body                = JText::_('COM_Q2C_PRODUCT_AAPROVAL_OWNER_BODY');
		$body                = str_replace('{sellername}', $loguser->name, $body);
		$body                = str_replace('{title}', $values->get('item_name', '', 'RAW'), $body);

		$url = JUri::base() . 'index.php?option=com_quick2cart&view=category&layout=default&Itemid=' . $itemid;
		$body                = str_replace('{link}', $url, $body);
		$body                = str_replace('{sitename}', $sitename, $body);
		$res                 = $comquick2cartHelper->sendmail($mailfrom, $subject, $body, $sendto);
	}

	/**
	 * This function return product images according to integration
	 * TODO: for now it only work for zoo & native, so the changes will be needed for other integration
	 *
	 * @param   integer  $item_id  item_id
	 *
	 * @since   1.0
	 * @return   null
	 */
	public function getProdutImages($item_id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Get the product id ( this is needed integration) & client(parent)
		$query->select($db->quoteName(array('parent', 'product_id')));
		$query->from($db->quoteName('#__kart_items'));
		$query->where($db->quoteName('item_id') . ' = ' . $item_id);

		$db->setQuery($query);
		$results = $db->loadObject();

		switch ($results->parent)
		{
			// Get Zoo item image
			case 'com_zoo':

				$query = $db->getQuery(true);

				$query->select($db->quoteName(array('i.elements', 'i.application_id', 'i.type', 'app.application_group')));
				$query->from($db->quoteName('#__zoo_item', 'i'));
				$query->join('LEFT', $db->quoteName('#__zoo_application', 'app') .
				' ON (' . $db->quoteName('app.id') . ' = ' . $db->quoteName('i.application_id') . ')');

				$query->where($db->quoteName('i.id') . ' = ' . $results->product_id);
				$db->setQuery($query);

				$zoo_item = $db->loadObject();

				$image_path[0] = $this->getItemFieldData($zoo_item->application_group, $zoo_item->type, $zoo_item->elements);

				return $image_path;

				break;

			default:
				if (!empty($item_id))
				{
					$db    = JFactory::getDBO();
					$query = "SELECT `images` FROM `#__kart_items` WHERE `item_id` = " . $item_id;
					$db->setQuery($query);
					$image_path = $db->loadResult();

					if (!empty($image_path))
					{
						return json_decode($image_path, false);
					}
				}
		}
	}

	/**
	 * Get field detail
	 *
	 * @param   array    $application_group  Zoo Item Application group
	 * @param   integer  $type               Zoo Item type
	 * @param   integer  $elements           Zoo element info
	 *
	 * @since   1.0
	 * @return   null
	 */
	public static function getItemFieldData($application_group, $type, $elements)
	{
		$elements = json_decode($elements, true);

		$app               = App::getInstance('zoo');
		$db                = JFactory::getDBO();
		$application_group = strtolower($application_group);
		$item_type         = strtolower($type);
		$zoo_config_file   = array();
		$fielContent = file_get_contents(JPATH_SITE . '/media/zoo/applications/' . $application_group . '/types/' . $item_type . '.config');
		$zoo_config_file   = json_decode($fielContent, true);

		// Get the image key
		$image_flag = 0;

		// Check is the image available
		foreach ($zoo_config_file['elements'] as $image_key => $arr_row)
		{
			if ($arr_row['type'] == "image" AND $arr_row['name'] != "Teaser Image")
			{
				$image_flag = 1;
				break;
			}
			elseif ($arr_row['type'] == "image" AND $arr_row['name'] == "Teaser Image")
			{
				$image_flag = 1;
				break;
			}
		}

		$result = array();

		// Get the image path from $element array
		if ($image_flag == 1)
		{
			$image = $elements[$image_key]['file'];
		}
		else
		{
			$image = '';
		}

		return $image;
	}

	/**
	 * This function sends mail to admin after editing product
	 *
	 * @param   array    $prod_values  product values
	 * @param   integer  $item_id      item id to remember or not
	 * @param   integer  $newProduct   newProduct URL
	 *
	 * @since   1.0
	 * @return   null
	 */
	public function SendMailToAdminApproval($prod_values, $item_id, $newProduct = 1)
	{
		$loguser             = JFactory::getUser();
		$comquick2cartHelper = new comquick2cartHelper;
		$app                 = JFactory::getApplication();
		$mailfrom            = $app->getCfg('mailfrom');
		$fromname            = $app->getCfg('fromname');
		$sitename            = $app->getCfg('sitename');
		$params              = JComponentHelper::getParams('com_quick2cart');
		$sendto              = $params->get('sale_mail');
		$currency            = $comquick2cartHelper->getCurrencySession();

		$multiple_img           = array();
		$count                  = 0;
		$prod_imgs              = $prod_values->get('qtc_prodImg', array(), "ARRAY");
		$quick2cartModelProduct = new quick2cartModelProduct;
		$multiple_img           = $quick2cartModelProduct->getProdutImages($item_id);
		$body                   = '';

		// Edit product
		if ($newProduct == 0)
		{
			$subject   = JText::_('COM_Q2C_EDIT_PRODUCT_SUBJECT');
			$subject   = str_replace('{sellername}', $loguser->name, $subject);
			$body      = JText::_('COM_Q2C_EDIT_PRODUCT_BODY');
			$body      = str_replace('{productname}', $prod_values->get('item_name', '', 'RAW'), $body);
			$pod_price = $prod_values->get('multi_cur', array(), "ARRAY");
			$body      = str_replace('{price}', $pod_price[$currency], $body);
			$body      = str_replace('{sellername}', $loguser->name, $body);
			$body      = str_replace('{sku}', $prod_values->get('sku', '', 'RAW'), $body);

			if (!empty($multiple_img))
			{
				$multiple_img = (array) $multiple_img;

				foreach ($multiple_img as $key => $img)
				{
					$body .= '<br><img src="' . JUri::root() . 'images/quick2cart/' . $img[$key] . '" alt="No image" ><br>';
				}
			}
		}

		// New product
		else
		{
			$subject = JText::_('COM_Q2C_PRODUCT_AAPROVAL_SUBJECT');
			$body    = JText::_('COM_Q2C_PRODUCT_AAPROVAL_BODY');
			$body    = str_replace('{title}', $prod_values->get('item_name', '', 'RAW'), $body);
			$body    = str_replace('{sellername}', $loguser->name, $body);
			$desc    = $prod_values->get('description', '', 'ARRAY');
			$desc    = strip_tags(trim($desc['data']));
			$body    = str_replace('{des}', $desc, $body);
			$body    = str_replace('{link}', JUri::base() . 'administrator/index.php?option=com_quick2cart&view=products&filter_published=0', $body);

			for ($i = 0; $i < count($multiple_img); $i++)
			{
				$body .= '<br><img src="' . JUri::ROOT() . 'images/quick2cart/' . $multiple_img[$i] . '" alt="No image" ><br>';
			}
		}

		$res = $comquick2cartHelper->sendmail($mailfrom, $subject, $body, $sendto);
	}
}
