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
?>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?>" >

<?php
		$input= JFactory::getApplication()->input;
		$layout = $input->get('layout');
		if($layout=="customerdetails")
		{
	?>
		<?php
$active = 'customerdetails';
$comquick2cartHelper = new comquick2cartHelper;
$view=$comquick2cartHelper->getViewpath('vendor','toolbar');
ob_start();
	include($view);
	$html = ob_get_contents();
ob_end_clean();
echo $html;
?>
		<legend><?php echo JText::_('QTC_STORE_CUSTOMER_DETAIL')?>
		</legend>

	<?php
		}
	 ?>

<?php
/* if user is on payment layout and log out at that time undefined order is is found
in such condition send to home page or provide error msg
*/

// Dont allow to display is user in not autorize
$helperobj=new comquick2cartHelper;
$result=$helperobj->getStoreOrdereAuthorization($this->store_id,$this->orderid);
$result=(!empty($result))?1:0;
if(empty($this->store_authorize)  || $result==0)
{
?>
<div class="well" >
	<div class="alert alert-danger">
		<span ><?php echo JText::_('QTC_NOT_AUTHORIZED_USER'); ?> </span>
	</div>
</div>
</div>
<?php
		return false;
}
if(isset($this->orders_site) && isset($this->undefined_orderid_msg) )
{
		return false;
}
$params = JComponentHelper::getParams('com_quick2cart');

$user=JFactory::getUser();
$jinput=JFactory::getApplication()->input;
$guest_email = $jinput->get('email','','STRING');

if($guest_email){
$guest_email_chk =0;
	$guest_email_chk = $helperobj->checkmailhash($this->orderinfo[0]->id,$guest_email);
	if(!$guest_email_chk ){
?>
<div class="well" >
	<div class="alert alert-danger">
		<span ><?php echo JText::_('QTC_GUEST_MAIL_UNMATCH'); ?> </span>
	</div>
</div>
</div>
<?php
		return false;
	}
}
else if(!$user->id && !$params->get( 'guest' )){
?>
<div class="well" >
	<div class="alert alert-danger">
		<span ><?php echo JText::_('QTC_LOGIN'); ?> </span>
	</div>
</div>
</div>
<?php
	return false;
}
	//print"<pre>";print_r($this->orderinfo[0] );  die("V Order");
$coupon_code=$this->orderinfo[0]->coupon_code ;

if($this->orderinfo[0]->address_type == 'BT')
	$billinfo = $this->orderinfo[0];
else if($this->orderinfo[1]->address_type == 'BT')
	$billinfo = $this->orderinfo[1];

if( $params->get( 'shipping' ) == '1' ){
if($this->orderinfo[0]->address_type == 'ST')
	$shipinfo = $this->orderinfo[0];
else if(isset($this->orderinfo[1]))
				if($this->orderinfo[1]->address_type == 'ST')
						$shipinfo = $this->orderinfo[1];
}
$this->orderinfo = $this->orderinfo[0];
$orders_site=( isset($this->orders_site) )?$this->orders_site:0;  // 1 for site 0 for admin
$orders_email=( isset($this->orders_email) )?$this->orders_email:0;
$emailstyle="style='background-color: #cccccc'";
$vendor_order_view=(!empty($this->store_id))?1:0;


$order_currency = $this->orderinfo->currency;
//$order_currency = ($this->orderinfo->currency)?$this->orderinfo->currency :$or_currency;

if(isset($this->order_blocks)){
$order_blocks = $this->order_blocks;
}
else{
$order_blocks  = array ('0'=>'shipping','1'=>'billing');//,'2'=>'cart','3'=>'order','4'=>'order_status');
}

$document = JFactory::getDocument();
//$document->addScript(JUri::root().'components/com_quick2cart/assets/js/order.js');

//$document->addStyleSheet(JUri::base().'components/com_quick2cart/assets/css/quick2cart.css' );//aniket

?>
<script type="text/javascript">
	function	qtc_showpaymentgetways()
	{
//		jQuery("#qtc_paymentmethods");
		document.getElementById("qtc_paymentmethods").style.display='block';
	}
</script>
<div>


<div id="qtc_wholeCustInfoDiv">

<?php if (in_array('billing', $order_blocks)) { ?>
<?php if($orders_email) { ?>
<div id="qtc_custinfo"><h4 <?php echo $emailstyle;?> ><?php echo JText::_('QTC_CUST_INFO'); ?></h4> </div>

<div class="well " style="float:left" >
<?php } ?>

<?php if($orders_site) { ?>
<div class="well qtc_ordersite" >
	<h4><?php echo JText::_('QTC_BILLIN_INFO'); ?></h4>
<?php }
else{ ?>
<fieldset class="qtc_ordersite" >
<legend><?php echo JText::_('QTC_BILLIN_INFO'); ?></legend>
<?php } ?>
	<table class="table table-condensed  " >
		<tr>
			<td><?php echo JText::_('QTC_BILLIN_FNAM');?></td>
			<td><?php echo $billinfo->firstname;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_BILLIN_LNAM');?></td>
			<td><?php echo $billinfo->lastname;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_BILLIN_ADDR');?></td>
			<td><?php echo $billinfo->address;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_BILLIN_ZIP');?></td>
			<td><?php echo $billinfo->zipcode;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_BILLIN_COUNTRY');?></td>
			<td><?php echo $comquick2cartHelper->getCountryName($billinfo->country_code);?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_BILLIN_STATE');?></td>
			<td><?php echo $comquick2cartHelper->getStateName($billinfo->state_code);?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_BILLIN_CITY');?></td>
			<td><?php echo $billinfo->city;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_BILLIN_PHON');?></td>
			<td><?php echo $billinfo->phone;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_BILLIN_EMAIL');?></td>
			<td><?php echo $billinfo->user_email;?></td>
		</tr>
	</table>
<?php if($orders_email) { ?>
</div>
<?php } ?>
<?php if($orders_site) { ?>
</div>
<?php }
else{ ?>
</fieldset>
<?php } ?>
<?php } ?>

<?php if( $params->get( 'shipping' ) == '1' && isset($shipinfo) ){ ?>
<?php if (in_array('shipping', $order_blocks)) { ?>

<?php if($orders_email) { ?>
<div style="float:left">
<?php } ?>
<?php if($orders_site) { ?>
<div class="well qtc_orderstateinfo" >
	<h4><?php echo JText::_('QTC_SHIPIN_INFO'); ?></h4>
<?php }
else{ ?>
<fieldset class="qtc_orderstateinfo" >
<legend><?php echo JText::_('QTC_SHIPIN_INFO'); ?></legend>
<?php } ?>
	<table class="table table-condensed  " >
		<tr>
			<td><?php echo JText::_('QTC_SHIPIN_FNAM');?></td>
			<td><?php echo $shipinfo->firstname;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_SHIPIN_LNAM');?></td>
			<td><?php echo $shipinfo->lastname;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_SHIPIN_ADDR');?></td>
			<td><?php echo $shipinfo->address;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_SHIPIN_ZIP');?></td>
			<td><?php echo $shipinfo->zipcode;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_SHIPIN_COUNTRY');?></td>
			<td><?php echo $comquick2cartHelper->getCountryName($shipinfo->country_code);?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_SHIPIN_STATE');?></td>
			<td><?php echo $comquick2cartHelper->getStateName($shipinfo->state_code);?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_SHIPIN_CITY');?></td>
			<td><?php echo $shipinfo->city;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_SHIPIN_PHON');?></td>
			<td><?php echo $shipinfo->phone;?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('QTC_SHIPIN_EMAIL');?></td>
			<td><?php echo $shipinfo->user_email;?></td>
		</tr>
	</table>

<?php if($orders_email) { ?>
</div>
<?php } ?>
<?php if($orders_site) { ?>
</div>
<?php }
else{ ?>
</fieldset>
<?php } ?>

<?php } ?>

<?php } /*chk for paramter shipping*/ ?>

</div>  <!-- customer info end  id=qtc_wholeCustInfoDiv-->
<div style="clear:both;"></div>

</div>

</div>

<?php
// CHECK for view override
		$comquick2cartHelper = new comquick2cartHelper;
		$view=$comquick2cartHelper->getViewpath('orders');
		ob_start();
			include($view);
			$html = ob_get_contents();
		ob_end_clean();
		echo $html;
?>
