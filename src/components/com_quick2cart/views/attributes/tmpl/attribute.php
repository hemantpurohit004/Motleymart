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

$jinput = JFactory::getApplication()->input;
$params = JComponentHelper::getParams('com_quick2cart');
$qtc_base_url = JUri::base();

$document = JFactory::getDocument();
$currencies=$params->get('addcurrency');

$entered_numerics= "'".JText::_('QTC_ENTER_NUMERICS')."'";
$js_key="techjoomla.jQuery( document ).ready(function() {
 var edit = ".$jinput->get( 'edits',0,'INT').";

 if (edit === 3) {
parent.SqueezeBox.close(); }
});";
$document = JFactory::getDocument();

?>
<script type="text/javascript">
	// Globle params
	var qtcJsData = {
			Q2C_ICON_TRASH:"<?php echo Q2C_ICON_TRASH; ?> ",
			urfnName : function() {
			   return '';
			}
		};

</script>
<?php
if (version_compare(JVERSION, '1.6.0', 'ge'))
{
	$js_key .="
	Joomla.submitbutton = function(task){ ";
}
else
{
	$js_key .="
		function submitbutton( task ){";
}
	$js_key.="
		if (task == 'attributes.cancel')
		{";
	        if (version_compare(JVERSION, '1.6.0', 'ge'))
				$js_key.="Joomla.submitform(task);";
			else
				$js_key.="document.adminForm.submit();";
	    $js_key.="
	    }else{
			var validateflag = document.formvalidator.isValid(document.adminForm);
			if (validateflag){";
				if (version_compare(JVERSION, '1.6.0', 'ge')){
					$js_key.="
				Joomla.submitform(task);";
				}else{
					$js_key.="
				document.adminForm.submit();";
				}
			$js_key.="
			}else{
				return false;
			}
		}
	}
	function checkfornum(el)
	{
		var i =0 ;
		for(i=0;i<el.value.length;i++){
		   if (el.value.charCodeAt(i) > 47 && el.value.charCodeAt(i) < 58) { alert('Numerics Not Allowed'); el.value = el.value.substring(0,i); break;}
		}
	}
function addopt(){

		var curr='".$currencies ."';
		var temp = new Array();
		var temp= curr.split(',');

		var newElem = techjoomla.jQuery('.qtc_attributeOpTable tbody tr:last').clone();

		/* Fine new tr number from last tr number. id=attri_opts2 */
		var trIdLenght = 'attri_opts'.length;
		/* Don't add var to lastTrIndex*/
		num = newElem.attr('id').slice(trIdLenght);
		var newNum  = (+num) + 1;
		newElem.attr('id', 'attri_opts' + newNum);


		// manipulate the name/id values of the input inside the new element

		//newElem.find('select[name=\"att_detail[attri_opt][' + num + '][status]\"]').attr({'name': 'att_detail[attri_opt][' + newNum + '][status]','value':1 });
		newElem.find('td input[name=\"att_detail[attri_opt][' + num + '][name]\"]').attr({'name': 'att_detail[attri_opt][' + newNum + '][name]','value':''});

		newElem.find('select[name=\"att_detail[attri_opt][' + num + '][prefix]\"]').attr({'name': 'att_detail[attri_opt][' + newNum + '][prefix]','value':'' });

		// newElem.find('input[name=\"att_detail[attri_opt][' + num + '][currency]['+temp[i]+']\"]').attr({'name': 'att_detail[attri_opt][99][currency]['+temp[i]+']]','value':'' });

		var currdiv=newElem.find('.qtc_currencey_textbox');
		var index=0;
		currdiv.find(':input')
		.each(function()
			{
					var newname='att_detail[attri_opt]['+newNum+'][currency]['+temp[index]+']';
					techjoomla.jQuery(this).attr('name',newname);
					techjoomla.jQuery(this).val('');
					index++;
			});

		//var ordernum=newElem.find('input[name=\"att_detail[attri_opt][' + num + '][order]\"]').val();
		var ordernum =  techjoomla.jQuery('.qtc_attributeOpTable tbody tr:last .qtc-attribute-Option-order').val();
		var newordernum  = (+ordernum) + 1;
		newElem.find('td .qtc-attribute-Option-order').attr({'name': 'att_detail[attri_opt][' + newNum + '][order]','value':newordernum });
		techjoomla.jQuery('.qtc_attributeOpTable tbody .btnAdd').last().replaceWith('<button type=\"button\" class=\"btn btn-mini btn-danger qtcRemoveOption\" id=\"btnRemove'+num+'\"  onclick=\"techjoomla.jQuery(this).closest(\'tr\').remove();\" ><i class=\"' + qtcJsData.Q2C_ICON_TRASH + ' \"></i></button> ');

		// insert the new element after the last 'duplicatable' input field
		techjoomla.jQuery('.qtc_attributeOpTable tbody tr:last').after(newElem);
		techjoomla.jQuery('#attri_opts' + num ).focus();

		techjoomla.jQuery('#attri_opts' + newNum).find('td input[name=\"att_detail[attri_opt][' + newNum + '][name]\"]').val('');
}


function removeopt(elem,id)
{
	var opt_id = techjoomla.jQuery('input[name=\"att_detail[attri_opt][' + id + '][id]\"]').val();
	if (opt_id){
	// var confirm = confirm('Do you want to remove this option?');
	if (1){
		techjoomla.jQuery.ajax({
			url: '".$qtc_base_url."/index.php?option=com_quick2cart&controller=attributes&task=delattributeoption&tmpl=component&opt_id='+opt_id,
			type: 'GET',
			success: function(msg)
			{
				// window.location.reload();
			}
		});
	}
}
techjoomla.jQuery(elem).parent().remove();

}
function qtc_closePopUp()
{
	window.setTimeout('closeme();', 300);

}
function closeme()
{
	parent.SqueezeBox.close();
}

function saveAttributeOptionCurrency(currdata,pid)
	{
		var currvalue='';
		techjoomla.jQuery('.currtext').each(function() {
			var bval = techjoomla.jQuery(this).val();
			var bid = techjoomla.jQuery(this).attr('id');
			currvalue+=bval+',';

		});
	}
function qtc_ispositive(ele)
{
		var val=ele.value;
		if (val==0 || val < 0)
		{
			ele.value='';
			alert('Enter positive order ');
			return false;
		}
	}
";
// $document->addScript(JUri::root().'components/com_quick2cart/assets/js/order.js');
$document->addScriptDeclaration($js_key);
$addpre_select = array();
$addpre_select[] = JHtml::_('select.option','+', JText::_('QTC_ADDATTRI_PREADD'));
$addpre_select[] = JHtml::_('select.option','-', JText::_('QTC_ADDATTRI_PRESUB'));
// $addpre_select[] = JHtml::_('select.option','=', JText::_('QTC_ADDATTRI_PRESAM'));

$pid =  $jinput->get('pid',0,'INTEGER');
$client =  $jinput->get( 'client','','STRING');
$edit =  $jinput->get( 'edit',0,'INTEGER');
$path = JPATH_SITE. '/components/com_quick2cart/models/attributes.php';

if (!class_exists('quick2cartModelAttributes'))
{
	//require_once $path;
	 JLoader::register('quick2cartModelAttributes', $path );
	 JLoader::load('quick2cartModelAttributes');
}

?>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?> qtc_container qtcattributePopUp" id="qtc_container">
<form method='POST' name='adminForm' class="form-validate" action='index.php' id='no-more-tables'>

	<legend><?php echo JText::_('QTC_ADD_ATTRI');?></legend>
	<div class="">
		<div class=" ">
			<table class="table  table-condensed" >
			<thead>
				<tr>
					<th width="42%" align="left"><?php echo JText::_( 'QTC_ADDATTRI_NAME' ); ?> </th>
					<th width="32%"	align="left"><?php echo JText::_( 'QTC_ADDATTRI_FIELD_TYPE_TO_USE' ); ?></th>
					<th width="%"	align="left"><?php echo JText::_( 'QTC_ATT_COMPALSARY_CK' ); ?> </th>
				</tr>
			</thead>
			<tbody>
				<td data-title="<?php echo JText::_('QTC_ADDATTRI_NAME');?>">
					<input id="atrri_name_id" class="input-small bill required" type="text" value="<?php echo (isset($this->itemattribute_name))? htmlentities($this->itemattribute_name):''; ?>" maxlength="250"  name="att_detail[attri_name]" title="<?php echo JText::_('QTC_ADDATTRI_NAME_DESC')?>">
					<input type="hidden" name="att_detail[product_id]" value="<?php echo $pid ?>" />
					<input type="hidden" name="att_detail[client]" value="<?php echo $client ?>" />
				</td>
				<td data-title="<?php echo JText::_('QTC_ADDATTRI_FIELD_TYPE_TO_USE');?>">
					<?php
					$fields = array();
					$default =  !empty($this->attributeFieldType)? $this->attributeFieldType :"Select";
					$tableDisplay ='display:table';

					if ($default == 'Textbox')
					{
						$tableDisplay ='display:none';
					}

					$fields[] = JHtml::_('select.option','Select', JText::_('QTC_ADDATTRI_SELECT_FIELD'));
					$fields[] = JHtml::_('select.option','Textbox', JText::_('QTC_ADDATTRI_TEXT_FIELD'));

					$fnparam = "this,'qtc_container'";
					echo JHtml::_('select.genericlist', $fields, "att_detail[fieldType]", 'class="no_chzn  qtcfieldType" onChange="qtc_fieldTypeChange('.$fnparam.')"', "value", "text",$default);
				?>
				</td>
				<td data-title="<?php echo JText::_('QTC_ATT_COMPALSARY_CK');?>">
					<?php
						$qtc_ck_att="checked";
						if (isset($this->attribute_compulsary))
						{
							$qtc_ck_att=($this->attribute_compulsary)?"checked":"";
						}
					?>

						<input type="checkbox" name="att_detail[iscompulsary_attr]" autocomplete="off" <?php echo $qtc_ck_att;?>  >
				</label>

				</td>
			</tbody>
			</table>
		</div>
	</div>

<!--
	<div class="">
		<div class="col-md-12">
			<div class="span4">
				<input id="atrri_name_id" class="input-small bill required" type="text" value="<?php echo (isset($this->itemattribute_name))?$this->itemattribute_name:''; ?>" maxlength="250"  name="att_detail[attri_name]" title="<?php echo JText::_('QTC_ADDATTRI_NAME_DESC')?>">
				<input type="hidden" name="att_detail[product_id]" value="<?php echo $pid ?>" />
				<input type="hidden" name="att_detail[client]" value="<?php echo $client ?>" />
			</div>
			<div class="span4" style="">
				<?php
					$fields = array();
					$default =  !empty($this->attributeFieldType)? $this->attributeFieldType :"Select";
					$tableDisplay ='display:table';

					if ($default == 'Textbox')
					{
						$tableDisplay ='display:none';
					}

					$fields[] = JHtml::_('select.option','Select', JText::_('QTC_ADDATTRI_SELECT_FIELD'));
					$fields[] = JHtml::_('select.option','Textbox', JText::_('QTC_ADDATTRI_TEXT_FIELD'));

					$fnparam = "this,'qtc_container'";
					echo JHtml::_('select.genericlist', $fields, "att_detail[fieldType]", 'class="no_chzn  qtcfieldType" onChange="qtc_fieldTypeChange('.$fnparam.')"', "value", "text",$default);
				?>
			</div>
			<div class="span4 ">

					<?php
						$qtc_ck_att="checked";
						if (isset($this->attribute_compulsary))
						{
							$qtc_ck_att=($this->attribute_compulsary)?"checked":"";
						}
					?>

						<input type="checkbox" name="att_detail[iscompulsary_attr]" autocomplete="off" <?php echo $qtc_ck_att;?> style="width:100%;" >

			</div>
		</div>
	</div>

-->

	<!--
	<div class="form-group">
		<h4><?php echo JText::_('QTC_ADDATTRI_NAME')?></h4>
		<div class="col-sm-10">
			<input id="atrri_name_id" class="input-medium bill required" type="text" value="<?php echo (isset($this->itemattribute_name))?$this->itemattribute_name:''; ?>" maxlength="250"  name="attri_name" title="<?php echo JText::_('QTC_ADDATTRI_NAME_DESC')?>">
	</div>
	</div>
	<div class="form-group">
		<label class="checkbox">
		<?php
			$qtc_ck_att="checked";
			if (isset($this->attribute_compulsary))
			{
				$qtc_ck_att=($this->attribute_compulsary)?"checked":"";
			}
		?>
			<input type="checkbox" name="iscompulsary_attr" autocomplete="off" <?php echo $qtc_ck_att;?> > <h5><?php echo JText::_('QTC_ATT_COMPALSARY_CK')?></h5	>
		</label>
	</div>
	-->
	<?php
	$k = 1;
	$lastkey_opt = count($this->attribute_opt);
	?>
<table class="table table-condensed item_attris_opt qtc_attributeOpTable"><!-- the table width is fixed to 450px -->
	<thead>
		<tr>
			<th width="140px" align="left"><b><?php echo JText::_( 'QTC_ADDATTRI_OPTNAME' ); ?> </b></th>
			<th width="40px"	align="left"><b><?php echo JText::_( 'QTC_ADDATTRI_OPTPREFIX' ); ?></b> </th>
			<th width="550px"	align="left"><b><?php echo JText::_( 'QTC_ADDATTRI_OPTVAL' ); ?></b> </th>
			<th width="130px"	align="left"><b><?php echo JText::_( 'QTC_ADDATTRI_OPTORDER' ); ?></b> </th>
			<th width="20px"	align="left"></th>
		</tr>
	</thead>
	<tbody>
	<?php
	for($k = 0; $k <= count($this->attribute_opt); $k++)
	{
			//echo '';
	?>
		<tr class="form-group form-inline clonedInput" id="attri_opts<?php echo $k; ?>" >
			<td data-title="<?php echo JText::_('QTC_ADDATTRI_OPTNAME');?>">
				<input type="hidden" name="att_detail[attri_opt][<?php echo $k; ?>][id]" value="<?php echo (isset($this->attribute_opt[$k]->itemattributeoption_id)) ? $this->attribute_opt[$k]->itemattributeoption_id:''; ?>">

				<input type="text" class="input-medium" name="att_detail[attri_opt][<?php echo $k; ?>][name]" placeholder="<?php echo JText::_('QTC_ADDATTRI_OPTNAME')?>" value="<?php echo (isset($this->attribute_opt[$k]->itemattributeoption_name)) ? htmlentities($this->attribute_opt[$k]->itemattributeoption_name) : ''; ?>">
			</td>
			<td data-title="<?php echo JText::_('QTC_ADDATTRI_OPTPREFIX');?>">
				<?php
				$addpre_val = (isset($this->attribute_opt[$k]->itemattributeoption_prefix))?$this->attribute_opt[$k]->itemattributeoption_prefix:'';
				echo JHtml::_('select.genericlist', $addpre_select, "att_detail[attri_opt][$k][prefix]", 'class=""   ', "value", "text", $addpre_val);
				?>
			</td>
			<td data-title="<?php echo JText::_('QTC_ADDATTRI_OPTVAL');?>">
				<?php
				$currencies=$params->get('addcurrency');
				$curr=explode(',',$currencies);
				?>
				<div class='qtc_currencey_textbox input-group form-group'  >
					<?php $quick2cartModelAttributes =  new quick2cartModelAttributes();

					foreach($curr as $value)    // key contain 0,1,2... // value contain INR...
					{
						$currvalue=array();
						$storevalue="";

						if (isset($this->attribute_opt[$k] ))
						{
							$currvalue=$quick2cartModelAttributes->getOption_currencyValue($this->attribute_opt[$k]->itemattributeoption_id,$value);
							$storevalue=(isset($currvalue[0]['price']))?$currvalue[0]['price'] : '';
						}
						?>
					<div class="input-group curr_margin " >
						<input type='text' name="att_detail[attri_opt][<?php echo $k; ?>][currency][<?php echo $value; ?>]" size='1' id='' value="<?php echo ((isset($currvalue[0]['price']))?$currvalue[0]['price'] : ''); ?>" class=" currtext form-control" Onkeyup="checkforalpha(this,46,<?php echo $entered_numerics; ?>);">
						<div class="input-group-addon "><?php echo $value; ?></div>
					</div>
					<?php
					}
					?>
				</div>
			</td>
			<td data-title="<?php echo JText::_('QTC_ADDATTRI_OPTORDER');?>">
				<input type="text" Onkeyup="checkforalpha(this,'',<?php echo $entered_numerics; ?>);" onchange="qtc_ispositive(this)"	 id="" class="qtc-attribute-Option-order input-mini" name="att_detail[attri_opt][<?php echo $k; ?>][order]" placeholder="<?php echo JText::_('QTC_ADDATTRI_OPTORDER')?>" value="<?php echo (isset($this->attribute_opt[$k]->ordering))?$this->attribute_opt[$k]->ordering:$k+1; ?>">

			</td>
			<td>

				<?php
				if ($k == $lastkey_opt)
				{ ?>
					<button type="button" class="btnAdd btn btn-mini btn-primary"  onclick="addopt();"><i class="<?php echo QTC_ICON_PLUS;?>"></i></button>
				<?php
				}
				else
				{ ?>
					<button type="button" class="btn btn-mini btn-danger" id="btnRemove<?php echo $k; ?>" onclick="techjoomla.jQuery(this).closest('tr').remove();" ><i class="<?php echo Q2C_ICON_TRASH; ?>"></i></button>
				<?php
				} ?>
			</td>
		</tr>

	<?php } ?>


		</tr>
	</tbody>
</table>
		<input type="hidden" name="att_detail[attri_id]" value="<?php echo (isset($this->itemattribute_id))?$this->itemattribute_id:''; ?>">
		<input type="hidden" name="product_id" value="<?php echo $pid ?>" />
		<input type="hidden" name="edit" value="<?php echo $edit ?>" />
		<input type="hidden" name="client" value="<?php echo $client ?>" />
	<div class="">
		<input type="hidden" name="option" value="com_quick2cart">
		<input type="hidden" name="task" value="attributes.save" />
		<input type="hidden" name="controller" value="attributes" />
		<input class="btn btn-success validate" type="submit" onclick="submitbutton('attributes.save');" value="<?php echo JText::_('QTC_ADDATTRI_SAVE')?>" >		<?php echo JHtml::_( 'form.token' ); ?>

		<input class="btn btn btn-default validate" type="button" onclick="qtc_closePopUp();" value="<?php echo JText::_('QTC_ADDATTRI_CANCEL')?>" >		<?php echo JHtml::_( 'form.token' ); ?>
	</div>

</form>
</div>
