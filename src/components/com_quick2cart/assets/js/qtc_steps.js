techjoomla.jQuery(document).ready(function()
{
	techjoomla.jQuery('#MyWizard').on('change', function(e, data)
	{
		values = techjoomla.jQuery('#adminForm').serialize();

		/* Get tab ID */
		var ref_this = techjoomla.jQuery("#qtc-steps li[class='active']");
		var stepId = ref_this[0].id;

		if (stepId == 'qtc_billing')
		{
			if (!techjoomla.jQuery(".qtc_address_pin").length && !techjoomla.jQuery("#q2c_billing").length)
			{
				alert(Joomla.JText._('COM_QUICK2CART_NO_ADDRESS_ERROR'));

				return false;
			}

			/*Check if shipping address is empty or not*/
			if (techjoomla.jQuery('input[name="shipping_address"]').length)
			{
				if(!techjoomla.jQuery('input[name="shipping_address"]:checked').val())
				{
					alert(Joomla.JText._('COM_QUICK2CART_SELECT_SHIPPING_ADDRESS_ERROR_MSG'));

					return false;
				}
			}

			/*Check if billing address is empty or not*/
			if (techjoomla.jQuery('input[name="billing_address"]').length)
			{
				if(!techjoomla.jQuery('input[name="billing_address"]:checked').val())
				{
					alert(Joomla.JText._('COM_QUICK2CART_SELECT_BILLING_ADDRESS_ERROR_MSG'));

					return false;
				}
			}
		}

		if(data.direction==='next')
		{
			/*First step1 validation */
			if(!qtc_chkValidStep(stepId))
			{
				return false;
			}

			techjoomla.jQuery.ajax({
				url: root_url+'index.php?option=com_quick2cart&view=cartcheckout&task=cartcheckout.qtc_autoSave&stepId='+stepId,
				type: 'POST',
				//async:false,
				data:values,
				dataType: 'json',
				beforeSend: function()
				{
					if(stepId == 'qtc_billing')
					{
						//techjoomla.techjoomla.jQuery('.qtc_billing_page').hide('slow');
						/*  remove previous html */
						techjoomla.jQuery('#qtc_reviewAndPayHTML').html('');
						techjoomla.jQuery('#qtcProdShippingMethos').html('');
					}

					/* Show loading msg */
					techjoomla.jQuery('#qtc_StepLoading').show();
				},
				complete: function()
				{
					/*if(stepId == 'qtc_billing')
					{
						techjoomla.jQuery('.qtc_billing_page').show();
					}*/
					techjoomla.jQuery('#qtc_StepLoading').hide();

				},
				success: function(response)
				{
					/* techjoomla.jQuery('#qtc_StepLoading').hide(); */
					if(stepId == 'qtc_billing')
					{
						techjoomla.jQuery('.qtc_billing_page').show();
					}

					/* Add payment related detail */
					if(stepId == 'qtc_billing' && response['shipMethoDetail'])
					{

						if(techjoomla.jQuery("#qtcProdShippingMethos").length)
						{
							techjoomla.jQuery('#qtcProdShippingMethos').html(response['shipMethoDetail']);
						}
					}

					if (response['shippingNotAvailable'])
					{
						alert(response['shippingNotAvailable']);
					}

					/* Add payment related detail */
					//if(stepId == 'qtc_billing' && response['payAndReviewHtml'])
					if(response['payAndReviewHtml'])
					{
						// Save order id in hidded form and add payment page html
						techjoomla.jQuery('#order_id').val(response['order_id']);
						techjoomla.jQuery('#qtc_reviewAndPayHTML').html(response['payAndReviewHtml']);

						// As order is placed, buyer could not edit cart
						techjoomla.jQuery('#qtc_step1_cartdetail').remove();

						// show "you could not edit cart" msg
						techjoomla.jQuery('#qtc_cartStepAlert').html("<div class=\"alert alert-info\">"+qtc_cartAlertMsg+"</div>");

						techjoomla.jQuery('#qtc_billing_alert_on_order_placed').html("<div class=\"alert alert-info\">"+Joomla.JText._('COM_QUICK2CART_SHIPPING_ADDRESS_ERROR_MSG')+"</div>");
						techjoomla.jQuery('#qtc_ckout_billing-info').hide();

						/* @TODO Remove */
						//techjoomla.jQuery('# registration tab').hide();


						// show "you could not edit shipping methods" msg
						techjoomla.jQuery('#qtcShippingMethTab').remove();
						techjoomla.jQuery('#qtc_shipStepAlert').html("<div class=\"alert alert-info\">"+qtc_shipMethRemovedMsg+"</div>");

						goToByScroll('qtcPaymentGatewayList');
					}
				},
				error: function(response)
				{
					techjoomla.jQuery('#'+stepId+'-error').show('slow');
					// show ckout error msg
					console.log(' ERROR!!' );
					return e.preventDefault();
				}
			});


			//	setTimeout(function(){ hideImage() },10);
		}

		// Scroll to top
		techjoomla.jQuery('html,body').animate({scrollTop: techjoomla.jQuery("#qtc-steps").offset().top},'slow');

	  if(data.step===1 && data.direction==='next') {
		// return e.preventDefault();
	  }
	});



	techjoomla.jQuery('#MyWizard').on('changed', function(e, data)
	{
		// Manage next and back button display according to tab and data
		qtcHideAndShowNextButton();

		// The save & exit button remains same even if we navigate to first tab hence added code
		qtc_changenexttoexit(0);

		var thisactive = techjoomla.jQuery("#qtc-steps li[class='active']");
		stepthisactive = thisactive[0].id;

		if(stepthisactive == techjoomla.jQuery("#qtc-steps li").first().attr('id'))
		{
			techjoomla.jQuery(".ad-form #btnWizardPrev").hide();
		}
		else
		{
			techjoomla.jQuery(".ad-form #btnWizardPrev").show();
		}

		if(stepthisactive == techjoomla.jQuery("#qtc-steps li").last().attr('id'))
		{
			techjoomla.jQuery(".ad-form .prev_next_wizard_actions").hide();
			var prev_button_html='<button id="btnWizardPrev1" onclick="techjoomla.jQuery(\'#MyWizard\').wizard(\'previous\');"	type="button" class="btn btn-prev" > <i class="icon-circle-arrow-left icon-white"></i>Prev</button>';

			if(stepthisactive == "qtc_summaryAndPay" ){
				techjoomla.jQuery('#ad_payHtmlDiv div.form-actions').prepend( prev_button_html );
				techjoomla.jQuery('#ad_payHtmlDiv div.form-actions input[type="submit"]').addClass('pull-right');
			}
			if(stepthisactive == "ad-review" ){
				techjoomla.jQuery('.ad_reviewAdmainContainer div.form-actions').prepend( prev_button_html );
			}
		}
		else
			techjoomla.jQuery(".ad-form .prev_next_wizard_actions").show();

		var unlimited_ad_checked=techjoomla.jQuery("input[name=unlimited_ad]:radio:checked").val();
/*
		if((stepthisactive=='ad-targeting'  || stepthisactive=='qtc_billing' ))
		{
			qtc_changenexttoexit(1);
		} */

	});
	techjoomla.jQuery('#MyWizard').on('finished', function(e, data) {
	});
	techjoomla.jQuery('#btnWizardPrev').on('click', function() {
	  techjoomla.jQuery('#MyWizard').wizard('previous');
	});

	/*
	 jQuery('#btnWizardNext').on('click', function()
	{
		jQuery('#MyWizard').wizard('next','foo');
	});
	*/

	techjoomla.jQuery('#btnWizardStep').on('click', function() {
	  var item = techjoomla.jQuery('#MyWizard').wizard('selectedItem');
	});
	techjoomla.jQuery('#MyWizard').on('stepclick', function(e, data) {
	  if(data.step===1) {
		// return e.preventDefault();
	  }
	});

	// optionally navigate back to 2nd step
	techjoomla.jQuery('#btnStep2').on('click', function(e, data) {
	  techjoomla.jQuery('[data-target=#step2]').trigger("click");
	});

});
function open_div(geo,camp)
{
	btnWizardNext();

}
function btnWizardNext()
{
	techjoomla.jQuery('#MyWizard').wizard('next','foo');

	/* THis function added in checkout view->default layout*/
	/*qtcHideAndShowNextButton();*/
}


function qtc_changenexttoexit(ischecked)
{
	if (ischecked == 1)
	{
		techjoomla.jQuery('.prev_next_wizard_actions #btnWizardNext').removeClass('btn-primary');
		techjoomla.jQuery('.prev_next_wizard_actions #btnWizardNext').addClass('btn-success');
		techjoomla.jQuery('.prev_next_wizard_actions #btnWizardNext span').text(savenexitbtn_text);
	}
	else
	{
		techjoomla.jQuery('.prev_next_wizard_actions #btnWizardNext span').text(savennextbtn_text);
		techjoomla.jQuery('.prev_next_wizard_actions #btnWizardNext').addClass('btn-primary');
		techjoomla.jQuery('.prev_next_wizard_actions #btnWizardNext').removeClass('btn-success');
	}

}

/*
function applycoupon(enterCopMsg)
{
	if (techjoomla.jQuery('#coupon_chk').is(':checked'))
	{
		if (techjoomla.jQuery('#coupon_code').val() =='')
		{
			alert(enterCopMsg);
		}
		else
		{
			var coupon_code=techjoomla.jQuery('#coupon_code').val();

			techjoomla.jQuery.ajax({
				url: '?option=com_quick2cart&task=cartcheckout.getcoupon&coupon_code='+coupon_code,
				type: 'GET',
				dataType: 'json',
				success: function(data) {
					amt=0;
					val=0;
					if (data != 0)
					{
						window.location.reload();
					}
					else
					{
						alert(enterCopMsg);
						techjoomla.jQuery('#coupon_code').val('');
					}
				}
			});
		}
	}
}
*/
