<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

// Load Quick2cart Controller for list views
require_once __DIR__ . '/q2clist.php';

/**
 * Payouts list controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerPayouts extends Quick2cartControllerQ2clist
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   array   $config  The array of config values.
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Payout', $prefix = 'Quick2cartModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Function for CSV export
	 *
	 * @return  null
	 *
	 * @since  1.5
	 *
	 * */
	public function csvexport()
	{
		$model = $this->getModel('payouts');
		$CSVData = $model->getCsvexportData();

		$filename = "StoreOwnerPayouts_" . date("Y-m-d");
		$csvData = null;

		$headColumn = array();
		$headColumn[0] = JText::_('COM_QUICK2CART_PAYOUT_ID');
		$headColumn[1] = JText::_('COM_QUICK2CART_PAYEE_NAME');
		$headColumn[2] = JText::_('COM_QUICK2CART_PAYPAL_EMAIL');
		$headColumn[3] = JText::_('COM_QUICK2CART_TRANSACTION_ID');
		$headColumn[4] = JText::_('COM_QUICK2CART_PAYOUT_DATE');
		$headColumn[5] = JText::_('COM_QUICK2CART_STATUS');
		$headColumn[6] = JText::_('COM_QUICK2CART_CASHBACK_AMOUNT');

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

				$date = JHtml::_('date', $data['date'], "Y-m-d");
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
	}
}
