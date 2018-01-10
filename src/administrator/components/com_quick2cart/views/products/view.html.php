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

/**
 * View class for list view of products.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewProducts extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->params              = JComponentHelper::getParams('com_quick2cart');
		$this->comquick2cartHelper = new comquick2cartHelper;
		$this->productHelper       = new productHelper;

		$this->product_types   = array();

		$this->product_types[1] = JHtml::_('select.option', 1, JText::_('QTC_PROD_TYPE_SIMPLE'));
		$this->product_types[2] = JHtml::_('select.option', 2, JText::_('QTC_PROD_TYPE_VARIABLE'));

		$this->products      = $this->items = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Creating status filter.
		$sstatus = array();

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->products as &$products)
		{
			$this->ordering[$products->parent_id][] = $products->item_id;
		}

		if (JVERSION < '3.0')
		{
			$sstatus[]     = JHtml::_('select.option', '', JText::_('JOPTION_SELECT_PUBLISHED'));
			$sstatus[]     = JHtml::_('select.option', 1, JText::_('COM_QUICK2CART_PUBLISH'));
			$sstatus[]     = JHtml::_('select.option', 0, JText::_('COM_QUICK2CART_UNPUBLISH'));
			$this->sstatus = $sstatus;
		}
		// Create clients array
		$clients = array();

		if (JVERSION < '3.0')
		{
			$clients[]     = JHtml::_('select.option', '', JText::_('COM_QUICK2CART_FILTER_SELECT_CLIENT'));
			$clients[]     = JHtml::_('select.option', 'com_quick2cart', JText::_('COM_QUICK2CART_NATIVE'));
			$clients[]     = JHtml::_('select.option', 'com_content', JText::_('COM_QUICK2CART_CONTENT_ARTICLES'));
			$clients[]     = JHtml::_('select.option', 'com_flexicontent', JText::_('COM_QUICK2CART_FLEXICONTENT'));
			$clients[]     = JHtml::_('select.option', 'com_k2', JText::_('COM_QUICK2CART_K2'));
			$clients[]     = JHtml::_('select.option', 'com_zoo', JText::_('COM_QUICK2CART_ZOO'));
			$clients[]     = JHtml::_('select.option', 'com_cobalt', JText::_('COM_QUICK2CART_COBALT'));
			$this->clients = $clients;
		}

		// Get all stores.
		$this->store_details = $this->comquick2cartHelper->getAllStoreDetails();

		$this->addToolbar();

		if (JVERSION >= '3.0')
		{
			$this->sidebar = JHtmlSidebar::render();
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
		// Get the toolbar object instance.
		$bar = JToolBar::getInstance('toolbar');

		JToolBarHelper::addNew('product.addnew', 'QTC_NEW');

		if (isset($this->items[0]))
		{
			JToolBarHelper::editList('products.edit', 'JTOOLBAR_EDIT');
			JToolBarHelper::divider();
			JToolBarHelper::custom('products.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('products.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);

			// Featurd and unfeatured buttons.

			if (JVERSION >= '3.0')
			{
				JToolBarHelper::custom('products.featured', 'featured', '', 'COM_QUICK2CART_FEATURE_TOOLBAR');
				JToolBarHelper::custom('products.unfeatured', 'star-empty', '', 'COM_QUICK2CART_UNFEATURE_TOOLBAR');
			}
			else
			{
				JToolBarHelper::custom('products.featured', 'quick2cart-feature.png', '', 'COM_QUICK2CART_FEATURE_TOOLBAR');
				JToolBarHelper::custom('products.unfeatured', 'quick2cart-unfeature', '', 'COM_QUICK2CART_UNFEATURE_TOOLBAR');
			}

			JToolBarHelper::deleteList('', 'products.delete', 'JTOOLBAR_DELETE');
		}
		// Featurd and unfeatured buttons.

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title(JText::_('COM_QUICK2CART_PRODUCTS'), 'cart');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_QUICK2CART_PRODUCTS'), 'products.png');
		}

		// Adding option btn
		JToolbarHelper::preferences('com_quick2cart');
	}
}
