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

/* if user is on payment layout and log out at that time undefined order is is found
in such condition send to home page or provide error msg
*/
if (isset($this->orders_site) && isset($this->undefined_orderid_msg) )
{
		return false;
}

$params = JComponentHelper::getParams('com_quick2cart');
$user=JFactory::getUser();

if (!$user->id && !$params->get('guest'))
{
?>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?>" >
<div class="well" >
	<div class="alert alert-danger">
		<span ><?php echo JText::_('QTC_LOGIN'); ?> </span>
	</div>
</div>
</div>
<?php
	return false;
}
	$session = JFactory::getSession();
	$document = JFactory::getDocument();
	// make cart empty
	JLoader::import('cart', JPATH_SITE . '/components/com_quick2cart/models');
	$Quick2cartControllercartcheckout = new Quick2cartControllercartcheckout;
	$Quick2cartModelcart=new Quick2cartModelcart;
	$Quick2cartModelcart->empty_cart();
?>

<?php
//if ($this->orderinfo[0]->processor)
{
/*
	$processor=$this->orderinfo[0]->processor;

	//$comquick2cartHelper->getPluginName()
	$model	= $this->getModel('cartcheckout');
	// gettng plugin name which is set in plugin option
	$plgname=$model->getPluginName($processor);
	$plgname=!empty($plgname)?$plgname:$processor;

	if (empty($this->payhtml))
	{
		?>
		<div class="techjoomla-bootstrap" >
			<div class="well">
				<div class="alert alert-danger">
					<span><?php echo JText::_('COM_QUICK2CART_ORDERSUMMERY_PLS_TRY_AGAIN_SOMETHING_WENT_WRONG');?></span>
				</div>
			</div>
		</div>

		<?php
		return false;
	}
*/
?>
<div>
	<?php
	$this->order_blocks = array ('0'=>'shipping','1'=>'billing','2'=>'cart');
	$this->order_authorized=1;
	// CHECK for view override
	$comquick2cartHelper = new comquick2cartHelper;
	$view=$comquick2cartHelper->getViewpath('orders','order');
	ob_start();
		include($view);
		$html = ob_get_contents();
	ob_end_clean();
	echo $html;
	?>
</div>
<?php
}
?>

<div style="clear:both">&nbsp;</div>
<!-- show payment option start -->
<div class="">
	<div class="paymentHTMLWrapper well well-small" id="qtcPaymentGatewayList">

		<?php
		$paymentListStyle = '' ;
		$mainframe = JFactory::getApplication();
		$qtcOrderPrice = 0;
		if (!empty($this->orderinfo->amount))
		{
			$qtcOrderPrice = (float)$this->orderinfo->amount;;
		}

		if (!empty($qtcOrderPrice))
		{
		?>
		<div class="" id="qtc_paymentlistWrapper" style="<?php echo $paymentListStyle?>">
			<div class="form-group " id="qtc_paymentGatewayList">
				<?php
				$default = "";
				$lable = JText::_('SEL_GATEWAY');
				$gateway_div_style=1;

				// Getting gateways
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('payment');
				//$params->get('gateways') = array('0' => 'paypal','1'=>'Payu');

				if ( !is_array($params->get('gateways')))
				{
					$gateway_param[] = $params->get('gateways');
				}
				else
				{
					$gateway_param = $params->get('gateways');
				}

				// Get payment plugins info.
				if (!empty($gateway_param))
				{
					$gateways = $dispatcher->trigger('onTP_GetInfo',array($gateway_param));
				}

				$this->gateways = $gateways;

				// START Q2C Sample development
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('system');

				// Call the plugin and get the result
				$result = $dispatcher->trigger('OnSystemBeforeDisplayingPaymentList', array($this->gateways,$orderWholeDetail, $isCartdetail = 0));

				if (!empty($result[0]))
				{
					$this->gateways = $result[0];
				}

				if (count($this->gateways) > 1)
				{
					$lable = JText::_('SEL_GATEWAY');
				}
				else
				{
					$lable = JText::_('QTC_PAYMENT');
				}

				// If only one geteway then keep it as selected
				if (!empty($this->gateways))
				{
					$default = $this->gateways[0]->id; // id and value is same
				}

				if (!empty($this->gateways) && count($this->gateways)==1) //if only one geteway then keep it as selected
				{
					$default=$this->gateways[0]->id; // id and value is same
					//$lable=JText::_('SEL_GATEWAY');
					$gateway_div_style=1;  // to show payment radio btn btn-defaulteven if only one payment gateway
				}
				?>

				<div class="">
					<h4><?php echo $lable ?> </h4>
				</div>
				<div class="" style="<?php echo ($gateway_div_style==1)?"" : "display:none;" ?>">
					<?php
					if (empty($this->gateways))
					{
						echo JText::_('NO_PAYMENT_GATEWAY');
					}
					else
					{
						$default = ''; // removed selected gateway 26993
						$imgpath = JUri::root()."components/com_quick2cart/assets/images/ajax.gif";
						$ad_fun = 'onChange=qtc_gatewayHtml(this.value,'.$order_id.',"'.$imgpath.'")';
						//$pg_list = JHtml::_('select.radiolist', $this->gateways, 'gateways', "class='required form-control'   ".$ad_fun . '  ', 'id', 'name',$default,false);
						//echo $pg_list;

						if (count($this->gateways) == 1)
						{
							echo $Quick2cartControllercartcheckout->qtc_singleGatewayHtml($this->gateways[0]->id, $order_id);
						}
						else
						{
							foreach ($this->gateways as $gateway)
							{
							?>

							<div class="radio">
								<label>
								<input type="radio" name="gateways" id="qtc_<?php echo $gateway->id; ?>" value="<?php echo $gateway->id; ?>" <?php echo $ad_fun; ?> ><?php echo $gateway->name; ?>
							  </label>
							</div>
							<?php
							}
						}
					}
					?>
				</div>
				<?php
				if (empty($gateway_div_style))
				{
					?>
						<div class="form-control qtc_left_top">
						<?php echo 	$this->gateways[0]->name; // id and value is same ?>
						</div>
					<?php
				}
				?>
			</div> <!-- END OF form-group-->
			<!-- show payment hmtl form-->
			<div id="qtc_payHtmlDiv">
				<?php

				?>
			</div>
		</div>
		<?php
		}
		else
		{
			//	$this->orderinfo[0]->processor = JText::_('COM_QUICK2CART_FREE_CHCKOUT');
			//$Quick2cartControllercartcheckout = new Quick2cartControllercartcheckout;
			echo $Quick2cartControllercartcheckout->getFreeOrderHtml($order_id);
		}
		?>
		<div style="clear:both">&nbsp;</div>
	</div> <!-- end of paymentHTMLWrapper-->
	<div style="clear:both">&nbsp;</div>
</div>
<!-- show payment option end -->

<?php
	//$Quick2cartModelcart->empty_cart();
?>
