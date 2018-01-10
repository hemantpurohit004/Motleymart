<?php 
/**
 *  @package    Quick2Cart
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die( 'Restricted access' );
JHtml::_('behavior.formvalidation');
$document =JFactory::getDocument();

//$document->addStyleSheet(JUri::root().'components/com_quick2cart/bootstrap/css/bootstrap.min.css' );



$js_key1="Joomla.submitbutton = function(task)
{
	if(task=='add')
	{
		window.location = 'index.php?option=com_quick2cart&view=shipmanager&layout=default';
	}
	else if(task=='remove')
	{
		Joomla.submitform(task);
	} 
	else
		window.location = 'index.php?option=com_quick2cart';
}";


	
	$document->addScriptDeclaration($js_key1);



?>



<div class="techjoomla-bootstrap" >
<form action="" name="adminForm" id="adminForm" class="form-validate" method="post">

		<?php
// @ sice version 3.0 Jhtmlsidebar for menu
    if(JVERSION>=3.0):
         if (!empty( $this->sidebar)) : ?>
            <div id="j-sidebar-container" class="span2">
                <?php echo $this->sidebar; ?>
            </div>
            <div id="j-main-container" class="span10">
        <?php else : ?>
            <div id="j-main-container">
        <?php endif;
    endif;
    ?>
				
				
				<table class="table table-condensed">
						
						<th title='<?php echo JText::_('DELET');?>'><?php echo JText::_('DELETE'); ?></th>
						<th title='<?php echo JText::_('KEY');?>'><?php echo JText::_('KEY'); ?></th>
						<th title='<?php echo JText::_('VALUE');?>' ><?php echo JText::_('VALUE'); ?></th>
						<th title='<?php echo JText::_('SHIP_VALUE');?>'><?php echo JText::_('SHIP_VALUE'); ?></th>
						
						
				<?php
				
			$i=0;
				
				foreach($this->shippinglist as $key)
				{	
					
					?>
						
				
							<tr class="row<?php echo $i % 2; ?>">
									<td class="center">
									<?php echo JHtml::_('grid.id', $i, $key->id); ?>
						
									</td>
									<td>
									<?php echo $key->key; ?>
									</td>
									<td>
									<?php echo $key->value; ?>
									</td>
									
									
									<td>
										
									<?php 
									
									$arr = array();
									foreach($key->shipcharges as $charges)
										{
											// echo $charges->shipprice; echo "  ";
											array_push($arr,$charges->shipprice);
											} 
											
										
											$arr_str=implode(', ',$arr);
										echo $arr_str;	
											?>
									
									</td>
									
									
									
							</tr>
							
		<?php	$i++;	}
		?>
		</table>
		
		
		
		
		<input type="hidden" name="option" value="com_quick2cart" />

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		
		<input type="hidden" name="controller" value="shipmanager" />
		<input type="hidden" name="view" value="shipmanager" /> 
		<input type="hidden" name="jversion" value="<?php echo JText::_( 'JVERSION'); ?>" />
		
		
		
	</form>	
</div>
