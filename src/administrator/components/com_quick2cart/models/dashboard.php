<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Dashboard Model for an Q2C.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartModelDashboard extends JModelLegacy
{
	/**
	 * construtor function
	 *
	 */
	public function __construct()
	{
		$this->db = JFactory::getDBO();

		// Get download id
		$params     = JComponentHelper::getParams('com_quick2cart');
		$this->downloadid = $params->get('downloadid');

		// Setup vars
		$this->updateStreamName = 'Quick2Cart';
		$this->updateStreamType = 'collection';
		$this->updateStreamUrl  = "https://techjoomla.com/updates/packages/all?dummy=quick2cart.xml";
		$this->extensionElement = 'pkg_quick2cart';
		$this->extensionType    = 'package';

		parent::__construct();
		global $option;
	}

	/**
	 * Function to get extension id
	 *
	 * @return  void
	 */
	public function getExtensionId()
	{
		$db = $this->getDbo();

		// Get current extension ID
		$query = $db->getQuery(true)
			->select($db->qn('extension_id'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q($this->extensionType))
			->where($db->qn('element') . ' = ' . $db->q($this->extensionElement));
		$db->setQuery($query);

		$extension_id = $db->loadResult();

		if (empty($extension_id))
		{
			return 0;
		}
		else
		{
			return $extension_id;
		}
	}

	/**
	 * Refreshes the Joomla! update sites for this extension as needed
	 *
	 * @return  void
	 */
	public function refreshUpdateSite()
	{
		// Extra query for Joomla 3.0 onwards
		$extra_query = null;

		if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $this->downloadid))
		{
			$extra_query = 'dlid=' . $this->downloadid;
		}

		// Setup update site array for storing in database
		$update_site = array(
			'name' => $this->updateStreamName,
			'type' => $this->updateStreamType,
			'location' => $this->updateStreamUrl,
			'enabled'  => 1,
			'last_check_timestamp' => 0,
			'extra_query'          => $extra_query
		);

		// For joomla versions < 3.0
		if (version_compare(JVERSION, '3.0.0', 'lt'))
		{
			unset($update_site['extra_query']);
		}

		$db = $this->getDbo();

		// Get current extension ID
		$extension_id = $this->getExtensionId();

		if (!$extension_id)
		{
			return;
		}

		// Get the update sites for current extension
		$query = $db->getQuery(true)
			->select($db->qn('update_site_id'))
			->from($db->qn('#__update_sites_extensions'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
		$db->setQuery($query);

		$updateSiteIDs = $db->loadColumn(0);

		if (!count($updateSiteIDs))
		{
			// No update sites defined. Create a new one.
			$newSite = (object) $update_site;
			$db->insertObject('#__update_sites', $newSite);

			$id = $db->insertid();

			$updateSiteExtension = (object) array(
				'update_site_id' => $id,
				'extension_id'   => $extension_id,
			);

			$db->insertObject('#__update_sites_extensions', $updateSiteExtension);
		}
		else
		{
			// Loop through all update sites
			foreach ($updateSiteIDs as $id)
			{
				$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__update_sites'))
					->where($db->qn('update_site_id') . ' = ' . $db->q($id));
				$db->setQuery($query);
				$aSite = $db->loadObject();

				// Does the name and location match?
				if (($aSite->name == $update_site['name']) && ($aSite->location == $update_site['location']))
				{
					// Do we have the extra_query property (J 3.2+) and does it match?
					if (property_exists($aSite, 'extra_query'))
					{
						if ($aSite->extra_query == $update_site['extra_query'])
						{
							continue;
						}
					}
					else
					{
						// Joomla! 3.1 or earlier. Updates may or may not work.
						continue;
					}
				}

				$update_site['update_site_id'] = $id;
				$newSite = (object) $update_site;
				$db->updateObject('#__update_sites', $newSite, 'update_site_id', true);
			}
		}
	}

	/**
	 * Function to get latest version of invitex
	 *
	 * @return  void
	 */
	public function getLatestVersion()
	{
		// Get current extension ID
		$extension_id = $this->getExtensionId();

		if (!$extension_id)
		{
			return 0;
		}

		$db = $this->getDbo();

		// Get current extension ID
		$query = $db->getQuery(true)
			->select($db->qn(array('version', 'infourl')))
			->from($db->qn('#__updates'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
		$db->setQuery($query);

		$latestVersion = $db->loadObject();

		if (empty($latestVersion))
		{
			return 0;
		}
		else
		{
			return $latestVersion;
		}
	}

	/**
	 * Method to get title of dashboard
	 *
	 * @param   string  $title    Title of box
	 * @param   string  $content  Content of box
	 * @param   object  $type     type of data
	 *
	 * @return  html  $html  title of dashboard
	 *
	 * @since   2.2
	 */
	public function getbox($title, $content, $type = null)
	{
		$html = '
			<div class="row-fluid">
				<div class="span12"><h5>' . $title . '</h5></div>
			</div>
			<div class="row-fluid">
				<div class="span12">' . $content . '</div>
			</div>';

		return $html;
	}

	/**
	 * Returns overall total income amount
	 *
	 * @return  float  get overall income
	 *
	 * @since   2.2
	 */
	public function getAllOrderIncome()
	{
		// Hack By Nitesh - Added Status D
		// Getting current currency
		$comquick2cartHelper = new comquick2cartHelper;
		$currency            = $comquick2cartHelper->getCurrencySession();
		$query = "SELECT FORMAT(SUM(amount), 2)
		 FROM #__kart_orders
		 WHERE (status='C' OR status='S' OR status='D') AND currency='" . $currency . "'
		 AND (processor NOT IN('jomsocialpoints', 'alphapoints') OR extra='points')";

		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();

		return $result;
	}

	/**
	 * Returns overall total income per month
	 *
	 * @return  float  get total income per month
	 *
	 * @since   2.2
	 */
	public function getMonthIncome()
	{
		$db = JFactory::getDBO();

		// Getting current currency
		$comquick2cartHelper = new comquick2cartHelper;
		$currency            = $comquick2cartHelper->getCurrencySession();

		// $backdate = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' - 30 days'));

		$curdate    = date('Y-m-d');
		$back_year  = date('Y') - 1;
		$back_month = date('m') + 1;
		$backdate   = $back_year . '-' . $back_month . '-' . '01';

		/* Query echo $query = "SELECT FORMAT(SUM(amount),2) FROM #__kart_orders
		WHERE status ='C' AND cdate between (".$curdate.",".$backdate." )
		GROUP BY YEAR(cdate), MONTH(cdate) order by YEAR(cdate), MONTH(cdate)
		*/

		$query = "SELECT FORMAT( SUM( amount ) , 2 ) AS amount, MONTH( cdate ) AS MONTHSNAME, YEAR( cdate ) AS YEARNM
		FROM `#__kart_orders`
		WHERE DATE(cdate)
		BETWEEN  '" . $backdate . "'
		AND  '" . $curdate . "'
		AND ( STATUS =  'C' OR STATUS =  'S') AND currency='" . $currency . "'
		GROUP BY YEARNM, MONTHSNAME
		ORDER BY YEAR( cdate ) , MONTH( cdate ) ASC";

		// @TODO WE HAVE TO CHECK WHETHER WE HAVE TO INCLUDE OR NOT

		// AND (processor NOT IN ('payment_jomsocialpoints',  'payment_alphapoints'))
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Returns overall total income per month
	 *
	 * @return  float  get total income per month
	 *
	 * @since   2.2
	 */
	public function getAllmonths()
	{
		$date2      = date('Y-m-d');

		// Get one year back date
		$date1 = date('Y-m-d', strtotime(date("Y-m-d", time()) . " - 365 day"));

		// Convert dates to UNIX timestamp
		$time1 = strtotime($date1);
		$time2 = strtotime($date2);
		$tmp = date('mY', $time2);
		$year = date('Y', $time1);

		// $months[] = array("month" => date('F', $time1), "year" => date('Y', $time1));

		while ($time1 < $time2)
		{
			$month31 = array(1,3,5,7,8,10,12);
			$month30 = array(4,6,9,11);

			$month = date('m', $time1);

			if (array_search($month, $month31))
			{
				$time1 = strtotime(date('Y-m-d', $time1) . ' +31 days');
			}
			elseif (array_search($month, $month30))
			{
				$time1 = strtotime(date('Y-m-d', $time1) . ' +30 days');
			}
			else
			{
				if (((0 == $year % 4) && (0 != $year % 100)) || (0 == $year % 400))
				{
					$time1 = strtotime(date('Y-m-d', $time1) . ' +29 days');
				}
				else
				{
					$time1 = strtotime(date('Y-m-d', $time1) . ' +28 days');
				}
			}

			if (date('mY', $time1) != $tmp && ($time1 < $time2))
			{
				$months[] = array(
					"month" => date('F', $time1),
					"year" => date('Y', $time1)
				);
			}
		}

		$months[] = array("month" => date('F', $time2),"year" => date('Y', $time2));

		return $months;
	}

	/**
	 * Function for pie chart
	 *
	 * @return  array  Get data for pie chart
	 *
	 * @since   2.2
	 */
	public function statsforpie()
	{
		$db                  = JFactory::getDBO();
		$session             = JFactory::getSession();

		// Getting current currency
		$comquick2cartHelper = new comquick2cartHelper;
		$currency            = $comquick2cartHelper->getCurrencySession();

		$qtc_graph_from_date = $session->get('qtc_graph_from_date');
		$socialads_end_date  = $session->get('socialads_end_date');

		$where   = "AND currency='" . $currency . "'";
		$groupby = '';

		if ($qtc_graph_from_date)
		{
			// For graph
			$where .= " AND DATE(mdate) BETWEEN DATE('" . $qtc_graph_from_date . "') AND DATE('" . $socialads_end_date . "')";
		}
		else
		{
			$day         = date('d');
			$month       = date('m');
			$year        = date('Y');
			$statsforpie = array();

			$backdate = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' - 30 days'));
			$groupby  = "";
		}

		// Pending order
		$query = " SELECT COUNT(id) AS orders FROM #__kart_orders WHERE status= 'P'
		AND (processor NOT IN('payment_jomsocialpoints','payment_alphapoints') OR extra='points') " . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Confirmed order
		$query = " SELECT COUNT(id) AS orders FROM #__kart_orders WHERE status= 'C'
		AND (processor NOT IN('payment_jomsocialpoints','payment_alphapoints') OR extra='points') " . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Rejected order
		$query = " SELECT COUNT(id) AS orders FROM #__kart_orders WHERE status= 'RF'
		AND (processor NOT IN('payment_jomsocialpoints','payment_alphapoints') OR extra='points') " . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		// Shipped order
		$query = " SELECT COUNT(id) AS orders FROM #__kart_orders WHERE status= 'S'
		AND (processor NOT IN('payment_jomsocialpoints','payment_alphapoints') OR extra='points') " . $where;
		$db->setQuery($query);
		$statsforpie[] = $db->loadObjectList();

		return $statsforpie;
	}

	/*
	public function getignoreCount()
	{
	$db=JFactory::getDBO();
	$session = JFactory::getSession();
	$qtc_graph_from_date=$session->get('qtc_graph_from_date');
	$socialads_end_date=$session->get('socialads_end_date');
	$where='';

	if ($qtc_graph_from_date)
	{
	$where="WHERE  DATE(idate) BETWEEN DATE('".$qtc_graph_from_date."') AND DATE('".$socialads_end_date."')";
	}

	$query = "SELECT COUNT(*) as ignorecount,DATE(idate) as idate FROM #__ad_ignore  ".$where." GROUP bY DATE(idate) ORDER BY DATE(idate)";

	$this->_db->setQuery($query);
	$cnt= $this->_db->loadObjectList();
	return $cnt;
	}
	*/

	/**
	 * Returns periodic income based on session data
	 *
	 * @return  INT  periodic income based on session data
	 *
	 * @since   2.2
	 */
	public function getperiodicorderscount()
	{
		$db      = JFactory::getDBO();
		$session = JFactory::getSession();

		$qtc_graph_from_date = $session->get('qtc_graph_from_date');
		$socialads_end_date  = $session->get('socialads_end_date');
		$where               = '';
		$groupby             = '';

		if ($qtc_graph_from_date)
		{
			$where = " AND DATE(mdate) BETWEEN DATE('" . $qtc_graph_from_date . "') AND DATE('" . $socialads_end_date . "')";
		}
		else
		{
			$qtc_graph_from_date = date('Y-m-d');
			$backdate            = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
			$where               = " AND DATE(mdate) BETWEEN DATE('" . $backdate . "') AND DATE('" . $qtc_graph_from_date . "')";
			$groupby             = "";
		}

		$query = "SELECT FORMAT(SUM(amount),2) FROM #__kart_orders WHERE (status ='C' OR status ='S')
		AND (processor NOT IN('payment_jomsocialpoints','payment_alphapoints') OR extra='points') " . $where;
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();

		return $result;
	}

	/**
	 * Returns periodic income based on session data
	 *
	 * @return  INT  periodic income based on session data
	 *
	 * @since   2.2
	 */
	public function notShippedDetails()
	{
		$where   = array();
		$where[] = ' o.`status`="C" ';
		$where   = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');

		$db    = JFactory::getDBO();
		$query = 'SELECT o.id,o.prefix,o.`name`,amount FROM `#__kart_orders` AS o ' . $where . ' ORDER BY o.`mdate` LIMIT 0,7';
		$db->setQuery($query);

		return $result = $db->loadAssocList();
	}

	/**
	 * Returns periodic income based on session data
	 *
	 * @return  INT  periodic income based on session data
	 *
	 * @since   2.2
	 */
	public function getpendingPayouts()
	{
		if (!class_exists('Quick2cartModelPayouts'))
		{
			JLoader::register('Quick2cartModelPayouts', JPATH_ADMINISTRATOR . '/components/com_quick2cart/models/payouts.php');
			JLoader::load('Quick2cartModelPayouts');
		}

		$Quick2cartModelPayouts = new Quick2cartModelPayouts;

		return $Quick2cartModelPayouts->getPayoutFormData();
	}

	/**
	 * Returns orders count
	 *
	 * @return  INT  $ordersCount  orders count
	 *
	 * @since   2.2
	 */
	public function getOrdersCount()
	{
		$db    = JFactory::getDBO();
		$query = "SELECT COUNT(id)
		 FROM #__kart_orders";
		$db->setQuery($query);
		$ordersCount = $db->loadResult();

		return $ordersCount;
	}

	/**
	 * Returns products count
	 *
	 * @return  INT  products count
	 *
	 * @since   2.2
	 */
	public function getProductsCount()
	{
		$db    = JFactory::getDBO();
		$query = "SELECT COUNT(item_id)
		 FROM #__kart_items";

		$db->setQuery($query);
		$productsCount = $db->loadResult();

		return $productsCount;
	}

	/**
	 * Returns stores count
	 *
	 * @return  INT  stores count
	 *
	 * @since   2.2
	 */
	public function getStoresCount()
	{
		$db    = JFactory::getDBO();
		$query = "SELECT COUNT(id)
		 FROM #__kart_store";

		$db->setQuery($query);
		$storesCount = $db->loadResult();

		return $storesCount;
	}
}
