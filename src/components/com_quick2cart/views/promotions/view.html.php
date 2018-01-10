<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Quick2cart.
 *
 * @since  1.6
 */
class Quick2cartViewPromotions extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->comquick2cartHelper = new comquick2cartHelper;
		$storeHelper = $this->comquick2cartHelper->loadqtcClass(JPATH_SITE . "/components/com_quick2cart/helpers/storeHelper.php", "storeHelper");
		$this->comquick2cartHelper->loadqtcClass(JPATH_ADMINISTRATOR . "/components/com_quick2cart/models/products.php", 'Quick2cartModelProducts');
		$this->Quick2cartModelProducts = new Quick2cartModelProducts;

		// Get toolbar path
		$this->toolbar_view_path = $this->comquick2cartHelper->getViewpath('vendor', 'toolbar');

		$user = JFactory::getUser();
		$userId = $user->get('id');
		$this->storeList = (array) $storeHelper->getUserStore($userId);

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Setup TJ toolbar
		$this->addTJtoolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Method to order fields
	 *
	 * @return void
	 */
	protected function getSortFields()
	{
		return array(
			'a.`id`' => JText::_('JGRID_HEADING_ID'),
			'a.`state`' => JText::_('COM_QUICK2CART_PROMOTIONS_PUBLISHED'),
			'a.`name`' => JText::_('COM_QUICK2CART_PROMOTIONS_NAME'),
			'a.`description`' => JText::_('COM_QUICK2CART_PROMOTIONS_DESCRIPTION'),
			'a.`from_date`' => JText::_('COM_QUICK2CART_PROMOTIONS_FROM_DATE'),
			'a.`exp_date`' => JText::_('COM_QUICK2CART_PROMOTIONS_EXP_DATE'),
			'a.`store_id`' => JText::_('COM_QUICK2CART_PROMOTIONS_STORE_ID'),
		);
	}

	/**
	 * Setup ACL based tjtoolbar
	 *
	 * @return  void
	 *
	 * @since   2.2
	 */
	protected function addTJtoolbar ()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_quick2cart/helpers/quick2cart.php';
		$canDo = Quick2cartHelper::getActions();

		// Add toolbar buttons
		jimport('techjoomla.tjtoolbar.toolbar');
		$tjbar = TJToolbar::getInstance('tjtoolbar', 'pull-right');

		if ($canDo->get('core.create'))
		{
			$tjbar->appendButton('promotion.add', 'TJTOOLBAR_NEW', QTC_ICON_PLUS, 'class="btn btn-small btn-success"');
		}

		if ($canDo->get('core.edit') && isset($this->items[0]))
		{
			$tjbar->appendButton('promotion.edit', 'TJTOOLBAR_EDIT', QTC_ICON_EDIT, 'class="btn btn-small btn-success"');
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				$tjbar->appendButton('promotions.publish', 'TJTOOLBAR_PUBLISH', QTC_ICON_PUBLISH, 'class="btn btn-small btn-success"');
				$tjbar->appendButton('promotions.unpublish', 'TJTOOLBAR_UNPUBLISH', QTC_ICON_UNPUBLISH, 'class="btn btn-small btn-warning"');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]))
			{
				$tjbar->appendButton('promotions.delete', 'TJTOOLBAR_DELETE', Q2C_ICON_TRASH, 'class="btn btn-small btn-danger"');
			}
		}

		$this->toolbarHTML = $tjbar->render();
	}
}
