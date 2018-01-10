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

$lang = JFactory::getLanguage();

/**
 * Quick2cartControllerProduct
 *
 * @package     Com_Quick2cart
 * @subpackage  site
 * @since       2.2
 */
class Quick2cartControllerProduct extends quick2cartController
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		$comquick2cartHelper = new comquick2cartHelper;
		$this->productHelper = new productHelper;

		$this->my_products_itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=category&layout=my');
		$this->add_product_itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=product&layout=default');
		$this->vDashItemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=vendor&layout=cp');

		parent::__construct($config);
	}

	/**
	 * For add new
	 *
	 * @return  ''
	 *
	 * @since	2.2
	 */
	public function addNew()
	{
		$link = JRoute::_('index.php?option=com_quick2cart&view=product&layout=default&Itemid=' . $this->add_product_itemid, false);

		$this->setRedirect($link);
	}

	/**
	 * For Edit
	 *
	 * @return  ''
	 *
	 * @since	2.2
	 */
	public function edit()
	{
		$input = JFactory::getApplication()->input;
		$cid   = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);

		$comquick2cartHelper = new comquick2cartHelper;
		$edit_link           = $comquick2cartHelper->getProductLink($cid[0], 'editLink');

		$this->setRedirect($edit_link);
	}

	/**
	 * For Save
	 *
	 * @param   integer  $saveClose  action
	 *
	 * @return  ''
	 *
	 * @since	2.5
	 */
	public function save($saveClose = 0)
	{
		$params   = JComponentHelper::getParams('com_quick2cart');
		$app      = JFactory::getApplication();
		$jinput   = $app->input;
		$cur_post = $jinput->post;

		$sku = $cur_post->get('sku', '', "RAW");
		$sku = trim($sku);

		$current_store = $cur_post->get('current_store');

		if (!empty($current_store))
		{
			$app->setUserState('current_store', $current_store);
		}

		$item_name = $jinput->get('item_name', '', 'STRING');

		// $currencydata = $cur_post['multi_cur'];
		$pid       = $jinput->get('pid', 0, 'INT');
		$client    = 'com_quick2cart';
		$stock     = $jinput->get('stock', '', 'INTEGER');
		$min_qty   = $jinput->get('min_item');
		$max_qty   = $jinput->get('max_item');

		$item_id   = $jinput->get('Itemid', '', 'INTEGER');

		$link = JUri::base() . "index.php?option=com_quick2cart&view=product&Itemid=" . $item_id;

		if ($min_qty > $max_qty)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_QUICK2CART_QUANTITY_ERROR'), 'error');
			$this->setRedirect($link);

			return false;
		}

		$cat       = $jinput->get('prod_cat', '', 'INTEGER');

		// $sku   = $jinput->get('sku');

		$on_editor = $params->get('enable_editor', 0);

		if (empty($on_editor))
		{
			$des = $jinput->get('description', '', 'STRING');
		}
		else
		{
			$des_data = $jinput->get('description', array(), "ARRAY");
			$des      = $des_data["data"];
		}

		$youtubleLink = $jinput->get('youtube_link', '', "RAW");
		$store_id     = $jinput->get('store_id');
		$data         = array();

		// Get currency field count
		$multi_curArray = $cur_post->get('multi_cur', array(), 'ARRAY');
		$originalCount  = count($multi_curArray);

		//  Remove empty currencies from multi_curr
		$filtered_curr = array_filter($multi_curArray, 'strlen');

		// Get currency field count after filter empty allow 0
		$filter_count = count($filtered_curr);

		if ($item_name && $originalCount == $filter_count)
		{
			$model = $this->getModel('attributes');

			// @TODO REMOVE ALL PARAMETER AND SEND FORMATEED POST DATEA
			$comquick2cartHelper = new comquick2cartHelper;

			// Whether have to save attributes or not
			$cur_post->set('saveAttri', 1);
			$cur_post->set('saveMedia', 1);

			/* $item_id = $comquick2cartHelper->saveProduct(
			 * $pid,$client,$current_store,$item_name,$cur_post,$stock,$min_qty,$max_qty,$cat,$sku,$des,$youtubleLink
			 );
			Code done by sanjivani
			$jinput->set('saveAttri',1);  // whether have to save attributes or not
			$jinput->set('saveMedia',1);
			*/

			$item_id = $comquick2cartHelper->saveProduct($cur_post);

			if (is_numeric($item_id))
			{
				/*
				$prodmodel = $this->getModel('product');

				$admin_app = $params->get('admin_approval');
				$url_item_id = $jinput->get('item_id');

				if ($admin_app == 1 && $url_item_id == '')
				{
				While saving new product and admin approval set to 1
				$prodmodel->SendMailToAdminApproval($cur_post, $item_id, $newProduct = 1);
				$prodmodel->SendMailToOwner($cur_post);
				}

				$on_edit = $params->get('mail_on_edit');

				if ($on_edit == 1 && $url_item_id != '')
				{
				while editing new product and admin approval set to 0
				$prodmodel->SendMailToAdminApproval($cur_post, $item_id, $newProduct = 0);
				}
				*/

				// $prodmodel->StoreAllAttribute($item_id,$cur_post['att_detail'],$cur_post['sku'],'com_quick2cart'); // already stored in saveproduct funtion

				if ($saveClose == 1)
				{
					return 1;
				}
				// End Added by Sneha

				$app->setUserState('item_id', $item_id);

				$redirectUrl = JRoute::_('index.php?option=com_quick2cart&view=product&item_id=' . $item_id . '&Itemid=' . $this->vDashItemid, false);
				$this->setRedirect($redirectUrl, JText::_('C_SAVE_M_S'));
			}
			else
			{
				$redirectUrl = JRoute::_('index.php?option=com_quick2cart&view=product&Itemid=' . $this->vDashItemid, false);
				$this->setRedirect($redirectUrl, JText::_('C_SAVE_M_NS'));
			}
		}
		else
		{
			$redirectUrl = JRoute::_('index.php?option=com_quick2cart&view=product&Itemid=' . $this->vDashItemid, false);
			$this->setRedirect($redirectUrl, JText::_('C_FILL_COMPULSORY_FIELDS'));
		}
	}

	/**
	 * For checkSku
	 *
	 * @param   STRING  $sku  product sku
	 *
	 * @return  ''
	 *
	 * @since	2.5
	 */
	public function checkSku($sku = '')
	{
		$ajaxCall = 0;

		if (empty($sku))
		{
			$ajaxCall = 1;
		}

		if (empty($sku))
		{
			$jinput = JFactory::getApplication()->input;
			$sku    = $jinput->get('sku', '', 'RAW');
		}

		$model  = $this->getModel('product');
		$itemid = $model->getItemidFromSku($sku);

		$return = '';

		if (!empty($itemid))
		{
			$return = '1';
		}

		if ($ajaxCall == 1)
		{
			// Ajax call.
			echo $return;
			jexit();
		}

		return $return;
	}

	/**
	 * For saveAndClose
	 *
	 * @return  ''
	 *
	 * @since	2.5
	 */
	public function saveAndClose()
	{
		$Quick2cartControllerProduct = new Quick2cartControllerProduct;
		$Quick2cartControllerProduct->save(1);

		$redirectUrl = JRoute::_('index.php?option=com_quick2cart&view=category&layout=my&Itemid=' . $this->vDashItemid, false);
		$this->setRedirect($redirectUrl, JText::_('C_SAVE_M_S'));
	}

	/**
	 * For save and new
	 *
	 * @return  ''
	 *
	 * @since	2.5
	 */
	public function saveAndNew()
	{
		$Quick2cartControllerProduct = new Quick2cartControllerProduct;
		$Quick2cartControllerProduct->save(1);

		$redirectUrl = JRoute::_('index.php?option=com_quick2cart&view=product&item_id=' . $item_id . '&Itemid=' . $this->vDashItemid, false);
		$this->setRedirect($redirectUrl, JText::_('C_SAVE_M_S'));
	}

	/**
	 * For Cancel action
	 *
	 * @return  ''
	 *
	 * @since	2.5
	 */
	public function cancel()
	{
		$redirectUrl = JRoute::_('index.php?option=com_quick2cart&view=category&layout=my&Itemid=' . $this->vDashItemid, false);
		$this->setRedirect($redirectUrl);
	}

	/**
	 * This functio upload product media file called via ajax
	 *
	 * @return  ''
	 *
	 * @since	2.5
	 */
	public function mediaUpload()
	{
		$comquick2cartHelper = new comquick2cartHelper;
		$path                = JPATH_SITE . DS . "components" . DS . "com_quick2cart" . DS . "helpers" . DS . "media.php";
		$mediaHelper         = $comquick2cartHelper->loadqtcClass($path, 'qtc_mediaHelper');
		$mediaHelper->uploadProdFiles();
	}

	/**
	 * This function starts download
	 *
	 * @return  ''
	 *
	 * @since	2.5
	 */
	public function downStart()
	{
		$comquick2cartHelper         = new comquick2cartHelper;
		$Quick2cartControllerProduct = new Quick2cartControllerProduct;
		$productHelper               = new productHelper;
		global $mainframe;
		$mainframe       = JFactory::getApplication();
		$myDonloadItemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=downloads');
		$jinput          = JFactory::getApplication()->input;
		$file_id         = $jinput->get('fid', 0, 'INTEGER');
		$strorecall      = $jinput->get('strorecall', 0, 'INTEGER');
		$guest_email     = $jinput->get('guest_email', '', 'RAW');
		$orderid         = $jinput->get('orderid', 0, 'INTEGER');
		$order_item_id   = $jinput->get('order_item_id', 0, 'INTEGER');
		$authorize       = $productHelper->mediaFileAuthorise($file_id, $strorecall, $guest_email, $order_item_id);

		if (!empty($authorize['validDownload']) && $authorize['validDownload'] == 1)
		{
			$productHelper = new productHelper;
			$filepath      = $productHelper->getFilePathToDownload($file_id);

			// Download will start
			$down_status = $productHelper->download($filepath, '', '', 0);

			if ($down_status === 2)
			{
				// If filepath not exists The requested download file does not exists
				JFactory::getApplication()->enqueueMessage(JText::_('QTC_DOWNLOAD_FILEPATH_NOTEXISTS'), 'error');
				$redirect_base_url = 'index.php?option=com_quick2cart&view=downloads&Itemid=';
				$mainframe->redirect(JUri::root() . substr(JRoute::_($redirect_base_url . $myDonloadItemid), strlen(JUri::base(true)) + 1));
			}

			// Update file details  ( not for free files)
			elseif (!empty($authorize['orderItemFileId']))
			{
				// YOU WILL GET FOR THIS FIELD ONLY FOR PURCHASE REQUIRED FILE
				$productHelper->updateFileDownloadCount($authorize['orderItemFileId']); // kart_orderItemFiles tables primary key
			}

			// Exit tab
			return;
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_('QTC_DOWNLOAD_NOT_AUTHORIZED'), 'error');
			$redirect_base_url = 'index.php?option=com_quick2cart&view=downloads&Itemid=';
			$mainframe->redirect(JUri::root() . substr(JRoute::_($redirect_base_url . $myDonloadItemid), strlen(JUri::base(true)) + 1));
		}

		JFactory::getApplication()->enqueueMessage(JText::_('SOME_ERROR_OCCURRED'), 'error');
		$redirect_base_url = 'index.php?option=com_quick2cart&view=downloads&Itemid=';

		$mainframe->redirect(JUri::root() . substr(JRoute::_($redirect_base_url . $myDonloadItemid), strlen(JUri::base(true)) + 1));
	}

	/**
	 * This function gives tax profile list
	 *
	 * @return  ''
	 *
	 * @since	2.5
	 */
	public function getTaxprofileList()
	{
		$jinput   = JFactory::getApplication()->input;
		$store_id = $jinput->get('store_id');
		$selected = $jinput->get('selected');

		$storeHelper    = new storeHelper;
		$tax_listSelect = $storeHelper->getStoreTaxProfilesSelectList($store_id, $selected, 'taxprofile_id', $fieldClass = '', 'taxprofile_id');

		$html = '';

		if (!empty($tax_listSelect))
		{
			$html = $tax_listSelect;
		}
		else
		{
			$html .= ' <label>' . JText::_('COM_QUICK2CART_NO_TAXPROFILE_FOR_STORE') . '</label>';
		}

		$data['html'] = $html;
		echo json_encode($html);
		jexit();
	}

	/**
	 * Method to give availale shipprofile against store.
	 *
	 * @return  Json Plugin shipping methods list.
	 *
	 * @since	2.5
	 */
	public function qtcUpdateShipProfileList()
	{
		$app      = JFactory::getApplication();
		$jinput   = $app->input;
		$store_id = $jinput->get('store_id', 0, "INTEGER");

		$qtcshiphelper          = new qtcshiphelper;
		$response['selectList'] = $qtcshiphelper->qtcLoadShipProfileSelectList($store_id, '');
		echo json_encode($response);

		$app->close();
	}

	/**
	 * Method to get globle option for global attribute
	 *
	 * @return  json formatted option data
	 *
	 * @since	2.5
	 */
	public function loadGlobalAttriOptions()
	{
		$app                      = JFactory::getApplication();
		$post                     = $app->input->post;
		$response                 = array();
		$response['error']        = 0;
		$response['goption']      = '';
		$response['errorMessage'] = '';

		$globalAttId = $post->get("globalAttId", '', "INTEGER");

		// Get global options
		$goptions = $this->productHelper->getGlobalAttriOptions($globalAttId);

		// Generate option select box
		$layout = new JLayoutFile('addproduct.attribute_global_options');
		$response['goptionSelectHtml'] = $layout->render($goptions);

		if (empty($goptions))
		{
			$response['error']        = 1;
			$response['errorMessage'] = JText::_('COM_QUICK2CART_GLOBALOPTION_NOT_FOUND');
		}
		else
		{
			$response['goption'] = $goptions;
		}

		echo json_encode($response);
		$app->close();
	}

	/**
	 * Function to load products details
	 *
	 * @return  null
	 *
	 * @since	2.5.5
	 */
	public function loadProductDetails()
	{
		$userData     = array();
		$userData[]   = 'Textbox';
		$app = JFactory::getApplication();
		$item_id = $app->input->get('prodId', '', 'int');
		$prod_container_num = $app->input->get('prod_container_num', '', 'int');

		$model = new Quick2cartModelcart;
		$productDetail = $model->getItemRec($item_id);
		$productHelper = new productHelper;
		$comquick2cartHelper = new comquick2cartHelper;
		$price = $model->getPrice($item_id, 1);

		$prod_price = $model->getPrice($item_id, 1);
		$basePrice = 0;

		if (isset($prod_price['discount_price']) && !is_null($prod_price['discount_price']))
		{
			$basePrice = $prod_price['discount_price'];
		}
		else
		{
			$basePrice = $prod_price['price'];
		}

		$productData['price'] = $basePrice;

		$curr = $comquick2cartHelper->getCurrencySession();
		$curr_sym = $comquick2cartHelper->getCurrencySymbol($curr);
		$productData['curr_sym'] = $curr_sym;

		if (!empty($price['discount_price']))
		{
			$productData['discount_price'] = $comquick2cartHelper->getFromattedPrice($price['discount_price']);
		}

		$productData['min'] = $productDetail->min_quantity;
		$productData['max'] = $productDetail->max_quantity;

		if (!empty($productDetail))
		{
			// Get attributes

			$attributes = $productHelper->getItemCompleteAttrDetail($item_id);

			if (!empty($attributes))
			{
				$attributeCount = 1;

				foreach ($attributes as $attribute)
				{
					$data['extraHiddenFields'] = array();

					$data['itemattribute_id'] = $attribute->itemattribute_id;
					$data['fieldType'] = $attribute->attributeFieldType;
					$data['parent'] = $productDetail->parent;
					$data['product_id'] = $productDetail->item_id;
					$data['attribute_compulsary'] = $attribute->attribute_compulsary;
					$data['attributeDetail'] = $attribute;

					// This field is used to give name to attribute select box
					$data['field_name'] = "qtcorder_productdetails[" . $prod_container_num . '][att_option][' . $attribute->itemattribute_id . ']';

					// If textbox then change field name in array format
					if (in_array($data['fieldType'], $userData))
					{
						// For Text box field name
						$data['field_name'] = "qtcorder_productdetails[" . $prod_container_num . '][att_option][' . $data['itemattribute_id'] . '][value]';

						// For textbox's option: we need to add hidden field
						$tmpArray = array();
						$tmpArray["name"] = "qtcorder_productdetails[" . $prod_container_num . '][att_option][' . $data['itemattribute_id'] . '][option_id]';
						$tmpArray["value"] = $attribute->optionDetails[0]->itemattributeoption_id;
						$data['extraHiddenFields'][] = $tmpArray;
					}

					// This field is used to give onchange event to attribute select box
					$data['fieldOnChange'] = "qtc_update_product_price('" . $productDetail->item_id . "','" . $prod_container_num . "')";

					$layout = new JLayoutFile('attribute_option_display', $basePath = JPATH_ROOT . '/components/com_quick2cart/layouts/productpage');

					$attributesData["attribute" . $attributeCount]['html'] = $layout->render($data);
					$attributesData["attribute" . $attributeCount]['name'] = $attribute->itemattribute_name;

					$attributeCount++;
				}

				$productData['attribute_html'] = $attributesData;
			}
		}

		echo json_encode($productData);

		jexit();
	}

	/**
	 * Function to load products shipping details
	 *
	 * @return  null
	 *
	 * @since	2.5.5
	 */
	public function loadProductShippingDetails()
	{
		$qtcshiphelper = new qtcshiphelper;
		$params = JComponentHelper::getParams('com_quick2cart');
		$shippingEnabled = $params->get('shipping', 0);

		if ($shippingEnabled)
		{
			$app = JFactory::getApplication();
			$prod_id = $app->input->get('prodId', '', 'int');

			if (!empty($prod_id))
			{
				$profieId = $qtcshiphelper->getItemsShipProfileId($prod_id);
			}
			else
			{
				$itemWiseShipDetail = array();

				echo $itemWiseShipDetail;

				jexit();
			}

			$tamt = $app->input->get('tamt', '', 'int');
			$qty = $app->input->get('qty', '', 'int');
			$shipping = $app->input->get('shipping', '', 'int');
			$billing = $app->input->get('billing', '', 'int');

			if (empty($shipping) || empty($billing) || empty($qty) || empty($tamt))
			{
				return false;
			}

			// Load customer_addressform Model
			if (!class_exists("Quick2cartModelcustomer_addressform"))
			{
				JLoader::register("Quick2cartModelcustomer_addressform", JPATH_SITE . "/components/com_quick2cart/models/customer_addressform.php");
				JLoader::load("Quick2cartModelcustomer_addressform");
			}

			$customer_addressFormModel = new Quick2cartModelcustomer_addressform;

			// Load CreateOrderHelper
			if (!class_exists("CreateOrderHelper"))
			{
				JLoader::register("CreateOrderHelper", JPATH_SITE . "/components/com_quick2cart/helpers/createorder.php");
				JLoader::load("CreateOrderHelper");
			}

			$createOrderHelper = new CreateOrderHelper;

			$address = new stdclass;
			$address->ship_chk = 0;

			if (!empty($shipping))
			{
				$address->shipping_address = $customer_addressFormModel->getAddress($shipping);
				$address->shipping_address = $createOrderHelper->mapUserAddress($address->shipping_address);
			}

			if (!empty($billing))
			{
				$address->billing_address = $customer_addressFormModel->getAddress($billing);
				$address->billing_address = $createOrderHelper->mapUserAddress($address->billing_address);
			}

			if (!empty($profieId))
			{
				// Get shipping methods list.
				$shipMeths = $qtcshiphelper->getShipProfileMethods($profieId);

				if (!empty($shipMeths))
				{
					$shipDetail = array();
					$shipMethsDetail = array();
					$shipDetail['item_id'] = $prod_id;

					// Add current cart item detail
					$citem = array();
					$citem['item_id'] = $prod_id;
					$citem['qty'] = $qty;
					$citem['tamt'] = $tamt;

					$comquick2cartHelper = new comquick2cartHelper;
					$curr = $comquick2cartHelper->getCurrencySession();
					$citem['currency'] = $curr;
					$shipDetail['itemDetail'] = $citem;

					foreach ($shipMeths as $meth)
					{
						$methodId = $meth['methodId'];

						// $shipDetail['shippingMeths'] = $qtcshiphelper->getItemsShipMethods($citem['item_id'], $bill, $ship, $citem, $shipMeths);
						$shipDetail['shippingMeths'][$methodId] = $qtcshiphelper->getItemsShipMethods($prod_id, $address, $citem, $meth);
					}

					$itemWiseShipDetail[] = $shipDetail;
				}

				echo json_encode($itemWiseShipDetail);
			}
		}

		jexit();
	}
}
