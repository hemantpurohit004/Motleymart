<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
//$lang = JFactory::getLanguage();
//$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);
		JHtml::_('behavior.tooltip');
$document = JFactory::getDocument();
$comquick2cartHelper=new comquick2cartHelper;
//$document->addStyleSheet(JUri::base().'components/com_quick2cart/css/quick2cart.css');
/*$input=JFactory::getApplication()->input;
$cid		= $input->get(  'cid','','ARRAY' );
*/
?>
<script type="text/javascript">


/*
function submitAction(action)
{
		var form = document.adminForm;
		if(action=='publish' || action=='unpublish')
		{
				if (document.adminForm.boxchecked.value==0){
					alert('<?php echo JText::_("QTC_MAKE_SEL");?>');
				return;
				}
				switch(action)
				{
					case 'publish': form.task.value='publish';
					break

					case 'unpublish': form.task.value='unpublish';
					break
				}
			//Joomla.submitform(action);
		}
		else if(action=="newCoupon")
		{
			form.task.value='newCoupon';
		}
		else
		{
			window.location ="index.php?option=com_quick2cart&view=vendor";
		}
	form.submit();

	return;

 }		*/

 <?php if(JVERSION >= '1.6.0'){ ?>
	Joomla.submitbutton = function(action){
<?php } else { ?>
	function submitbutton( action ) {
<?php } ?>

	var form = document.adminForm;
		if(action=='publish' || action=='unpublish')
		{
			Joomla.submitform(action);
		}
		else if(action=='deletevendor')
		{
				if (document.adminForm.boxchecked.value==0){
					alert("<?php echo JText::_('QTC_MAKE_SEL');?>");
				return;
				}
				var r=confirm("<?php echo JText::_('QTC_DELETE_CONFIRM_VENDER');?>");
				if (r==true)
				{
					var aa;
				}
				else
					return;
		}
		else if(action=="addvendor")
		{
			window.location = "index.php?option=com_quick2cart&view=vendor&layout=newvendor";
			return;  // to avoid submit form
		}
		else if(action=='edit')  /** edit */
		{
			if (document.adminForm.boxchecked.value==0){
				alert("<?php echo JText::_('QTC_MAKE_SEL');?>");
			return;
			}
			else if(document.adminForm.boxchecked.value > 1)
			{
				alert("<?php echo JText::_('QTC_MAKE_ONE_SEL');?>");
				return;
			}
			//console.log(document.adminForm.boxchecked.value);
			//window.location = 'index.php?option=com_quick2cart&view=managecoupon';
		}

	submitform( action );
	return;

 }
</script>

<div class="techjoomla-bootstrap">
<form  method="post" name="adminForm" id="adminForm" class="form-validate">
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
	<table class="adminlist table table-striped table-bordered table-condensed">
		<thead>
		<tr>
			<th colspan="7" width="100%">
			   <div class="filter-search pull-left">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search_list" value="<?php echo htmlspecialchars($this->lists['search']);?>" class="input-medium" onchange="document.adminForm.submit();" />
				<button class="btn btn-success" onclick="this.form.submit();"><?php echo JText::_( 'SA_GO' ); ?></button>
				<button class="btn btn-primary" onclick="document.getElementById('search_list').value='';this.form.getElementById('filter_type').value='0';this.form.getElementById('filter_logged').value='0';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
			  </div >
			<div class="btn-group pull-right hidden-phone"> </div>
			</th>
			<th colspan="5" >
			<div style="float:right;" >
			<?php
				/*if(version_compare(JVERSION, '3.0', 'lt')) {
					$qtc_publish_style="icon-ok-sign ";
					$qtc_unpublish_style="icon-minus";
					$qtc_icon_plus="icon-plus-sign ";
				}
				else
				{ // for joomla3.0
					$qtc_publish_style="icon-checkmark ";
					$qtc_unpublish_style="icon-minus-2";
					$qtc_icon_plus=" icon-plus-2 ";
				}			*/
			?>
<!--				<button type="button" class="btn btn-success btn_height" title="<?php echo JText::_( 'SA_NEW' ); ?>" onclick="submitAction('newCoupon');"><i class="<?php echo $qtc_icon_plus; ?> icon22-white22"></i></button>
				<button type="button" class="btn btn-success btn_height" title="<?php echo JText::_( 'SA_PUBLISH' ); ?>" onclick="submitAction('publish');"><i class="<?php echo $qtc_publish_style;?> icon22-white22"></i></button>
				<button type="button" class="btn btn-warning btn_height" title="<?php echo JText::_( 'SA_UNPUBLISH' ); ?>" onclick="submitAction('unpublish');"><i class="<?php echo $qtc_unpublish_style;?> icon22-white22"></i></button>
		-->
			<!--		<a class="toolbar" onclick="if (document.adminForm.boxchecked.value==0){alert('Please first make a selection from the list');}else{ Joomla.submitbutton('publish')}" href="#">
						<span class="icon-32-publish"></span>Publish</a> -->


			</div>
			</th>
		</tr>

		<tr>
			<!-- -->
			<!--	<th width="2%" class="title">
					<?php echo JText::_( 'AD_NUM' ); ?>
				</th> -->
				<th width="2%" align="center" class="title hidden">
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
				</th>


				<th class="store_id hidden" align="left" width="12%" align="center">
					<?php echo JHtml::_('grid.sort',   JText::_( 'STORE_ID'), 'id',   $this->lists['order_Dir'],   $this->lists['order'] ); ?>
				</th>
				<th class="title" align="left" width="20%" align="center">
					<?php echo JHtml::_('grid.sort',   JText::_( 'STORE_TITLE'), 'title',   $this->lists['order_Dir'],   $this->lists['order'] ); ?>
				</th>
				<th class="vendor_name" align="left" width="20%" align="center">
					<?php echo JHtml::_('grid.sort',   JText::_( 'VENDOR_NAME'), 'title',   $this->lists['order_Dir'],   $this->lists['order'] ); ?>
				</th>
				<?php if(empty($this->site)) // show to admin
				{ ?>
				<th class="description hidden" align="left" width="12%" align="center">
					<?php echo JHtml::_('grid.sort',   JText::_( 'STORE_DESCRIPTION'), 'description',   $this->lists['order_Dir'],   $this->lists['order'] ); ?>
				</th>
				<?php }?>
<!--
				<th width="15%" class="title" align="center">
					<?php  echo JHtml::_('grid.sort',   JText::_( 'STORE_PUB'), 'live',   $this->lists['order_Dir'],   $this->lists['order'] ); ?>
				</th> -->
				<?php if(empty($this->site))
					{  // called from site
				?>
				<th class="owner hidden" align="left" width="10%" align="center">
					<?php echo JHtml::_('grid.sort',   JText::_( 'STORE_OWNER_NAME'), 'owner',   $this->lists['order_Dir'],   $this->lists['order'] ); ?>
				</th>
				<?php } ?>
				<th class="email" align="left" width="20%" align="center">
					<?php  echo JHtml::_('grid.sort',   JText::_( 'STORE_EMAIL'), 'store_email',   $this->lists['order_Dir'],   $this->lists['order'] ); ?>
				</th>
				<th width="20%" class="phone" align="center">
					<?php echo JHtml::_('grid.sort',   JText::_( 'STORE_PHONE'), 'phone',   $this->lists['order_Dir'],   $this->lists['order'] ); ?>
				</th>
				<th width="20%" class="sale" align="center">
					<?php echo JHtml::_('grid.sort',   JText::_( 'TOTAL_SALE'), 'total_sale',   $this->lists['order_Dir'],   $this->lists['order'] ); ?>
				</th>

<!--
				<th width="10%" class="title" align="left">
					<?php echo JHtml::_('grid.sort',   JText::_( 'STORE_FEE'), 'fee',   $this->lists['order_Dir'],   $this->lists['order'] ); ?>
				</th>
-->

				<?php if(!empty($this->site))
				{ ?>
				<th width="10%" class="title" align="left">
					<?php echo  JText::_( 'STORE_ROLE'); ?>
				</th>
				 <?php 	}?>

			</tr>
		</thead>

		<tbody>
		<?php
			$k = 0;
			$n=count( $this->storeinfo );
			for ($i=0 ; $i < $n; $i++)
			{
				$row 	= $this->storeinfo[$i];
				$published 	= JHtml::_('grid.published', $row, $i );	//print_r($row);
				//$link 	="index.php?option=com_quick2cart&amp;view=vendor&amp;layout=newvendor&amp;store_id=". $row->id. '';

				//$link =JUri::root()."index.php?option=com_quick2cart&view=vendor&layout=store&store_id=".$row->id;
				$link =JRoute::_(JUri::base()."index.php?option=com_quick2cart&view=vendor&layout=createstore&store_id=".$row->id);
			?>
			<tr class="<?php echo 'row$k'; ?>">
				<td align="center" class="hidden"> <!-- check box -->
					<?php echo JHtml::_('grid.id', $i, $row->id ); ?>
				</td>
				<!-- STORE ID -->
				<td align="center"  class="hidden">
					<?php echo $row->id; ?>
				</td>
				<!-- STORE NAME / store title -->
				<td align="left">
					<a href="<?php echo $link; ?>">
						<?php echo JHtml::tooltip(JText::_('QTC_TOOLTIP_VIEW_STORE'), JText::_('QTC_STORE_TOOLTIP_TITLE'), '', $row->title ) ;?>
					</a>
				</td>
				<!-- STORE OWNER NAME  -->
				<td align="left">
					<?php echo $row->username; ?>
				</td>
				<!-- STORE description -->
				<?php if(empty($this->site)) // show to admin
				{ ?>
				<td align="center" class="hidden">
					<?php echo $row->description; ?>
				</td>
				<?php }?>
				<!-- STORE PUBLISHED/UNPUBLISHED / LIVE -->
				<!-- <td align="center">
					<?php echo $published; ?>
				</td>
-->
				<!-- STORE OWNER-->
				<?php if(empty($this->site))
				{ ?>
				<td align="left"  class="hidden">

						<?php echo ($row->owner); ?>
				<?php }?>
				</td>
				<!-- store_email -->
				<td align="center">
					<?php echo $row->store_email ?>
				</td>
				<!-- STORE PHONE NO -->
				<td align="left">
					<?php echo $row->phone ?>
				</td>
				<!--Added by Sneha-->
				<!-- STORE Total Sale NO -->
				<td align="left">
					<?php
						$storeHelper=new storeHelper();
						$total_sale=$storeHelper->getTotalSalePerStore($row->id);
						if($total_sale)
							echo $comquick2cartHelper->getFromattedPrice($total_sale);
					 ?>
				</td>
				<!-- STORE FEE -->
<!--
				<td align="center">
					<?php echo $row->fee ?>
				</td>
-->

				<?php if(!empty($this->site))
				{
				$comquick2cartHelper = new comquick2cartHelper;
					$role=$comquick2cartHelper->getRole($row->role);
				?>
						<td align="center">
							<?php echo $role;?>
						</td>
				<?php }?>
			</tr>
			<?php
				$k = 1 - $k;
				}
			?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="13">
					<div class="pager"><?php echo $this->pagination->getListFooter(); ?></div>
				</td>
			</tr>
				</tfoot>
	</table>

	<input type="hidden" name="option" value="com_quick2cart" />
	<input type="hidden" name="view" value="vendor" />
	<input type="hidden" name="task" value="" />
	<?php if(!empty($this->site))
	{  // called from site
	?>
		<input type="hidden" name="layout" value="mystores" />
	<?php
	}?>
	<?php if(empty($this->site))  // called from admin
	{
	?>
		<input type="hidden" name="controller" value="vendor" />
	<?php
	}?>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
</div>
