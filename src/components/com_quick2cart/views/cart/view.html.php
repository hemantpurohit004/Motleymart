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

jimport('joomla.application.component.view');

/**
 * This Class supports Cart.
 *
 * @package     Joomla.Site
 * @subpackage  com_quick2cart
 * @since       1.0
 */
class Quick2cartViewcart extends JViewLegacy
{
	/**
	 * Render view.
	 *
	 * @param   array  $tpl  An optional associative array of configuration settings.
	 *
	 * @since   1.0
	 * @return   null
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();
		$model	= $this->getModel('cart');
		$params = JComponentHelper::getParams('com_quick2cart');
		$input = JFactory::getApplication()->input;
		$layout = $input->get('layout');

		JLoader::import('cartcheckout', JPATH_SITE . '/components/com_quick2cart/models');
		$cartCheckoutModel = new Quick2cartModelcartcheckout;

		// Get cart details
		$cart = $cartCheckoutModel->getCheckoutCartitemsDetails();
		$this->cart = $cart;

		// Get promtion discount
		$path = JPATH_SITE . '/components/com_quick2cart/helpers/promotion.php';

		if (!class_exists('PromotionHelper'))
		{
			JLoader::register('PromotionHelper', $path);
			JLoader::load('PromotionHelper');
		}

		$promotionHelper = new PromotionHelper;
		$this->coupon = $promotionHelper->getSessionCoupon();
		$this->promotions = $promotionHelper->getCartPromotionDetail($this->cart, $this->coupon);

		/*if ($user->id != 0)
		{
			$userdata = $cartCheckoutModel->userdata();
			$this->userdata = $userdata;
		}*/

		parent::display($tpl);
	}
}
