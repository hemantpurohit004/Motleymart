<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

require_once JPATH_ADMINISTRATOR . '/components/com_quick2cart/models/zone.php';

/**
 * View to edit
 *
 * @since  1.6
 */
class Quick2cartViewCustomer_Addressform extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	protected $canSave;

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
		$app = JFactory::getApplication();
		$jinput = $app->input;

		$this->form    = $this->get('Form');
		$this->item    = $this->get('Data');
		$this->params  = $app->getParams('com_quick2cart');
		$this->canSave = $this->get('CanSave');

		$Quick2cartModelZone = new Quick2cartModelZone;
		$this->countrys = $Quick2cartModelZone->getCountry();
		$this->params = JComponentHelper::getParams('com_quick2cart');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		parent::display($tpl);
	}
}
