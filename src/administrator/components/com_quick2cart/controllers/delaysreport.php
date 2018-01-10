<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die( 'Restricted access' );

$lang = JFactory::getLanguage();
$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);

jimport('joomla.application.component.controller');

/**
 * reports controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerDelaysreport extends quick2cartController
{
	/**
	 * CSV export
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	public function  csvexport()
	{
		$model = $this->getModel("delaysreport");
		$CSVData = $model->getCsvexportData();
		$filename = "DelaysReport_" . date("Y-m-d");
		$csvData = null;

		// $csvData.= "Item_id;Product Name;Store Name;Store Id;Sales Count;Amount;Created By;";

		$headColumn = array();

		// $headColumn[0] = JText::_('COM_QUICK2CART_DELAYSREPORT_ID');

		// 'Product Name';
		$headColumn[1] = JText::_('COM_QUICK2CART_DELAYSREPORT_ORDER_ID');
		$headColumn[2] = JText::_('COM_QUICK2CART_DELAYSREPORT_DAYS');
		$headColumn[3] = JText::_('COM_QUICK2CART_DELAYSREPORT_BUYER');
		$headColumn[4] = JText::_('COM_QUICK2CART_DELAYSREPORT_STATUS');
		$csvData .= implode(";", $headColumn);
		$csvData .= "\n";
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: csv" . date("Y-m-d") . ".csv");
		header("Content-disposition: filename=" . $filename . ".csv");

		if (!empty($CSVData))
		{
			$storeHelper = new storeHelper;

			foreach ($CSVData as $data)
			{
				$csvrow = array();
				$csvrow[1] = '"' . $data['prefix'] . $data['id'] . '"';
				$delay = $storeHelper->GetDelaysInOrder($data['id']);

				if ($delay)
				{
					$csvrow[2] = '"' . $delay . '"';
				}
				else
				{
					$csvrow[2] = '"-"';
				}

				$csvrow[3] = '"' . $data['name'] . '"';

				if ($data['status'] == 'C')
				{
					$status = JText::_('ORDER_CONFIRMED');
				}
				elseif ($data['status'] == 'S')
				{
					$status = JText::_('ORDER_SHIPPED');
				}
				else
				{
					$status = JText::_('ORDER_CANCELLED');
				}

				$csvrow[4] = '"' . $status . '"';
				$csvData .= implode(";", $csvrow);
				$csvData .= "\n";
			}
		}

		ob_clean();
		echo $csvData . "\n";
		jexit();
		$link = JUri::base() . substr(JRoute::_('index.php?option=com_quick2cart&view=delaysreport', false), strlen(JUri::base(true)) + 1);
		$this->setRedirect($link);
	}
}
