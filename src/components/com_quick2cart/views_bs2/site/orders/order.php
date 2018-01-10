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
/*
 * If user is on payment layout and log out at that time undefined order is found in such condition send to home page or provide error msg
 */
if (isset($this->orders_site) && isset($this->undefined_orderid_msg))
{
	return false;
}

$params        = JComponentHelper::getParams('com_quick2cart');
$this->comquick2cartHelper = new comquick2cartHelper;
$this->productHelper = new productHelper();
$user          = JFactory::getUser();
$jinput        = JFactory::getApplication()->input;
$guest_email   = $jinput->get('email', '', 'STRING');
$myorderItemid = $this->comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=orders&layout=default');

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
				<div class="alert alert-error">
					<span>
						<?php echo JText::_('QTC_GUEST_MAIL_UNMATCH');?> </span>
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
				<div class="alert alert-error">
				<span><?php echo JText::_('QTC_LOGIN');?> </span>
			</div>
		</div>
	</div>
	<?php
	return false;
}

// 1 check : for "MY ORDERS"=check for authorized user or not ( it should be
// site,authorized to view order and not store related view)
if (isset($this->orders_site) && empty($this->order_authorized) && !$params->get('guest'))
{
	$authorized = 0;
	// 2 check : "FOR STORE ORDER " order should be releated to store
	// if vendor releated view is present then current order should be releated to store
	if (!empty($this->storeReleatedView))
	{
		// 3. store releated view but not logged in then (directly accessed
		// known url at that time it require )
		if (empty($user->id))
		{
?>
			<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
				<div class="well">
					<div class="alert alert-error">
						<span><?php echo JText::_('QTC_LOGIN'); ?> </span>
					</div>
				</div>
			</div>
			<?php
			return false;
		}

		$result     = $this->comquick2cartHelper->getStoreOrdereAuthorization($this->store_id, $this->orderid);
		$authorized = (!empty($result)) ? 1 : 0;
	}

	if ($authorized == 0)
	{
?>
		<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
			<div class="well">
				<div class="alert alert-error">
					<span><?php echo JText::_('QTC_NOT_AUTHORIZED_USER_TO_VIEW_ORDER'); ?> </span>
				</div>
			</div>
		</div>
		<?php
		return false;
	}
	// end of if($authorized==0)
}

$coupon_code = $this->orderinfo[0]->coupon_code;

if (!empty($this->orderinfo[0]->address_type) && $this->orderinfo[0]->address_type == 'BT')
{
	$billinfo = $this->orderinfo[0];
}
elseif (!empty($this->orderinfo[1]->address_type) && $this->orderinfo[1]->address_type == 'BT')
{
	$billinfo = $this->orderinfo[1];
}

if ($params->get('shipping') == '1')
{
	if (!empty($this->orderinfo[0]->address_type) && $this->orderinfo[0]->address_type == 'ST')
	{
		$shipinfo = $this->orderinfo[0];
	}
	elseif (isset($this->orderinfo[1]))
	{
		if (!empty($this->orderinfo[1]->address_type) && $this->orderinfo[1]->address_type == 'ST')
		{
			$shipinfo = $this->orderinfo[1];
		}
	}
}

$this->orderinfo   = $this->orderinfo[0];
// 1 for site 0 for admin
$orders_site       = (isset($this->orders_site)) ? $this->orders_site : 0;
$orders_email      = (isset($this->orders_email)) ? $this->orders_email : 0;
$emailstyle        = "style='background-color: #cccccc'";
$vendor_order_view = (!empty($this->store_id)) ? 1 : 0;
$order_currency    = $this->orderinfo->currency;

if (isset($this->order_blocks) && !empty($this->order_blocks))
{
	$order_blocks = $this->order_blocks;
}
else
{
	$order_blocks = array(
		'0' => 'shipping',
		'1' => 'billing',
		'2' => 'cart',
		'3' => 'order',
		'4' => 'order_status'
	);
}

$document = JFactory::getDocument();?>
<script src="<?php echo JUri::root() . 'components/com_quick2cart/assets/js/bootstrap-tooltip.js'; ?>"></script>
<script src="<?php echo JUri::root() . 'components/com_quick2cart/assets/js/bootstrap-popover.js'; ?>"></script>
<script type="text/javascript">
	techjoomla.jQuery(document).ready(function()
	{
		techjoomla.jQuery('.discount').popover();
	});

	function qtc_showpaymentgetways()
	{
		/*techjoomla.jQuery("#qtc_paymentmethods");*/
		document.getElementById("qtc_paymentmethods").style.display='block';
	}
</script>

<?php

$this->wrapperDivStyle = !empty($orders_email) ? "border: 1px solid #DDDDDD ;padding: 15px;margin-bottom: 10px;" : '';
$this->emailTable = "width:100%;";
$this->email_table_bordered = !empty($orders_email) ? $this->emailTable . "border-width: 1px 1px 1px 0px; border-style: solid solid solid none; border-color: #DDD #DDD #DDD; border-collapse: separate;" : '';
?>

<div class="<?php echo Q2C_WRAPPER_CLASS; ?> q2c_border" style="<?php echo $this->wrapperDivStyle; ?>"  >
	<!--LM -- Added Print Button -- Start-->
<!-- Commented by vm Till completely developed
	<div class="row qtcPrintBtnCover">
		<input type="button" value="Print" onclick="PrintElem('#printOrder')" class="btn btn-primary pull-right qtcPrintBtn"/>
		<div class="clearfix"></div>
	</div>
-->
	<!--LM -- Added Print Button -- End-->
	<div id="printOrder"><!-- LM Added div-- this div will be printed out-->
	<?php

	//if (in_array('order', $order_blocks))
	{
		if ($orders_email)
		{
			// Display site address in invoice
			//~ $view                = $this->comquick2cartHelper->getViewpath('orders', 'orders_siteinvoice');
			//~ ob_start();
				//~ include($view);
				//~ $html = ob_get_contents();
			//~ ob_end_clean();
			//~ echo $html;
			?>

			<h4 <?php echo $emailstyle; ?> >
			 <?php echo (!empty($this->invoice) ? JText::_('COM_QUICK2CART_INVOICE_DETAIL_INFO') : JText::_('COM_QUICK2CART_ORDER_DETAIL_INFO')); ?> &nbsp;
			 <span style="font-size: x-large;">
				 <i>
				  <?php  echo ' #' . $this->orderinfo->prefix . $this->orderinfo->id; ?>
				  </i>
			 </span>
			 </h4>
			<?php
		}
		elseif ($orders_site)
		{
			//<!--  START  code for back and home buttom if and only if site view,not email,and store releated view:: -->
			$input               = JFactory::getApplication()->input;
			$calledfromStoreview = $input->get('calledStoreview', 0, "INT");

			// Show store toolbar
			if (!empty($calledfromStoreview))
			{
				$active              = 'storeorder';
				$view                = $this->comquick2cartHelper->getViewpath('vendor', 'toolbar');
				ob_start();
				include($view);
				$html = ob_get_contents();
				ob_end_clean();
				echo $html;
			} ?>

				<legend>
					<?php echo JText::_('QTC_ORDER_DETAIL') . '  ';  ?>
					<strong><i> <?php echo ' #' . $this->orderinfo->prefix . $this->orderinfo->id;?></i></strong>
				</legend>
			<?php
		}
			?>
		<div class="row-fluid">
			<?php

			// Display basic order detail.
			$view                = $this->comquick2cartHelper->getViewpath('orders', 'default_basicdetails');
			ob_start();
				include($view);
				$html = ob_get_contents();
			ob_end_clean();
			echo $html;
			?>
		</div>
		<?php
	} //if (in_array('order', $order_blocks))
	?>
	<div style="clear: both;"></div>

	<?php
	// Q2C Sample development - add extra html
	$dispatcher = JDispatcher::getInstance();
	JPluginHelper::importPlugin('system');
	$result              = $dispatcher->trigger('addHtmlOnOrderDetailPage', array(
		$this->orderinfo->order_id,
		$this->orderinfo,
		$this->orderitems
	));

	// Call the plugin and get the result
	$orderDetailPageHtml = '';
	$addTabPlace         = '';

	if (!empty($result))
	{
		$orderDetailPageHtml = $result[0];
		$addTabPlace         = !empty($result[0]['tabPlace']) ? $result[0]['tabPlace'] : ''; // further
		// use
	}

	// END - Q2C Sample development -
	// TRIGGER HTML addHtmlOnOrderDetailPage
	if (!empty($orderDetailPageHtml['html']))
	{
		echo $orderDetailPageHtml['html'];
	}?>

	<!-- Display cart detail -->
	<div class="row-fluid">
		<div class="qtcPadding">

		<?php
		$view                = $this->comquick2cartHelper->getViewpath('orders', 'default_cartdetail');
		ob_start();
		include($view);
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;	?>
		</div>
	</div>

	<!-- Display billing and shipping info -->
	<div class="row-fluid">
		<?php
		$view                = $this->comquick2cartHelper->getViewpath('orders', 'default_billing');
		ob_start();
		include($view);
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;	?>
	</div>

	<?php
	$calledStoreview   = $jinput->get('calledStoreview');

	if (!empty($vendor_order_view) && empty($orders_email) && !empty($calledStoreview))
	{
	?>
	<div class="row-fluid">
		<?php

			$view                = $this->comquick2cartHelper->getViewpath('orders', 'statushistrory');
			ob_start();
			include($view);
			$html = ob_get_contents();
			ob_end_clean();
			echo $html;	?>
		</div>
	<?php
	}

	if ($orders_email && $this->orderinfo->status == 'P' && !$user->id && $params->get('guest'))
	{
		$Itemid = $this->comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=orders');?>
		<div>
			<a href="<?php echo JUri::base() . substr(JRoute::_('index.php?option=com_quick2cart&view=orders&layout=order&email=' . $guest_email . '&orderid=' . $this->orderinfo->id . '&paybuttonstatus=1' . '&Itemid=' . $Itemid), strlen(JUri::base(true)) + 1);?>">
				<?php echo JText::_('QTC_ORDER_PROCES_GUEST_LINK');?>
			</a>
		</div>
		<?php
	}?>
	</div> <!-- End of printOrder div-->
</div> <!-- End of wrapper class-->
<!--LM -- Added Print Button Js -- Start-->
<script type="text/javascript">

    function PrintElem(elem)
    {
        Popup(techjoomla.jQuery(elem).html());
    }

    function Popup(data)
    {
        var mywindow = window.open('', 'printOrder', 'height=400,width=600');
        /*optional stylesheet*/ //mywindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
        mywindow.document.write(data);

        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10

        mywindow.print();
        mywindow.close();

        return true;
    }

</script>
<!--LM -- Added Print Button Js -- End-->
