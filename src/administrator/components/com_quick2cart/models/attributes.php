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
 * This Class supports attributes.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelAttributes extends JModelLegacy
{
	/**
	 * Function to get item attribute
	 *
	 * @param   INT  $item_id  id
	 *
	 * @return  boolean
	 */
	public function getItemAttributes($item_id)
	{
		$db = JFactory::getDBO();

		if (!empty($item_id))
		{
			try
			{
				$query = $db->getQuery(true);
				$query->select("*")->from('#__kart_itemattributes')->where(" item_id = " . $item_id)->order(" itemattribute_id ASC");
				$db->setQuery($query);

				return $db->loadobjectList();
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return array();
			}
		}
	}

	/**
	 * Function to get item attribute options
	 *
	 * @param   INT  $attr_id  attribute id
	 *
	 * @return  ARRAY
	 */
	public function getItemAttributeOptions($attr_id)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT opt.itemattributeoption_name FROM #__kart_itemattributeoptions AS opt WHERE opt.itemattribute_id='
		. (int) $attr_id . ' ORDER BY opt.ordering';
		$db->setQuery($query);
		$options = $db->loadColumn();

		return $options;
	}

	/**
	 * Function to get attribute
	 *
	 * @return  boolean
	 */
	public function getAttribute()
	{
		$jinput = JFactory::getApplication()->input;
		$id     = $jinput->get('attr_id');
		$query  = "SELECT itemattribute_name,attribute_compulsary,`attributeFieldType` FROM #__kart_itemattributes  WHERE itemattribute_id=" . (int) $id;
		$this->_db->setQuery($query);
		$result = $this->_db->loadObject();

		return $result;
	}

	/**
	 * This attribute option
	 *
	 * @param   INT  $id  id
	 *
	 * @return  boolean
	 */
	public function getAttributeoption($id = '')
	{
		if (empty($id))
		{
			$jinput = JFactory::getApplication()->input;
			$id     = $jinput->get('attr_id');
		}

		$query = 'SELECT * FROM #__kart_itemattributeoptions AS opt WHERE opt.itemattribute_id=' . (int) $id . ' ORDER BY opt.ordering';
		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();

		if (!empty($result))
		{
			$comquick2cartHelper = new comquick2cartHelper;
			$path                = JPATH_SITE . '/components/com_quick2cart/models/cart.php';
			$Quick2cartModelcart = $comquick2cartHelper->loadqtcClass($path, "Quick2cartModelcart");

			foreach ($result as $key => $attriOption)
			{
				if (!empty($attriOption->child_product_item_id))
				{
					// Fetch item details
					$result[$key]->child_product_detail = $Quick2cartModelcart->getItemRec($attriOption->child_product_item_id);
				}
			}
		}

		return $result;
	}

	/**
	 * This function delete item
	 *
	 * @param   INT  $item_id  id
	 *
	 * @return  boolean
	 */
	public function deleteItem($item_id)
	{
		$db    = JFactory::getDBO();
		$query = "DELETE FROM #__kart_items  WHERE item_id=" . (int) $item_id;
		$this->_db->setQuery($query);

		return $this->_db->execute();
	}

	/**
	 * This function delete attribute
	 *
	 * @param   INT  $id  id
	 *
	 * @return  boolean
	 */
	public function delattribute($id)
	{
		$query = "DELETE FROM #__kart_itemattributes  WHERE itemattribute_id=" . (int) $id;
		$this->_db->setQuery($query);
		$this->_db->execute();
		$query = "DELETE FROM #__kart_itemattributeoptions  WHERE itemattribute_id=" . (int) $id;
		$this->_db->setQuery($query);
		$this->_db->execute();
	}

	/**
	 * This function delete attribute
	 *
	 * @param   INT  $id  id
	 *
	 * @return  boolean
	 */
	public function delattributeOnly($id)
	{
		$query = "DELETE FROM #__kart_itemattributes  WHERE itemattribute_id=" . (int) $id;
		$this->_db->setQuery($query);

		return $this->_db->execute();
	}

	/**
	 * This function delete attribute options
	 *
	 * @param   INT  $id  id
	 *
	 * @return  boolean
	 */
	public function delattributeoption($id)
	{
		$query = "DELETE FROM #__kart_itemattributeoptions  WHERE itemattributeoption_id=" . (int) $id;
		$this->_db->setQuery($query);

		return $this->_db->execute();
	}

	/**
	 * This function save/update attribute.
	 *
	 * @param   ARRAY   $data  data
	 *
	 * @param   STRING  $sku   sku
	 *
	 * @return  boolean
	 */
	public function store($data, $sku = '')
	{
		global $mainframe;
		$mainframe  = JFactory::getApplication();
		$currentAttributeId = '';

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$result = $dispatcher->trigger('onQuick2cartBeforeAttributeSave', array($data));

		if (!empty($result[0]))
		{
			$data = $result[0];
		}

		// Depricated Start
		$result = $dispatcher->trigger('OnBeforeq2cAttributeSave', array($data));

		if (!empty($result[0]))
		{
			$data = $result[0];
		}

		// Depricated End

		// To store attribute name in #__kart_itemattributes table

		// Field type= textbox then there will not be any options
		$userFields   = array();
		$userFields[] = 'Textbox';
		$userdata     = (!empty($data['fieldType']) && in_array($data['fieldType'], $userFields)) ? 1 : 0;

		if (empty($userdata))
		{
			$ind = 0;

			if (!empty($data['attri_opt']))
			{
				foreach ($data['attri_opt'] as $key => $att_options)
				{
					$ind = $key;
				}
			}
		}

		$DelTask = 0;

		if (!empty($data['delete_attri']))
		{
			$DelTask = 1;
			$data    = $this->deleteAttributeOption($data);
		}

		if (empty($userdata))
		{
			$data = $this->removeInvalideOption($data);

			// If Options r not present
			if (count($data['attri_opt']) == 0)
			{
				$this->noOptionThenDelAttr($data['attri_id']);

				// If true when delete task
				$return = ($DelTask == 1)?true:false;

				return $return;
			}
		}

		$row = new stdClass;

		if ($data['attri_name'])
		{
			// $row = new stdClass;
			$row->itemattribute_name  = $data['attri_name'];
			$row->attributeFieldType  = $data['fieldType'];
			$row->global_attribute_id = isset($data['global_attribute_set']) ? $data['global_attribute_set'] : 0;

			if (isset($data['is_stock_keeping']))
			{
				$row->is_stock_keeping = 1;
			}

			// 1. store attribute name
			if (isset($data['iscompulsary_attr']))
			{
				$row->attribute_compulsary = true;
			}
			else
			{
				$row->attribute_compulsary = false;
			}

			// While updating the attribute
			if (!empty($data['attri_id']))
			{
				// @TODO VM: if fieldtype = text then delete all attribute option (in db)
				$row->itemattribute_id = $data['attri_id'];

				if (!$this->_db->updateObject('#__kart_itemattributes', $row, "itemattribute_id"))
				{
					echo $this->_db->stderr();

					return false;
				}
				/*if ATTRIB IS GOING TO UPDATE THEN COMPARE (DB OPTIONS AND POST OPTION) ,DEL EXTRA OPTION FROM DB ONLY */
				$att_option_ids = array();

				// GETTING OPTION ID'S ARRAY
				foreach ($data['attri_opt'] as $option)
				{
					$att_option_ids[] = $option['id'];
				}

				if (!empty($att_option_ids))
				{
					$productHelper = new productHelper;
					$productHelper->deleteExtraAttributeOptions($data['attri_id'], $att_option_ids);
				}
			}
			else
			{
				// For new attribute
				// Load Attributes model  // REQUIRE WHEN CALLED FROM BACKEND
				$comquick2cartHelper = new comquick2cartHelper;
				$path                = JPATH_SITE . '/components/com_quick2cart/models/cart.php';
				$Quick2cartModelcart = $comquick2cartHelper->loadqtcClass($path, "Quick2cartModelcart");

				if (!empty($data['item_id']))
				{
					$item_id = $data['item_id'];
				}
				elseif (!empty($data['product_id']) && !empty($data['client']))
				{
					$item_id = $Quick2cartModelcart->getitemid($data['product_id'], $data['client']);
				}
				elseif (!empty($data['sku']))
				{
					$item_id = $Quick2cartModelcart->getitemid(0, $data['client'], $data['sku']);
				}

				$row->item_id = $item_id;

				if (!$this->_db->insertObject('#__kart_itemattributes', $row, 'itemattribute_id'))
				{
					echo $this->_db->stderr();

					return false;
				}
			}
		}

		if (!empty($userdata))
		{
			// Add extra option for user data
			$quick2cartModelAttributes = new quick2cartModelAttributes;

			$option                    = array();
			/*$option['itemattribute_id'] = $row->itemattribute_id;
			$option['itemattributeoption_name'] = $row->itemattribute_name;
			$option['itemattributeoption_price'] = 0;
			$option['itemattributeoption_prefix'] = '+';
			$option['ordering'] = 1;*/
			$op                        = $data['attri_opt'][0];
			$op['name']                = $row->itemattribute_name;

			// $option['itemattributeoption_price'] = 0;
			$op['prefix']              = '+';

			foreach ($op['currency'] as $key => $curr)
			{
				$op['currency'][$key] = 0;
			}

			// Set default option to data
			$data['attri_opt'][0] = $op;
		}

		if (isset($data['iscompulsary_attr']))
		{
			$row->attribute_compulsary = true;
		}
		else
		{
			$row->attribute_compulsary = false;
		}

		$is_stock_keepingAttri = (isset($data['is_stock_keeping']) && ($row->attribute_compulsary == true))?1:0;

		//  2. to store attribute option in #__itemattributeoptions_ table
		foreach ($data['attri_opt'] as $key => $attri_opt)
		{
			//  Generate detail for child product id
			$optionDetail = array();
			$optionDetail['item_id'] = $data['item_id'];
			$optionDetail['attri_name'] = $data['attri_name'];
			$optionDetail['attri_opt'] = $attri_opt;

			if ($attri_opt['name'] && $attri_opt['currency'] && $attri_opt['prefix']) // && $attri_opt['order'])
			{
				$opt                             = new stdClass;
				$opt->itemattributeoption_name   = $attri_opt['name'];
				$opt->global_option_id           = $attri_opt['globalOptionId'];
				$opt->state           = isset($attri_opt['state']) ? $attri_opt['state'] : 1;
				$currkeys                        = array_keys($attri_opt['currency']);

				// Make array of currency keys
				$currkey                         = $currkeys[0];
				$opt->itemattributeoption_price  = $attri_opt['currency'][$currkey];
				$opt->itemattributeoption_prefix = $attri_opt['prefix'];
				$opt->ordering                   = $attri_opt['order'];

				// Hack By Nitesh
				$opt->itemattributeoption_price_mrp = $attri_opt['mrp'][$currkey];

				// UPDATING ATT OPTION
				if (!empty($attri_opt['id']))
				{
					// Update attribute option
					$opt->itemattributeoption_id = $attri_opt['id'];

					if (!$this->_db->updateObject('#__kart_itemattributeoptions', $opt, 'itemattributeoption_id'))
					{
						echo $this->_db->stderr();

						return false;
					}
					else
					{
						// After success update table #__kart_option_currency
						$db    = JFactory::getDBO();
						$query = "select * from `#__kart_option_currency` where itemattributeoption_id=" . (int) $opt->itemattributeoption_id;
						$db->setQuery($query);
						$result = $db->loadAssocList();

						if ($result)
						{
							foreach ($attri_opt['currency'] as $key => $value)
							{
								$flag     = -1;

								// To check currency field is present or not for that product
								$updateid = -1;

								foreach ($result as $dbkey => $dbvalue)
								{
									if ($key == $dbvalue['currency'])
									{
										$flag     = 1;
										$updateid = $dbvalue['id'];
										break;
									}
								}

								// Found currency so update updateid row
								if ($flag == 1 && $updateid)
								{
									$updateobj        = new stdClass;
									$updateobj->id    = $updateid;
									$updateobj->price = $value;

									if (!$this->_db->updateObject('#__kart_option_currency', $updateobj, 'id'))
									{
										echo $this->_db->stderr();

										return false;
									}
								}
								else
								{
									$updateobj                         = new stdClass;
									$updateobj->id                     = null;
									$updateobj->itemattributeoption_id = (int) $opt->itemattributeoption_id;
									$updateobj->currency               = $key;
									$updateobj->price                  = $value;

									if (!$this->_db->insertObject('#__kart_option_currency', $updateobj, 'id'))
									{
										echo $this->_db->stderr();

										return false;
									}
								}
							}
						}
						else
						{
							foreach ($attri_opt['currency'] as $key => $value)
							{
								$currobj                         = new stdClass;
								$currobj->id                     = null;
								$currobj->itemattributeoption_id = (int) $opt->itemattributeoption_id;
								$currobj->currency               = $key;
								$currobj->price                  = $value;

								if (!$this->_db->insertObject('#__kart_option_currency', $currobj, 'id'))
								{
									echo $this->_db->stderr();

									return false;
								}
							}
						}

						// Update child product stock
						if ($is_stock_keepingAttri)
						{
							$optionDetail['itemattributeoption_id'] = $opt->itemattributeoption_id;
							$chileProdItem_id = $this->createChildProd($optionDetail);

							// Ideally this should not require for update
							$this->mapChildprodutToOption($optionDetail['itemattributeoption_id'], $chileProdItem_id);
						}
					}
				}
				else
				{
					// Adding new  ATT OPTION
					$opt->itemattribute_id = $row->itemattribute_id;

					if (!$this->_db->insertObject('#__kart_itemattributeoptions', $opt, 'itemattributeoption_id'))
					{
						echo $this->_db->stderr();

						return false;
					}
					else
					// If INSERTED AND NOT ERROR THEN STORE OPTION CURRENCY
					{
						// Add attribute option to DB
						$insert_id = $opt->itemattributeoption_id; // get last inserted id

						foreach ($attri_opt['currency'] as $key => $value)
						{
							$option                         = new stdClass;
							$option->id                     = null;
							$option->itemattributeoption_id = (int) $insert_id;
							$option->currency               = $key;
							$option->price                  = (float) $value;

							if (!$this->_db->insertObject('#__kart_option_currency', $option, 'id'))
							{
								echo $this->_db->stderr();

								return false;
							}
						}

						// Update child product stock
						if ($is_stock_keepingAttri)
						{
							$optionDetail['itemattributeoption_id'] = $opt->itemattributeoption_id;
							$chileProdItem_id = $this->createChildProd($optionDetail);

							// Map child product id to option
							$this->mapChildprodutToOption($optionDetail['itemattributeoption_id'], $chileProdItem_id);
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Save the product basic option.
	 *
	 * @param   object  $curr_post  Post objec.
	 *
	 * @since   2.2
	 * @return   null
	 */
	public function storecurrency($curr_post)
	{
		$itemname = $curr_post->get('item_name', '', 'STRING');
		$store_id = $curr_post->get('store_id', '', 'STRING');
		$pid      = $curr_post->get('pid', '', 'STRING');

		// @TODO check - ^sanjivani
		if (empty($pid))
		{
			// For native product manager
			$pid = $curr_post->get('item_id', '', 'STRING');
		}

		// @TODO check - sanjivani
		$client                    = $curr_post->get('client', '', 'STRING');
		$sku                       = $curr_post->get('sku', '', 'RAW');
		$res                       = '';
		$message                   = '';
		$comquick2cartHelper       = new comquick2cartHelper;
		$db                        = JFactory::getDbo();
		$params                    = JComponentHelper::getParams('com_quick2cart');

		// Used to store in kart_item table
		$kart_curr_param           = $params->get('addcurrency');
		$kart_curr_param_array     = explode(',', $kart_curr_param);
		$kart_item_curr            = $kart_curr_param_array[0];
		$quick2cartModelAttributes = new quick2cartModelAttributes;

		// @TODO check - ^sanjivani

		// $item_id = $quick2cartModelAttributes->getitemid($pid, $client, $sku);
		$item_id          = $quick2cartModelAttributes->getitemid($pid, $client);
		$img_dimensions   = array();
		$img_dimensions[] = 'small';
		$img_dimensions[] = 'medium';
		$img_dimensions[] = 'large';
		$image_path       = array();

		// STORING ALL IMAGES images upladed (on new or edit)
		foreach ($_FILES as $key => $imgfile)
		{
			// Only process Q2C image file. ()
			$position = strpos($key, 'prod_img');

			if (is_numeric($position) && !empty($imgfile['name']))
			{
				$image_path[] = $comquick2cartHelper->imageupload($key, $img_dimensions);
			}
		}

		$qtc_prodImgs = $curr_post->get('qtc_prodImg', array(), 'ARRAY');

		if (!empty($qtc_prodImgs))
		{
			foreach ($image_path as $newImg)
			{
				$qtc_prodImgs[] = $newImg;
			}

			// $image_path = $curr_post->get('qtc_prodImg', array(), 'ARRAY');
			$image_path = array_filter($qtc_prodImgs, "trim");
		}

		if (!empty($image_path))
		{
			$image_path = json_encode($image_path);
		}
		else
		{
			$image_path = '';
		}

		// @TODO save images and store in DB
		$images        = "";

		// GETTING ATTRIBUTE DETAILS,multi currency and discount details
		$att_detail    = $curr_post->get('att_detail', array(), 'ARRAY');
		$multi_cur     = $curr_post->get('multi_cur', array(), 'ARRAY');
		$multi_dis_cur = $curr_post->get('multi_dis_cur', array(), 'ARRAY');

		if (!$item_id)
		{
			$state = $curr_post->get('state');

			if (empty($state))
			{
				$state = 0;
			}

			// Call the trigger to add extra field in product page.
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin("system");
			$result = $dispatcher->trigger("onQuick2cartBeforeProductBasicDetailSave", array($curr_post, 'insert'));

			// Depricated
			$result = $dispatcher->trigger("beforeSavingProductBasicDetail", array($curr_post, 'insert'));

			// Save new product
			$item_id = $this->storeInKartItem('insert', $image_path, $multi_cur[$kart_item_curr], $curr_post);
		}
		else
		{
			// Dont set default value as 1 (require for unpublish)
			$state = $curr_post->get('state');

			if (isset($state))
			{
				$state = $state;
			}

			$productHelper = $comquick2cartHelper->loadqtcClass(JPATH_SITE . "/components/com_quick2cart/helpers/product.php", "productHelper");
			$productHelper->deleteNotReqProdImages($item_id, $image_path);

			// Call the trigger to add extra field in product page.
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin("system");
			$result = $dispatcher->trigger("onQuick2cartBeforeProductBasicDetailSave", array($curr_post, 'update'));

			// Depricated
			$result = $dispatcher->trigger("beforeSavingProductBasicDetail", array($curr_post, 'update'));

			$item_id = $this->storeInKartItem('update', $image_path, $multi_cur[$kart_item_curr], $curr_post);
		}

		$message = $item_id;
		$query   = "SELECT * FROM #__kart_base_currency  WHERE item_id = " . (int) $item_id;
		$db->setQuery($query);
		$res = $db->loadAssocList();

		if ($res)
		{
			foreach ($multi_cur as $cur_name => $cur_value)
			{
				$db   = JFactory::getDBO();
				$flag = 0;

				foreach ($res as $k => $v)
				{
					if ($cur_name == $v['currency'])
					{
						// Take currency value frm post and match whith Db currency
						$flag = 1;
						break;
					}
				}

				if ($flag == 1)
				{
					$dis_curr = (!empty($multi_dis_cur[$cur_name]) ? (float) $multi_dis_cur[$cur_name] : 'NULL');

					$dis_curr = " , discount_price=" . $dis_curr;

					$query    = "UPDATE #__kart_base_currency SET price=" . (float) $cur_value . " "
					. $dis_curr . " WHERE `item_id`=" . (int) $item_id . " AND `currency`='" . $cur_name . "'";
					$db->setQuery($query);
					$update = $db->execute();
				}
				else
				{
					$items                 = new stdClass;
					$items->item_id        = $item_id;
					$items->currency       = $cur_name;
					$items->price          = $cur_value;

					$items->discount_price = (!empty($multi_dis_cur[$cur_name])?(float) $multi_dis_cur[$cur_name]:'NULL');

					if (!$db->insertObject('#__kart_base_currency', $items))
					{
						$messagetype = 'notice';
						$message     = JText::_('QTC_PARAMS_SAVE_FAIL') . " - " . $db->stderr();
					}
				}
			}
		}
		else
		{
			// Curr_post contain INR ,USD etc .....
			foreach ($multi_cur as $cur_name => $cur_value)
			{
				$items           = new stdClass;
				$items->item_id  = $item_id;
				$items->currency = $cur_name;
				$items->price    = $cur_value;

				if (isset($multi_dis_cur[$cur_name]) && $multi_dis_cur[$cur_name] != '')
				{
					$items->discount_price = $multi_dis_cur[$cur_name];
				}

				if (!$db->insertObject('#__kart_base_currency', $items))
				{
					$messagetype = 'notice';
					$message     = JText::_('QTC_PARAMS_SAVE_FAIL') . " - " . $db->stderr();
				}
			}
		}

		return $message;
	}

	/**
	 * Function to get product alias
	 *
	 * @param   ARRAY  $curr_post  current post
	 *
	 * @return  null
	 */
	public function getAlias($curr_post)
	{
		// Alias added
		$alias = $curr_post->get('item_alias', '', 'STRING');
		$pid = $curr_post->get('pid', '', 'STRING');
		$client = $curr_post->get('client', '', 'STRING');
		$itemId = $this->getitemid($pid, $client);
		$alias = trim($alias);
		$db = JFactory::getDbo();

		if (empty($alias))
		{
			$alias = $curr_post->get('item_name', '', 'STRING');
		}

		if ($alias)
		{
			if (JFactory::getConfig()->get('unicodeslugs') == 1)
			{
				$alias = JFilterOutput::stringURLUnicodeSlug($alias);
			}
			else
			{
				$alias = JFilterOutput::stringURLSafe($alias);
			}
		}

		// Check if product with same alias is present
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_category/tables');
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_quick2cart/tables');
		$table = JTable::getInstance('Product', 'Quick2cartTable', array('dbo', $db));

		if ($table->load(array('alias' => $alias)) && ($table->item_id != $itemId || $itemId == 0))
		{
			$msg = JText::_('COM_QUICK2CART_SAVE_ALIAS_WARNING');

			while ($table->load(array('alias' => $alias)))
			{
				$alias = JString::increment($alias, 'dash');
			}

			JFactory::getApplication()->enqueueMessage($msg, 'warning');
		}

		// Check if category with same alias is present
		$category = JTable::getInstance('Category', 'JTable', array('dbo', $db));

		if ($category->load(array('alias' => $alias)))
		{
			$msg = JText::_('COM_QUICK2CART_SAVE_PRODUCT_WARNING_DUPLICATE_CATEGORY_ALIAS');

			while ($category->load(array('alias' => $alias)))
			{
				$alias = JString::increment($alias, 'dash');
			}

			JFactory::getApplication()->enqueueMessage($msg, 'warning');
		}

		$quick2cartViews = array('adduserform', 'createorder', 'productpage', 'shipprofileform', 'vendor',
			'attributes', 'customer_addressform', 'promotion', 'shipprofiles', 'zoneform', 'cart',
			'downloads', 'promotions', 'stores', 'zones', 'cartcheckout', 'taxprofileform', 'category',
			'orders', 'taxprofiles', 'couponform', 'payouts', 'registration', 'taxrateform', 'coupons',
			'product', 'shipping', 'taxrates');

		if (in_array($alias, $quick2cartViews))
		{
			$alias = JString::increment($alias, 'dash');

			while ($table->load(array('alias' => $alias)))
			{
				$alias = JString::increment($alias, 'dash');
			}
		}

		if (trim(str_replace('-', '', $alias)) == '')
		{
			$alias = JFactory::getDate()->format("Y-m-d-H-i-s");
		}

		return $alias;
	}

	/**
	 * Function to store in cart item
	 *
	 * @param   INT     $operation  operation
	 *
	 * @param   ARRAY   $images     currency
	 *
	 * @param   INT     $price      client
	 *
	 * @param   STRING  $curr_post  item id
	 *
	 * @return  null
	 */
	public function storeInKartItem($operation, $images, $price, $curr_post)
	{
		$db                  = JFactory::getDbo();
		$params              = JComponentHelper::getParams('com_quick2cart');
		$on_editor           = $params->get('enable_editor', 0);

		$kart_item           = new stdClass;
		$kart_item->parent   = $curr_post->get('client', '', 'STRING');
		$kart_item->store_id = $curr_post->get('store_id', '', 'INT');

		// @TODO check - ^manoj

		// $kart_item->store_id = $curr_post->get('current_store','','INT');
		$kart_item->product_id   = $curr_post->get('pid', '', 'INT');
		$kart_item->product_type = $curr_post->get('qtc_product_type', '', 'STRING');
		$kart_item->name         = $curr_post->get('item_name', '', 'STRING');
		$kart_item->alias        = $this->getAlias($curr_post);
		$kart_item->price        = $price;
		$kart_item->category     = $curr_post->get('prod_cat', '', 'INT');
		$kart_item->sku          = $curr_post->get('sku', '', 'RAW');
		$des                     = $curr_post->get('description', array(), 'ARRAY');
		$kart_item->display_in_product_catlog = 1;

		if (!$on_editor)
		{
			// Remove html when editor is OFF
			$kart_item->description = !empty($des['data']) ? strip_tags($des['data']) : '';
		}
		else
		{
			$kart_item->description = !empty($des['data']) ? $des['data'] : '';
		}

		$kart_item->video_link = $curr_post->get('youtube_link', '', 'STRING');

		// #40581 = temporary fix (For zoo state field is overlapping with item's state field)
		$stateField = $curr_post->get('qtcProdState', '', 'INT');

		if ($stateField === 0 || $stateField === 1)
		{
			$kart_item->state = $stateField;
		}
		else
		{
			$kart_item->state = $curr_post->get('state', '0', 'INT');
		}

		$stock = $curr_post->get('stock');

		// @HAVE TO CODE TO STORE IMAGES
		if ($stock !== "") // if stock is present it may be 0 But not NULL
		{
			$kart_item->stock = $stock;
		}

		$kart_item->metadesc             = $curr_post->get('metadesc', '', 'STRING');
		$kart_item->metakey              = $curr_post->get('metakey', '', 'STRING');
		$kart_item->item_length          = (FLOAT) $curr_post->get('qtc_item_length', '', 'STRING');
		$kart_item->item_width           = (FLOAT) $curr_post->get('qtc_item_width', '', 'STRING');
		$kart_item->item_height          = (FLOAT) $curr_post->get('qtc_item_height', '', 'STRING');
		$kart_item->item_length_class_id = $curr_post->get('length_class_id', '', 'INT');
		$kart_item->item_weight          = (FLOAT) $curr_post->get('qtc_item_weight', '', 'STRING');
		$kart_item->item_weight_class_id = $curr_post->get('weigth_class_id', '', 'INT');
		$kart_item->taxprofile_id        = $curr_post->get('taxprofile_id', '', 'INT');
		$kart_item->shipProfileId        = $curr_post->get('qtc_shipProfile', '', 'INT');

		$min_quantity = $curr_post->get('min_item', 1, 'INT');
		$max_quantity = $curr_post->get('max_item', 999, 'INT');

		if ($min_quantity == 0)
		{
			$kart_item->min_quantity = 1;
		}
		else
		{
			$kart_item->min_quantity = $min_quantity;
		}

		if ($max_quantity == 0)
		{
			$kart_item->max_quantity = 999;
		}
		else
		{
			$kart_item->max_quantity = $max_quantity;
		}

		$kart_item->slab = $curr_post->get('item_slab', '', 'INT');

		if ($operation == 'insert')
		{
			if (!empty($images))
			{
				$kart_item->images = $images;
			}

			$kart_item->cdate = date("Y-m-d");
			$kart_item->mdate = date("Y-m-d");

			if (!$db->insertObject('#__kart_items', $kart_item, 'item_id'))
			{
				$messagetype = 'notice';
				$message     = JText::_('QTC_PARAMS_SAVE_FAIL') . " - " . $db->stderr();
			}
			else
			{
				$inserid = $db->insertid();

				if ($kart_item->parent == "com_quick2cart")
				{
					$quick2cartModelAttributes = new quick2cartModelAttributes;
					$quick2cartModelAttributes->copyItemidToProdid($inserid);
				}
				// Add point to Community extension when product added into Quick2cart
				$params         = JComponentHelper::getParams('com_quick2cart');
				$integrate_with = $params->get('integrate_with', 'none');
				$user           = JFactory::getUser();

				if ($integrate_with != 'none')
				{
					$streamAddProd = $params->get('streamAddProd', 1);
					$point_system  = $params->get('point_system', '0');

					// According to integration create social lib class obj.
					$comquick2cartHelper = new comquick2cartHelper;
					$libclass            = $comquick2cartHelper->getQtcSocialLibObj();

					// Add in activity.
					if ($streamAddProd)
					{
						// Add social stream

						/*$actType = $actSubtype = $actLink = $actTitle = $actAccess = $title = $content = '';
						$actType = 'mini';
						$action = 'product_creation';
						$contextType = 'esqtcstream';
						$targetId = '';
						$actAccess = '';
						$title = ''; */

						$prodLink    = '<a class="" href="' . $comquick2cartHelper->getProductLink($inserid, 'detailsLink', 1) . '">' . $kart_item->name . '</a>';
						$store_info  = $comquick2cartHelper->getSoreInfo($kart_item->store_id);

						// @TODO AS ITEM_ID NOT

						/* $storeLink   = '<a class="" href="' . JUri::root() . substr(
						 * JRoute::_('index.php?option=com_quick2cart&view=vendor&layout=store&store_id=' . $kart_item->store_id),
						 *  strlen(JUri::base(true)) + 1) . '">' . $store_info['title'] . '</a>';*/
						$content = JText::sprintf('QTC_ACTIVITY_ADD_PROD', $prodLink, $store_info['title']);
						$libclass->pushActivity($user->id, $act_type = '', $act_subtype = '', $content, $act_link = '', $title = '', $act_access = '');
					}

					// Add points
					$point_system         = $params->get('point_system');
					$options['extension'] = 'com_quick2cart';

					if ($integrate_with == "EasySocial")
					{
						$options['command'] = 'add_product';
					}
					elseif ($integrate_with == "JomSocial")
					{
						$options['command'] = 'addproduct.points';
					}

					$libclass->addpoints($user, $options);
				}
			}

			return !empty($inserid)?$inserid:"";
		}
		elseif ($operation == 'update')
		{
			$kart_item->images         = $images;
			$kart_item->mdate          = date("Y-m-d");
			$pid                       = $curr_post->get('pid', '', 'STRING');
			$client                    = $curr_post->get('client', '', 'STRING');
			$sku                       = $curr_post->get('sku', '', 'RAW');
			$res                       = '';
			$quick2cartModelAttributes = new quick2cartModelAttributes;
			$item_id                   = $quick2cartModelAttributes->getitemid($pid, $client);
			$db                        = JFactory::getDBO();
			$kart_item->item_id        = $item_id;

			if (!$db->updateObject('#__kart_items', $kart_item, 'item_id'))
			{
				$message = JText::_('QTC_PARAMS_SAVE_FAIL') . " - " . $db->stderr();
			}

			return $item_id;
		}
	}

	/**
	 * Function to get currency value
	 *
	 * @param   INT     $pid      product id
	 *
	 * @param   INT     $curr     currency
	 *
	 * @param   STRING  $client   client
	 *
	 * @param   STRING  $item_id  item id
	 *
	 * @return  null
	 */
	public function getCurrenciesvalue($pid, $curr, $client, $item_id = '')
	{
		if (empty($item_id))
		{
			$quick2cartModelAttributes = new quick2cartModelAttributes;
			$item_id                   = $quick2cartModelAttributes->getitemid($pid, $client);
		}

		$db    = JFactory::getDBO();
		$query = "SELECT * FROM #__kart_base_currency  WHERE item_id = " . (int) $item_id . " AND currency='" . $curr . "'";
		$db->setQuery($query);
		$result = $db->loadAssocList();

		return $result;
	}

	/**
	 * Function to get item id
	 *
	 * @param   INT     $product_id  product id
	 *
	 * @param   STRING  $client      client
	 *
	 * @param   STRING  $sku         product sku
	 *
	 * @return  null
	 */
	public function getitemid($product_id = 0, $client = '', $sku = "")
	{
		$db = JFactory::getDBO();

		if (!empty($product_id))
		{
			$query = "SELECT `item_id` FROM `#__kart_items`  where `product_id`=" . (int) $product_id . " AND parent='$client'";
		}
		else
		{
			$query = "SELECT `item_id` FROM `#__kart_items`  where parent='" . $client . "' AND sku=\"" . $sku . "\"";
		}

		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * This item value
	 *
	 * @param   INT     $pid     product id
	 *
	 * @param   STRING  $client  client
	 *
	 * @return  null
	 */
	public function getItemvalue($pid, $client)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT `name` FROM `#__kart_items`  where `product_id`=" . (int) $pid . " AND parent='$client'";
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * This function gets option currency value
	 *
	 * @param   INT     $iap_id  option attribute id
	 * @param   STRING  $curr    currency
	 *
	 * @return  null
	 */
	public function getOption_currencyValue($iap_id, $curr)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT * FROM #__kart_option_currency WHERE `itemattributeoption_id`=" . (int) $iap_id . " AND currency='" . $curr . "'";
		$db->setQuery($query);
		$result = $db->loadAssocList();

		return $result;
	}

	/*
	 * This function update item stock,min_quantity,max_quantity from kart_items table ()
	 *
	 * @param  $pid product_id
	 * @param  $Stock status of item
	 * @param $min_qty minimum quanty to buy
	 * @param $max_qty max quanty to buy
	 * @param $max_qty max quanty to buy
	 * @client client Parent eg zoo,com_content etc.
	 **/

	/*function addItemStock($pid, $stock, $client, $min_qty, $max_qty)
	{
	$message = '';

	if (empty($min_qty)) $min_qty = 1;

	if (empty($max_qty)) $max_qty = 999;
	$quick2cartModelAttributes = new quick2cartModelAttributes();
	$item_id = $quick2cartModelAttributes->getitemid($pid, $client);
	$db = JFactory::getDBO();
	$updateobj = new stdClass;
	$updateobj->item_id = $item_id;

	if ($stock >= 0 && $stock != "") // if stock is present it may be 0 But not NULL

	{
	$updateobj->stock = $stock;
	}
	$updateobj->min_quantity = $min_qty;
	$updateobj->max_quantity = $max_qty;

	if (!$db->updateObject('#__kart_items', $updateobj, 'item_id'))
	{
	$message = JText::_('QTC_PARAMS_SAVE_FAIL') . " - " . $db->stderr();
	}
	return $message;
	}*/
	/*This function check whether attribute option is valid or not ( Data is sufficient or not)
	To save attribute atleast first option if filled
	*/

	/**
	 * This function to validate attribute options
	 *
	 * @param   ARRAY  $options  options
	 * @param   ARRAY  $index    index
	 *
	 * @return  null
	 */
	public function validateAttributeOption($options, $index = 0)
	{
		if ($options[$index]['name'] && $options[$index]['prefix'] && $options[$index]['order'])
		{
			// Of currency text count
			$noofcurr        = count($options[$index]['currency']);
			$filledcurr      = array_filter($options[$index]['currency'], 'strlen');

			// Count after removing empty fields
			$filledcurrCount = count($filledcurr);

			if ($filledcurrCount == $noofcurr)
			{
				return true;
			}

			return false;
		}

		return false;
	}

	/**
	 * This function delete attributeOptions
	 *
	 * @param   ARRAY  $data  data
	 *
	 * @return  null
	 */
	public function deleteAttributeOption($data)
	{
		$delete_attri = $data->get('delete_attri');
		$attri_id     = $data->get('attri_id');

		if (!empty($delete_attri) && !empty($attri_id))
		{
			$del_ids       = explode(',', trim($data->get('delete_attri', '', 'RAW')));

			// Remove only null/ empty element (keep zero)
			$del_ids_array = array_filter($del_ids, 'strlen');
			$del_ids       = implode(',', $del_ids_array);
			$db            = JFactory::getDBO();

			// Step 1. Delete attribute Option
			$query = "DELETE FROM `#__kart_itemattributeoptions` where `itemattribute_id`="
			. $data->get('attri_id') . " AND  `itemattributeoption_id` IN (" . $del_ids . ")";
			$db->setQuery($query);

			if (!$db->execute())
			{
				print $this->_db->getErrorMsg();

				return false;
			}

			// Step 2: after successful deletion :: Remove deleted option from data array
			$attri_opt = $data->get('attri_opt', array(), 'ARRAY');

			foreach ($attri_opt as $key => $option)
			{
				if (in_array($option['id'], $del_ids_array))
				{
					unset($attri_opt[$key]);
				}
			}
		}

		return $data;
	}

	/**
	 * This function delete attribute if doesn't have options
	 *
	 * @param   INT  $att_id  attribute id
	 *
	 * @return  null
	 *
	 * @since	2.5
	 */
	public function noOptionThenDelAttr($att_id)
	{
		if (!empty($att_id))
		{
			$db    = JFactory::getDBO();
			$query = "Select count(*) from `#__kart_itemattributeoptions` where itemattribute_id=" . $att_id;
			$db->setQuery($query);
			$count = $db->loadResult();

			// Delete attribute
			if ($count == 0)
			{
				$query = " delete from `#__kart_itemattributes` where itemattribute_id=" . $att_id;
				$db->setQuery($query);

				if (!$db->execute())
				{
					return $this->_db->getErrorMsg();
				}
			}
		}
	}

	/**
	 * This function removes invalid options
	 *
	 * @param   ARRAY  $data  option data
	 *
	 * @return  ARRAY
	 *
	 * @since	2.5
	 */
	public function removeInvalideOption($data)
	{
		foreach ($data['attri_opt'] as $key => $options)
		{
			$status = $this->validateAttributeOption($data['attri_opt'], $key);

			// Remove option from data
			if ($status == false)
			{
				unset($options[$key]);
			}
		}

		return $data;
	}

	/**
	 * This function return product details
	 *
	 * @param   INT  $product_id  product id
	 * @param   INT  $client      client id
	 * @param   INT  $item_id     item id
	 *
	 * @return  ARRAY
	 *
	 * @since	2.5
	 */
	public function getItemDetail($product_id = 0, $client = '', $item_id = "")
	{
		$db      = JFactory::getDBO();
		$colList = " `item_id`,`parent`,`product_id`,`store_id`,`name`,`stock`,`min_quantity`,`max_quantity`,`category`,`sku`,`images`,`description`,
			`video_link`,`state`,`featured`,params ";
		$colList = "*";

		if (!empty($item_id))
		{
			$query = 'SELECT ' . $colList . ' FROM `#__kart_items`  where `item_id`=' . (int) $item_id;
			$db->setQuery($query);
		}
		else
		{
			$query = 'SELECT ' . $colList . ' FROM `#__kart_items`  where `product_id`=' . (int) $product_id . " AND parent='$client'";
		}

		$db->setQuery($query);
		$result = $db->loadAssoc();

		return $result;
	}

	/**
	 * This function copy itemid to parent id (For client= com_quick2cart)
	 *
	 * @param   INT  $item_id  item id
	 *
	 * @return  integer  item_id Item id of Q2C product
	 *
	 * @since	2.5
	 */
	public function copyItemidToProdid($item_id)
	{
		$db                    = JFactory::getDBO();
		$kart_item             = new stdClass;
		$kart_item->product_id = $item_id;
		$kart_item->item_id    = $item_id;

		if (!$db->updateObject('#__kart_items', $kart_item, 'item_id'))
		{
			$message = JText::_('QTC_PARAMS_SAVE_FAIL') . " - " . $db->stderr();
		}
	}

	/**
	 * This function create the child product
	 *
	 * @param   ARRAY  $data  data need to create child product
	 *
	 * @return  child product id
	 *
	 * @since	2.5
	 */
	public function createChildProd($data)
	{
		$parent_prod_id = $data['item_id'];
		$attri_name = $data['attri_name'];
		$attri_opt = $data['attri_opt'];
		$optId = $data['itemattributeoption_id'];
		$childProductName = $parent_prod_id . "-" . $attri_name . "-" . $attri_opt['name'];

		$comquick2cartHelper = new comquick2cartHelper;
		$path                = JPATH_SITE . '/components/com_quick2cart/models/cart.php';
		$Quick2cartModelcart = $comquick2cartHelper->loadqtcClass($path, "Quick2cartModelcart");
		$parentDetail = $Quick2cartModelcart->getItemRec($parent_prod_id);

		// $this->storeInKartItem('insert', array(), array(), $parentDetail = new stdClass);
		$kart_item           = new stdClass;
		$kart_item->parent   = $parentDetail->parent;
		$kart_item->store_id = $parentDetail->store_id;
		$kart_item->display_in_product_catlog = 0;
		$kart_item->parent_id = $parent_prod_id;

		// @TODO have to update later

		// $kart_item->product_id   = $parentDetail->get('pid', '', 'INT');

		// Child product will be simple
		$kart_item->product_type = 1;
		$kart_item->name         = $childProductName;
		$kart_item->price        = 0;
		$kart_item->category     = $parentDetail->category;
		$kart_item->sku          = !empty($attri_opt['sku'])?$attri_opt['sku']:$childProductName . "-" . $optId;
		$kart_item->description = '';
		$kart_item->video_link = '';
		$kart_item->state = 1;
		$kart_item->stock = $attri_opt['stock'];

		/*	For now. not requore to set this options
		 * 	$kart_item->metadesc             = $parentDetail->get('metadesc', '', 'STRING');
		$kart_item->metakey              = $parentDetail->get('metakey', '', 'STRING');
		$kart_item->item_length          = $parentDetail->get('qtc_item_length', '', 'FLOAT');
		$kart_item->item_width           = $parentDetail->get('qtc_item_width', '', 'FLOAT');
		$kart_item->item_height          = $parentDetail->get('qtc_item_height', '', 'FLOAT');
		$kart_item->item_length_class_id = $parentDetail->get('length_class_id', '', 'INT');
		$kart_item->item_weight          = $parentDetail->get('qtc_item_weight', '', 'FLOAT');
		$kart_item->item_weight_class_id = $parentDetail->get('weigth_class_id', '', 'INT');
		$kart_item->taxprofile_id        = $parentDetail->get('taxprofile_id', '', 'INT');
		$kart_item->shipProfileId        = $parentDetail->get('qtc_shipProfile', '', 'INT');
		$kart_item->slab = $parentDetail->get('item_slab', '', 'INT');
		*/
		$kart_item->min_quantity = 1;
		$kart_item->max_quantity = 999;

		$operation = 'insertObject';

		if (!empty($attri_opt['child_product_item_id']))
		{
			$operation = 'updateObject';
			$kart_item->item_id = $attri_opt['child_product_item_id'];
		}

		if ($operation == 'insertObject')
		{
			$kart_item->cdate = date("Y-m-d");
			$kart_item->mdate = date("Y-m-d");
		}
		else
		{
			$kart_item->mdate          = date("Y-m-d");
		}

		$db = JFactory::getDbo();

		if (!$db->$operation('#__kart_items', $kart_item, 'item_id'))
		{
			$message = JText::_('QTC_PARAMS_SAVE_FAIL') . " - " . $db->stderr();
		}
		else
		{
			if ($kart_item->parent == "com_quick2cart" && $operation == 'insertObject')
			{
				$this->copyItemidToProdid($kart_item->item_id);
			}
		}

		return $kart_item->item_id;
	}

	/**
	 * This function map the child product's item id to main products option id
	 *
	 * @param   integer  $itemAttrOptionId  Item attribute's option id
	 * @param   integer  $childItem_id      Item id of Q2C product
	 *
	 * @return  flag
	 *
	 * @since	2.5
	 */
	public function mapChildprodutToOption($itemAttrOptionId, $childItem_id)
	{
		$db              = JFactory::getDBO();
		$opt             = new stdClass;
		$opt->itemattributeoption_id = $itemAttrOptionId;
		$opt->child_product_item_id = $childItem_id;

		try
		{
			if (!$db->updateObject('#__kart_itemattributeoptions', $opt, 'itemattributeoption_id'))
			{
				$message = JText::_('COM_QUICK2CART_UNABLE_TO_COPY_CHILD_ITEM_ID') . " - " . $db->stderr();
			}
		}
		catch (Exception $e)
		{
			echo $e->getMessage();

			return 0;
		}

		return 1;
	}
}
