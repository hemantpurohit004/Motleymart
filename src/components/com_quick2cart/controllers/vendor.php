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
/*$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);*/

/**
 * Quick2cartControllerVendor controller.
 *
 * @since  1.6
 */
class Quick2cartControllerVendor extends quick2cartController
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		$comquick2cartHelper = new comquick2cartHelper;

		$this->my_stores_itemid    = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=stores&layout=my');
		$this->create_store_itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=vendor&layout=createstore');

		parent::__construct($config);
	}

	/**
	 * Method Add New.
	 *
	 * @return bool
	 *
	 * @since 1.6
	 */
	public function addNew()
	{
		$link = JRoute::_('index.php?option=com_quick2cart&view=vendor&layout=createstore&Itemid=' . $this->create_store_itemid, false);

		$this->setRedirect($link);
	}

	/**
	 * Method Edit.
	 *
	 * @return bool
	 *
	 * @since 1.6
	 */
	public function edit()
	{
		$input = JFactory::getApplication()->input;
		$cid   = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);

		$link = JRoute::_(
		'index.php?option=com_quick2cart&view=vendor&layout=createstore&store_id=' . $cid[0] . '&Itemid=' .
		$this->create_store_itemid, false
		);

		$this->setRedirect($link);
	}

	/**
	 * Method Save.
	 *
	 * @return bool
	 *
	 * @since 1.6
	 */
	public function save()
	{
		$jinput      = JFactory::getApplication()->input;
		$post        = $jinput->post;
		$btnAction   = $post->get('btnAction');
		$storeHelper = new storeHelper;
		$result      = $storeHelper->saveVendorDetails($post);
		$comquick2cartHelper = new comquick2cartHelper;

		$qtcadminCall = $jinput->get('qtcadminCall');

		if ($btnAction == 'vendor.saveAndClose')
		{
			$link = JRoute::_('index.php?option=com_quick2cart&view=stores&layout=my&Itemid=' . $this->my_stores_itemid, false);
		}
		else
		{
			$link = $comquick2cartHelper->quick2CartRoute('index.php?option=com_quick2cart&view=vendor&layout=createstore&store_id=' . $result['store_id']);
		}

		if (!empty($qtcadminCall))
		{
			$link = JUri::root() . 'administrator/index.php?option=com_quick2cart';
		}

		$this->setRedirect($link, $result['msg']);
	}

	/**
	 * Method Cancel.
	 *
	 * @return bool
	 *
	 * @since 1.6
	 */
	public function cancel()
	{
		$link = JRoute::_('index.php?option=com_quick2cart&view=stores&layout=my&Itemid=' . $this->my_stores_itemid, false);

		$this->setRedirect($link);
	}

	/**
	 * Method refreshVendorDashboard.
	 *
	 * @return bool
	 *
	 * @since 1.6
	 */
	public function refreshVendorDashboard()
	{
		$jinput              = JFactory::getApplication()->input;
		$comquick2cartHelper = new comquick2cartHelper;
		$storeid             = $jinput->get('storeid', '0', 'INT');
		$periodicorderscount = '';

		$fromDate = $jinput->get('fromDate', '', 'STRING');

		if ($fromDate)
		{
			$fromDate = date('Y-m-d H:i:s', strtotime($fromDate));
		}

		$toDate = $jinput->get('toDate', '', 'STRING');

		if ($fromDate)
		{
			$toDate = date('Y-m-d H:i:s', strtotime($toDate));
		}

		$app = JFactory::getApplication();
		$app->setUserState('from', $fromDate);
		$app->setUserState('to', $toDate);

		echo 1;
		jexit();
	}

	/**
	 * refreshStoreView
	 *
	 * @return bool
	 *
	 * @since 1.6
	 */
	public function refreshStoreView()
	{
		global $mainframe;
		$mainframe     = JFactory::getApplication();
		$jinput        = JFactory::getApplication()->input;
		$post          = $jinput->post;
		$store_id      = $jinput->get('store_id');
		$current_store = $post->get('current_store');

		if (!empty($current_store))
		{
			$mainframe->setUserState('current_store', $current_store);
		}

		$mainframe->setUserState('store_cat', $current_store);
		$this->setRedirect(JUri::base() . 'index.php?option=com_quick2cart&view=vendor&layout=vendor&layout=store&store_id=' . $store_id);
	}

	/**
	 * This fuction will send customer email to store owner
	 *
	 * @return bool
	 *
	 * @since 1.6
	 */
	public function contactUsEmail()
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);
		$jinput  = JFactory::getApplication()->input;
		$storeid = $jinput->get('store_id');
		$post    = $jinput->post;

		$model = $this->getModel('vendor');
		$model->sendcontactUsEmail($post);
		$msgsent = JText::_('QTC_MSG_SENT');

		/*$this->setRedirect( JUri::base()."index.php?option=com_quick2cart&view=category", $msgsent );*/

		$this->setRedirect(
		JUri::base() . "index.php?option=com_quick2cart&view=vendor&layout=contactus&store_id=" .
		$storeid . '&tmpl=component', $msgsent
		);
	}

	/**
	 * Method to ckUniqueVanityURL.
	 *
	 * @return bool
	 *
	 * @since 1.6
	 */
	public function ckUniqueVanityURL()
	{
		$jinput    = JFactory::getApplication()->input;
		$vanityURL = $jinput->get('vanityURL', '', 'RAW');
		$model     = $this->getModel('vendor');
		$status    = $model->ckUniqueVanityURL($vanityURL);

		if (!empty($status))
		{
			// Present vanity URL
			echo 1;
		}
		else
		{
			echo 0;
		}

		jexit();
	}

	/**
	 * Method to Get region.
	 *
	 * @return bool
	 *
	 * @since 1.6
	 */
	public function getRegions()
	{
		$app        = JFactory::getApplication();
		$input      = JFactory::getApplication()->input;
		$country_id = $input->get('country_id', '0', 'int');

		// Flag to show default value of state select box
		$defaultValue        = $input->get('default_value', '0', 'int');
		$Quick2cartModelZone = $this->getModel('zone');
		$Quick2cartModelZone = new Quick2cartModelZone;

		if (!empty($country_id))
		{
			$stateList = $Quick2cartModelZone->getRegionList($country_id);

			$options = array();

			if ($defaultValue == 1)
			{
				$options[] = JHtml::_('select.option', '', JTEXT::_('QTC_BILLIN_SELECT_STATE'));
			}
			else
			{
				$options[] = JHtml::_('select.option', 0, JTEXT::_('COM_QUICK2CART_ZONE_ALL_STATES'));
			}

			if ($stateList)
			{
				foreach ($stateList as $state)
				{
					// This is only to generate the <option> tag inside select tag
					$options[] = JHtml::_('select.option', $state->region_id, $state->region);
				}
			}

			// Now generate the select list and echo that
			$stateList = JHtml::_('select.genericlist', $options, 'qtcstorestate', ' class="qtc_store_state"', 'value', 'text');
			echo $stateList;
		}

		$app->close();
	}
}
