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

// 1.check user is logged or not
$mainframe = JFactory::getApplication();
$input=$mainframe->input;
$store_id		= $input->get( 'store_id','0' );
$user=JFactory::getUser();
if(!$user->id){
?>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?>" >
<div class="well" >
	<div class="alert alert-danger">
		<span ><?php echo JText::_('QTC_LOGIN'); ?> </span>
	</div>
</div>
</div>
<?php
	return false;
}
//$lang = JFactory::getLanguage();
//$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);
/*
	//include(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_quick2cart'.DS.'views'.DS.'vendor'.DS.'tmpl'.DS.'default.php');
	$comquick2cartHelper = new comquick2cartHelper;
	$view=$comquick2cartHelper->getViewpath('vendor','',"SITE","ADMIN");
	ob_start();
		include($view);
	$html  = ob_get_contents();
	ob_end_clean();

echo $html;*/
?>
<?php
//$lang = JFactory::getLanguage();
//$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);
		JHtml::_('behavior.tooltip');
$document = JFactory::getDocument();
//$document->addStyleSheet(JUri::base().'components/com_quick2cart/assets/css/quick2cart.css');
/*$input=JFactory::getApplication()->input;
$cid		= $input->get(  'cid','','ARRAY' );
*/
?>
<script type="text/javascript">



function submitAction(action)
{
		var form = document.adminForm;
		console.log(action);
		if(action=='publish' || action=='unpublish' || action=='delete' || action=="edit")
		{
				if (document.adminForm.boxchecked.value==0)
				{
					alert("<?php echo $this->escape(JText::_('QTC_MAKE_SEL')); ?>");
					return;
				}
				switch(action)
				{
					case 'publish': form.task.value='publish';
					break

					case 'unpublish': form.task.value='unpublish';
					break

					case 'delete':
						var r=confirm("<?php echo JText::_('QTC_DELETE_CONFIRM_VENDER');?>");
						if (r==true)
						{
							var aa;
							form.task.value='delete';
						}
						else
						{
							return false;
						}

					break
					case "edit":

						if(document.adminForm.boxchecked.value > 1)
						{
							alert("<?php echo JText::_('QTC_MAKE_ONE_SEL');?>");
							return;
						}
						form.task.value='edit';
					break;


				}	//switch end
			//Joomla.submitform(action);
		}
		else if(action=="addNew")
		{
			form.task.value='addNew';
		}
		else
		{
			window.location = 'index.php?option=com_quick2cart&view=vendor';
		}
form.submit();
	return;

 }
</script>

<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
<form  method="post" name="adminForm" id="adminForm" class="form-validate">
	<legend><?php echo sprintf(JText::_('QTC_MANAGE_SPECIFIC_STORE'),"");?></legend>
	<table class="  table table-striped table-condensed">
		<thead>
		<tr>
			<th colspan="7">
			<div style="float:right;" >

			<button type="button" class="btn btn-info  btn_margin" onclick="window.open('<?php echo JRoute::_('index.php?option=com_zoo&view=submission&layout=submission&Itemid=1919&store_id='.$store_id	);?>','_self')" > <i class="<?php echo QTC_ICON_PLUS; ?> <?php echo Q2C_ICON_WHITECOLOR; ?>"></i> <?php echo JText::_( 'QTC_MANAGE_STORE_ADD_PROD' ); ?></button>
			<button type="button" class="btn btn-info  btn_margin" onclick="window.open('<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders&layout=storeorder');?>','_self')" > <i class="<?php echo QTC_ICON_CART;?> <?php echo Q2C_ICON_WHITECOLOR; ?>"></i> <?php echo JText::_( 'QTC_MANAGE_STORE_ORDERS' ); ?></button>
			<button type="button" class="btn btn-info  btn_margin" onclick="window.open('<?php echo JRoute::_('index.php?option=com_quick2cart&view=orders&layout=mycustomer');?>','_self')" > <i class="<?php echo QTC_ICON_USER;?>"></i> <?php echo JText::_( 'QTC_MANAGE_STORE_CUSTOMER' ); ?></button>
			<button type="button" class="btn btn-info  btn_margin" onclick="window.open('<?php echo JRoute::_('index.php?option=com_quick2cart&view=managecoupon&layout=default');?>','_self')" > <i class="<?php echo Q2C_ICON_ARROW_CHEVRON_RIGH; ?> <?php echo Q2C_ICON_WHITECOLOR; ?>"></i> <?php echo JText::_( 'QTC_MANAGE_STORE_COUPON' ); ?></button>

			</div>
			</th>
		</tr>
		</thead>

	</table>
	<!-- Providing store info -->
	<div class="row" > <!-- store info div starts -->
	<?php
		if(!empty($this->storeDetailInfo))
		{
			$sinfo=$this->storeDetailInfo;
			$comquick2cartHelper = new comquick2cartHelper;
			echo $comquick2cartHelper->getStoreDetailHTML($sinfo);
	 ?>

		<?php }
		//print"<pre>";print_r($this->storeDetailInfo);
		?>

	</div> <!-- store info div END -->

	<input type="hidden" name="option" value="com_quick2cart" />
	<input type="hidden" name="view" value="vendor" />
	<input type="hidden" name="task" value="" />
	<?php if(!empty($this->site))
	{  // called from site
	?>
	<!-- 	<input type="hidden" name="layout" value="mystores" /> -->
	<?php
	}?>
	<?php if(empty($this->site))  // called from admin
	{
	?>
	<!-- 	<input type="hidden" name="controller" value="vendor" /> -->
	<?php
	}?>

	<input type="hidden" name="boxchecked" value="0" />

	<?php echo JHtml::_( 'form.token' ); ?>
</form>
</div>

