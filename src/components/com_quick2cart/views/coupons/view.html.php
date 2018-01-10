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

jimport('joomla.application.component.view');

/**
 * View class for a list of coupons.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewCoupons extends JViewLegacy
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
	public function display ($tpl = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$user->id)
		{
			?>
			<div class="<?php echo Q2C_WRAPPER_CLASS;?> my-coupons">
				<div class="well" >
					<div class="alert alert-danger">
						<span><?php echo JText::_('QTC_LOGIN'); ?></span>
					</div>
				</div>
			</div>

			<?php
			return false;
		}

		$zoneHelper = new zoneHelper;

		// Check whether view is accessible to user
		if (!$zoneHelper->isUserAccessible())
		{
			return;
		}

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = $app->getParams('com_quick2cart');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->publish_states = array(
			'' => JText::_('JOPTION_SELECT_PUBLISHED'),
			'1'  => JText::_('JPUBLISHED'),
			'0'  => JText::_('JUNPUBLISHED')
		);

		// Get toolbar path
		$comquick2cartHelper = new comquick2cartHelper;
		$this->toolbar_view_path = $comquick2cartHelper->getViewpath('vendor', 'toolbar');

		// Get store id (list))from model and pass to  getManagecoupon()
		$this->store_role_list = $store_role_list = $comquick2cartHelper->getStoreIds();

		if ($this->store_role_list)
		{
			$this->store_id = $store_id = (!empty($change_storeto)) ? $change_storeto : $store_role_list[0]['store_id'];
			$this->selected_store = $store_id;

			if ($this->store_id)
			{
				// $this->authorized_store_id= storeid of user
				$this->authorized_store_id = $authorized_store_id = $comquick2cartHelper->createCouponAuthority($store_id);
			}
		}

		// Setup TJ toolbar
		$this->addTJtoolbar();

		$this->_prepareDocument();
		parent::display($tpl);
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
			$tjbar->appendButton('couponform.addNew', 'TJTOOLBAR_NEW', QTC_ICON_PLUS, 'class="btn btn-small btn-success"');
		}

		if ($canDo->get('core.edit') && isset($this->items[0]))
		{
			$tjbar->appendButton('couponform.edit', 'TJTOOLBAR_EDIT', QTC_ICON_EDIT, 'class="btn btn-small btn-success"');
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->published))
			{
				$tjbar->appendButton('coupons.publish', 'TJTOOLBAR_PUBLISH', QTC_ICON_PUBLISH, 'class="btn btn-small btn-success"');
				$tjbar->appendButton('coupons.unpublish', 'TJTOOLBAR_UNPUBLISH', QTC_ICON_UNPUBLISH, 'class="btn btn-small btn-warning"');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]))
			{
				$tjbar->appendButton('coupons.delete', 'TJTOOLBAR_DELETE', Q2C_ICON_TRASH, 'class="btn btn-small btn-danger"');
			}
		}

		$this->toolbarHTML = $tjbar->render();
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 */
	protected function _prepareDocument()
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_Q2C_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', JText::_('COM_QUICK2CART_ZONES_PAGE'));
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
