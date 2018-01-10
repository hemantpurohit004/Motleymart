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

global $mainframe;
$mainframe = JFactory::getApplication();

$document = JFactory::getDocument();
//$document->addStyleSheet(JUri::base().'components/com_quick2cart/assets/css/quick2cart.css' );//aniket
$params = JComponentHelper::getParams('com_quick2cart');
$guestcheckout=$params->get('guest');
$registerFormStyle='';

?>
<?php
	$jinput=JFactory::getApplication()->input;
	$itemid = $jinput->get('Itemid');
	$rurl='index.php?option=com_quick2cart&view=cartcheckout&Itemid='.$itemid;
	$returnurl=base64_encode($rurl);
?>

	<!--Start User Details Tab-->
<?php

if (!$user->id)
{

	//1. IF GEUEST CHECKOUT IS ON THEN SET guest as default
	$registerMehod=1;

	if ($guestcheckout==1)
	{
		$registerMehod=0;
	}

	//2.consider page refresh ::set last selected option.
	$session =JFactory::getSession();
	$ckoutMethod=$session->get('one_pg_ckoutMethod');
	$checked='checked="checked"';

	// LAST SELECTED OPTION
	if ($ckoutMethod == 'guest')
	{
		$registerMehod = 0;
	}
	elseif ($ckoutMethod == 'register')
	{
		// session veriablt to register
		$registerMehod=1;
	}

	?>
	<?php
	$showBillShipTab = 0;

	if(!empty($qtc_hideregistrationTabFlag) && $qtc_hideregistrationTabFlag !='0')
	{
		$registorStyle = 'display:none;';
		$showBillShipTab = 1;
	}
	?>
	<div id="qtc_user-info" class="com_quick2cart-checkout-steps " style="<?php echo $registorStyle; ?>">
		<div class="checkout-heading">
			<span><?php echo JText::_('COM_QUICK2CART_USER_INFO');?></span>
			<span id="" class="qtcHandPointer btn btn-xs btn-primary pull-right" onclick="qtc_hideShowLoginTab('qtc_user-info-content', 'qtc_ckout_billing-info')"><?php echo JText::_('COM_QUICK2CART_MODIFY');?></span>
			<div class="clearfix"></div>
		</div>
		<!--<div class="checkout-content row checkout-first-step-user-info" id="user-info-tab">  -->
		<div id="qtc_user-info-content" class="container-fluid">
			<div class="row" >
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 paddleft">
					  <h3><?php echo JText::_('COM_QUICK2CART_CHECKOUT_NEW_CUSTOMER'); ?></h3>
					  <p><?php echo JText::_('COM_QUICK2CART_CHECKOUT_OPTIONS'); ?></p>
					<!-- registration -->
					  <?php if(1): ?>
					  <div class="radio">

							<label for="register" title="<?php echo JText::_('COM_QUICK2CART_CHECKOUT_REGISTER_DESC'); ?>">
								<input type="radio" name="qtc_guest_regis" value="register" id="register" <?php echo (!empty($registerMehod)? $checked: '');?> onchange="qtc_checkoutMethod(this)" />
								<b><?php echo JText::_('COM_QUICK2CART_CHECKOUT_REGISTER'); ?></b>
							</label>
						</div>
					  <?php endif; ?>

					  <!-- guest -->
					  <?php if ($guestcheckout==1) : ?>
					  <div class="radio">
					  <label for="guest">
						<input type="radio" name="qtc_guest_regis" value="guest" id="guest" <?php echo (empty($registerMehod)? $checked: '');?> onchange="qtc_checkoutMethod(this)" />
						<b><?php echo JText::_('COM_QUICK2CART_CHECKOUT_GUEST'); ?></b>
						</label>
						</div>
					  <?php endif; ?>
					  <br />
					  <?php //if($this->params->get('allow_buy_guestreg', 0)): ?>
					  <?php if(1): ?>
					  <p><?php echo JText::_('COM_QUICK2CART_CHECKOUT_REGISTER_ACCOUNT_HELP_TEXT'); ?></p>
					  <?php endif; ?>
				</div>
				<div id="login" class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
					  <h3><?php echo JText::_('COM_QUICK2CART_CHECKOUT_RETURNING_CUSTOMER'); ?></h3>
					  <p><?php echo JText::_('COM_QUICK2CART_CHECKOUT_RETURNING_CUSTOMER_WELCOME'); ?></p>
					  <b><?php echo JText::_('COM_QUICK2CART_CHECKOUT_USERNAME'); ?></b><br />
					  <input type="text" name="email" value="" />
					  <br />
					  <br />
					  <b><?php echo JText::_('COM_QUICK2CART_CHECKOUT_PASSWORD'); ?></b><br />
					  <input type="password" name="password" value="" />
					  <br />
					  <br />
					  <input type="button" value="<?php echo JText::_('COM_QUICK2CART_CHECKOUT_LOGIN'); ?>" id="button-login" class="button btn btn-default " onclick="qtc_ckpg_login(this)"/><br />
					  <br />
				</div>
			</div>
			<hr/>
			<div class="row">
				<div class="col-xs-12">
				<input type="button" class=" btn  btn-primary" id="button-user-info" value="<?php echo JText::_('COM_QUICK2CART_CONTINUE');?>" onclick="qtc_guestContinue('qtc_user-info-content')">
				</div>
				<div class="clearfix"></div>
				<br />
			</div>
		</div>


		<!--Added by Sneha, to display message to login on checkout-->
		<div class="form-group" id="qtc_loginmail_msg_div" style="display:none">
			<span class="help-inline qtc_removeBottomMargin" id="loginmail_msg"></span>
		</div>
	</div>
	<?php
}
?>
	<!--End User Details Tab-->
