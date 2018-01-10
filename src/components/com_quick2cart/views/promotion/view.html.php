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
 * View to edit
 *
 * @since  1.6
 */
class Quick2cartViewPromotion extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

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
		JFactory::getDocument()->addStyleSheet(JUri::base() . 'administrator/components/com_quick2cart/assets/css/quick2cart.css');

		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$model = $this->getModel();

		$comquick2cartHelper = new comquick2cartHelper;
		$storeHelper = $comquick2cartHelper->loadqtcClass(JPATH_SITE . "/components/com_quick2cart/helpers/storeHelper.php", "storeHelper");
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$this->storeList = (array) $storeHelper->getUserStore($userId);

		// Get toolbar path
		$this->toolbar_view_path = $comquick2cartHelper->getViewpath('vendor', 'toolbar');

		if (empty($this->storeList))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_QUICK2CART_CREATE_ORDER_AUTHORIZATION_ERROR'), 'Warning');

			return false;
		}

		if ($this->item->id)
		{
			$this->conditionList = $model->getRuleConditions($this->item->id);
			$this->conditionMaxCount = $model->getConditionsMax($this->item->id);
			$this->discount = $model->getDiscountRecords($this->item->id);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->discount_type = array(
		'flat' => JText::_('C_FLAT'), 'percentage' => JText::_('C_PER'));

		$this->condition_type = array('AND' => JText::_('COM_QUICK2CART_CONDITION_TRUE_ALL'),'OR' => JText::_('COM_QUICK2CART_CONDITION_TRUE_ANY'));

		$this->promotionDescription = $model->generatePromotionDescription($this->item);

		parent::display($tpl);
	}
}
