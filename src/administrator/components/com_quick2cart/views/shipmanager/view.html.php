<?php
/**
 *  @package    Quick2Cart
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
// no direct access
defined( '_JEXEC' ) or die( ';)' );

jimport( 'joomla.application.component.view');


class quick2cartViewshipmanager extends JViewLegacy
{
	

	function display($tpl = null)
	{
		$this->_setToolBar();
		
		$mainframe = JFactory::getApplication();
		$jinput=$mainframe->input;
		// $layout		= $jinput->get( 'layout','' ); 
		//$this->setLayout('list');
		$option = $jinput->get('option');
		$model = $this->getModel('shipmanager');
		$country=$model->getCountry();	
		$this->assignRef("country",$country);		

		$model = $this->getModel('shipmanager');
		$shippinglist=$model->getshippinglist();	
		$this->assignRef("shippinglist",$shippinglist);	
		
		 // FOR DISPLAY SIDE FILTER
     if(JVERSION>=3.0)
            $this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}//function display ends here
	
	function _setToolBar()
	{	// Get the toolbar object instance
		//$delmsg=JText::_('C_ORDER_DELETE_CONF');
		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::title( JText::_( 'QTC_SHIPMANAGER' ), 'icon-48-quick2cart.png' );
		$delmsg=JText::_('C_ORDER_DELETE_CONF');
		$mainframe = JFactory::getApplication();
		$jinput=$mainframe->input;
		$layout		= $jinput->get( 'layout','' );
		
		if($layout=='list')
		{
		JToolBarHelper::addNew();
		JToolbarHelper::deleteList($delmsg, 'remove','JTOOLBAR_DELETE');
		}
		//JToolBarHelper::cancel( 'cancel', 'Close' );
	
	}
	
	
	
}// class
