<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 *
 * @since  2.5
 */
class Quick2cartViewAttributeSetMapping extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   STRING  $tpl  template name
	 *
	 * @return  null
	 *
	 * @since  2.5
	 */
	public function display($tpl = null)
	{
		$this->model = $this->getModel('attributesetmapping');
		$this->attributeSetsList = $this->model->getAttributeSets();
		$this->comquick2cartHelper = new comquick2cartHelper;
		$this->cats = $this->comquick2cartHelper->getQ2cCatsJoomla('', 0, 'prod_cat', ' required ');
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  null
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolBarHelper::title(JText::_('COM_QUICK2CART_ATTRIBUTESET_CATEGORY_MAPPING'), 'pencil-2');
		JToolBarHelper::apply('attributesetmapping.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('attributesetmapping.save', 'JTOOLBAR_SAVE');
		JToolBarHelper::cancel('attributesetmapping.cancel', 'JTOOLBAR_CLOSE');
		JToolBarHelper::preferences('com_quick2cart');
	}
}
