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

JHtml::_('behavior.formvalidation');
jimport('joomla.form.formvalidator');
JHtml::_('behavior.tooltip');
JHtmlBehavior::framework();
jimport('joomla.html.parameter');
jimport('joomla.filesystem.file');
JHtml::_('behavior.modal');

$root_url = JUri::root();
$document = JFactory::getDocument();

if (!class_exists('comquick2cartHelper'))
{
  //require_once $path;
 	$path = JPATH_SITE . '/components/com_quick2cart/helper.php';
   JLoader::register('comquick2cartHelper', $path);
   JLoader::load('comquick2cartHelper');
}
$getLanguageConstantForJs = comquick2cartHelper::getLanguageConstantForJs();


$stepjs_initalization="
var  qtc_cartAlertMsg =\"".JText::_("COM_QUICK2CART_COULD_NOT_CHANGE_CART_DETAIL_NOW")."\";
var  qtc_shipMethRemovedMsg =\"".JText::_("COM_QUICK2CART_COULD_NOT_CHANGE_SHIPMETH_DETAIL_NOW")."\";

";
$document->addScriptDeclaration($stepjs_initalization);

$document->addStyleSheet($root_url.'components/com_quick2cart/assets/css/fuelux2.3.1.css');
$document->addStyleSheet($root_url.'components/com_quick2cart/assets/css/qtc_steps.css');

$user=JFactory::getUser();
$jinput=JFactory::getApplication()->input;
$params = JComponentHelper::getParams('com_quick2cart');
$entered_numerics= "'".JText::_('QTC_ENTER_NUMERICS')."'";
?>
<?php

// DECIDE WHETHER TERMS & CONDITON HAVE TO USE
$showTermsCond = $params->get('termsConditons', 0);
$termsCondArtId = $params->get('termsConditonsArtId', 0);
$isShippingEnabled = $params->get('shipping', 0);
$shippingMode = $params->get('shippingMode', 'itemLevel');
$termsCondArtId = trim($termsCondArtId) ;
$showTersmAndCond = 0;

if (!empty($showTermsCond) && !empty($termsCondArtId))
{
	$showTersmAndCond = 1;
}
$productHelper =  new productHelper;
$document = JFactory::getDocument();
?>

<?php
if ($this->onBeforeCheckoutViewDisplay)
{
	echo $this->onBeforeCheckoutViewDisplay;
}

if (empty($this->cart)){
?>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?> quick2cart_coat" >

	<div class="well" >
		<div class="alert alert-danger">
			<span ><?php echo JText::_("QTC_EMPTY_CART"); ?> </span>
		</div>
	</div>
</div>
<?php
	return false;
}?>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?> quick2cart_coat" >
	<div>
		<h1 class="">
			<?php echo JText::_('QTC_CHECKOUT');?>
		</h1>
	</div>
<?php

$showoptioncol = 0;

foreach ($this->cart as $citem)
{
	if (!empty($citem['options']))
	{
		// Atleast one found then show
		$showoptioncol = 1;
		break;
	}
}

$userbill=(isset($this->userdata['BT']))?$this->userdata['BT']:'';
$usership=(isset($this->userdata['ST']))?$this->userdata['ST']:'';
$baseurl=JRoute::_ (JUri::root().'index.php');

$qtc_hideregistrationTabFlag=$params->get('useGuestCheckoutOnly',0); /*  ON setting useGuestCheckoutOnly= 1.registration tab hide(cart detail will display),coupon div hide,*/
//// for getting current tab status one page chkout::
$session =JFactory::getSession();
$qtc_tab_state = $session->get('one_pg_ckout_tab_state');

$js="
var isgst=".$params->get('guest').";
var qtc_baseurl='".$baseurl."';
var statebackup;

techjoomla.jQuery('#dis_cop').hide();
	// used in new checkout
	function validateExtraFields()
	{
		var showTersmAndCond =".$showTersmAndCond.";

		if (showTersmAndCond)
		{
			// TERMS AND CONDITION
			// If (adminForm.qtc_accpt_terms.checked == false)
			if (document.getElementById('qtc_accpt_terms').checked)
			{
			}
			else
			{  // not checked
				return \"".JText::_('COM_QUICK2CART_TERMS_CONDITION_ALERT_MSG')."\";
			}
		}

	}


";
$document->addScriptDeclaration($js);

if (version_compare(JVERSION, '1.6.0', 'ge'))
{
$js = "
	Joomla.submitbutton = function(pressbutton){";
}
else{
	$js = "function submitbutton(pressbutton) {";
}
$js .="
		show_ship();
		submitform(pressbutton);
		return;
}

	function submitform(pressbutton){
		 if (pressbutton) {
		 	document.adminForm.task.value = pressbutton;
		 }
		 if (typeof document.adminForm.onsubmit == 'function') {
		 	document.adminForm.onsubmit();
		 }
		 	document.adminForm.submit();
	}
";
$document->addScriptDeclaration($js);
?>

<script type="text/javascript">
	var tjWindowWidth = techjoomla.jQuery('div#qtc_mainwrapper').width();

	function loadingImage()
	{
		jQuery('<div id="appsloading"></div>')
		.css("background", "rgba(255, 255, 255, .8) url('"+root_url+"components/com_quick2cart/assets/images/ajax.gif') 50% 15% no-repeat")
		.css("top", jQuery('#TabConetent').position().top - jQuery(window).scrollTop())
		.css("left", jQuery('#TabConetent').position().left - jQuery(window).scrollLeft())
		.css("width", jQuery('#TabConetent').width())
		.css("height", jQuery('#TabConetent').height())
		.css("position", "fixed")
		.css("z-index", "1000")
		.css("opacity", "0.80")
		.css("-ms-filter", "progid:DXImageTransform.Microsoft.Alpha(Opacity = 80)")
		.css("filter", "alpha(opacity = 80)")
		.appendTo('#TabConetent');
	}
	function hideImage()
	{
		techjoomla.jQuery('#appsloading').remove();
	}
// for new checkout
function qtc_chkValidStep(stepId)
{
		// for billing tab
		if (stepId=="qtc_billing" && techjoomla.jQuery("#adminForm").length)
		{
			var  qtcBillForm = document.adminForm;

			if (document.formvalidator.isValid(qtcBillForm))
			{
				// return true;
			}
			else
			{
				return false;
			}

			// check for extra fields
			var checkMsg = validateExtraFields();
			if (checkMsg) {
				// if not checked
				alert(checkMsg);
				return false;
			}

			/*techjoomla.jQuery('#adminForm').validate({
				debug: true,
				rules: {
				"bill[email1]": {
						required: true,
						email: true
						},
				"bill[phon]": {
					 required: true,
					 digits: true
				 }
				},
				errorClass:"help-inline",
				errorElement:"span",
				highlight:function(element, errorClass, validClass){
					techjoomla.jQuery(element).parents('.form-group').addClass('error');
					techjoomla.jQuery(element).closest('.form-group').removeClass('success').addClass('error');
				},
				unhighlight: function(element, errorClass, validClass) {
					techjoomla.jQuery(element).parents('.form-group').removeClass('error');
					techjoomla.jQuery(element).parents('.form-group').addClass('success');
				}
			});*/
		}

		return true;
}
// for new checkout
function qtc_gatewayHtml(ele,orderid,loadingImgPath)
{
	techjoomla.jQuery.ajax({
		url: '?option=com_quick2cart&task=cartcheckout.qtc_gatewayHtml&tmpl=component&gateway='+ele+'&order_id='+orderid,
		type: 'POST',
		data:'',
		dataType: 'text',
		beforeSend: function()
		{
			var loadMsg = "<?php echo JText::_( "QTC_PAYMENT_GATEWAY_LOADING_MSG" , true); ?>";
			techjoomla.jQuery('#qtc_paymentGatewayList').after('<div class=\"com_quick2cart_ajax_loading\"><div class=\"com_quick2cart_ajax_loading_text\">'+loadMsg+' </div><img class=\"com_quick2cart_ajax_loading_img\" src="'+root_url+'components/com_quick2cart/assets/images/ajax.gif"></div>');
		},
		complete: function() {
			techjoomla.jQuery('.com_quick2cart_ajax_loading').remove();

		},
		success: function(data)
		{
			if (data)
			{
				techjoomla.jQuery('#qtc_payHtmlDiv').html(data);
				techjoomla.jQuery('#qtc_payHtmlDiv div.form-actions input[type="submit"]').addClass('pull-right');

				//var prev_button_html='<button id="btnWizardPrev1" onclick="jQuery(\'#MyWizard\').wizard(\'previous\');"	type="button" class="btn btn-default btn-primary pull-left" > <i class="icon-circle-arrow-left <?php echo Q2C_ICON_WHITECOLOR; ?>" ></i>Prev</button>';
				//techjoomla.jQuery('#ad_payHtmlDiv div.form-actions').prepend(prev_button_html);

			}

		}
	});
}

function show_ship(){
	var totalprice = techjoomla.jQuery('#total_after_tax').val();

	if (techjoomla.jQuery('#ship_chk').is(':checked'))
	{
		techjoomla.jQuery('.ship_tr').hide();

		techjoomla.jQuery.each(techjoomla.jQuery('.bill'),function()
		{
			var bval = techjoomla.jQuery(this).val();
			var bid = techjoomla.jQuery(this).attr('id');

			/*when we r going to copy value from state to ship_stateid select box*/
			if (bid=='country')
			{
				/*var ship_country= techjoomla.jQuery('select#ship_country option:selected').val();*/
				/*if (bval!=ship_country)*/ /*bill country and ship_country not same*/
				generateoption(statebackup,'ship_country','')
			}
			techjoomla.jQuery('#ship_'+bid).val(bval);
		});

		/*calculateship(totalprice);*/

		/*changeFormClass('form-horizontal', 2);*/
		changeWidthClass('col-md-12');
	}
	else if(techjoomla.jQuery('#ship_chk').length == 0)
	{
		changeWidthClass('col-md-12');
		/*changeFormClass('form-horizontal', 1);*/
	}
	else
	{
		techjoomla.jQuery('.ship_tr').show();
		/*changeFormClass('form-vertical', 1);*/
		changeWidthClass('col-md-6');
	}
}

function generateState(countryId,Dbvalue)
{
	var country=techjoomla.jQuery('#'+countryId).val();
		if (country==undefined)
		{
			return (false);
		}
	techjoomla.jQuery.ajax({
		url: '?option=com_quick2cart&task=cartcheckout.loadState&tmpl=component&country='+country,
		type: 'GET',
		dataType: 'json',
		success: function(data)
		{
			if (countryId=='country')
			{
				statebackup=data;
				show_ship();
			}
			generateoption(data,countryId,Dbvalue);
		}
	});
}

function generateoption(data,countryId,Dbvalue)
{
	var country=techjoomla.jQuery('#'+countryId).val();
	var options, index, select, option;

	// add empty option according to billing or shipping
	if (countryId=='country'){
		select = techjoomla.jQuery('#state');
		default_opt = "<?php echo JText::_('QTC_BILLIN_SELECT_STATE')?>";
	}
	else{
			select = techjoomla.jQuery('#ship_state');
			default_opt = "<?php echo JText::_('QTC_SHIPIN_SELECT_STATE')?>";
		}

	// REMOVE ALL STATE OPTIONS
	select.find('option').remove().end();

	// To give msg TASK  "please select country START"
	var selected="selected=\"selected\"";
	var op='<option '+selected+' value="">'  +default_opt+   '</option>'     ;

	if (countryId=='country'){
		techjoomla.jQuery('#state').append(op);
	}
	else{
		techjoomla.jQuery('#ship_state').append(op);
	}
	 // END OF msg TASK

	if (data)
	{
		options = data.options;
		for (index = 0; index < data.length; ++index)
		{
			var opObj = data[index];
			selected="";

			if (opObj.id==Dbvalue)
			{
				selected="selected=\"selected\"";
			}
			var op='<option '+selected+' value=\"'+opObj.id+'\">'  +opObj.region+   '</option>';

			if (countryId=='country'){
				techjoomla.jQuery('#state').append(op);
			}
			else{
				techjoomla.jQuery('#ship_state').append(op);
			}
		}	 // end of for
	}
}

techjoomla.jQuery(document).ready(function()
{
	techjoomla.jQuery(".bill").bind("change",show_ship);

	var DBuserbill="<?php echo (isset($userbill->state_code))?$userbill->state_code:''; ?>";
	var DBusership="<?php echo (isset($usership->state_code))?$usership->state_code:''; ?>";
	var tax_tot = techjoomla.jQuery('#total_after_tax').val();
	generateState("country",DBuserbill) ;
	setTimeout(function(){
			generateState("ship_country",DBusership) ;
		},1000);

	show_ship();
	techjoomla.jQuery('.discount').popover();

/*
		techjoomla.jQuery('#adminForm').validate({
			debug: true,
			rules: {
			"bill[email1]": {
					required: true,
					email: true
					},
			"bill[phon]": {
				 required: true,
				 digits: true
			 }
			},
			errorClass:"help-inline",
			errorElement:"span",
			highlight:function(element, errorClass, validClass){
				techjoomla.jQuery(element).parents('.form-group').addClass('error');
				techjoomla.jQuery(element).closest('.form-group').removeClass('success').addClass('error');
			},
			unhighlight: function(element, errorClass, validClass) {
				techjoomla.jQuery(element).parents('.form-group').removeClass('error');
				techjoomla.jQuery(element).parents('.form-group').addClass('success');
			}
		});*/
});  // on ready end


function caltotal(totalpriceid,amt,minqty,maxqty,minmsg,maxmsg,obj)
{
	if (obj.value < minqty)
	{
		alert(minmsg+" "+minqty);
		obj.value=minqty;
		return false;
	}
	elseif (obj.val() > maxqty)
	{
		alert(maxmsg+" "+maxqty);
		obj.val()=maxqty;
		return false;
	}

	var correct = checkforalpha(obj,'',"<?php echo JText::_('QTC_ENTER_NUMERICS')  ?>");
	if (correct)
		update_cart();

}

function update_cart()
{
	var cartfields=techjoomla.jQuery('.cart_fields').serializeArray();

	techjoomla.jQuery.ajax({
		url: "?option=com_quick2cart&task=updatecart&tmpl=component",
		type: "POST",
		data:  cartfields,
		dataType: 'json',
		success: function(data)
		{
			window.location.reload();
		}
	});

}

function calculateship(totalprice)
{
	var ship_country=techjoomla.jQuery('#ship_country').val();
	var ship_state=techjoomla.jQuery('#ship_state').val();
	var ship_city=techjoomla.jQuery('#ship_city').val();
	var data=new Array();

	if (ship_country && ship_state && ship_city)
	{
		var saveData = {};
		saveData.totalprice = totalprice;
		saveData.country = ship_country;
		saveData.region  = ship_state;
		saveData.city = ship_city;

		var jsondata=JSON.stringify(saveData);

		techjoomla.jQuery.ajax({
		url: '?option=com_quick2cart&task=cartcheckout.calFinalShipPrice&tmpl=component',
		type: 'POST',
		dataType: 'json',
		data:{data : jsondata},
		success: function(shipprice)
		{
			//" success shipprice['charges']= "+shipprice['charges'] +"shipprice['totalamt']=" +shipprice['totalamt']);
			if (shipprice['charges'] && shipprice['totalamt'])
			{
				var tax=document.getElementById("ship_amt");
				var netamt=document.getElementById("after_ship_amt");
				var final_amt=document.getElementById("after_ship_amt");
			//	alert("tax" +tax + netamt + final_amt);
				if (tax && netamt && final_amt)
				{
					tax.innerHTML =shipprice['charges'];
					netamt.innerHTML =shipprice['totalamt'];
					final_amt.innerHTML =shipprice['totalamt'];
				}
			}
		}
		});
	}
}

function chkbillmail(email){

	var userid="<?php echo $user->id;?>";
	if (userid > 0) // if user is not logged in
	{
		return (false);
	}
	var status=false;
	techjoomla.jQuery.ajax({
	url: '?option=com_quick2cart&task=cartcheckout.chkbillmail&tmpl=component&email='+email,
	type: 'GET',
	dataType: 'json',
	success: function(data)
	{
		if (data[0] == 1){
			var duplicateemail="<div class=\"alert alert-danger qtc_removeBottomMargin \">"+data[1]+"</div>";
			techjoomla.jQuery('#billmail_msg').html(duplicateemail);
			techjoomla.jQuery("input[type=submit]").attr("disabled", "disabled");
			techjoomla.jQuery('#qtc_billmail_msg_div').show();
			status=false;
		}
		else{
			techjoomla.jQuery('#billmail_msg').html('');
			techjoomla.jQuery("input[type=submit]").removeAttr("disabled");
			techjoomla.jQuery('#qtc_billmail_msg_div').hide();
			status=true;
		}
	}
	});

	return (status);
}
// Added by Sneha, take to user details page when entered registered email on billing
function chkbillmailregistered(email){

	var userid="<?php echo $user->id;?>";
	if (userid > 0) // if user is not logged in
	{
		return (false);
	}
	var status=false;
	techjoomla.jQuery.ajax({
	url: '?option=com_quick2cart&task=cartcheckout.chkbillmail&tmpl=component&email='+email,
	type: 'GET',
	dataType: 'json',
	success: function(data)
	{
		if (data[0] == 1){
			var duplicateemail="<div class=\"alert alert-danger qtc_removeBottomMargin \">"+data[1]+"</div>";

			// Get login type (guest or registration)
			var guestck = "";
			 guestck = techjoomla.jQuery('input[name=qtc_guest_regis]:checked').val();

			if(guestck == 'guest')
			{
				if (confirm("<?php echo JText::_("COM_QUICK2CART_R_U_SURE_U_WANT_USE_SAMEEMAIL")?>"))
				{
					// Show user info block
					techjoomla.jQuery('#qtc_user-info').slideUp('slow');
				}
				else
				{
					// Show user info block
					techjoomla.jQuery('#qtc_user-info').slideDown('slow');

					// Show msg
					techjoomla.jQuery('#qtc_loginmail_msg_div').show();

					goToByScroll('qtc_user-info');

				}
			}
			else if(guestck == 'register')
			{
				alert("<?php echo JText::_("QTC_BILLMAIL_EXISTS")?>");
				// Show user info block
				techjoomla.jQuery('#qtc_user-info').slideDown('slow');

				// Show msg
				techjoomla.jQuery('#qtc_loginmail_msg_div').show();

				goToByScroll('qtc_user-info');

			}
			/*location.href='http://202.88.154.166:2084/buy2donate/index.php/shop/cartcheckout?view=cartcheckout';
			techjoomla.jQuery('#user-info-tab').css("display","block");
			techjoomla.jQuery('#billing-info-tab').css("display","none");*/

			techjoomla.jQuery('#loginmail_msg').html(duplicateemail);
		/*	techjoomla.jQuery("input[type=submit]").attr("disabled", "disabled");

			// Show user info block
			techjoomla.jQuery('#qtc_user-info').slideDown('slow');

			// Show msg
			techjoomla.jQuery('#qtc_loginmail_msg_div').show();

			goToByScroll('qtc_user-info');
			status=false;*/
		}
		else{
			techjoomla.jQuery('#billmail_msg1').html('');
			techjoomla.jQuery("input[type=submit]").removeAttr("disabled");
			techjoomla.jQuery('#qtc_billmail_msg_div1').hide();
			status=true;
		}
	}
	});

	return (status);
}



	// This is a functions that scrolls to #{blah}link
	function goToByScroll(id){
			 // Remove "link" from the ID
		 //id = id.replace("link", "");
			 // Scroll
		 techjoomla.jQuery('html,body').animate({
				 scrollTop: techjoomla.jQuery("#"+id).offset().top},
				 'slow');
	}
	function addEditLink(selectorObj)
	{
		var objId = techjoomla.jQuery(selectorObj).attr('id');
		/*if (techjoomla.jQuery(objId).find(" a .qtc_editTab"))
		{
			alert("fountd");
			techjoomla.jQuery(objId).find(" a .qtc_editTab").remove();
		}*/
		techjoomla.jQuery(selectorObj).append('<a class="qtc_editTab" onclick="qtc_hideshowTabs(this)"><?php echo JText::_('COM_QUICK2CART_EDIT'); ?></a>');
	}
	function qtc_hideAllEditLinks()
	{
		techjoomla.jQuery("a.qtc_editTab").hide();
	}
function qtc_showAllEditLinks()
	{
		techjoomla.jQuery("a.qtc_editTab").show();
	}

	function qtc_checkoutMethod(obj)
	{
		var regType=techjoomla.jQuery(obj).val();
		if (regType)
		{
			techjoomla.jQuery.ajax({
			url: '?option=com_quick2cart&task=cartcheckout.setCheckoutMethod&tmpl=component&regType='+regType,
			type: 'POST',
			dataType: 'json',
			success: function(shipprice)
			{

			}
			});
		}
	}


	techjoomla.jQuery(document).ready(function(){

	techjoomla.jQuery(".checkout-content").hide();
	var current_state="<?php echo $qtc_tab_state;?>";
	// if NOT null, undefine NaN,"",0,false
	if (current_state) {
	// on refresh go to current tab
		switch (current_state)
		{
			case 'qtc_cart':
					techjoomla.jQuery("#qtc_cartInfo-tab").show();
					// ADDING EDIT LINK TO LOGIN TAB
					addEditLink(techjoomla.jQuery('#user-info .checkout-heading'));
			break;
		}
	}
	else
	{
		var userid=techjoomla.jQuery('#userid').val();
		// vm: commented bz we r going to hide registration tab
		var hideRegTab="<?php echo $qtc_hideregistrationTabFlag;?>";
		if (parseInt(userid)==0 && hideRegTab==0){
			//LOGGED IN
		techjoomla.jQuery(".checkout-first-step-user-info").show();
		}
		else{
			techjoomla.jQuery(".checkout-first-step-cart-info").show();
		}
	}
});  //END OF DOC READY


function qtc_hideshowTabs(obj)
{
	// HIDE ALL
	techjoomla.jQuery('.checkout-content').slideUp('slow');
	// vm:scroll to tab
	var parentid=techjoomla.jQuery(obj).parent().parent().attr('id');
	goToByScroll(parentid);

	/*var order_id=techjoomla.jQuery('#order_id').val();
	if (order_id)
	{	// add edit link 		// bill detail ,payment detail only
		addEditLink(techjoomla.jQuery('#bill-info .checkout-heading'));
		addEditLink(techjoomla.jQuery('#payment-info .checkout-heading'));
	}
	else
	{ // add edit link cart & bill detail
		addEditLink(techjoomla.jQuery('#qtc_cartInfo .checkout-heading'));
		addEditLink(techjoomla.jQuery('#bill-info .checkout-heading'));
	}*/

	// HIDE CURRENT OBJ
	//techjoomla.jQuery(obj).hide();
	//techjoomla.jQuery('#payment_tab_table_html').html();
	techjoomla.jQuery(obj).parent().parent().find('.checkout-content').slideDown('slow');

	// hide ckout error msg
	techjoomla.jQuery('#qtcShowCkoutErrorMsg').hide();
}

function qtc_ckpg_login(objid)
{
	var d= techjoomla.jQuery('#qtc_user-info #login :input');

	techjoomla.jQuery.ajax({
		url: qtc_baseurl+'?option=com_quick2cart&task=registration.login_validate&tmpl=component',
		type: 'post',
		data: techjoomla.jQuery('#qtc_user-info #login :input'),
		dataType: 'json',
		beforeSend: function() {
			techjoomla.jQuery('#button-login').attr('disabled', true);
			techjoomla.jQuery('#button-login').after('<span class="wait">&nbsp;Loading..</span>');
		},
		complete: function() {
			techjoomla.jQuery('#button-login').attr('disabled', false);
			techjoomla.jQuery('.wait').remove();
		},
		success: function(json) {
				window.location.reload();
		},
		error: function(xhr, ajaxOptions, thrownError) {
		}
	});
}

//function for validation of overview
function open_div(geo,camp)
{
	btnWizardNext();

}//function open_div() ends here

/*+manoj - start*/
/*
techjoomla.jQuery(document).ready(function()
{
	tjWindowWidth = techjoomla.jQuery('div.sa_steps_parent').width();

	if ((tjWindowWidth/2) < 350 )
	{
		changeFormClass('form-vertical', 1);
	}
	else
	{
		changeFormClass('form-horizontal', 1);
	}
});


techjoomla.jQuery(window).resize(function()
{
	tjWindowWidth = techjoomla.jQuery('div.sa_steps_parent').width();

	if ((tjWindowWidth/2) < 350 )
	{
		changeFormClass('form-vertical', 1);
	}
	else
	{
		changeFormClass('form-horizontal', 1);
	}
});
*/

function changeFormClass(newClass, multiplier)
{
	//tjWindowWidth = multiplier * techjoomla.jQuery('div.sa_steps_parent').width();
	tjWindowWidth = multiplier * techjoomla.jQuery('#q2c_billing').width();

	//~ if ((tjWindowWidth/2) < 350 )
	//~ {
		if (newClass=='form-vertical')
		{
			techjoomla.jQuery('div#qtc_mainwrapper').removeClass('form-horizontal');
			techjoomla.jQuery('div#qtc_mainwrapper').addClass('form-vertical');
		}
	//~ }
	//~ else
	//~ {
		if (newClass=='form-horizontal')
		{
			techjoomla.jQuery('div#qtc_mainwrapper').removeClass('form-vertical');
			techjoomla.jQuery('div#qtc_mainwrapper').addClass('form-horizontal');
		}
	//~ }
}

function changeWidthClass(newClass)
{
	if (newClass=='col-md-12')
	{
		techjoomla.jQuery('div#q2c_billing').removeClass('col-md-6');
		techjoomla.jQuery('div#q2c_billing').addClass('col-md-12');
	}

	if (newClass=='col-md-6')
	{
		techjoomla.jQuery('div#q2c_billing').removeClass('col-md-12');
		techjoomla.jQuery('div#q2c_billing').addClass('col-md-6');
	}
}
/*+manoj - end*/
</script>



<?php
$helperobj =new comquick2cartHelper;
$comquick2cartHelper = new comquick2cartHelper;
?>

<?php
if (!$user->id)
{
	global $mainframe;
	$mainframe = JFactory::getApplication();
	$ssession = JFactory::getSession();
	$jinput=JFactory::getApplication()->input;
	$itemid = $jinput->get('Itemid');
	$cart = $ssession->get('cart'.$user->id);
	$ssession->set('cart_temp',$cart);
	//$cart1 = $ssession->get('cart'.$user->id);
	$ssession->set('socialadsbackurl', $_SERVER["REQUEST_URI"]);
}

$document->addScriptDeclaration($js);
   ?>
<script type="text/javascript">
techjoomla.jQuery(document).ready(function() {
	var width = techjoomla.jQuery(window).width();
	var height = techjoomla.jQuery(window).height();
	techjoomla.jQuery('a#modal_billform').attr('rel','{handler: "iframe", size: {x: '+(width-(width*0.30))+', y: '+(height-(height*0.12))+'}}');
});

</script>
	<!--row-->
	<div class="">

		<!-- main div starts here-->
		<div class="ad-form">
			<!--fuelux wizard-example -->
			<div class="fuelux wizard-example">

			<div class="sa_steps_parent ">
				<!--wizard-->
				<div id="MyWizard" class="wizard">

					<!--steps nav-->
					<?php $s=1; ?>

					<ol class="qtc-steps-ol  steps clearfix col-md-12" id="qtc-steps">
					<!--<ul id="qtc-steps" class="steps nav">-->

						<li id="qtc_cartDetails" data-target="#step1" class="active">
							<span class="badge"><?php echo $s++; ?></span>
							<span class="hidden-xm hidden-sm"><?php echo JText::_('COM_QUICK2CART_CART_INFO'); ?></span>
							<span class="chevron"></span>
						</li>

						<li id="qtc_billing" data-target="#step2" >
							<span class="badge"><?php echo $s++; ?></span>
							<span class="hidden-xm hidden-sm"><?php echo JText::_('COM_QUICK2CART_BILLING_INFO'); ?></span>
							<span class="chevron"></span>
						</li>

						<?php
						if ($isShippingEnabled && $shippingMode != "orderLeval")
						{
							?>
						<li id="qtc_shippingStep" data-target="#step3" >
							<span class="badge"><?php echo $s++; ?></span>
							<span class="hidden-xm hidden-sm"><?php echo JText::_('COM_QUICK2CART_SHIP_INFO'); ?></span>
							<span class="chevron"></span>
						</li>
						<?php
						} ?>
						<li id="qtc_summaryAndPay" data-target="#step4">
							<span class="badge"><?php echo $s++; ?></span>
							<span class="hidden-xm hidden-sm"><?php echo JText::_('COM_QUICK2CART_PAYMENT_INFO')?></span>
							<span class="chevron"></span>
						</li>

					</ol>
					<!--steps nav-->
				</div>
				<!--wizard-->
			</div>
				<!--tab-content step-content-->
				<div id="TabConetent" class="tab-content step-content">

				<form method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class=" form-validate" onsubmit="return validateForm();">
					<!--step1-->
					<div class="tab-pane step-pane active" id="step1">
						<div id="qtc_step1_cartdetail">
						<?php
						$comquick2cartHelper = new comquick2cartHelper;
						$cartpath = $comquick2cartHelper->getViewpath('cartcheckout','default_cartdetail');

						$currentBSViews = $params->get("currentBSViews","bs2","STRING");
						$data = new stdclass;
						$data->cart = $this->cart;
						$data->coupon = $this->coupon;
						$data->showoptioncol = $showoptioncol;
						$data->promotions = !empty($this->promotions) ? $this->promotions : array();

						$layoutName = "cartcheckout." . $currentBSViews . ".cart_checkout";
						$layout = new JLayoutFile($layoutName);
						$response = $layout->render($data);
						echo $response;
						?>
						</div>
						<div  id="qtc_cartStepAlert"></div>
					</div>

					<input type="hidden" name="order_id" id="order_id" value="" /> <!--Used for order update -->

					<!--step2-->
					<div class="tab-pane step-pane" id="step2">
						<div class="qtcAddBorder">
						<?php
						// show user info
						$html="";
						$comquick2cartHelper = new comquick2cartHelper;

						// show user info
						$html="";
						$comquick2cartHelper = new comquick2cartHelper;
						ob_start();
						$path=$comquick2cartHelper->getViewpath('registration','default');
							include_once($path);
							$html = ob_get_contents();
						ob_end_clean();
						echo $html;

						// If user is not logged-in then dont show addresses
						if (!empty($user->id))
						{
							?>
							<div class="row">
									<?php
									if (!empty($this->addressesListHtml))
									{
									?>
									<div class="col-xs-12">
										<h3 class="checkout-addresses"><?php echo JText::_("COM_QUICK2CART_CREATE_ORDER_SELECT_ADDRESS");?></h3>
									</div>
									<?php
									}
									?>
									<div id="qtc_user_addresses" class="checkout-addresses col-xs-12">
										<div class="row qtc_user_addresses_wrapper">
											<?php echo $this->addressesListHtml;?>
										</div>
									</div>
								</div>
							<div class="clearfix">&nbsp;</div>
							<div class="row">
								<div class="container-fluid">
									<div class="qtcadd_address_button center">
										<a class="btn btn-success" onclick="addAddress('<?php echo JUri::root();?>')"><?php echo JText::_('COM_QUICK2CART_ADD_CUSTOMER_ADDRESS');?></a>
									</div>
								</div>
							</div>
							<?php
							if ($showTersmAndCond)
							{
								$Itemid = $helperobj->getitemid('index.php?option=com_content&view=article');
								$terms_link = JUri::root().substr(JRoute::_('index.php?option=com_content&view=article&id='.$termsCondArtId."&Itemid=".$Itemid."&tmpl=component"),strlen(JUri::base(true))+1);
							?>
								<div class="checkbox checkout-addresses">
									<label>
										<input class="qtc_checkbox_style" type="checkbox" name="qtc_accpt_terms" id="qtc_accpt_terms" aria-invalid="false"><?php  echo JText::_( 'COM_QUICK2CART_ACCEPT' ); ?>

										<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" href="<?php echo $terms_link;?>" class="modal qtc_modal" title="<?php echo JText::_( 'COM_QUICK2CART_TERMS_CONDITION' ); ?>">
												<?php  echo JText::_( 'COM_QUICK2CART_TERMS_CONDITION' ); ?>
										</a>
									</label>
								</div>
								<?php
							}
						}
						else
						{
							// billing and shipping info
							$comquick2cartHelper = new comquick2cartHelper;
							$billpath = $comquick2cartHelper->getViewpath('cartcheckout','default_billing');
							?>
							<div class="clearfix">&nbsp;</div>
							<div class="row">
								<div class="container-fluid checkout-addresses">

									<?php
									ob_start();
										include($billpath);
										$html = ob_get_contents();
									ob_end_clean();

									echo $html;

									?>
								</div>
							</div>
							<?php
						}
						?>
						</div>
					</div>
					<!--step2-->

					<?php
					if ($isShippingEnabled)
					{
						?>
					<!--step3-->
					<div class="tab-pane step-pane" id="step3">
						<!-- bill msg -->
						<div class="row ">
						</div>
						<!-- Shipping method will goes here -->
						<div id="qtcProdShippingMethos">
						</div>

						<div  id="qtc_shipStepAlert">

						</div>

					</div>
					<!--step3-->
					<?php
					}
						?>
						<input type="hidden" name="task" id="task" value="cartcheckout.qtc_autoSave" />
				</form>


					<!--step4-->
					<div class="tab-pane step-pane" id="step4">
						<!-- bill msg -->
						<div class="">
							<div class="alert alert-warning">
								<?php echo JText::_('COM_QUICK2CART_REVIE_AND_PAY_HEML') ?>
							</div>
						</div>

						<div id="qtc_reviewAndPayHTML"></div>
					</div>
					<!--step3-->
				</div>
				<!--tab-content step-content-->

				<!--pull-right-->
				<?php $this->target_div=0;?>
				<br/>
				<div class="prev_next_wizard_actions">
					<div class="">
						<button id="btnWizardPrev" type="button" style="display:none" class="btn btn-default pull-left" > <i class="<?php echo Q2C_ICON_ARROW_CHEVRON_LEFT; ?> <?php echo Q2C_ICON_WHITECOLOR; ?>" ></i>&nbsp;<?php echo JText::_('COM_QUICK2CART_PREV');?></button>
						<button id="btnWizardNext" type="button" class="btn btn-primary pull-right" data-last="Finish" onclick="return open_div(<?php echo ($this->target_div)?'1' : '0'; ?>);">
							<span><?php echo JText::_("COM_QUICK2CART_BTN_SAVEANDNEXT");?></span>
							<i class=" <?php echo Q2C_ICON_ARROW_CHEVRON_RIGH; ?> <?php echo Q2C_ICON_WHITECOLOR; ?>"></i>
						</button>
					</div>
				</div>

				<div id="qtc_StepLoading" style="display:none;">

					<div class="com_quick2cart_ajax_loading" >
						<div class="com_quick2cart_ajax_loading_text"></div>
						<img class="com_quick2cart_ajax_loading_img" src="<?php echo $root_url?>components/com_quick2cart/assets/images/ajax.gif">
					</div>
				</div>

				<!--pull-right-->

			</div>
			<!--fuelux wizard-example -->
				<!--/div-->
				<div style="clear:both;"></div>
		</div>
		<!-- main div starts here-->
	</div>
</div>

<script type="text/javascript">
	var root_url="<?php echo $root_url; ?>";
	var valid_msg="<?php echo JText::_('COM_SA_FLOAT_VALUE_NOT_ALLOWED'); ?>";
	var savennextbtn_text="<?php echo JText::_("COM_QUICK2CART_BTN_SAVEANDNEXT");?>";
	var savenexitbtn_text="<?php echo JText::_("COM_QUICK2CART_BTN_SAVEANDEXIT");?>";

</script>

