<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die(';)');
jimport('joomla.html.html');
jimport('joomla.plugin.helper');

/**
 * qtczoneShipHelper
 *
 * @package     Com_Quick2cart
 * @subpackage  site
 * @since       2.2
 */
class QtczoneShipHelper
{
	/**
	 * For internal use. Handle view related actions.
	 *
	 * @param   object  $jinput    Joomla's jinput Object.
	 * @param   object  $plugview  plugin view.
	 *
	 * @since   2.2
	 * @return   URL param that have to add by component
	 */
	public function _viewHandler($jinput, $plugview = 'default')
	{
		$viewhandler = '_shipViewHandler_' . $plugview;
		$qtczoneShipHelper = new qtczoneShipHelper;

		return $actionDetail = $qtczoneShipHelper->$viewhandler($jinput);

		/*switch($plugview)
			{
				case 'createshipmeth':

					$actionStatus['urlPramStr'] = 'plugview=createshipmeth';
					$actionStatus['urlPramStr'] = $qtczoneShipHelper->_viewHandler($jinput);

				break;
				case 'saveshipmethod':
					$actionStatus['urlPramStr'] = 'plugview=createshipmeth';

				break;
			}*/
	}

	/**
	 * For internal use. Handle view related actions.
	 *
	 * @param   object  $jinput  Joomla's jinput Object.
	 *
	 * @since   2.2
	 * @return   URL param that have to add by component
	 */
	public function _shipViewHandler_default($jinput)
	{
		$qtcshiphelper = new qtcshiphelper;
		$post = $jinput->post;
		$plugtask = $post->get('plugtask', 'default');
		$actionStatus['urlPramStr'] = '';
		$actionStatus['statusMsg'] = '';

		switch ($plugtask)
		{
			case 'publish':
					$qtcshiphelper->changeShipMethState($jinput, 1);
					$actionStatus['urlPramStr'] = 'plugview=default';

					$actionStatus['statusMsg'] = JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_METH_PUBLISHED_SUCCESS');
			break;

			case 'unpublish':
					$qtcshiphelper->changeShipMethState($jinput, 0);
					$actionStatus['urlPramStr'] = 'plugview=default';

					$actionStatus['statusMsg'] = JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_METH_UNPUBLISHED_SUCCESS');
			break;

			case 'delete':
					$status = $qtcshiphelper->deleteShipMeth($jinput, 'qtc_default_zoneshipping');
					$actionStatus['urlPramStr'] = 'plugview=default';

					if (!empty($status))
					{
						$actionStatus['statusMsg'] = JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_METH_DELETED_SUCCESS');
					}
			break;
		}

		return $actionStatus;
	}

	/**
	 * For internal use. Handle view related actions.
	 *
	 * @param   object  $jinput  Joomla's jinput Object.
	 *
	 * @since   2.2
	 * @return   URL param that have to add by component
	 */
	public function _shipViewHandler_createshipmeth($jinput)
	{
		$qtcshiphelper = new qtcshiphelper;
		$post = $jinput->post;
		$plugtask = $post->get('plugtask', 'default');
		$actionStatus['urlPramStr'] = '';
		$actionStatus['statusMsg'] = '';

		switch ($plugtask)
		{
			case 'cancel':
				$actionStatus['urlPramStr'] = 'plugview=default';

			break;
			case 'newshipmeth':
				$actionStatus['urlPramStr'] = 'plugview=createshipmeth';

			break;
			case 'qtcshipMethodSave':
				$shipMethId = $qtcshiphelper->createShippingMethod($jinput);
				$actionStatus['urlPramStr'] = 'plugview=createshipmeth';

				if (!empty($shipMethId))
				{
					// Saved
					$actionStatus['urlPramStr'] = $actionStatus['urlPramStr'] . "&methodId=" . $shipMethId;
					$actionStatus['statusMsg'] = JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_METH_SAVED');
				}
				else
				{
					// Not saved
					$actionStatus['statusMsg'] = JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_METH_NOTSAVED');
				}
			break;
			case 'qtcshipMethodSaveAndClose':
				$shipMethId = $qtcshiphelper->createShippingMethod($jinput);
				$actionStatus['urlPramStr'] = 'plugview=default';

				if (!empty($shipMethId))
				{
					// Saved
					$actionStatus['urlPramStr'] = 'plugview=default';
					$actionStatus['statusMsg'] = JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_METH_SAVED');
				}
				else
				{
					// Not saved
					$actionStatus['statusMsg'] = JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_METH_NOTSAVED');
				}
			break;
		}

		return $actionStatus;
	}

	/**
	 * For internal use. Handle view related actions.
	 *
	 * @param   STRING  $plugLayout  layout
	 * @param   STRING  $jinput      Joomla's jinput Object.
	 *
	 * @since   2.2
	 * @return   URL param that have to add by component
	 */
	public function _qtcLoadViewData($plugLayout = 'default', $jinput = '')
	{
		$qtczoneShipHelper = new qtczoneShipHelper;

		// $viewhandler = '_qtcLoadViewDataFor' . $plugLayout;
		$data = array();

		switch ($plugLayout)
		{
			case 'createshipmeth': $data = $qtczoneShipHelper->loadViewDataForCreateshipmeth($jinput);
			break;

			case 'setrates' : $data = $qtczoneShipHelper->loadViewDataForSetratesView($jinput);
			break;

			case 'editrate' : $data = $qtczoneShipHelper->loadViewDataForEditrate($jinput);
			break;

			case 'default':
				$data = $qtczoneShipHelper->ViewDataForShipmethListView($jinput);
			break;
		}

		// $aretirnUrlPramStr = $qtczoneShipHelper->$viewhandler($jinput);
		return $data;
	}

	/**
	 * function to get store log
	 *
	 * @param   STRING  $name     store name.
	 * @param   STRING  $logdata  log data.
	 *
	 * @since   2.2
	 * @return   Layoutdata.
	 */
	public function Storelog($name,$logdata)
	{
		jimport('joomla.error.log');
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";

		$path = JPATH_SITE . '/plugins/payment/' . $name . '/' . $name . '/';

		$my = JFactory::getUser();

		JLog::addLogger(
			array(
				'text_file' => $logdata['JT_CLIENT'] . '_' . $name . '.log',
				'text_entry_format' => $options ,
				'text_file_path' => $path
			),
			JLog::INFO,
			$logdata['JT_CLIENT']
		);

		$logEntry = new JLogEntry('Transaction added', JLog::INFO, $logdata['JT_CLIENT']);
		$logEntry->user = $my->name . '(' . $my->id . ')';
		$logEntry->desc = json_encode($logdata['raw_data']);

		JLog::add($logEntry);

		// $logs = &JLog::getInstance($logdata['JT_CLIENT'].'_'.$name.'.log',$options,$path);

		// $logs->addEntry(array('user' => $my->name.'('.$my->id.')','desc'=>json_encode($logdata['raw_data'])));
	}

	/**
	 * This function load data which is require for creatshipmeth layout (INTERNAL USE ONLY).
	 *
	 * @param   object  $jinput  Joomla's jinput Object.
	 *
	 * @since   2.2
	 * @return   Layoutdata.
	 */
	public function loadViewDataForCreateshipmeth($jinput)
	{
		$shipForm = array();
		$shipForm['methodId'] = $shipMethId = $jinput->get('methodId', 0);

		// $shipForm['methodName'] = ;

		$qtczoneShipHelper = new qtczoneShipHelper;
		$taxHelper = new taxHelper;

		if (!empty($shipMethId))
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select(" id AS methodId,name,store_id,taxprofileId,state,shipping_type,min_value,max_value")
			->from('#__kart_zoneShipMethods')
			->where('id=' . $shipMethId);
			$db->setQuery((string) $query);
			$shipForm = $db->loadAssoc();

			// If ship type= price per store item
			if ($shipForm['shipping_type'] == 3 )
			{
				$query = $db->getQuery(true);
				$query->select(" mc.*")
				->from('#__kart_zoneShipMethodCurr AS mc')
				->where('mc.methodId=' . $shipMethId);
				$db->setQuery((string) $query);
				$shipMethCurr = $db->loadAssocList();

				$shipForm['shipMethCurr'] = $shipMethCurr;
			}

			// Get tax profiles
			$shipForm['taxprofiles'] = $taxHelper->getUsersTaxprofiles();
		}
		else
		{
			$shipForm['store_id'] = '';
			$shipForm['methodType'] = 0;
			$shipForm['taxprofileId'] = 0;
			$shipForm['state'] = 1;
			$shipForm['shipping_type'] = 0;

			// Get tax profiles
			$shipForm['taxprofiles'] = $taxHelper->getUsersTaxprofiles();
		}

		return $shipForm;
	}

	/**
	 * Load view data for setrates view
	 *
	 * @param   object  $jinput  Joomla's jinput Object.
	 *
	 * @since   2.2
	 * @return   Layoutdata.
	 */
	public function loadViewDataForSetratesView($jinput)
	{
		$taxHelper = new taxHelper;
		$qtczoneShipHelper = new qtczoneShipHelper;
		$qtcshiphelper = new qtcshiphelper;

		$shipFormData = array();
		$rateId = $jinput->get('rateId', 0);
		$methodId = $jinput->get('methodId', 0);

		$shipMethDetail = $qtcshiphelper->getShipMethDetail($methodId);

		// Load Zone helper.
		$path = JPATH_SITE . "/components/com_quick2cart/helpers/zoneHelper.php";

		// If (!class_exists('zoneHelper'))
		{
			JLoader::register('zoneHelper', $path);
			JLoader::load('zoneHelper');
		}

		$zoneHelper = new zoneHelper;

		// Get user's accessible zone list
		$shipFormData['zonelist'] = $zoneHelper->getStoreZoneList($shipMethDetail['store_id']);
		$shipFormData['ratelist'] = array();

		if (!empty($methodId))
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select(" id AS rateId,methodId,zone_id,rangeFrom,rangeTo")
			->from('#__kart_zoneShipMethodRates')
			->where('methodId=' . $methodId);
			$db->setQuery((string) $query);
			$rateList = $db->loadAssocList();

			if (!empty($rateList))
			{
				foreach ($rateList as $key => $rate)
				{
					$query = $db->getQuery(true);
					$query->select(" id AS rateCurrId,rateId,shipCost,handleCost,currency")
					->from('#__kart_zoneShipMethodRateCurr')
					->where('rateId=' . $rate['rateId']);
					$db->setQuery((string) $query);
					$rateList[$key]['rateCurrDetails'] = $db->loadAssocList();
				}
			}

			$shipFormData['ratelist'] = $rateList;
		}

		return $shipFormData;
	}

	/**
	 * This function load data which is require for creatshipmeth layout (INTERNAL USE ONLY).
	 *
	 * @param   object  $jinput  Joomla's jinput Object.
	 *
	 * @since   2.2
	 * @return   Layoutdata.
	 */
	public function ViewDataForShipmethListView($jinput)
	{
		$storeHelper = new storeHelper;

		// Get all stores.
		$user = JFactory::getUser();

		$storeList = $storeHelper->getUserStore($user->id);
		$storeIds = array();

		foreach ($storeList as $store)
		{
			$storeIds[] = $store['id'];
		}

		$accessibleStoreIds = '';

		if (!empty($storeIds))
		{
			$accessibleStoreIds = implode(',', $storeIds);
		}

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select(" id AS methodId,name,taxprofileId,state,shipping_type,min_value,max_value")
		->from('#__kart_zoneShipMethods');
		$query->where('(store_id IN (' . $accessibleStoreIds . '))');
		$db->setQuery((string) $query);
		$data = $db->loadAssocList();

		return $data;
	}

	/**
	 * This function gives ship type name (eg .weight based per item) from method type (eg 1,2,3 etc).
	 *
	 * @param   integer  $methType  method type.
	 *
	 * @since   2.2
	 * @return   Method name.
	 */
	public function getShipTypeName($methType)
	{
		$methodName = "";

		switch ($methType)
		{
			case 3:
				return  JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_STORE_ITEM');
			break;

			case 1:
				return  JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_STORE_QTY');
			break;

			case 2:
				return  JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_STORE_WEIGHT');
			break;
		}
	}

	/**
	 * This function load data which is require for edit rate layout (INTERNAL USE ONLY).
	 *
	 * @param   object  $jinput  Joomla's jinput Object.
	 *
	 * @since   2.2
	 * @return   Layoutdata.
	 */
	public function loadViewDataForEditrate($jinput)
	{
		$qtcshiphelper = new qtcshiphelper;
		$shipFormData = array();
		$rateId = $jinput->get('rateId', 0);

		if (!empty($rateId))
		{
			// Get rate detials
			$shipFormData['rateDetail'] = $qtcshiphelper->getShipMethRateDetail($rateId);
		}

		$qtcshiphelper = new qtcshiphelper;

		// Load Zone helper.
		$path = JPATH_SITE . "/components/com_quick2cart/helpers/zoneHelper.php";

		// If (!class_exists('zoneHelper'))
		{
			JLoader::register('zoneHelper', $path);
			JLoader::load('zoneHelper');
		}

		$zoneHelper = new zoneHelper;

		// Get user's accessible zone list
		$shipFormData['zonelist'] = $zoneHelper->getUserZoneList();

		return $shipFormData;
	}

	/**
	 * This method return array available shipping plugin methods
	 *
	 * @param   INT  $store_id  store id
	 *
	 * @since   2.2
	 * @return   Array of shipping method detail.
	 */
	public function getAvailableShipMethods($store_id)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select(" id AS methodId,name")
		->from('#__kart_zoneShipMethods')
		->where('store_id=' . $store_id);
		$db->setQuery((string) $query);
		$data = $db->loadAssocList();

		return $data;
	}

	/**
	 * This method return shipping methods detail.
	 *
	 * @param   INT  $shipMethId  shipmethodid
	 *
	 * @since   2.2
	 * @return   Array of shipping method detail.
	 */
	public function getShipMethodDetail($shipMethId)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select(" id AS methodId,name,store_id,taxprofileId,state,shipping_type,min_value,max_value")
		->from('#__kart_zoneShipMethods')
		->WHERE('id = ' . $shipMethId);
		$db->setQuery((string) $query);
		$data = $db->loadAssoc();

		return $data;
	}

	/**
	 * This method provide aplicable shipping charge detail using for provided shipping method.
	 *
	 * @param   object  $vars  gives billing, shipping, item_id, methodId(unique plug shipping method id) etc.
	 *
	 * @since   2.2
	 * @return  Shipping charges.
	 */
	public function getShipMethodChargeDetail($vars)
	{
		$shipMethDetail = $this->getShipMethodDetail($vars->shipMethId);

		// 1.Check method is applicable or not (Min & max amount) etc.
		$shipMethRateDetail = $this->getApplicableShipMethRateDetail($vars, $shipMethDetail);
		$ShippingCharges = 0;

		if ($shipMethRateDetail !== false)
		{
			$ShippingCharges = $shipMethRateDetail['shipCost'] + $shipMethRateDetail['handleCost'];
		}

		$comp_params = JComponentHelper::getParams('com_quick2cart');
		$isTaxationEnabled = $comp_params->get('enableTaxtion', 0);

		// 2. check shipping tax charges
		if (!empty($shipMethDetail['taxprofileId']) && $isTaxationEnabled)
		{
			$address = new stdClass;
			$address->billing_address = $vars->billing_address;
			$address->shipping_address = $vars->shipping_address;
			$address->ship_chk = $vars->ship_chk;

			// $itemTaxDetails[$item_id] = $taxHelper->getItemTax($citem['product_final_price'], $citem['item_id'], $address);

			// LOAD tax helper
			$path = JPATH_SITE . '/components/com_quick2cart/helpers/taxHelper.php';

			if (!class_exists('productHelper'))
			{
				// Require_once $path;
				JLoader::register('taxHelper', $path);
				JLoader::load('taxHelper');
			}

			$taxHelper = new taxHelper;
			$taxDetail = $taxHelper->getItemTax($ShippingCharges, $vars->cartItemDetail['item_id'], $address, $shipMethDetail['taxprofileId']);
		}

		if ($shipMethRateDetail !== false)
		{
			$shipTaxDetail = isset($taxDetail) ? $taxDetail['taxdetails'] : array();
			$taxOnShipCharge = isset($taxDetail) ? $taxDetail['taxAmount'] : 0;

			$retData['item_id'] = $vars->cartItemDetail['item_id'];
			$retData['methodId'] = $shipMethDetail['methodId'];
			$retData['name'] = $shipMethDetail['name'];

			$retData['totalShipCost'] = $ShippingCharges + $taxOnShipCharge;

			// This is the unique id of method's price entry id. Which will be used further to revalidate price
			$retData['plugMethRateId'] = $shipMethRateDetail['id'];

			if (!empty($vars->cartItemDetail['product_attributes']))
			{
				$retData['product_attributes'] = $vars->cartItemDetail['product_attributes'];
			}

			if (!empty($vars->cartItemDetail['product_attribute_names']))
			{
				$retData['product_attribute_names'] = $vars->cartItemDetail['product_attribute_names'];
			}

			return $retData;
		}
	}

	/**
	 * This method Check method is applicable or not (Min & max amount) etc..
	 *
	 * @param   object  $vars            gives billing, shipping, item_id, methodId(unique plug shipping method id) etc.
	 *
	 * @param   object  $shipMethDetail  ship method details
	 *
	 * @since   2.2
	 * @return  Shipping charges.
	 */
	public function getApplicableShipMethRateDetail($vars, $shipMethDetail)
	{
		// Get zone id from address
		$shipMethId = $vars->shipMethId;
		$shipping_address = $vars->shipping_address;

		// Get item cart detail
		$cartItemDetail = $vars->cartItemDetail;
		$currency = $cartItemDetail['currency'];

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('mr.id AS rateId');
		$query->from('#__kart_zonerules AS zr');
		$query->join('LEFT', '#__kart_zoneShipMethodRates AS mr ON mr.zone_id = zr.zone_id');
		$query->where('mr.methodId=' . $shipMethId);

		$applicable = 0;

		if (!empty($shipMethDetail))
		{
			if ($shipMethDetail['shipping_type'] == 1)
			{
				// Check wether quantity is within rage
				if (($shipMethDetail['min_value'] <= $cartItemDetail['qty'])
					&& ((int) $shipMethDetail['max_value'] == -1
					|| $cartItemDetail['qty'] <= $shipMethDetail['max_value']))
				{
					$applicable = 1;
				}

				$query->where('mr.rangeFrom <=' . $cartItemDetail['qty']);
				$query->where('mr.rangeTo >=' . $cartItemDetail['qty']);
			}
			elseif ($shipMethDetail['shipping_type'] == 2)
			{
				// Ship  method type= weight
				$qtcshiphelper = new qtcshiphelper;
				$productDetail = $vars->productDetail;

				// Get product wt and weight class id
				$itemWt = $productDetail['item_weight'];
				$itemWt = $itemWt * $cartItemDetail['qty'];
				$fromWtClass = $productDetail['item_weight_class_id'];

				// Get Base unit
				$unit = $this->getWeightUniteSymbol();

				// Get KG weiht class id
				$toWtClassid = $qtcshiphelper->getWeightDetailFromUnite($unit);

				// Convert item weight
				$newItemWt = (float) $qtcshiphelper->convertWeight($itemWt, $fromWtClass, $toWtClassid['id']);

				// Check whether mehod
				if (((float) $shipMethDetail['min_value'] <= $newItemWt)
					&& ((int) $shipMethDetail['max_value'] == -1
					|| $newItemWt <= (float) $shipMethDetail['max_value']))
				{
					$applicable = 1;
				}

				$query->where('mr.rangeFrom <=' . $newItemWt);
				$query->where('mr.rangeTo >=' . $newItemWt);

				// Get applicable rate
			}
			else
			{
				//  For flat rate pers store method. Get methods price rel things
				$methDetail = $this->getPriceRelMethDetail($shipMethId, $currency);

				// For flat rate per store item price.
				if (($methDetail['min_value'] <= $cartItemDetail['tamt'])
					&& ((int) $methDetail['max_value'] == -1
					|| $cartItemDetail['tamt'] <= $methDetail['max_value']))
				{
					$applicable = 1;
				}

				// Range field are not present for flat rate per store method
			}
		}

		// If method is not applicable
		if ($applicable == 0)
		{
			return false;
		}

		$query->where('zr.country_id=' . (int) $shipping_address['country']);
		$query->where("( zr.region_id = 0 OR zr.region_id = " . (int) $shipping_address['state'] . ')');
		$db->setQuery($query);
		$rateId = $db->loadResult();

		// If rate is not i.e  not applicable
		if (empty($rateId))
		{
			return false;
		}

		// Get method rate details;
		return $shipMethRateDetail = $this->getShipMethRateDetail($rateId, $currency);
	}

	/**
	 * For price releated methods, get price related things ..
	 *
	 * @param   integer  $methodId  shipping method id.
	 * @param   integer  $currency  current currency.
	 *
	 * @since   2.2
	 * @return  shipping method rate Details.
	 */
	public function getPriceRelMethDetail($methodId, $currency)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('mc.id,mc.min_value,mc.max_value,mc.currency');
		$query->from('#__kart_zoneShipMethodCurr AS mc');
		$query->where('mc.methodId=' . $methodId);
		$query->where("mc.currency='" . $currency . "'");
		$db->setQuery($query);

		return $shipMethRateDetail = $db->loadAssoc();
	}

	/**
	 * This method provide all detail about shipping methods charges..
	 *
	 * @param   integer  $shipMethRateId  Applicable shipping method rate.
	 * @param   integer  $currency        current currency.
	 *
	 * @since   2.2
	 * @return  shipping method rate Details.
	 */
	public function getShipMethRateDetail($shipMethRateId, $currency)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('rc.id,rc.rateId,rc.shipCost,rc.handleCost,rc.currency');
		$query->from('#__kart_zoneShipMethodRateCurr AS rc');
		$query->where('rc.rateId=' . $shipMethRateId);
		$query->where("rc.currency='" . $currency . "'");
		$db->setQuery($query);

		return $shipMethRateDetail = $db->loadAssoc();
	}

	/**
	 * This return weight unite symbol.
	 *
	 * @param   ARRAY  $fieldData  fielddata
	 *
	 * @since   2.2
	 * @return  symbol.
	 */
	public function getFieldHtmlForShippingType($fieldData)
	{
		$productHelper = new productHelper;
		$shipping_type = !empty($fieldData['shipping_type']) ? $fieldData['shipping_type'] : 0;
		$fieldHtml = '';

/*		if ($limitLield  == 'MIN')
		{
			$fieldName = "shipForm[min_value]";
		}
		else
		{
			$fieldName = "shipForm[max_value]";
		}
*/

		$minFieldHtml = '';
		$maxFieldHtml = '';

		switch ($shipping_type)
		{
			case 3 :

			$currFieldValues = !empty($fieldData['DefFieldValues']['min']) ? $fieldData['DefFieldValues']['min'] :array();
			$minFieldHtml = $productHelper->getMultipleCurrFields($name = 'shipForm[min_value]', $currFieldValues);

			//  Get maximum field detail
			$currFieldValues = !empty($fieldData['DefFieldValues']['max']) ? $fieldData['DefFieldValues']['max'] :array();

			$maxFieldHtml = $productHelper->getMultipleCurrFields($name = 'shipForm[max_value]', $currFieldValues);
			$maxFieldHtml .= '<p class="text-info">' . JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_MAXIMUM_AMT_HELP") . '</p>';

			$fieldLable = JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_PRICE_LABLE");

			break;

			case 1 || 2 || 0:
			// Minimum amount field
			$minFieldAmount = !empty($fieldData['minFieldAmt']) ? $fieldData['minFieldAmt'] : 0;
			$minFieldName = !empty($fieldData['minFieldName']) ? $fieldData['minFieldName'] : 'shipForm[min_value]';
			$minFieldId = !empty($fieldData['minFieldId']) ? $fieldData['minFieldId'] : 'qtcMinAmount';

			$minFieldHtml = '<input id="' . $minFieldId . '" name="' .
			$minFieldName . '" class=" required validate-name" type="text" value="' . $minFieldAmount . '">';

			// Max amount field
			$maxFieldAmount = !empty($fieldData['maxFieldAmt']) ? $fieldData['maxFieldAmt'] : "-1";
			$maxFieldName = !empty($fieldData['maxFieldName']) ? $fieldData['maxFieldName'] : 'shipForm[max_value]';
			$maxFieldId = !empty($fieldData['maxFieldId']) ? $fieldData['maxFieldId'] : 'qtcMaxAmount';

			$maxFieldHtml = '<input id="' . $maxFieldId . '" name="' .
			$maxFieldName . '" class="required validate-name" type="text" value="' . $maxFieldAmount . '">
			<p class="text-info">' . JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_MAXIMUM_AMT_HELP") . '</p>';

				switch ($shipping_type)
				{
					case 1;
						$fieldLable = JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_QTY_LABLE");
					break;

					case 2;
						$fieldLable = JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_WT_LABLE");
					break;

					case 0;
						$fieldLable = '';
					break;
				}
			break;
		}

		$return['minFieldHtml'] = $minFieldHtml;
		$return['maxFieldHtml'] = $maxFieldHtml;
		$return['minFieldLable'] = JText::sprintf("PLG_QTC_DEFAULT_ZONESHIPPING_MIN_LMIT", $fieldLable);
		$return['maxFieldLable'] = JText::sprintf("PLG_QTC_DEFAULT_ZONESHIPPING_MAX_LMIT", $fieldLable);

		return json_encode($return);
	}

	/**
	 * This return weight unite symbol.
	 *
	 * @since   2.2
	 * @return  symbol.
	 */
	public function getWeightUniteSymbol()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('w.unit');
		$query->from('#__kart_weights AS w');
		$query->where('w.value=1');
		$query->where('w.state=1');
		$query->order('w.id');
		$db->setQuery($query);

		return $shipMethRateDetail = $db->loadResult();
	}
}
