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

jimport('joomla.application.component.view');

require_once JPATH_ADMINISTRATOR . '/components/com_quick2cart/models/zone.php';

/**
 * View class for vendor.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewVendor extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->params        = JComponentHelper::getParams('com_quick2cart');
		$Quick2cartModelZone = new Quick2cartModelZone;
		$comquick2cartHelper = new comquick2cartHelper;
		$storeHelper         = new storeHelper;
		$this->addToolbar();
		$mainframe = JFactory::getApplication();
		$input     = $mainframe->input;

		$option = $input->get('option');
		$layout = $input->get('layout', 'createstore');

		if ($layout == "createstore")
		{
			$this->countrys    = $Quick2cartModelZone->getCountry();
			$this->orders_site = 1;
			$store_id          = $input->get('store_id', '0');

			// DEFAULT ALLOW TO CREAT STORE
			$this->allowToCreateStore = 1;

			// Means edit task
			if (!empty($store_id))
			{
				// $this->store_authorize=$comquick2cartHelper->store_authorize("vendor_createstore",$store_id);
				$this->editview  = 1;
				$this->storeinfo = $storeinfo = $comquick2cartHelper->editstore($store_id);

				// Get weight and length select box
				$this->legthList  = $storeHelper->getLengthClassSelectList(0, $this->storeinfo[0]->length_id);
				$this->weigthList = $storeHelper->getWeightClassSelectList(0, $this->storeinfo[0]->weight_id);
			}
			else
			{
				// NEW STORE TASK:: CK FOR WHETHER WE HV TO ALLOW OR NOT
				$storeHelper = new storeHelper;

				// $this->allowToCreateStore=$storeHelper->isAllowedToCreateNewStore();
				// Get weight and length select box
				$this->legthList  = $storeHelper->getLengthClassSelectList();
				$this->weigthList = $storeHelper->getWeightClassSelectList();
			}

			// START Q2C Sample development
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('system');

			// Call the plugin and get the result // @DEPRICATED
			$result              = $dispatcher->trigger('qtcOnBeforeEditStore', array($store_id));
			$beforecart          = '';
			$OnBeforeCreateStore = '';

			if (!empty($result))
			{
				$OnBeforeCreateStore = $result[0];
			}

			$result = $dispatcher->trigger('onQuick2cartBeforeStoreEdit', array($store_id));

			if (!empty($result))
			{
				// If more than one plugin returns

				/* $OnBeforeCreateStore = $result[0];
				$OnBeforeCreateStore = join('', $result);*/
				$OnBeforeCreateStore .= trim(implode("\n", $result));
			}

			$this->OnBeforeCreateStore = $OnBeforeCreateStore;
		}
		/* else
		{
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'desc',			'word' );
		$filter_type		= $mainframe->getUserStateFromRequest( "$option.filter_type",		'filter_type', 		0,			'string' );
		$filter_state = $mainframe->getUserStateFromRequest( $option.'search_list', 'search_list', '', 'string' );
		$search = $mainframe->getUserStateFromRequest( $option.'search', 'search','', 'string' );
		$search = JString::strtolower( $search );
		$limit = '';
		$limitstart = '';
		$cid[0]='';

		if($search==null)
		$search='';

		$model	= $this->getModel( 'vendor' );
		$task = $input->get('task');

		$comquick2cartHelper = new comquick2cartHelper;*/
		/*$this->storeinfo=$storeinfo=$comquick2cartHelper->getStoreDetail();*/

		/*$total 		= $model->getTotal();
		$this->pagination=$pagination = $model->getPagination();
		$this->storeinfo=$storeinfo= $model->getVendors();



		// search filter
		$lists['search_select']	= $search;
		$lists['search']		= $search;
		$lists['search_list']	= $filter_state;
		$lists['order']			= $filter_type;
		$lists['order_Dir']		= $filter_order_Dir;
		$lists['limit']			= $limit;
		$lists['limitstart']	= $limitstart;
		$this->lists=$lists;
		}*/
		// end of else

		// FOR DISPLAY SIDE FILTER
		if (JVERSION >= '3.0')
		{
			if ($layout == 'salespervendor')
			{
				$this->sidebar = JHtmlSidebar::render();
			}
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		// Get the toolbar object instance
		$bar    = JToolBar::getInstance('toolbar');
		$input  = JFactory::getApplication()->input;
		$layout = $input->get('layout', 'createstore');

		if ($layout == "createstore")
		{
			JFactory::getApplication()->input->set('hidemainmenu', true);

			$store_id = $input->get('store_id', '0');
			$isNew    = ($store_id == 0);

			if ($isNew)
			{
				$viewTitle = JText::_('AD_VENDER_TITLE');
			}
			else
			{
				$viewTitle = JText::_('COM_QUICK2CART_EDIT_STORE');
			}

			if (JVERSION >= '3.0')
			{
				JToolBarHelper::title($viewTitle, 'pencil-2');
			}
			else
			{
				JToolBarHelper::title($viewTitle, 'store.png');
			}

			JToolBarHelper::back('COM_QUICK2CART_BACK', 'index.php?option=com_quick2cart&view=stores');
		}
		elseif ($layout == "salespervendor")
		{
			JToolBarHelper::title(JText::_('SALES_PER_VENDER_TITLE'), 'icon-48-quick2cart.png');
			JToolBarHelper::back('QTC_HOME', 'index.php?option=com_quick2cart');

			// CSV EXPORT
			if (JVERSION >= '3.0')
			{
				JToolBarHelper::custom('csvexport', 'icon-32-save.png', 'icon-32-save.png', 'COM_QUICK2CART_SALES_CSV_EXPORT', false);
			}
			else
			{
				$button = "<a href='#' onclick=\"javascript:document.getElementById('task').value = 'csvexport';
				document.getElementById('controller').value = 'salespervendor';
				document.adminForm.submit();\" ><span class='icon-32-save' title='Export'></span>" .
				JText::_('COM_QUICK2CART_SALES_CSV_EXPORT') . "</a>";

				$bar->appendButton('Custom', $button);
			}
		}
	}
}
