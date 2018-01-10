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
class Quick2cartControllerReports extends quick2cartController
{
	/**
	 * Function to save reports
	 *
	 * @return  null
	 *
	 * @since 1.5
	 * */
	public function save()
	{
		JSession::checkToken()( or jexit('Invalid Token');

		// Get model
		$model = $this->getModel('reports');
		$result = $model->savePayout();
		$redirect = JRoute::_('index.php?option=com_quick2cart&view=reports&layout=payouts', false);

		if ($result)
		{
			$msg = JText::_('COM_QUICK2CART_PAYOUT_SAVED');
		}
		else
		{
			$msg = JText::_('COM_QUICK2CART_PAYOUT_ERROR_SAVING');
		}

		$this->setRedirect($redirect, $msg);
	}

	/**
	 * Function to edit pay
	 *
	 * @return  null
	 *
	 * @since  1.5
	 * */
	public function edit_pay()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('reports');
		$result = $model->editPayout();

		$redirect = JRoute::_('index.php?option=com_quick2cart&view=reports&layout=payouts', false);

		if ($result)
		{
			$msg = JText::_('COM_QUICK2CART_PAYOUT_SAVED');
		}
		else
		{
			$msg = JText::_('COM_QUICK2CART_PAYOUT_ERROR_SAVING');
		}

		$this->setRedirect($redirect, $msg);
	}

	/**
	 * Function for CSV Export
	 *
	 * @return  null
	 *
	 * @since  1.5
	 * */
	public function  csvexport()
	{
		$model = $this->getModel("reports");
		$CSVData = $model->getCsvexportData();
		$filename = "StoreOwnerPayouts_" . date("Y-m-d");
		$csvData = null;

		// $csvData.= "Item_id;Product Name;Store Name;Store Id;Sales Count;Amount;Created By;";
		$headColumn = array();
		$headColumn[0] = JText::_('COM_QUICK2CART_PAYOUTS_ID');
		$headColumn[1] = JText::_('COM_QUICK2CART_PAYOUTS_NAME');
		$headColumn[2] = JText::_('COM_QUICK2CART_PAYOUTS_EMAIL');
		$headColumn[3] = JText::_('COM_QUICK2CART_PAYOUTS_TRANS_ID');
		$headColumn[4] = JText::_('COM_QUICK2CART_PAYOUTS_DATE');
		$headColumn[5] = JText::_('COM_QUICK2CART_PAYOUTS_STATUS');
		$headColumn[6] = JText::_('COM_QUICK2CART_PAYOUTS_AMOUNT');

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
				$csvrow[0] = '"' . $data['id'] . '"';
				$csvrow[1] = '"' . $data['payee_name'] . '"';
				$csvrow[2] = '"' . $data['email_id'] . '"';
				$csvrow[3] = '"' . $data['transaction_id'] . '"';

				if (JVERSION < '1.6.0')
				{
					$date = JHtml::_('date', $data['date'], '%Y/%m/%d');
				}
				else
				{
					$date = JHtml::_('date', $data['date'], "Y-m-d");
				}

				$csvrow[4] = '"' . $date . '"';

				if ($data['status'] == 1)
				{
					$status = JText::_('COM_QUICK2CART_PAID');
				}
				else
				{
					$status = JText::_('COM_QUICK2CART_NOT_PAID');
				}

				$csvrow[5] = '"' . $status . '"';
				$csvrow[6] = '"' . $data['amount'] . '"';
				$csvData .= implode(";", $csvrow);
				$csvData .= "\n";
			}
		}

		ob_clean();
		echo $csvData . "\n";
		jexit();

		$link = JUri::base() . substr(JRoute::_('index.php?option=com_quick2cart&view=reports&layout=payouts', false), strlen(JUri::base(true)) + 1);
		$this->setRedirect($link);
	}
}
