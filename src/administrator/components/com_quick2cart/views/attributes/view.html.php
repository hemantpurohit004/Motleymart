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
class Quick2cartViewattributes extends JViewLegacy
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
		$input  = JFactory::getApplication()->input;
		$layout = $input->get('layout', '');

		if ($layout == 'attribute')
		{
			$id                     = $input->get('attr_id', 0, 'INT');
			$this->itemattribute_id = $id;

			// $this->_setToolBar();

			$attribute = $this->get('Attribute');

			if ($attribute)
			{
				$this->itemattribute_name   = $attribute->itemattribute_name;
				$this->attribute_compulsary = $attribute->attribute_compulsary;
			}

			$attribute_options   = $this->get('Attributeoption');
			$this->attribute_opt = $attribute_options;
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
	public function _setToolBar()
	{
		$document = JFactory::getDocument();

		// Commented by aniket
		// $document->addStyleSheet(JUri::base().'components/com_quick2cart/assets/css/quick2cart.css'); Aniket
		$bar      = JToolBar::getInstance('toolbar');
		JToolBarHelper::title(JText::_('QTC_SETT'), 'icon-48-quick2cart.png');
		JToolBarHelper::save('save', JText::_('QTC_SAVE'));
		JToolBarHelper::cancel('cancel', JText::_('QTC_CLOSE'));
	}
}
