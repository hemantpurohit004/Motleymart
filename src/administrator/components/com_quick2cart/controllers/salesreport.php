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

jimport('joomla.application.component.controlleradmin');

/**
 * Salesreport list controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerSalesreport extends JControllerAdmin
{
	/**
	 * Gives sales reports csv export.
	 *
	 * @since   2.2.2
	 * @return   null.
	 */
	public function csvexport()
	{
		$model   = $this->getModel("salesreport");
		$CSVData = $model->getCsvexportData();

		$filename      = "SalesReport_" . date("Y-m-d");
		$csvDataString = null;

		$headColumn    = array();
		$headColumn[0] = JText::_('COM_QUICK2CART_SALESREPORT_STORE_ITEMID');
		$headColumn[1] = JText::_('COM_QUICK2CART_SALESREPORT_PROD_NAME');
		$headColumn[2] = JText::_('COM_QUICK2CART_SALESREPORT_STORE_NAME');
		$headColumn[3] = JText::_('COM_QUICK2CART_SALESREPORT_STORE_ID');
		$headColumn[4] = JText::_('COM_QUICK2CART_SALESREPORT_SALES_COUNT');
		$headColumn[5] = JText::_('COM_QUICK2CART_SALESREPORT_AMOUNT');
		$headColumn[6] = JText::_('COM_QUICK2CART_SALESREPORT_CREATED_BY');

		$csvDataString .= implode(";", $headColumn);
		$csvDataString .= "\n";

		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: csv" . date("Y-m-d") . ".csv");
		header("Content-disposition: filename=" . $filename . ".csv");

		// Getting all store list
		$comquick2cartHelper = new comquick2cartHelper;
		$store_details       = $comquick2cartHelper->getAllStoreDetails();
		$productHelper = new productHelper;

		if (!empty($CSVData))
		{
			foreach ($CSVData as $data)
			{
				$store_id = $data['store_id'];

				$csvrow = array();

				$csvrow[0] = '"' . $data['item_id'] . '"';
				$csvrow[1] = '"' . $data['item_name'] . '"';
				$csvrow[2] = '""';

				if (!empty($store_details[$store_id]))
				{
					$csvrow[2] = '"' . $store_details[$store_id]['title'] . '"';
				}

				$csvrow[3] = '"' . $data['store_id'] . '"';
				$csvrow[4] = '"' . $data['saleqty'] . '"';

				// GETTING PRODUCT PRICE
				$prodAttDetails = $productHelper->getProdPriceWithDefltAttributePrice($data['item_id']);

				// CONSIDERING FIELD DISCOUNT, NOT COUPON DISCOUNT
				$discountPrice = $prodAttDetails['itemdetail']['discount_price'];
				$prodBasePrice = !empty($discountPrice) ? $discountPrice : $prodAttDetails['itemdetail']['price'];

				$prodPrice = $prodBasePrice + $prodAttDetails['attrDetail']['tot_att_price'];
				$prodPrice = strip_tags($comquick2cartHelper->getFromattedPrice($prodPrice));

				$csvrow[5] = '"' . $prodPrice . '"';

				$csvrow[6] = '""';

				if (!empty($store_details[$store_id]))
				{
					$csvrow[6] = '"' . $store_details[$store_id]['firstname'] . '"';
				}

				$csvDataString .= implode(";", $csvrow);
				$csvDataString .= "\n";
			}
		}

		ob_clean();
		echo $csvDataString . "\n";
		jexit();

		$link = 'index.php?option=com_quick2cart&view=salesreport';

		$this->setRedirect($link);
	}
}
