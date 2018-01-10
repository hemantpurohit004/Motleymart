function qtc_place_order()
{
	var values = techjoomla.jQuery('#createOrder').serialize();

	var  createOrder = document.createOrder;

	if (!document.formvalidator.isValid(createOrder))
	{
		return false;
	}

	var callurl = "index.php?option=com_quick2cart&task=createorder.qtc_place_order";

	techjoomla.jQuery.ajax({
		url: callurl,
		beforeSend: function(){
			openModal();
			},
		type: "POST",
		data:values,
		cache: false,
		success: function(data)
		{
			closeModal();
			var result = JSON.parse(data);

			if (result.status == "success")
			{
				alert(Joomla.JText._('COM_QUICK2CART_CREATE_ORDER_ORDER_SUCCESS_MESSAGE'));
				location.reload();
			}
			else
			{
				alert(result.status+" : "+result.message);
				location.reload();
			}
		}
	});
}

/* Function to update price of product */
function qtc_update_product_price(prod_id, container_number)
{
	/* variable values contains the form data  */
	var values = techjoomla.jQuery('#createOrder').serialize();

	var callurl = "index.php?option=com_quick2cart&task=createorder.qtc_update_product_price&prod_id="+prod_id+"&container_number="+container_number;

	techjoomla.jQuery.ajax({
		url: callurl,
		type: "GET",
		data:values,
		async:false,
		cache: false,
		success: function(data)
		{
			/* variable prod_info contains all the product info and its attribute info */
			var prod_info = JSON.parse(data);
			var productPrice = Number(prod_info[0].price);
			var quantity = Number(prod_info[0].count);
			var optionTotal = Number('0');

			/* index 1 of prod_info contains all the information of product attributes */

			if ('1' in prod_info)
			{
				var optionPrice = Number('0');

				/* loop to traverse through all selected atributes and return sum of attribute prices */
				for (var key in prod_info[1])
				{
					if (prod_info[1].hasOwnProperty(key))
					{
						if (prod_info[1][key].itemattributeoption_prefix == "+")
						{
							optionPrice = Number(optionPrice) + Number(prod_info[1][key].optionprice);
						}
						else
						{
							optionPrice = Number(optionPrice) - Number(prod_info[1][key].optionprice);
						}
					}
				}

				optionTotal = (optionPrice*quantity);
			}

			/* productTotal contains final price of product considering quantity and atribures selected */
			var productTotal = (productPrice*quantity)+(optionTotal);
			techjoomla.jQuery('#qtc_prod_total'+container_number).html(productTotal);
			/* set product price to hidden field in clone */
			techjoomla.jQuery('input[name="qtc_product_total['+container_number+'][ptotal]"]').val(productTotal);

			/* Function to get shipping methods for selected product*/
			qtcGetProductShippingDetails(prod_id, container_number);
		}
	});
	qtc_update_order_price();
}

function qtc_update_order_price()
{
	var values = techjoomla.jQuery('#createOrder').serialize();

	var callurl = "index.php?option=com_quick2cart&task=createorder.qtc_update_order_price";

	techjoomla.jQuery.ajax({
		url: callurl,
		type: "GET",
		data:values,
		async:false,
		cache: false,
		success: function(data)
		{
			techjoomla.jQuery('#qtc_order_total_price').html(data);
		}
	});
}

function openModal()
{
	document.getElementById('q2c-ajax-call-loader-modal').style.display = 'block';
	document.getElementById('q2c-ajax-call-fade-content-transparent').style.display = 'block';
}

function closeModal()
{
	document.getElementById('q2c-ajax-call-loader-modal').style.display = 'none';
	document.getElementById('q2c-ajax-call-fade-content-transparent').style.display = 'none';
}

function selectShip(id)
{
	techjoomla.jQuery('.addressship').each(function(){
		techjoomla.jQuery(this).prop('checked', false);
	});

	techjoomla.jQuery('#shipping_address'+id).prop('checked', true);
}

function selectBill(id)
{
	techjoomla.jQuery('.addressbill').each(function(){
	techjoomla.jQuery(this).prop('checked', false);
	});

	techjoomla.jQuery('#billing_address'+id).prop('checked', true);
}

function nextstep()
{
	var result = validateselectclient();

	if (result)
	{
		techjoomla.jQuery('#qtctab_selectclient').removeClass('active');
		techjoomla.jQuery('#qtctab_addproduct').addClass('active');
		techjoomla.jQuery('#tab2id').addClass('active');
		techjoomla.jQuery('#tab1id').removeClass('active');
	}
}

function validateselectclient()
{
	if(jQuery('#qtcuser').val() == 0)
	{
		techjoomla.jQuery('#qtc_order_error').html('<div class="alert alert-error">'+Joomla.JText._('COM_QUICK2CART_SELECT_CLIENT_ERROR_MSG')+'</div>');
		return false;
	}

	if (techjoomla.jQuery('input[name="shipping_address"]').length)
	{
		if(!techjoomla.jQuery('input[name="shipping_address"]:checked').val())
		{
			techjoomla.jQuery('#qtc_order_error').html('<div class="alert alert-error">'+Joomla.JText._('COM_QUICK2CART_SELECT_SHIPPING_ADDRESS_ERROR_MSG')+'</div>');
			return false;
		}
	}

	if(!techjoomla.jQuery('input[name="billing_address"]:checked').val())
	{
		techjoomla.jQuery('#qtc_order_error').html('<div class="alert alert-error">'+Joomla.JText._('COM_QUICK2CART_SELECT_BILLING_ADDRESS_ERROR_MSG')+'</div>');
		return false;
	}

	return true;
}

/* Function to open add client form*/
function addClient(baseUrl)
{
	var register = baseUrl+"index.php?option=com_quick2cart&view=registration&layout=addnewuser&tmpl=component";

	SqueezeBox.open(register ,{handler: 'iframe', size: {x: window.innerWidth-150, y: window.innerHeight-150}});
}

/* Function to display all addreses of selected client*/
function showAddresses()
{
	var userId = techjoomla.jQuery("#qtcuser option:selected").val();

	var callurl = "index.php?option=com_quick2cart&task=customer_addressform.getUserAddressList&uid="+userId;

	techjoomla.jQuery.ajax({
		url: callurl,
		beforeSend: function(){
			openModal();
			},
		type: "GET",
		cache: false,
		success: function(data)
		{
			/* Ajax result contains all the address list for selected client*/
			techjoomla.jQuery('#qtc_user_addresses .qtc_user_addresses_wrapper').html(data);
			techjoomla.jQuery('#qtc_order_error').empty();

			if (userId != "0")
			{
				techjoomla.jQuery('.qtcadd_address_button').show();

				if (data != '')
				{
					techjoomla.jQuery('#qtc_user_addresses .qtc_user_addresses_wrapper').prepend("<h2>"+Joomla.JText._('COM_QUICK2CART_CREATE_ORDER_SELECT_ADDRESS')+"</h2><br>");
				}
			}
			else
			{
				techjoomla.jQuery('.qtcadd_address_button').hide();
			}

			closeModal();
		}
	});
}

/* Function to open form for adding new address for selected client*/
function addAddress(baseUrl)
{
	var userId = techjoomla.jQuery("#qtcuser option:selected").val();

	SqueezeBox.open(baseUrl+'index.php?option=com_quick2cart&view=customer_addressform&tmpl=component&userid='+userId ,{handler: 'iframe', size: {x: window.innerWidth-250, y: window.innerHeight-150}});
}

/* Function to open form for updating selected address for selected client*/
function editAddress(id)
{
	var userId = techjoomla.jQuery("#qtcuser option:selected").val();

	SqueezeBox.open(qtc_base_url+'index.php?option=com_quick2cart&view=customer_addressform&tmpl=component&id='+id+'&userid='+userId ,{handler: 'iframe', size: {x: window.innerWidth-250, y: window.innerHeight-150}});
}

/* Function to delete selected address for selected client*/
function deleteAddress(id)
{
	var deleteFlag = confirm(Joomla.JText._('COM_QUICK2CART_DELETE_ADDRESS'));

	if (deleteFlag == 1)
	{
		var callurl = "index.php?option=com_quick2cart&task=customer_addressform.delete&addressId="+id;

		techjoomla.jQuery.ajax({
			url: callurl,
			type: "GET",
			cache: false,
			success: function(data)
			{
				if (data == '1')
				{
					techjoomla.jQuery('.qtc-address'+id).remove();

					/* if there are no address div then hide address title (Select Address)*/
					if (!techjoomla.jQuery(".qtc_address_pin").length && !techjoomla.jQuery("#q2c_billing").length)
					{
						techjoomla.jQuery(".q2c-wrapper .qtcAddBorder .checkout-addresses").hide();
					}
					else
					{
						techjoomla.jQuery(".q2c-wrapper .qtcAddBorder .checkout-addresses").show();
					}

					alert(Joomla.JText._('COM_QUICK2CART_CUSTOMER_ADDRESS_DELETE_MSG'));
				}
			}
		});
	}
	else
	{
		return false;
	}
}

/* Function to delete product clone*/
function qtcremoveProduct(id)
{
	techjoomla.jQuery("#qtc_product_div"+id).parent().remove();
}

/* Function to show product price*/
function qtcpopulateProducts(prod_container_num)
{
	var storeId = techjoomla.jQuery("#qtcorder_productdetails"+prod_container_num+"store_id"+" option:selected").val();

	if (storeId != '')
	{
		var callurl = "index.php?option=com_quick2cart&task=stores.getAllProductsFromStore&storeId="+storeId;

		techjoomla.jQuery.ajax({
			url: callurl,
			beforeSend: function(){
			openModal();
			},
			type: "GET",
			cache: false,
			success: function(data)
			{
				techjoomla.jQuery('#qtc_product_attributes'+prod_container_num).html("");
				techjoomla.jQuery('#qtc_product_shipping_details'+prod_container_num).html("");
				techjoomla.jQuery('#qtcorder_productdetails'+prod_container_num).html(data);

				jQuery('#qtcorder_productdetails'+prod_container_num).trigger("liszt:updated");

				closeModal();
			}
		});
	}
}

/* Function to check order limit for the product*/
function checklimit(prod_container_num,price,currency)
{
	var productId = techjoomla.jQuery("#qtcorder_productdetails"+prod_container_num+" option:selected").val();
	var quantity = techjoomla.jQuery('#qtc_prod_quantity'+prod_container_num).val();
	var min_val = techjoomla.jQuery('#qtc_prod_quantity'+prod_container_num).attr("min");
	var max_val = techjoomla.jQuery('#qtc_prod_quantity'+prod_container_num).attr("max");

	if (quantity != '')
	{
		if (quantity < min_val)
		{
			alert(Joomla.JText._("QTC_MIN_LIMIT_MSG")+min_val);
			techjoomla.jQuery('#qtc_prod_quantity'+prod_container_num).val(min_val);
		}

		if (quantity > max_val)
		{
			alert(Joomla.JText._("QTC_MAX_LIMIT_MSG")+max_val);
			techjoomla.jQuery('#qtc_prod_quantity'+prod_container_num).val(max_val);
		}

		var qty = techjoomla.jQuery('#qtc_prod_quantity'+prod_container_num).val();
		var fprice = price.replace(/\D/g,'');

		techjoomla.jQuery('#qtc_prod_total_currency'+prod_container_num).html(currency);
		qtc_update_product_price(productId,prod_container_num);
	}
	else
	{
		alert(Joomla.JText._('QTC_ENTER_NUMERICS'));
		techjoomla.jQuery('#qtc_prod_quantity'+prod_container_num).val('');
		techjoomla.jQuery('#qtc_prod_total'+prod_container_num).html('');
		techjoomla.jQuery('#qtc_prod_total_currency'+prod_container_num).html('');
	}
}

