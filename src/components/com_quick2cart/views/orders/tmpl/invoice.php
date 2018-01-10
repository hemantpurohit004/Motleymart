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
$user = JFactory::getUser();
$input= JFactory::getApplication()->input;
$this->comquick2cartHelper = new comquick2cartHelper;
$this->params = JComponentHelper::getParams('com_quick2cart');
$jinput = JFactory::getApplication()->input;
$order_id = $jinput->get('orderid');
$jinput->set('orderid', $order_id);
$store_id = $jinput->get('store_id');
$guest_email   = $jinput->get('email', '', 'RAW');
$order = $this->orders;

//$order = $order_bk = $this->comquick2cartHelper->getorderinfo($order_id, $store_id);
$this->orderinfo = $order['order_info'];
$this->orderitems = $order['items'];
$this->orders_site = 1;
$this->orders_email = 1;
$this->order_authorized = 1;

// Invoice is always store related
$this->storeReleatedView = 1;
$adminCall   = $jinput->get('adminCall', '', 'INTEGER');

if (empty($adminCall))
{
	// Guest checkout and and called from 1 pg ckout
	if ($guest_email)
	{
		$guest_email_chk = 0;
		$guest_email_chk = $this->comquick2cartHelper->checkmailhash($this->orderinfo[0]->id, $guest_email);

		if (!$guest_email_chk && empty($this->qtcSystemEmails))
		{
	?>
			<div class="<?php echo Q2C_WRAPPER_CLASS;?>">
				<div class="well">
					<div class="alert alert-danger">
						<span>
							<?php echo JText::_('QTC_GUEST_MAIL_UNMATCH');?>
						</span>
					</div>
				</div>
			</div>
			<?php
			return false;
		}
	}
	elseif (!$user->id)
	{
	?>
		<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
				<div class="well">
					<div class="alert alert-danger">
					<span><?php echo JText::_('QTC_LOGIN');?> </span>
				</div>
			</div>
		</div>
		<?php
		return false;
	}
}

if (empty($store_id))
{
	?>
	<div class="<?php echo Q2C_WRAPPER_CLASS;?> q2c-orders">
		<div class="well" >
			<div class="alert alert-danger">
				<span><?php echo JText::_('COM_QUICK2CART_INVOICE_MISSING_STORE_ID'); ?></span>
			</div>
		</div>
	</div>

	<?php
	return false;
}

$billemail = "";

if (!empty($this->orderinfo[0]->address_type) && $this->orderinfo[0]->address_type == 'BT')
{
	$billemail = $this->orderinfo[0]->user_email;
}
elseif (!empty($this->orderinfo[1]->address_type) && $this->orderinfo[1]->address_type == 'BT')
{
	$billemail = $this->orderinfo[1]->user_email;
}

$fullorder_id = $order['order_info'][0]->prefix . $order_id;
$this->qtcSystemEmails = 1;

if (!JFactory::getUser()->id && $this->params->get('guest'))
{
	$jinput->set('email', md5($billemail));
}
?>

	<?php
	/* if user is on payment layout and log out at that time undefined order is is found
	in such condition send to home page or provide error msg
	*/
	if(isset($this->orders_site) && isset($this->undefined_orderid_msg) )
	{
			return false;
	}

	$user=JFactory::getUser();
	$jinput=JFactory::getApplication()->input;
	$guest_email = $jinput->get('email','','STRING');

	if($guest_email)
	{
		$guest_email_chk =0;
		$guest_email_chk = $this->comquick2cartHelper->checkmailhash($this->orderinfo[0]->id,$guest_email);
		if(!$guest_email_chk )
		{
			?>

			<div class="well" >
				<div class="alert alert-danger">
					<span ><?php echo JText::_('QTC_GUEST_MAIL_UNMATCH'); ?> </span>
				</div>
			</div>

		<?php
			return false;
		}
	}
	else if(!$user->id && !$this->params->get( 'guest' ))
	{ ?>

		<div class="well" >
			<div class="alert alert-danger">
				<span ><?php echo JText::_('QTC_LOGIN'); ?> </span>
			</div>
		</div>

	<!--Q2C_WRAPPER_CLASS -->
	<?php
		return false;
	}
	?>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?>" style="font-family: Helvetica; border-width: 1px 1px 1px 1px; border-style: solid; border-color: #DDD; border-collapse: separate;padding:5px;">
	<?php
	// 1 check : for "MY ORDERS"=check for authorized user or not ( it should be site,authorized to view order and not store releated view)
	if(isset($this->orders_site) && empty($this->order_authorized) )
	{
		$authorized=0;
		//2 check : "FOR STORE ORDER " order should be releated to store
		if( !empty($this->storeReleatedView))  // if vendor releated view is present then current order should be releated to store
		{
			//3. store releated view but not logged in then (directly accessed known url at that time it require )
			if(empty($user->id))
			{
					?>
					<div class="well" >
						<div class="alert alert-danger">
							<span><?php echo JText::_('QTC_LOGIN'); ?> </span>
						</div>
					</div>
				<?php
			return false;
			}

			$result=$this->comquick2cartHelper->getStoreOrdereAuthorization($this->store_id,$this->orderid);
			$authorized=(!empty($result))?1:0;
		}

		if($authorized==0)
		{
			?>
				<div class="well" >
					<div class="alert alert-danger">
						<span><?php echo JText::_('QTC_NOT_AUTHORIZED_USER_TO_VIEW_ORDER'); ?> </span>
					</div>
				</div>
			<?php
				return false;
		}// end of if($authorized==0)
	}

	$coupon_code=$this->orderinfo[0]->coupon_code ;

	if (!empty($this->orderinfo[0]->address_type) && $this->orderinfo[0]->address_type == 'BT')
	{
		$billinfo = $this->orderinfo[0];
	}
	elseif (!empty($this->orderinfo[1]->address_type) && $this->orderinfo[1]->address_type == 'BT')
	{
		$billinfo = $this->orderinfo[1];
	}

	if( $this->params->get( 'shipping' ) == '1' )
	{
		if($this->orderinfo[0]->address_type == 'ST')
			$shipinfo = $this->orderinfo[0];
		else if(isset($this->orderinfo[1]))
						if($this->orderinfo[1]->address_type == 'ST')
								$shipinfo = $this->orderinfo[1];
	}

	$this->orderinfo = $this->orderinfo[0];
	// 1 for site 0 for admin
	$orders_site       = (isset($this->orders_site)) ? $this->orders_site : 0;
	$orders_email      = (isset($this->orders_email)) ? $this->orders_email : 0;
	$emailstyle        = "style='background-color: #cccccc;  padding: 7px;'";
	$vendor_order_view = (!empty($this->store_id)) ? 1 : 0;
	$order_currency    = $this->orderinfo->currency;

	$order_currency = $this->orderinfo->currency;
	//$order_currency = ($this->orderinfo->currency)?$this->orderinfo->currency :$or_currency;

	if(isset($this->order_blocks))
	{
		$order_blocks = $this->order_blocks;
	}
	else
	{
		$order_blocks  = array ('0'=>'shipping','1'=>'billing','2'=>'cart','3'=>'order','4'=>'order_status');
	}

	$document = JFactory::getDocument();
?>


	<script src="<?php echo JUri::root().'components/com_quick2cart/assets/js/bootstrap-tooltip.js'?>"></script>
	<script src="<?php echo JUri::root().'components/com_quick2cart/assets/js/bootstrap-popover.js'?>"></script>

	<script type="text/javascript">
		techjoomla.jQuery(document).ready(function()
		{
			techjoomla.jQuery('.discount').popover(
			);

		});

		function	qtc_showpaymentgetways()
		{
			document.getElementById("qtc_paymentmethods").style.display='block';
		}
	</script>


<div style="padding:20px;">
	<table  style="  width: 100%; " >
		<thead>
			<tr style="vertical-align:middle;border:0;">
				<td style="vertical-align:middle;border:0;">
					<h2><?php echo JText::_('QTC_INVOICE_VIEW_HEAD');?></h2>
				</td>
				<td style="vertical-align:middle; text-align:right;border:0;">
					<h4 style="margin:0; padding:0;"><span><strong><?php echo JText::_('QTC_INVOICE_DATE');?>:</strong></span>
					<?php echo JFactory::getDate($this->orderinfo->cdate)->Format(JText::_("COM_QUICK2CART_DATE_FORMAT_SHOW_SHORT"));?></h4>
				</td>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td  style="background-color: #ccc;  padding: 7px;">
					<h4 style="margin:0; padding:0;"><?php echo JText::_('COM_QUICK2CART_INVOICE_SOLD_BY_LBL');?></h4>
				</td>
				<td  style="background-color: #ccc;  padding: 7px;">
					<h4 style="margin:0; padding:0;"><?php echo JText::_('QTC_INVOICE_DETAIL') ?></h4>
				</td>
			</tr>

			<tr>
				<td  style="border:1px solid #dddddd;  padding: 5px 10px;" class="tdaddress">
					<?php $storeinfo = $this->comquick2cartHelper->getSoreInfoInDetail($jinput->get('store_id')); ?>
						<h4 style="margin:0; padding:0;"><?php echo $storeinfo['title']; ?></h4>
						<address class="shop-address"><?php echo $storeinfo['address']; ?></address>
						<p>
							<strong><?php echo JText::_('COM_QUICK2CART_INVOICE_SHOP_EMAIL_LBL'); ?> </strong>
							<a href="mailto:<?php echo $storeinfo['store_email']; ?>" title="<?php echo $storeinfo['title']; ?>">
								<?php echo $storeinfo['store_email']; ?>
							</a>
						</p>
						<?php
						if (!empty($storeinfo['phone']))
						{
						?>
						<p>
							<strong><?php echo JText::_('COM_QUICK2CART_INVOICE_SHOP_PHONE_LBL'); ?></strong>
							<span><?php echo $storeinfo['phone']; ?></span>
						</p>
						<?php
						}
						?>
				</td>
				<td  style="border:1px solid #dddddd;  padding: 5px 10px;" class="tdaddress">
					<p>
						<strong><?php echo JText::_('QTC_INVOICE_ID'); ?></strong>
						<span>
							<?php echo $this->orderinfo->prefix . $this->orderinfo->id . '-' . $jinput->get('store_id'); ?>
						</span>
					</p>
					<p>
						<strong><?php echo JText::_('QTC_INVOICE_DATE');?></strong>
						<span>
							<?php echo JFactory::getDate($this->orderinfo->cdate)->Format(JText::_("COM_QUICK2CART_DATE_FORMAT_SHOW_SHORT")); ?>
						</span>
					</p>
					<p>
						<strong><?php echo JText::_('QTC_INVOICE_USER');?></strong>
						<span>
							<?php
								$table   = JUser::getTable();
								$user_id = intval( $this->orderinfo->payee_id );
								if ($user_id)
								{
									$creaternm = '';
									if ($table->load( $user_id ))
									{
										$creaternm = JFactory::getUser($this->orderinfo->payee_id);
									}
									echo (!$creaternm) ? JText::_('QTC_NO_USER'): $creaternm->username;
								}
								else
								{
									echo $billinfo->user_email;
								}
							?>
						</span>
					</p>
					<p>
						<strong><?php echo JText::_('QTC_INVOICE_PAID_MSG'); ?></strong>
						<span>
							<?php
								if ($this->orderinfo->status === "C")
								{
									echo "<span style='color:  #73AD21;'><b>".JText::_('QTC_INVOICE_PAID')."</b></span>";
								}
								elseif ($this->orderinfo->status === "P")
								{
									echo "<span style='color: red;'><b>".JText::_('COM_QUICK2CART_INVOICE_PENDING')."</b></span>";
								}
							?>
						</span>
					</p>
					<?php if($this->orderinfo->processor) { ?>
					<p>
						<strong><?php echo JText::_('QTC_INVOICE_PAYMENT');?></strong>:
						<span><?php echo $this->orderinfo->processor; ?></span>
					</p>
					<?php
					}
					?>

					<?php if($this->orderinfo->transaction_id) { ?>
					<p>
						<strong><?php echo JText::_('QTC_INVOICE_PAYMENT_TRANSAC');?></strong>:
						<span><?php echo $this->orderinfo->transaction_id; ?></span>
					</p>
					<?php
					}
					?>
					<?php if(!empty($billinfo->vat_number)) { ?>
					<p>
						<strong><?php echo JText::_('QTC_BILLIN_VAT_NUM');?></strong>:
						<span><?php echo $billinfo->vat_number ?></span>
					</p>
					<?php
					}
					?>
				</td>
			</tr>
		</tbody>
	</table>
	<div style="clear:both;"></div>

	<?php
	$price_col_style = "style=\"".(!empty($orders_email)? 'text-align: right;' :'')."\"";
	$showoptioncol = 0;

	foreach($this->orderitems as $citem)
	{
		if(!empty($citem->product_attribute_names))
		{
			// Found attributes for atleast one product
			$showoptioncol=1;
			break;
		}
	}

		// Added by vijay
		if (empty($multivendor_enable))
		{
			$storeinfo     = $this->comquick2cartHelper->getSoreInfoInDetail($this->orderitems[0]->store_id);
		}
		?>
		<!-- this row will not appear when printing -->
		<div class="">
			<?php
			if (isset($this->email_table_bordered))
			{
				$this->email_table_bordered .= ";width:100%;";
			}
			else
			{
				$this->email_table_bordered = ";width:100%;";
			}
				// Display basic order detail.
			$view                = $this->comquick2cartHelper->getViewpath('orders', 'default_billing');
			ob_start();
				include($view);
				$html = ob_get_contents();
			ob_end_clean();
			echo $html;
			?>
		</div>
		<!-- Added by vijay ends here -->
		<!-- Table row -->
		<div class="">
			<div class="" style="width: 100%;">
				<!-- Display cart detail -->
				<?php
				$view = $this->comquick2cartHelper->getViewpath('orders', 'default_cartdetail');
				ob_start();
				include($view);
				$html = ob_get_contents();
				ob_end_clean();
				echo $html;	?>

			</div><!-- /.col -->
		</div><!-- /.row -->

		<?php
		$mainSiteAdress = $this->params->get('mainSiteAdress');
		$vat_num = $this->params->get('vat_num');

		if ($mainSiteAdress || $vat_num)
		{
		?>
		<div style="clear:both;">&nbsp;</div>
		<div class="row qtcPadding">
			<div class="" style=" color: gray;">
			<!--
			@dj Site invoice detail and store invoice detail are different. Here we should display site detail.
			-->
				 <div><b><i><?php echo JText::_('QTC_INVOICE_CONT_INFO'); ?></i></b></div>

				<?php
				if(!empty($mainSiteAdress))
				{
				?>
					<div><b><?php echo JText::_('COM_QUICK2CART_INV_STIE_ADDRESS');?></b> :
					<?php echo $mainSiteAdress;
					?>
					</div>
					<?php
				}

				if(!empty($vat_num))
				{
				?>
					<div><b><?php echo JText::_('QTC_INVOICE_VAT');?></b> :
					<?php echo $vat_num;
					?>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php
		}
	?>
		<div style="clear:both;">&nbsp;</div>

</div>
<!--
<style>
@media print {

*{display:block; position:static; float:n one;}
script,style{display:none;}
}

</style>
-->

