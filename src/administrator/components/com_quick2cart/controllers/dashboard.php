<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.controllerform');

/**
 * Dashboard form controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerDashboard extends JControllerForm
{
	/**
	 * Function to get version
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	public function getVersion()
	{
		if (!class_exists('comquick2cartHelper'))
		{
			$path = JPATH_SITE . '/components/com_quick2cart/helper.php';
			JLoader::register('comquick2cartHelper', $path);
			JLoader::load('comquick2cartHelper');
		}

		$helperobj = new comquick2cartHelper;
		echo $latestversion = $helperobj->getVersion();
		jexit();
	}

	/**
	 * Function to set session for graph
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	public function SetsessionForGraph()
	{
		$periodicorderscount = '';
		$fromDate = $_GET['fromDate'];
		$toDate = $_GET['toDate'];
		$periodicorderscount = 0;

		$session = JFactory::getSession();
		$session->set('qtc_graph_from_date', $fromDate);
		$session->set('socialads_end_date', $toDate);

		$model = $this->getModel('dashboard');
		$statsforpie = $model->statsforpie();

		// $ignorecnt = $model->getignoreCount();
		$periodicorderscount = $model->getperiodicorderscount();
		$session->set('statsforpie', $statsforpie);

		// $session->set('ignorecnt', $ignorecnt);
		$session->set('periodicorderscount', $periodicorderscount);

		header('Content-type: application/json');
		echo json_encode(array("statsforpie" => $statsforpie /*,"ignorecnt" => $ignorecnt*/));
		jexit();
	}

	/**
	 * Function to make chart
	 *
	 * @return  null
	 *
	 * @since   1.6
	 */
	public function makechart()
	{
		$month_array_name = array(
			JText::_('SA_JAN'),
			JText::_('SA_FEB'),
			JText::_('SA_MAR'),
			JText::_('SA_APR'),
			JText::_('SA_MAY'),
			JText::_('SA_JUN'),
			JText::_('SA_JUL'),
			JText::_('SA_AUG'),
			JText::_('SA_SEP'),
			JText::_('SA_OCT'),
			JText::_('SA_NOV'),
			JText::_('SA_DEC')
		);

		$session = JFactory::getSession();
		$qtc_graph_from_date = '';
		$socialads_end_date = '';

		$qtc_graph_from_date = $session->get('fromDate', '');
		$socialads_end_date = $session->get('socialads_end_date', '');
		$total_days = (strtotime($socialads_end_date) - strtotime($qtc_graph_from_date)) / (60 * 60 * 24);
		$total_days++;

		$statsforpie = $session->get('statsforpie', '');
		$model = $this->getModel('dashboard');
		$statsforpie = $model->statsforpie();

		$ignorecnt = $session->get('ignorecnt', '');
		$periodicorderscount = $session->get('periodicorderscount');
		$imprs = 0;
		$clicks = 0;
		$max_invite = 100;
		$cmax_invite = 100;
		$yscale = "";
		$titlebar = "";
		$daystring = "";
		$finalstats_date = array();
		$finalstats_clicks = array();
		$finalstats_imprs = array();
		$day_str_final = '';
		$emptylinechart = 0;
		$barchart = '';
		$fromDate = $session->get('qtc_graph_from_date', '');
		$toDate = $session->get('socialads_end_date', '');

		$dateMonthYearArr = array();
		$fromDateSTR = strtotime($fromDate);
		$toDateSTR = strtotime($toDate);

		// Hack By Nitesh - Added delivered_orders
		$pending_orders = $confirmed_orders = $shiped_orders = $delivered_orders = $refund_orders = 0;

		if (empty($statsforpie[0]) && empty($statsforpie[1]) && empty($statsforpie[2]))
		{
			$barchart = JText::_('NO_STATS');
			$emptylinechart = 1;
		}
		else
		{
			if (!empty($statsforpie[0]))
			{
				$pending_orders = $statsforpie[0][0]->orders;
			}

			if (!empty($statsforpie[1]))
			{
				$confirmed_orders = $statsforpie[1][0]->orders;
				$shiped_orders = $statsforpie[3][0]->orders;
			}

			if (!empty($statsforpie[1]))
			{
				$refund_orders = $statsforpie[2][0]->orders;
			}

			// Hack By Nitesh - Added delivered_orders
			if (!empty($statsforpie[4]))
			{
				$delivered_orders = $statsforpie[3][0]->orders;
			}
		}

		/*$barchart='<img src="http://chart.apis.google.com/chart?cht=lc&chtt=+'
		.$titlebar.'|'
		* .JText::_('NUMICHITSMON').'  	+&chco=0000ff,ff0000&chs=900x310&chbh=a,25&chm='.$chm_str.'&chd=t:'.$imprs.'|'.$clicks
		* .'&chxt=x,y&chxr=0,0,200&chds=0,'.$max_invite.',0,'.$cmax_invite.'&chxl=1:|'.$yscale.'|0:|'. $daystring.'|" />';*/

		// Hack By Nitesh - Added delivered_orders
		header('Content-type: application/json');
		echo json_encode(
				array(
					"pending_orders" => $pending_orders,
					"confirmed_orders" => $confirmed_orders,
					"shiped_orders" => $shiped_orders,
					"refund_orders" => $refund_orders,
					"delivered_orders" => $delivered_orders,
					"periodicorderscount" => $periodicorderscount,
					"emptylinechart" => $emptylinechart
				)
			);
		jexit();
	}

	/**
	 * Manual Setup related chages: For now - 1. for overring the bs-2 view
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function setup()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$jinput = JFactory::getApplication()->input;
		$takeBackUp = $jinput->get("takeBackUp", 1);

		$comquick2cartHelper     = new comquick2cartHelper;
		$defTemplate = $comquick2cartHelper->getSiteDefaultTemplate(0);
		$templatePath = JPATH_SITE . '/templates/' . $defTemplate . '/html/';

		$statusMsg = array();
		$statusMsg["component"] = array();

		// 1. Override component view
		$siteBs2views = JPATH_ROOT . "/components/com_quick2cart/views_bs2/site";

		// Check for com_quick2cart folder in template override location
		$compOverrideFolder  = $templatePath . "com_quick2cart";

		if (JFolder::exists($compOverrideFolder))
		{
			if ($takeBackUp)
			{
				// Rename
				$backupPath = $compOverrideFolder . '_' . date("Ymd_H_i_s");
				$status = JFolder::move($compOverrideFolder, $backupPath);
				$statusMsg["component"][] = JText::_('COM_QUICK2CART_TAKEN_BACKUP_OF_OVERRIDE_FOLDER') . $backupPath;
			}
			else
			{
				$delStatus = JFolder::delete($compOverrideFolder);
			}
		}

		// Copy
		$status = JFolder::copy($siteBs2views, $compOverrideFolder);
		$statusMsg["component"][] = JText::_('COM_QUICK2CART_OVERRIDE_DONE') . $compOverrideFolder;

		// 2. Create Override plugins folder if not exist
		$pluginsPath = JPATH_ROOT . "/components/com_quick2cart/views_bs2/plugins/";

		// Check for com_quick2cart folder in template override location
		$pluginsOverrideFolder  = $templatePath . "plugins";
		$createFolderStatus = JFolder::create($pluginsOverrideFolder);

		if ($createFolderStatus)
		{
			$statusMsg["plugins"][] = JText::_('COM_QUICK2CART_CREATE_PLUGINS_FOLDER_STATUS');

			// Check for override tjshipping plugin
			$newtjshipping = $pluginsPath . "/tjshipping";
			$tjshippingOverrideFolder = $pluginsOverrideFolder . "/tjshipping";

			if (JFolder::exists($tjshippingOverrideFolder))
			{
				if ($takeBackUp)
				{
					// Rename
					$backupPath = $tjshippingOverrideFolder . '_' . date("Ymd_H_i_s");
					$status = JFolder::move($tjshippingOverrideFolder, $backupPath);

					$statusMsg["plugins"][] = JText::sprintf('COM_QUICK2CART_TAKEN_OF_PLUGIN_ND_BACKUP_PATH', 'tjshipping', $backupPath);
				}
				else
				{
					$delStatus = JFolder::delete($tjshippingOverrideFolder);
				}
			}

			// Copy
			$status = JFolder::copy($newtjshipping, $tjshippingOverrideFolder);
			$statusMsg["plugins"][] = JText::sprintf('COM_QUICK2CART_COMPLETED_PLUGINS_OVERRIDE', "<b> tjshipping</b>");
		}
		else
		{
			$statusMsg["plugins"][] = JText::_('COM_QUICK2CART_CREATE_PLUGINS_FOLDER_FAILED');
		}

		// 3. Modules override
		$modules = JFolder::folders(JPATH_ROOT . "/components/com_quick2cart/views_bs2/modules/");
		$statusMsg["modules"] = array();

		foreach ($modules as $modName)
		{
			$this->overrideModule($templatePath, $modName, $statusMsg, $takeBackUp);
		}

		$this->displaySetup($statusMsg);
				exit;
	}

	/**
	 * Override the Modules
	 *
	 * @param   string  $templatePath  templatePath eg JPATH_SITE . '/templates/protostar/html/'
	 * @param   string  $modName       Module name
	 * @param   array   &$statusMsg    The array of config values.
	 * @param   array   $takeBackUp    flag
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function overrideModule($templatePath, $modName, &$statusMsg, $takeBackUp)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$bs2ModulePath = JPATH_ROOT . "/components/com_quick2cart/views_bs2/modules/" . $modName;
		$overrideBs2ModulePath = $templatePath . $modName;

		$statusMsg["modules"][] = JText::sprintf('COM_QUICK2CART_OVERRIDING_THE_MODULE', $modName);

		if (JFolder::exists($overrideBs2ModulePath))
		{
			if ($takeBackUp)
			{
				// Rename
				$backupPath = $overrideBs2ModulePath . '_' . date("Ymd_H_i_s");
				$status = JFolder::move($overrideBs2ModulePath, $backupPath);

				$statusMsg["modules"][] = JText::sprintf('COM_QUICK2CART_TAKEN_OF_MODULE_ND_BACKUP_PATH',  $modName, $backupPath);
			}
			else
			{
				$delStatus = JFolder::delete($overrideBs2ModulePath);
			}
		}

		// Copy
		$status = JFolder::copy($bs2ModulePath, $overrideBs2ModulePath);
		$statusMsg["modules"][] = JText::sprintf('COM_QUICK2CART_COMPLETED_MODULE_OVERRIDE', "<b>" . $modName . "</b>");
	}

	/**
	 * Override the Modules
	 *
	 * @param   array  $statusMsg  The array of config values.
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function displaySetup($statusMsg)
	{
		echo "<br/> =================================================================================";
		echo "<br/> " . JText::_("COM_QUICK2CART_BS2_OVERRIDE_PROCESS_START");
		echo "<br/> =================================================================================";

		foreach ($statusMsg as $key => $extStatus)
		{
			echo "<br/> <br/><br/>*****************  " . JText::_("COM_QUICK2CART_BS2_OVERRIDING_FOR")
			. " <strong>" . $key . "</strong> ****************<br/>";

			foreach ($extStatus as $k => $status)
			{
				$index = $k + 1;
				echo $index . ") " . $status . "<br/> ";
			}
		}

		echo "<br/> " . JText::_("COM_QUICK2CART_BS2_OVERRIDING_DONE");
	}
}
