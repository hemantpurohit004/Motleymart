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

jimport('joomla.html.pane');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.modal', 'a.modal');

$lang = JFactory::getLanguage();
$lang->load('com_quick2cart', JPATH_SITE);

/*list of attributes of item*/
$params = JComponentHelper::getParams('com_quick2cart');
$qtc_base_url = JUri::base();

$document = JFactory::getDocument();

$addpre_select[] = JHtml::_('select.option','+', JText::_('QTC_ADDATTRI_PREADD'));
$addpre_select[] = JHtml::_('select.option','-', JText::_('QTC_ADDATTRI_PRESUB'));
//$addpre_select[] = JHtml::_('select.option','=', JText::_('QTC_ADDATTRI_PRESAM'));

$add_link = $qtc_base_url.'index.php?option=com_quick2cart&view=attributes&layout=attribute&tmpl=component&pid='.$pid.'&client='.$client;
$del_link= $qtc_base_url.'index.php?option=com_quick2cart&controller=attributes&task=delattribute';
?>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?>" >
<?php
if( $pid && $client )
{
	$quick2cartModelAttributes =  new quick2cartModelAttributes();
	if(!empty($item_id)) {
	$attributes = $quick2cartModelAttributes->getItemAttributes($item_id);
	$attributes_count = $quick2cartModelAttributes->getItemAttributes($item_id);
	//echo count($attributes_count);
	}

?>
	<script type="text/javascript">
		function EditAttribute(att_id,pid)
		{
				var td_id = '#item_attris'+att_id;
				var tr_id = '.att_'+att_id;
				//alert(td_id);
				techjoomla.jQuery.ajax({
				url:'<?php echo $qtc_base_url;?>?option=com_quick2cart&task=attributes.EditAttribute&att_id='+att_id+'&pid='+pid,
				type: 'GET',
				dataType: 'json',

				success: function(data)
				{
					//jQuery(td_id).html(data);
					techjoomla.jQuery(tr_id).replaceWith(data);
					if(data){
					SqueezeBox.initialize({});
					SqueezeBox.assign($$('a.modal'), {
						parse: 'rel'
					});
				}
				}
			});
		}
		function AddNewAttribute(pid,count)
		{
			var rowCount = '';
			if ( techjoomla.jQuery('#empty_attr').length == 1 ){
				rowCount = 0;
			}
			else
			{
				rowCount = techjoomla.jQuery('#item_attris >tbody >tr').length;
			}
			//alert(rowCount);
			techjoomla.jQuery.ajax({
			url:'<?php echo $qtc_base_url;?>index.php?option=com_quick2cart&task=attributes.AddNewAttribute&pid='+pid+'&count='+rowCount,
			type: 'GET',
			dataType: 'json',

			success: function(data)
			{
				if (rowCount == 0)
				{
					techjoomla.jQuery('#empty_attr').remove();
					techjoomla.jQuery("#item_attris tbody").append(data);
				}
				else
				{
					jQuery('#item_attris tr:last').after(data);
				}

				if(data)
				{
					SqueezeBox.initialize({});
					SqueezeBox.assign($$('a.modal'), {
						parse: 'rel'
				});

				SqueezeBox.trash();
			}
			}
			});
		}
		function deleteAttribute(dellink,pid)
		{
				if (confirm("<?php echo JText::_( 'COM_QUICK2CART_ATTRIBUTE_DELET_CONFIRM' ); ?>") == true)
				{
					var tr_id = '.att_'+dellink;
					techjoomla.jQuery.ajax({
					url:'<?php echo $qtc_base_url;?>index.php?option=com_quick2cart&task=attributes.delattribute&pid='+pid+'&attr_id='+dellink,
					type: 'GET',
					dataType: 'json',

					success: function(data)
					{
						techjoomla.jQuery(tr_id).remove();
						if (data.html != null)
						{
							jQuery("#item_attris").html(data);
						}
					}
				});
				}

		}
	</script>
	<div class="table-responsive">
		<table id="item_attris" class="table table-striped table-bordered table-condensed item_attris">
			<thead>
				<tr>
					<th width="35%" align="left"><b><?php echo JText::_( 'QTC_ADDATTRI_NAME' ); ?> </b></th>
					<th width="30%"	align="left"><b><?php echo JText::_( 'QTC_ADDATTRI_OPT' ); ?></b> </th>
					<th width="15%"	align="left"></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$invalid_op_price=array();

				if(!empty($attributes) )
				{
					foreach($attributes as $attributes)
					{ ?>
						<tr class="<?php echo "att_".$attributes->itemattribute_id; ?>">
							<td> <?php echo $attributes->itemattribute_name; ?></td>
							<td id="<?php echo "att_list_".$attributes->itemattribute_id; ?>">
								<?php
								$comquick2cartHelper = new comquick2cartHelper;
								$currencies=$params->get('addcurrency');
								$curr=explode(',',$currencies);
								//$atri_options = $comquick2cartHelper->getAttributeOptionCurrPrice($attributes->itemattribute_id,implode($curr, "','"));
								$atri_options = $comquick2cartHelper->getAttributeDetails($attributes->itemattribute_id	);

								foreach($atri_options as $atri_option)
								{?>
									<div>
									<?php
										$noticeicon="";
										 $opt_str= $atri_option->itemattributeoption_name.": ".$atri_option->itemattributeoption_prefix;
										 $itemnotice='';
										foreach($curr as $value)
										{
											if(property_exists($atri_option,$value))
											{
												if($atri_option->$value)
												{
													$opt_str.= $atri_option->$value." ".$value.", ";
												}
											}
											else
											{
												$invalid_op_price[$value]=$value;	// add current cur
												if(empty($itemnotice))
												{
													$noticeicon="<i class='" . Q2C_TOOLBAR_ICON_HOME . "'></i> ";
												}
											}
										}
										//echo $detail_str=(empty($itemnotice))?$opt_str:$noticeicon.$opt_str;
										echo $detail_str=$noticeicon.$opt_str;
									?>
									</div>
									<?php
								}
								?>

							</td>
							<?php  $edit_link = $add_link.'&attr_id='.$attributes->itemattribute_id.'&edit=1';?>
							<?php  $del_link= $del_link.'&attr_id='.$attributes->itemattribute_id; ?>
							<td>
								<a  rel="{handler: 'iframe', size: {x : window.innerWidth-450, y : window.innerHeight-250}, onClose: function(){EditAttribute('<?php echo $attributes->itemattribute_id; ?>','<?php echo $item_id;?>');}}" class="btn btn-primary modal qtc_modal" href="<?php echo $edit_link; ?> ">
									<i class="<?php echo $qtc_icon_edit; ?>"></i>
								</a>
								 <button type="button" class="btn  btn-small btn-danger "  onclick="deleteAttribute('<?php echo $attributes->itemattribute_id;?>','<?php echo $item_id; ?>' )">
									<i class="<?php echo Q2C_ICON_TRASH; ?>"></i>
								</button>

							 </td>
						</tr>
				<?php } // end of foreach($attributes as $attributes)
				?>
				<?php
				$count_tr = '';
				}
				else
				{
					$count_tr = 1;
					?>
						<tr id="empty_attr">
							<td colspan="3"> <?php echo JText::_( 'QTC_ADDATTRI_EMPTY_MSG' ); ?></td>
						</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>
	<div style="clear:both;"></div>
		<?php
		if(count($invalid_op_price) > 0)
		{
			$msg_curr=implode("/",$invalid_op_price);
		?>
			<div class="alert ">
				<button type="button" class="close" data-dismiss="alert"></button>
					<strong><?php echo JText::_('QTC_NOTE'); ?></strong><?php echo JText::sprintf('QTC_NOTICE_ATTRIBUTE_OPTION_CURR_NOT_FOUND',$msg_curr,$noticeicon); ?>
			</div>
		<?php
		}
}
	?>

	<a rel="{handler: 'iframe', size: {x : window.innerWidth-450, y : window.innerHeight-150}, onClose: function(){AddNewAttribute('<?php echo $item_id; ?>','<?php echo $count_tr;?>');}}" class="btn btn-primary btn-small <?php echo ($button_dis == ""?'modal':$button_dis)?> qtcAddAttributeLink" href="<?php echo $add_link; ?>" style="display: inline;">
	<?php echo JText::_('QTC_ADD_ATTRIB'); ?></a>

	<?php
?>
</div>

<div style="clear:both;"></div>
