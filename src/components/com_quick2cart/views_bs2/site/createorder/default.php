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
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.formvalidation');
JHTML::_('behavior.modal');
?>
<script>

	techjoomla.jQuery(window).load(function (){

		if (techjoomla.jQuery( "#select-product-tab" ).hasClass( "active" ))
		{
			techjoomla.jQuery('#qtctab_selectclient').addClass('active');
			techjoomla.jQuery('#qtctab_addproduct').removeClass('active');
			techjoomla.jQuery('#tab2id').removeClass('active');
			techjoomla.jQuery('#tab1id').addClass('active');
		}
	});

	techjoomla.jQuery(document).ready(function ()
	{
		/* Initialise chosen*/
		jQuery('#qtcorder_productdetails0store_id').chosen();
		jQuery('#qtcorder_productdetails0').chosen();
		jQuery('#qtcuser').chosen();

		techjoomla.jQuery('.qtcadd_address_button').hide();

		techjoomla.jQuery('.nav-tabs li').click(function()
		{
			techjoomla.jQuery('#qtc_order_error').empty();

			var linumber  = techjoomla.jQuery(this).index();

			var errorOccured = 0;
			for(var i=0 ; i <= linumber ; i++)
			{
				var navli = techjoomla.jQuery(".nav-tabs li").get( i );

				var thiscontenttab	= techjoomla.jQuery('a',techjoomla.jQuery(navli)).attr('href');

				var check_validation = validateselectclient();

				if(!check_validation)
				{
					errorOccured = 1;

					techjoomla.jQuery('.nav-tabs li').removeClass('active');

					techjoomla.jQuery(navli).addClass('active');
					var tabToShow = techjoomla.jQuery('a',navli).attr('href');

					techjoomla.jQuery('.tab-content .tab-pane').removeClass('active');
					techjoomla.jQuery('a[href="'+tabToShow+'"]').closest('li').addClass('active');
					techjoomla.jQuery('.tab-content '+tabToShow+'').addClass('active');
					return false;
					break;
				}
			}


			if (techjoomla.jQuery("#qtcuser option:selected").val() == '')
			{
				alert(Joomla.JText._('COM_QUICK2CART_SELECT_CLIENT_ERROR_MSG'));
				return false;
			}
		});
	});

	var i = 0;

	/* Function to create clone for product div*/
	function qtcaddMoreProduct()
	{
		i++;
		var clone = techjoomla.jQuery('#qtc_product_clone').clone(false);

		/* code to change name and ids of elements in new clone - start*/
		clone.find('#qtc_product_div0').attr("id", "qtc_product_div"+i);
		clone.find('#qtcorder_productdetails0store_id').attr("id", "qtcorder_productdetails"+i+"store_id");
		clone.find('#qtcorder_productdetails0').attr("id", "qtcorder_productdetails"+i);
		clone.find('#qtc_product_shipping_details_div0').attr("id", "qtc_product_shipping_details_div"+i);
		clone.find('#qtc_product_shipping_details_div'+i).addClass("hidden");

		/* Remove chosen data from clone*/
		clone.find('.chzn-container').remove();

		clone.find('#qtcorder_productdetails_label0').attr("id", "qtcorder_productdetails_label"+i);
		clone.find('#qtcorder_productdetails_label'+i).attr("for", "qtcorder_productdetails"+i+"store_id");
		clone.find('#qtc_select_product_select0').attr("id", "qtc_select_product_select"+i);
		clone.find('#qtc_select_product_select'+i).attr("for", "qtcorder_productdetails"+i);
		clone.find("[name='qtcorder_productdetails[0][store_id]']").attr("name", "qtcorder_productdetails["+i+"][store_id]");
		clone.find('#qtcorder_productdetails'+i+'store_id').attr("onchange", "qtcpopulateProducts('"+i+"')");

		clone.find("[name='qtcorder_productdetails[0][product_id]']").attr("name", "qtcorder_productdetails["+i+"][product_id]");
		clone.find("[name='qtc_product_total[0][ptotal]']").attr("name", "qtc_product_total["+i+"][ptotal]");
		clone.find('#qtcorder_productdetails'+i).attr("onchange", "qtcgetProductDetails('"+i+"')");

		clone.find('#qtc_prod_quantity0').attr("id", "qtc_prod_quantity"+i);
		clone.find("[name='qtcorder_productdetails[0][product_quantity]']").attr("name", "qtcorder_productdetails["+i+"][product_quantity]");

		clone.find('#qtc_prod_price0').attr("id", "qtc_prod_price"+i);
		clone.find('#qtc_prod_price_curr0').attr("id", "qtc_prod_price_curr"+i);

		clone.find('#qtc_prod_total0').attr("id", "qtc_prod_total"+i);
		clone.find('#qtc_prod_total_currency0').attr("id", "qtc_prod_total_currency"+i);

		clone.find('#qtc_remove_Product_button0').attr("id", "qtc_remove_Product_button"+i);
		clone.find('#qtc_select_product_select_div0').attr("id", "qtc_select_product_select_div"+i);
		clone.find('#qtc_product_attributes0').attr("id", "qtc_product_attributes"+i);
		clone.find('#qtc_product_attributes'+i).empty();

		clone.find('#qtc_product_shipping_details0').attr("id", "qtc_product_shipping_details"+i);
		clone.find('#qtc_product_shipping_details'+i).empty();

		/* code to change name and ids of elements in new clone - end*/

		/* code to change name and ids of elements in new clone - end*/
		techjoomla.jQuery('#qtc_add_product_wrapper').append(clone);

		/* To remove styling of chosen*/
		var x = document.getElementById('qtcorder_productdetails'+i+'store_id');

		if (x.hasAttribute('style'))
		{
			x.removeAttribute("style");
		}

		var y = document.getElementById('qtcorder_productdetails'+i);

		if (y.hasAttribute('style'))
		{
			y.removeAttribute("style");
		}

		techjoomla.jQuery("#qtc_prod_quantity"+i).val("");
		techjoomla.jQuery("#qtc_prod_price"+i).html("0");
		techjoomla.jQuery("#qtc_prod_total"+i).html("0");

		/* code to add remove button which deletes the clone*/
		techjoomla.jQuery("#qtc_remove_Product_button"+i).append('<a class="btn btn-danger btn-mini" onclick="qtcremoveProduct('+i+');" title="'+Joomla.JText._('COM_Q2C_REMOVE_TOOLTIP')+'"><i class=" <?php echo QTC_ICON_MINUS; ?> "></i>');

		/* reinitailise chosen*/
		jQuery('#qtcorder_productdetails'+i+'store_id').chosen();
		jQuery('#qtcorder_productdetails'+i).chosen();
		jQuery('#qtcorder_productdetails'+i).trigger('liszt:updated');
	}

	function qtcGetProductShippingDetails(productId, prod_container_num)
	{
		var qty = techjoomla.jQuery("#qtc_prod_quantity"+prod_container_num).val();
		var tamt = techjoomla.jQuery("#qtc_prod_total"+prod_container_num).text();
		var tamt = techjoomla.jQuery("#qtc_prod_total"+prod_container_num).text();
		var shipping = techjoomla.jQuery("input[name=shipping_address]").val();
		var billing = techjoomla.jQuery("input[name=billing_address]").val();
		var currency = techjoomla.jQuery("#qtc_prod_total_currency"+prod_container_num).text();
		var callurl = "index.php?option=com_quick2cart&task=product.loadProductShippingDetails&tmpl=component&prodId="+productId+"&qty="+qty+"&tamt="+tamt+"&shipping="+shipping+"&billing="+billing;

		techjoomla.jQuery.ajax({
			url: callurl,
			type: "GET",
			cache: false,
			success: function(data)
			{
				try
				{
					var shipping_info = techjoomla.jQuery.parseJSON(data);
				}catch (e)
				{
					var shipping_info = "";
				}

				techjoomla.jQuery("#qtc_product_shipping_details"+prod_container_num).html("");

				techjoomla.jQuery("#qtc_product_shipping_details_div"+prod_container_num).addClass('hidden');

				techjoomla.jQuery.each(shipping_info, function(i, item)
				{

					var flagForChecked = 0;
					techjoomla.jQuery.each(shipping_info[i].shippingMeths, function(j, itemz)
					{
						flagForChecked++;

						if (flagForChecked == 1)
						{
							var check = " checked='true'";
						}
						else
						{
							var check = "";
						}

						techjoomla.jQuery("#qtc_product_shipping_details"+prod_container_num).append('<input type="hidden" name="itemshipMethDetails['+itemz.methodId+'][item_id]" value="'+item.item_id+'">');

						techjoomla.jQuery("#qtc_product_shipping_details"+prod_container_num).append('<input type="hidden" name="itemshipMethDetails['+itemz.methodId+'][methodId]" value="'+itemz.methodId+'">');

						techjoomla.jQuery("#qtc_product_shipping_details"+prod_container_num).append('<input type="hidden" name="itemshipMethDetails['+itemz.methodId+'][methRateId]" value="'+itemz.plugMethRateId+'">');

						techjoomla.jQuery("#qtc_product_shipping_details"+prod_container_num).append('<input type="hidden" name="itemshipMethDetails['+itemz.methodId+'][totalShipCost]" value="'+itemz.totalShipCost+'">');

						techjoomla.jQuery("#qtc_product_shipping_details"+prod_container_num).append('<input type="hidden" name="itemshipMethDetails['+itemz.methodId+'][client]" value="'+itemz.client+'">');

						if (itemz.hasOwnProperty('totalShipCost'))
						{
							techjoomla.jQuery("#qtc_product_shipping_details_div"+prod_container_num).removeClass('hidden');

							techjoomla.jQuery("#qtc_product_shipping_details"+prod_container_num).append('<div class="span3"><label class="radio"><input type="radio" name="itemshipMeth['+prod_container_num+']['+item.item_id+']" value="'+itemz.methodId+'" aria-invalid="false" '+check+'><span>'+currency+'&nbsp;'+itemz.totalShipCost+'</span>&nbsp;&nbsp;&nbsp;'+itemz.name+'</label></div>');
						}
					});
				});
			}
		});
	}
/* Function to show product attributes*/
function qtcgetProductDetails(prod_container_num)
{
	var productId = techjoomla.jQuery("#qtcorder_productdetails"+prod_container_num+" option:selected").val();

	if (productId != '')
	{
		var callurl = "index.php?option=com_quick2cart&task=product.loadProductDetails&tmpl=component&prodId="+productId+"&prod_container_num="+prod_container_num+"&renderer_view=createorder";

		techjoomla.jQuery.ajax({
			url: callurl,
			beforeSend: function(){
			openModal();
			},
			type: "GET",
			cache: false,
			success: function(data)
			{
				var prod_info = JSON.parse(data);
				var currency = prod_info.curr_sym;

				if (prod_info.hasOwnProperty('discount_price'))
				{
					techjoomla.jQuery("#qtc_prod_price"+prod_container_num).html('<del>'+prod_info.price+'</del>');
					techjoomla.jQuery("#qtc_prod_price_curr"+prod_container_num).html(currency);
					techjoomla.jQuery("#qtc_prod_price"+prod_container_num).append(prod_info.discount_price);
					techjoomla.jQuery('#qtc_prod_quantity'+prod_container_num).attr("onchange", "checklimit('"+prod_container_num+"','"+prod_info.discount_price+"','"+currency+"')");

					techjoomla.jQuery('#qtc_prod_total_currency'+prod_container_num).html(currency);
					qtc_update_product_price(productId,prod_container_num);
				}
				else
				{
					techjoomla.jQuery("#qtc_prod_price"+prod_container_num).html(prod_info.price);
					techjoomla.jQuery('#qtc_prod_quantity'+prod_container_num).attr("onchange", "checklimit('"+prod_container_num+"','"+prod_info.price+"','"+currency+"')");
					techjoomla.jQuery('#qtc_prod_total_currency'+prod_container_num).html(currency);
					qtc_update_product_price(productId,prod_container_num);
				}

				techjoomla.jQuery('#qtc_order_total_currency').html(currency);

				techjoomla.jQuery('#qtc_prod_quantity'+prod_container_num).attr("min", prod_info.min);
				techjoomla.jQuery('#qtc_prod_quantity'+prod_container_num).val(prod_info.min);
				techjoomla.jQuery('#qtc_prod_quantity'+prod_container_num).attr("max", prod_info.max);

				techjoomla.jQuery('#qtc_product_attributes'+prod_container_num).empty();

				for (var key in prod_info.attribute_html)
				{
					techjoomla.jQuery('#qtc_product_attributes'+prod_container_num).append("<div class='span4 prod_attribute_options' name='attribute_options[]'><div>"+prod_info.attribute_html[key].name+"</div>"+prod_info.attribute_html[key].html);
				}

				techjoomla.jQuery('#qtc_product_shipping_details'+prod_container_num).html("");

				/* Function to get shipping methods for selected product*/
				qtcGetProductShippingDetails(productId, prod_container_num);

				closeModal();
			}
		});
	}
}
</script>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?> container-fluid">
	<form name="createOrder" id="createOrder" class="form-validate form-horizontal" method="post">
		<div class="clearfix"></div>
		<div class="row-fluid">
			<h1 class="span12"><?php echo JText::_("COM_QUICK2CART_CREATE_ORDER_VIEW_TITLE");?></h1>
		</div>
		<div class="row-fluid">
			<div class="span12 alert alert-info"><?php echo JText::_("COM_QUICK2CART_CREATE_ORDER_VIEW_INFO");?></div>
		</div>
		<div class="row-fluid qtc-createorder-buttons">
			<a class="btn btn-primary pull-right qtcMarginLeft span3" onclick="addClient('<?php echo JUri::root();?>')"><i class="icon-user">&nbsp;</i><?php echo JText::_("COM_QUICK2CART_CREATE_ORDER_ADD_CLIENT");?></a>
			<a class="btn btn-primary pull-right span3" target="_blank" href="<?php echo JUri::root() . 'index.php?option=com_quick2cart&view=product';?>"><i class=" icon-plus-2 icon22-white22 ">&nbsp;</i><?php echo JText::_("COM_QUICK2CART_POINTS_ADD_PRODUCT_TITLE");?></a>
		</div>
		<div class="clearfix"></div>
		<!-- CODE FOR TABS START-->
			<div>
				<ul class="nav nav-tabs">
					<li id="select-customer-tab" class="active">
						<a href="#qtctab_selectclient" data-toggle="tab"><?php echo JText::_( "COM_QUICK2CART_SELECT_CUSTOMER"); ?></a>
					</li>
					<li id="select-product-tab">
						<a href="#qtctab_addproduct" data-toggle="tab"><?php echo JText::_( "COM_QUICK2CART_SELECT_PRODUCT"); ?></a>
					</li>
				</ul>
				<div class="tab-content">
					<!-- tab 1 start -->
					<div class="tab-pane active" id="qtctab_selectclient">
						<div id="qtc_order_error" class="row-fluid">&nbsp;</div>
						<div class="control-group">
							<div class="control-label"><?php echo JText::_("COM_QUICK2CART_SELECT_CUSTOMER"); ?></div>
							<div class="controls">
							<?php
								echo JHtml::_('select.genericlist', $this->users, "qtcuser", 'class="ad-status inputbox input-medium required" size="1" required onchange="showAddresses();" name="qtcuser"', "id", "username");
							?>
							</div>
						</div>
						<div class="clearfix">&nbsp;</div>
						<div class="qtcadd_address_button center">
							<a class="btn btn-success" onclick="addAddress('<?php echo JUri::root();?>')"><?php echo JText::_('COM_QUICK2CART_ADD_CUSTOMER_ADDRESS');?></a>
						</div>
						<div id="qtc_user_addresses" class="row-fluid">
							<div class="qtc_user_addresses_wrapper">
							</div>
						</div>
						<div class="qtc_next_button center">
							<a class="btn btn-success" onclick="nextstep()"><?php echo JText::_('QTC_NEXT_BUY');?></a>
						</div>
					</div>
					<!-- tab 1 end -->

					<!-- tab 2 start -->
					<div class="tab-pane" id="qtctab_addproduct">
						<div id="qtc_add_product_wrapper">
							<div id="qtc_product_clone">
								<div id="qtc_product_div0" class="qtc_product_div">
									<div class="row-fluid">
										<div class="span3">
											<label id="qtcorder_productdetails_label0" for="qtcorder_productdetails0store_id"><?php echo JText::_("COM_QUICK2CART_SELET_STORE"); ?></label>
											<div>
											<?php
												echo JHtml::_('select.genericlist', $this->stores, "qtcorder_productdetails[0][store_id]", 'class="ad-status inputbox input-medium required qtcorder_select_chosen" required="required" size="1" onchange="qtcpopulateProducts(\'0\');"', "store_id", "title");
											?>
											</div>
										</div>
										<div class="span3 qtc_select_product_select_div0">
											<label id="qtc_select_product_select0" for="qtcorder_productdetails0" class=""><?php echo JText::_("COM_QUICK2CART_SELECT_PRODUCT"); ?></label>
											<select name="qtcorder_productdetails[0][product_id]" id="qtcorder_productdetails0" class="required qtcorder_select_chosen" required="required" onchange="qtcgetProductDetails('0')">
												<option selected="selected"><?php echo JText::_('COM_QUICK2CART_SELECT_PRODUCT')?></option>
											</select>
										</div>
										<div class="span1">
											<label for="qtc_prod_quantity0"><?php echo JText::_("QTC_PRODUCT_QTY");?></label>
											<input id="qtc_prod_quantity0" min="0" max="0" name="qtcorder_productdetails[0][product_quantity]" class="input input-small qtc-small-input" type="number" value="">
										</div>
										<div class="span2 center qtc_prod_price_div">
											<label for="qtc_prod_price0"><?php echo JText::_("QTC_CART_PRICE");?></label>
											<div>
												<span id="qtc_prod_price_curr0"></span>
												<span id="qtc_prod_price0" class="">0</span>
											</div>
										</div>
										<div class="span2">
											<label for="qtc_prod_total0"><?php echo JText::_("QTC_TOTALPRICE");?></label>
											<div>
												<span id="qtc_prod_total_currency0"></span>
												<span id="qtc_prod_total0" class="">0</span>
											</div>
											<input type="hidden" name="qtc_product_total[0][ptotal]" value='0'></input>
										</div>
										<span id="qtc_remove_Product_button0">
										</span>
										<div class="span12">
											<div id="qtc_product_attributes0" class="row-fluid qtc_product_attributes">
											</div>
										</div>
										<div id="qtc_product_shipping_details_div0" class="hidden row-fluid qtc_product_shipping_title_div">
											<h4 class="span12"><?php echo JText::_("COM_QUICK2CART_CHOOSE_YOUR_DELIVARY_OPTION");?></h4>
										</div>
										<div id="qtc_product_shipping_details0" class="row-fluid qtc_product_shipping_method_div">
										</div>
									</div>
								</div>
							</div>
						</div>
						<h3>
							<div class="pull-right qtc-total-order-price">
								<span><?php echo JText::_('COM_QUICK2CART_CREATE_ORDER_TOTAL_ORDER_PRICE');?>&nbsp;</span>
								<span id="qtc_order_total_currency"></span>
								<span>&nbsp;</span>
								<span id="qtc_order_total_price" class="pull-right">0</span>
							</div>
						</h3>
						<div class="clearfix"></div>
						<div class="row-fluid">
							<div class="span12 alert alert-info"><?php echo JText::_("COM_QUICK2CART_CREATE_ORDER_VIEW_ORDER_INFO");?></div>
						</div>
						<div class="qtc-create-order-buttons center">
							<a onclick="qtcaddMoreProduct();" class="btn btn-primary" title="Add More"><i class=" icon-plus-2 icon22-white22 "></i><?php echo JText::_('COM_QUICK2CART_CREATE_ORDER_ADD_MORE_PRODUCTS');?>
							</a>
							<a onclick="qtc_place_order();" class="btn btn-success"><?php echo JText::_('QTC_CHKOUT');?>
							</a>
						</div>
					</div>
					<!-- tab 2 end -->
				</div>
			</div>
			<!-- CODE FOR TABS END -->
			<div id="q2c-ajax-call-fade-content-transparent"></div>
			<div id="q2c-ajax-call-loader-modal">
				<img id="q2c-ajax-loader" src="<?php echo JUri::root() . 'components/com_quick2cart/assets/images/ajax.gif';?>"/>
			</div>
	</form>
</div>
