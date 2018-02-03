<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();
//~ jimport('joomla.html.pane');
//~ JHtml::_('behavior.tooltip');
//~ JHtml::_('behavior.formvalidation');

//~ $document = JFactory::getDocument();
//~ $productHelper =  new productHelper;
//~ $jinput = JFactory::getApplication()->input;
//~ $lang = JFactory::getLanguage();
//~ $lang->load('com_quick2cart', JPATH_ADMINISTRATOR);
//$params = JComponentHelper::getParams('com_quick2cart');
//$qtc_base_url = JUri::base();

$entered_numerics= "'".JText::_('QTC_ENTER_NUMERICS')."'";
//$document = JFactory::getDocument();
//$currencies=$params->get('addcurrency');
$addpre_select = array();
$addpre_select[] = JHtml::_('select.option','+', JText::_('QTC_ADDATTRI_PREADD'));
$addpre_select[] = JHtml::_('select.option','-', JText::_('QTC_ADDATTRI_PRESUB'));
//$addpre_select[] = JHtml::_('select.option','=', JText::_('QTC_ADDATTRI_PRESAM'));

$pid =  $jinput->get('pid',0,'INTEGER');
$client =  $jinput->get( 'client','','STRING');
$this->attribute_opt = array();

	$attri_model=new quick2cartModelAttributes();
	// clearing previous contain(garbage)
	$this->attribute_opt=array();
	if (!empty($this->allAttribues[$i]))
	{
		$this->attribute_opt = $attri_model->getAttributeoption($this->allAttribues[$i]->itemattribute_id);
		$att_op_count=count($this->attribute_opt);
	}
	else
	{
		$att_op_count=0;
	}

	//  For field type and is compulsory field
	$hideFieldsForStockableAttribute = (!empty($stockableAttrId) && $stockableAttrId == $this->allAttribues[$i]->itemattribute_id) ? "display:none;" : '';
?>
<div class="row-fluid">
<div class="span12 ">
	<div class="clearfix"></div>

	<div class="row-fluid ">
		<div class="">
			<div class="span4">
					<label class="" title="<?php echo JText::_('QTC_PROD_SEL_ATTRIBUTE_TOOLTIP');?>" ><b><?php echo JText::_('QTC_PROD_SEL_ATTRIBUTE'); ?></b></label>
			</div>
			<div class="span4 "></div>
			<div class="span4 ">
					<b  class="<?php echo $stockCheckboxClass; ?>" title="<?php echo JText::_('COM_QUICK2CART_IS_STOCKABLE_UNIT_DESC');?>" ><?php echo JText::_('COM_QUICK2CART_IS_STOCKABLE_UNIT')?> </b>
			</div>
		</div> <!-- END OF SPAN12-->
	</div>

	<div class="row-fluid">
		<div class="span12">
			<div class="span4">
			<?php
				$selectedAttrSet = !empty($this->allAttribues[$i]->global_attribute_id) ? $this->allAttribues[$i]->global_attribute_id : 0;
				echo JHtml::_('select.genericlist', $attributeSetOptions, "att_detail[". $i . "][global_attribute_set]",'class=" qtcAttriSetType" style="" onChange="qtcLoadArribute(this.id, ' . $i . ', qtcJsData)" ','value','text',$selectedAttrSet, "qtcAttriSetTypeId" + $i);
			?>
				<span class="attri_ajax_loading_<?php echo $i; ?>" style="display:none;">
					<img class="" src="<?php echo JUri::root() ?>components/com_quick2cart/assets/images/loadin16x16.gif" height="15" width="15">
				</span>
			</div>
			<div class="span4 " ></div>
			<div class="span3 " >
				<?php
					$qtc_stck_att="";

					if (isset($this->allAttribues[$i]->is_stock_keeping) && $this->allAttribues[$i]->is_stock_keeping == 1)
					{
						$qtc_stck_att = ($this->allAttribues[$i]->is_stock_keeping) ? "checked" : "";
					}
				?>
				<!-- // (SHOW only varible product) OR If any of product attribute has one of stockable attribute then don't show checkbox  -->
				<label class="checkbox  <?php echo ($stockableAttrId || $qtc_stck_att  || $productType != 2) ? "qtc_hideEle" : ''?>"" title="<?php echo JText::_('COM_QUICK2CART_IS_STOCKABLE_UNIT_DESC');?>">

				<input type="checkbox" class="qtc_is_stock_keeping <?php echo ($qtc_stck_att == "checked") ? "qtc_hideEle" : ''?>" name="att_detail[<?php echo $i; ?>][is_stock_keeping]" autocomplete="off" <?php echo $qtc_stck_att;?> onchange="qtcOnChange_is_stock_keeping(this)">
				</label>

				<?php
					// Edit view: for stockable attribute-> hide checkbod and show badges
				if(($qtc_stck_att == "checked"))
				{
					?>
					<span class="label label-success"><?php echo JText::_("COM_QUICK2CART_STOCK_KEEPING_ATTR_BEDGE");?></span>

					<?php
				}
				?>
			</div>
		</div> <!-- END OF SPAN12-->
	</div>

	<div class="row-fluid qtcMarginTop10px">
		<div class="span12">
			<div class="span4">
					<label class="qtcAttriTabLabel" ><b><?php echo JText::_('QTC_ADDATTRI_NAME'); ?></b></label>
			</div>
			<div class="span4">
					<b class="qtcfieldTypeTitle" style="<?php echo $hideFieldsForStockableAttribute ?>"><?php echo JText::_('QTC_ADDATTRI_FIELD_TYPE_TO_USE')?> </b>
			</div>
			<div class="span4">
				<b class="checkboxdivTitle" style="<?php echo $hideFieldsForStockableAttribute ?>"> <?php echo JText::_('QTC_ATT_COMPALSARY_CK')?></b>
			</div>
		</div> <!-- END OF SPAN12-->
	</div>
	<div class="row-fluid">
		<div class="span12">
			<!---s ATTRIBURE NAME -->
<!--
			$i for  attribute
-->
			<div class="span4">
				<input id="atrri_name" name="att_detail[<?php echo $i; ?>][attri_name]" class="input-medium bill inputbox qtc_attrib " class="qtcAttrNameClass" type="text" value="<?php echo (isset($this->allAttribues[$i]->itemattribute_name)) ? htmlentities($this->allAttribues[$i]->itemattribute_name):''; ?>" maxlength="250" title="<?php echo JText::_('QTC_ADDATTRI_NAME_DESC')?>">
				<!-- Hidded field -->
				<input id="atrri_name_id" name="att_detail[<?php echo $i; ?>][attri_id]" class="input-medium bill inputbox " type="hidden" value="<?php echo (isset($this->allAttribues[$i]->itemattribute_id))?$this->allAttribues[$i]->itemattribute_id:''; ?>" >

				<input type="hidden" name="att_detail[<?php echo $i; ?>][global_atrri_id]" class="global_atrri_idClass"  value="<?php echo (isset($this->allAttribues[$i]->global_attribute_id)) ? $this->allAttribues[$i]->global_attribute_id:''; ?>" >

			</div>
			<!-- Field type-->
			<div class="span4" style="">
				<?php
					$fields = array();
					$default =  !empty($this->allAttribues[$i]->attributeFieldType)? $this->allAttribues[$i]->attributeFieldType :"Select";
					$tableDisplay ='display:table';

					if ($default == 'Textbox')
					{
						$tableDisplay ='display:none';
					}

					$fields = $productHelper->getAttributeFieldsOption();
					$fnparam = "this,'".$attribute_container_id."'";
				?>
				<div style="<?php echo $hideFieldsForStockableAttribute ?>">
					<?php
						echo JHtml::_('select.genericlist', $fields, "att_detail[$i][fieldType]", 'class="no_chzn qtcfieldType span6" onChange="qtc_fieldTypeChange('.$fnparam.')"', "value", "text",$default);
					?>
				</div>
			</div>
			<!--- IS COMPULSORY  -->
			<div class="span3 ">

				<label class="checkbox " style="<?php echo $hideFieldsForStockableAttribute ?>">
				<?php
					$qtc_ck_att="";

					if (isset($this->allAttribues[$i]->attribute_compulsary) && $this->allAttribues[$i]->attribute_compulsary == 1)
					{
						$qtc_ck_att=($this->allAttribues[$i]->attribute_compulsary)?"checked":"";
					}
				?>
				<input type="checkbox" class="checkboxdiv" name="att_detail[<?php echo $i; ?>][iscompulsary_attr]" autocomplete="off" <?php echo $qtc_ck_att;?> >
				</label>
			</div>
		</div><!-- end of span12 2nd-->

	</div>
	<?php
	$k = 1;
	$lastkey_opt = $att_op_count ;//count($this->attribute_opt);
	?>
	<div class="qtcMarginTop10px">
		<table class="table table-condensed removeMargin qtc_attributeOpTable" style="<?php echo $tableDisplay; ?>"><!-- the table width is fixed to 450px -->
			<thead>
				<tr>
					<th width="30%" align="left"><?php echo JText::_( 'QTC_ADDATTRI_OPTNAME' ); ?> </th>
					<th width="100px" align="left"><?php echo JText::_('QTC_PROD_STATUS'); ?> </th>
					<th class="<?php echo $stockRelatedFildClass; ?> qtcStockSkufields" width="80px"	align="left"><?php echo JText::_('QTC_ADDATTRI_STOCK'); ?></th>
					<th class="<?php echo $stockRelatedFildClass; ?> qtcStockSkufields" width="40px"	align="left"><?php echo JText::_('QTC_ADDATTRI_SKU'); ?></th>
					<th width="10%"	align="left"><?php echo JText::_( 'QTC_ADDATTRI_OPTPREFIX' ); ?></th>
					<th width="20%"	align="left"><?php echo JText::_( 'MRP' ); //QTC_ADDATTRI_OPTVAL?> </th>
					<th width="20%"	align="left"><?php echo JText::_( 'Selling Price' ); //QTC_ADDATTRI_OPTVAL ?> </th>
					<th width="10%"	align="left"><?php echo JText::_( 'QTC_ADDATTRI_OPTORDER' ); ?></th>
					<th width="5%"	align="left"></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$showAddBtn = 1;

			if (!empty($this->allAttribues[$i]->itemattribute_id))
			{
				/* Dont show add btn for global attibure set or stockable  attribute */
				// (!empty($stockableAttrId) && $stockableAttrId === $this->allAttribues[$i]->itemattribute_id))
				if ($selectedAttrSet)
				{
					// Don't generate last option for stock keeping attibure
					$att_op_count = $att_op_count -1;
					$showAddBtn = 0;
				}
			}

			for ($k = 0; $k <=$att_op_count ; $k++)
			{
				$childProdDetail = !empty($this->attribute_opt[$k]->child_product_detail) ? $this->attribute_opt[$k]->child_product_detail : new stdClass;
				echo '';
			?>
			<tr class="form-inline clonedInput" id="attri_opts<?php echo $k; ?>" >
				<td>
					<input type="hidden" name="att_detail[<?php echo $i; ?>][attri_opt][<?php echo $k; ?>][id]" value="<?php echo (!empty($this->attribute_opt[$k]->itemattributeoption_id))?$this->attribute_opt[$k]->itemattributeoption_id:''; ?>">
					<input type="hidden" class="qtcGlobalOptionId" name="att_detail[<?php echo $i; ?>][attri_opt][<?php echo $k; ?>][globalOptionId]" value="<?php echo (!empty($this->attribute_opt[$k]->global_option_id)) ? $this->attribute_opt[$k]->global_option_id:''; ?>">
					<input type="hidden" class="" name="att_detail[<?php echo $i; ?>][attri_opt][<?php echo $k; ?>][child_product_item_id]" value="<?php echo (!empty($this->attribute_opt[$k]->child_product_item_id)) ? $this->attribute_opt[$k]->child_product_item_id:''; ?>">
					<input type="text" class="input-small qtcOptionNameClass" name="att_detail[<?php echo $i; ?>][attri_opt][<?php echo $k; ?>][name]" placeholder="<?php echo JText::_('QTC_ADDATTRI_OPTNAME')?>" value="<?php echo (isset($this->attribute_opt[$k]->itemattributeoption_name))? htmlentities($this->attribute_opt[$k]->itemattributeoption_name):''; ?>">
				</td>
				<td >
					<?php
					$option_status = array();
					$option_status[] = JHtml::_('select.option',1, JText::_('QTC_PROD_PUBLISH'));
					$option_status[] = JHtml::_('select.option',0, JText::_('QTC_PROD_UNPUBLISH'));

					$defOptionStatus = (isset($this->attribute_opt[$k]->state))?$this->attribute_opt[$k]->state:'';
					echo JHtml::_('select.genericlist', $option_status, "att_detail[$i][attri_opt][$k][state]", 'class="chzn-done input-medium"', "value", "text", $defOptionStatus);
					?>
				</td>
				<td class="<?php echo $stockRelatedFildClass; ?> qtcStockSkufields">
					<!-- For stockable attribute - sku and stock field -->
					<input type="text" class="input-mini qtcAttriOptionStock" name="att_detail[<?php echo $i; ?>][attri_opt][<?php echo $k; ?>][stock]" Onkeyup="" onchange=""	 id=""   placeholder="<?php echo JText::_('QTC_ADDATTRI_STOCK')?>" value="<?php echo isset($childProdDetail->stock) ? $childProdDetail->stock : '' ?>">
				</td>
				<td class="<?php echo $stockRelatedFildClass; ?> qtcStockSkufields">
					<input type="text" class="input-medium	 qtcAttriOptionSku" name="att_detail[<?php echo $i; ?>][attri_opt][<?php echo $k; ?>][sku]" Onkeyup="" onchange=""	 id="" class=""  placeholder="<?php echo JText::_('QTC_ADDATTRI_SKU')?>" value="<?php echo !empty($childProdDetail->sku) ? $childProdDetail->sku : '' ?>">
				</td>
				<td >
					<?php
					$addpre_val = (isset($this->attribute_opt[$k]->itemattributeoption_prefix))?$this->attribute_opt[$k]->itemattributeoption_prefix:'';
					echo JHtml::_('select.genericlist', $addpre_select, "att_detail[$i][attri_opt][$k][prefix]", 'class="chzn-done input-mini" data-chosen="qtc"', "value", "text", $addpre_val);
				?>
				</td>
				<td>
					<?php
					$currencies=$params->get('addcurrency');
					$curr=explode(',',$currencies);
					$currencies_sym=$params->get('addcurrency_sym');

					if (!empty($currencies_sym) )
					{
						$curr_syms=explode(',',$currencies_sym);
					}
					?>

					<div class='control-group'  >
						<span class='qtc_currencey_textbox1 input-append '>
							<?php $quick2cartModelAttributes =  new quick2cartModelAttributes();

							foreach($curr as $currKey=>$value)    // key contain 0,1,2... // value contain INR...
							{
									$currvalue=array();
									$storevalue="";

									if (isset($this->attribute_opt[$k] ))
									{
										$currvalue=$quick2cartModelAttributes->getOption_currencyValue($this->attribute_opt[$k]->itemattributeoption_id,$value);
										$storevalue=(isset($currvalue[0]['price']))?$currvalue[0]['price'] : '';
									}

									if (!empty($curr_syms[$currKey]))
									{
										$currtext = $curr_syms[$currKey];
									}
									else
									{
										$currtext = $value;
									}
									?>
								<div class="input-append curr_margin " >
									<input type='text' name="att_detail[<?php echo $i; ?>][attri_opt][<?php echo $k; ?>][mrp][<?php echo $value; ?>]" size='1' id='' value="<?php echo (isset($this->attribute_opt[$k]->itemattributeoption_price_mrp))?$this->attribute_opt[$k]->itemattributeoption_price_mrp:0; ?>" class="span1 currtext controls" Onkeyup="checkforalpha(this,46,<?php echo $entered_numerics; ?>);">
									<span class="add-on "><?php echo $currtext; ?></span>
								</div>
							<?php
							}
						?>
						</span>
					</div>
				</td>

				<td>
					<?php
					$currencies=$params->get('addcurrency');
					$curr=explode(',',$currencies);
					$currencies_sym=$params->get('addcurrency_sym');

					if (!empty($currencies_sym) )
					{
						$curr_syms=explode(',',$currencies_sym);
					}
					?>
					<div class='control-group'  >
						<span class='qtc_currencey_textbox input-append '>
						<?php $quick2cartModelAttributes =  new quick2cartModelAttributes();

							foreach($curr as $currKey=>$value)    // key contain 0,1,2... // value contain INR...
							{
									$currvalue=array();
									$storevalue="";

									if (isset($this->attribute_opt[$k] ))
									{
										$currvalue=$quick2cartModelAttributes->getOption_currencyValue($this->attribute_opt[$k]->itemattributeoption_id,$value);
										$storevalue=(isset($currvalue[0]['price']))?$currvalue[0]['price'] : '';
									}

									if (!empty($curr_syms[$currKey]))
									{
										$currtext = $curr_syms[$currKey];
									}
									else
									{
										$currtext = $value;
									}
									?>
								<div class="input-append curr_margin " >
									<input type='text' name="att_detail[<?php echo $i; ?>][attri_opt][<?php echo $k; ?>][currency][<?php echo $value; ?>]" size='1' id='' value="<?php echo ((isset($currvalue[0]['price']))?$currvalue[0]['price'] : ''); ?>" class="span1 currtext controls" Onkeyup="checkforalpha(this,46,<?php echo $entered_numerics; ?>);">
									<span class="add-on "><?php echo $currtext; ?></span>
								</div>
						<?php
							}
						?>
						</span>
					</div>
				</td>
				<td>
					<input type="text" name="att_detail[<?php echo $i; ?>][attri_opt][<?php echo $k; ?>][order]" Onkeyup="checkforalpha(this,'',<?php echo $entered_numerics; ?>);" onchange="qtc_ispositive(this)"	 id="" class="span1 qtc-attribute-Option-order"  placeholder="<?php echo JText::_('QTC_ADDATTRI_OPTORDER')?>" value="<?php echo (isset($this->attribute_opt[$k]->ordering))?$this->attribute_opt[$k]->ordering:1; ?>">
				</td>
				<td class="qtcAddRmBTD">
				<?php

				/* Dont show add btn for stockable attribute */
				if ($k == $lastkey_opt && $showAddBtn)
				{ ?>
					 <button type="button"  class="btnAdd btn btn-mini btn-primary" id="<?php echo "qtc_container". $i;?>" title="<?php echo JText::_('QTC_ADD_MORE_OPTION_TITLE')?>" onclick="addopt(this.id);"><i class="<?php echo QTC_ICON_PLUS;?> icon22-white22"></i></button>
					<?php
				}
				else
				{
					?>
					 <button type="button" class="btn btn-mini btn-danger qtcRemoveOption" id="btnRemove<?php echo $k; ?>" title="<?php echo JText::_('QTC_REMOVE_MORE_OPTION_TITLE');?>" onclick="techjoomla.jQuery(this).closest('tr').remove();" ><i class="icon-trash icon22-white22"></i></button>
					<?php
				}
				?>
				</td>
			</tr>

			<?php } ?>

			</tbody>
		</table>

		<div class="qtc_float_right">
			<div class="">
			<?php
				$hideAddGlobalOptionFields = 1;

				if ($selectedAttrSet && $this->allAttribues[$i]->global_attribute_id)
				{
					$hideAddGlobalOptionFields = 0;
				}
			?>
			<div class="qtcGlobalOptionsList" style="<?php echo !empty($hideAddGlobalOptionFields) ? "display:none;" : '' ?>">
				<?php
				// Get global options
				// https://docs.joomla.org/J3.x:JLayout_Improvements_for_Joomla!
				$goptions = $this->productHelper->getGlobalAttriOptions($this->allAttribues[$i]->global_attribute_id);

				// Generate option select box
				$layout = new JLayoutFile('addproduct.attribute_global_options',  null, array('client' => 0, 'component' => 'com_quick2cart'));
				echo $layout->render($goptions);
				?>

			</div>
		</div>

		</div>
	</div>
</div><!---span12 div end -->
</div><!---row fluid end -->



	<?php echo JHtml::_( 'form.token' ); ?>
<!-- </form> -->

