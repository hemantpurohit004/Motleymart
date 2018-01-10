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
require_once JPATH_SITE . '/components/com_quick2cart/models/store.php';
/**
 * View class for create order view.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.5.1
 */
class Quick2cartViewCreateOrder extends JViewLegacy
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
		$this->params = JComponentHelper::getParams('com_quick2cart');
		$user = JFactory::getUser();
		$mainframe = JFactory::getApplication();
		$input     = $mainframe->input;

		$model = $this->getModel('createorder');
		$storeModel = new Quick2cartModelstore;
		$authorisedToViewThisView = $storeModel->getStoreId($user->id);
		JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$quick2cartUsers = JFormHelper::loadFieldType('quick2cartusers', false);

		$Comquick2cartHelper = new Comquick2cartHelper;

		$defaultStore = array();
		$defaultStore['store_id'] = '';
		$defaultStore['title'] = JText::_('COM_QUICK2CART_SELET_STORE');

		$this->stores = array();
		$this->stores[] = $defaultStore;

		$storeList[] = $this->stores;

		$this->stores = array_merge($this->stores, $Comquick2cartHelper->getStoreIds($user->id));

		// Get users list
		$this->users = $quick2cartUsers->getInput();

		if (!empty($authorisedToViewThisView))
		{
			parent::display($tpl);
		}
		else
		{
			$mainframe->enqueueMessage(JText::_('COM_QUICK2CART_CREATE_ORDER_AUTHORIZATION_ERROR'), 'Warning');
		}
	}
}
