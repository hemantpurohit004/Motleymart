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
 * View class for a list of payouts.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewPayouts extends JViewLegacy
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

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = $app->getParams('com_quick2cart');

		$this->comquick2cartHelper = new comquick2cartHelper;
		$this->reportsHelper = new reportsHelper;

		$this->logged_userid = $user->id;
		$this->totalpaidamount = $this->reportsHelper->getTotalPaidOutAmount($this->logged_userid);
		$this->totalAmount2BPaidOut = $this->reportsHelper->getTotalAmount2BPaidOut($this->logged_userid);
		$this->commission_cut = $this->reportsHelper->getCommissionCut($this->logged_userid);
		$this->balanceamt1 = $this->totalAmount2BPaidOut - $this->totalpaidamount - $this->commission_cut;

		// Get toolbar path
		$this->toolbar_view_path = $this->comquick2cartHelper->getViewpath('vendor', 'toolbar');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Method Prepares the document
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
			$this->params->def('page_heading', JText::_('COM_QUICK2CART_MY_CASHBACK'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

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
