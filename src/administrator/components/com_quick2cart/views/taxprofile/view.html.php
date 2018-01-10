<?php
/**
 * @version     2.2
 * @package     com_quick2cart
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <contact@techjoomla.com> - http://techjoomla.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class Quick2cartViewTaxprofile extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$layout = $jinput->get('layout','edit');
		$model = $this->getModel('taxprofile');

		if ($layout == 'edit')
		{
			$this->state	= $this->get('State');
			$this->item		= $this->get('Item');
			$this->form		= $this->get('Form');

			// Get taxprofile_id
			$taxprofile_id = $app->input->get('id',0);

			// Getting saved tax rules.
			if (!empty($taxprofile_id))
			{
				$this->taxrules = $model->getTaxRules($taxprofile_id);
			}

			// Get store name while edit view
			if (!empty($this->item->id) && !empty($this->item->store_id))
			{
				$comquick2cartHelper = new comquick2cartHelper;
				$this->storeDetails = $comquick2cartHelper->getSoreInfo($this->item->store_id);

				// Getting tax rates and Adress types
				$this->taxrate = $model->getTaxRateListSelect($this->item->store_id, '');
				$this->address = $model->getAddressList();
			}

			// Check for errors.
			if (count($errors = $this->get('Errors'))) {
				throw new Exception(implode("\n", $errors));
			}

			$this->addToolbar();
		}
		else
		{
			$this->taxRule_id = $jinput->get('id');
			$defaultTaxRateId = '';
			$defaultAddressId = '';

			// Getting saved tax rules.
			if (!empty($this->taxRule_id))
			{
				$this->taxrules = $model->getTaxRules('', $this->taxRule_id);

				if (!empty($this->taxrules))
				{
					$defaultTaxRateId = $this->taxrules[0]->taxrate_id;
					$defaultAddressId = $this->taxrules[0]->address;
				}

				// Get store id of taxrule
				$taxHelper = new taxHelper;
				$store_id = $taxHelper->getStoreIdFromTaxrule($this->taxRule_id);

				if (empty($store_id))
				{
					$this->qtcStoreNotFoundMsg();
				}

				// Getting tax rates and Adress types
				$this->taxrate = $model->getTaxRateListSelect($store_id, $defaultTaxRateId);
				$this->address = $model->getAddressList($defaultAddressId);
			}



		}
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);

		if ($isNew)
		{
			$viewTitle = JText::_('COM_QUICK2CART_ADD_TAXPROFILE');
		}
		else
		{
			$viewTitle = JText::_('COM_QUICK2CART_EDIT_TAXPROFILE');
		}

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title($viewTitle, 'pencil-2');
		}
		else
		{
			JToolBarHelper::title($viewTitle, 'taxprofile.png');
		}

        if (isset($this->item->checked_out)) {
		    $checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        } else {
            $checkedOut = false;
        }
		$canDo		= Quick2CartHelper::getActions();

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit')||($canDo->get('core.create'))))
		{

			JToolBarHelper::apply('taxprofile.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('taxprofile.save', 'JTOOLBAR_SAVE');
		}
		if (!$checkedOut && ($canDo->get('core.create'))){
			JToolBarHelper::custom('taxprofile.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		if (empty($this->item->id)) {
			JToolBarHelper::cancel('taxprofile.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('taxprofile.cancel', 'JTOOLBAR_CLOSE');
		}

	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function qtcStoreNotFoundMsg()
	{
		?>
		<div class="techjoomla-bootstrap" >
			<div class="well" >
				<div class="alert alert-error">
					<span ><?php echo JText::_('QTC_SOMTHING_IS_WRONG_STORE_ID_NOT_FOUND'); ?> </span>
				</div>
			</div>
		</div><!-- eoc techjoomla-bootstrap -->
		<?php
		return false;
	}
}
