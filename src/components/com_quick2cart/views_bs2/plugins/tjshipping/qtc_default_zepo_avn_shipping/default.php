<?php

/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// no direct access
defined('_JEXEC') or die;
$qtczepoShipHelper = new qtczepoShipHelper;
$comquick2cartHelper = new comquick2cartHelper();
$allStoresDetail = $comquick2cartHelper->getAllStoreIds();
$storesList = array();
$session = JFactory::getSession();
$store_id = $session->get('storeid');

if (!empty($store_id))
{
	$storeShippingConfig = $qtczepoShipHelper->getStoreShippingConfig($store_id);
}

// Get all stores of logged in users
foreach ($allStoresDetail as $storeDetails)
{
	$store = new stdclass;
	$store->id = $storeDetails['id'];
	$store->title = $storeDetails['title'];
	$storesList[] = $store;
}

$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
?>
<script type="text/javascript">
techjoomla.jquery = jQuery.noConflict();

function qtcShipSubmitAction(action)
{
	var form = document.qtcshipform;

	if (action == "add" )
	{
		if (document.getElementById("request_url").value == "")
		{
			alert("<?php echo JText::_('PLG_QTC_ZEPO_AVN_REQUEST_URL_ERROR');?>");

			return false;
		}

		if (techjoomla.jquery("input[name='payment_mode[]']:checked").length == 0)
		{
			alert("<?php echo JText::_('PLG_QTC_ZEPO_AVN_PAYMENR_MODE_ERROR');?>");
			return false;
		}

		if (techjoomla.jquery("input[name='service_name[]']:checked").length == 0)
		{
			alert("<?php echo JText::_('PLG_QTC_ZEPO_AVN_SHIPPING_SERVICE_ERROR');?>");
			return false;
		}

		form.plugtask.value='savestoreconfig';
		var e = document.getElementById("store");
		var selectedOptiontext = e.options[e.selectedIndex].text;
		form.storename.value = selectedOptiontext;

		form.submit();
	}

	if (action == "showstoreconfig" )
	{
		form.plugtask.value='showstoreconfig';
		var e = document.getElementById("store");
		var selectedOptiontext = e.options[e.selectedIndex].text;
		form.storeid.value = selectedOptiontext;

		form.submit();
	}
 }
</script>

<form name="qtcshipform" method="post" id="adminForm" enctype="multipart/form-data">
	<legend><?php echo JText::_('PLG_QTC_ZEPO_AVN')?></legend>
	<div class="form-horizontal">
		<div class="control-group">
			<div class="control-label"><?php echo JText::_('select store'); ?></div>
			<div class="controls">
				<?php
					echo JHtml::_('select.genericlist', $storesList, "store", 'class="inputbox input-medium" size="1" onchange="qtcShipSubmitAction('."'showstoreconfig'".');" name="store"', "id", "title", $store_id);
				?>
			</div>
		</div>
		<div>
			<div class="control-group">
				<div class="control-label"><?php echo JText::_('Request URL'); ?></div>
				<div class="controls">
					<input type="text" class="input input-small" id="request_url" name="request_url" value="<?php echo !empty($storeShippingConfig['request_url'])?$storeShippingConfig['request_url']:'';?>">
				</div>
			</div>
			<div class="control-group">
				<?php
					$payment_mode = array();
					if (!empty($storeShippingConfig['payment_mode']))
					{
						$payment_mode = json_decode($storeShippingConfig['payment_mode']);
					}
				?>
				<div class="control-label"><?php echo JText::_('Payment mode'); ?></div>
				<div class="controls">
					<input type="checkbox" name="payment_mode[]" value="Cod" <?php echo in_array('Cod',$payment_mode)?'checked="checked"':'';?>>COD</input>
					<input type="checkbox" name="payment_mode[]" value="Online" <?php echo in_array('Online',$payment_mode)?'checked="checked"':'';?>>Online</input>
				</div>
			</div>
			<div class="control-group">
				<?php
					$shipping_type = array();
					$shipping_type[]="fsd";

					if (!empty($storeShippingConfig['shipping_type']))
					{
						$shipping_type = json_decode($storeShippingConfig['shipping_type']);
					}
				?>
				<div class="control-label"><?php echo JText::_('Shipping type'); ?></div>
				<div class="controls">
					<input type="checkbox" name="service_name[]" value="Standard" <?php echo in_array('Standard',$shipping_type)?'checked="checked"':'';?>>Standard</input>
					<input type="checkbox" name="service_name[]" value="Priority" <?php echo in_array('Priority',$shipping_type)?'checked="checked"':'';?>>Priority</input>
					<input type="checkbox" name="service_name[]" value="Economy" <?php echo in_array('Economy',$shipping_type)?'checked="checked"':'';?>>Economy</input>
				</div>
			</div>
		</div>
		<!-- Add action buttons-->
		<div class="center">
			<button type="button" class="btn btn-success btn-small" onclick="qtcShipSubmitAction('add');"><?php echo JText::_('QTC_SAVE'); ?></button>
			<a class="btn btn-danger btn-small" href="<?php echo JUri::root().'administrator/index.php?option=com_quick2cart&view=shipping';?>" onclick="qtcShipSubmitAction();"><?php echo " " . JText::_('QTC_CLOSE'); ?></a>
		</div>
	</div>
	<!-- Component related things -->
	<input type="hidden" name="com_quick2cart" value="shipping" />
	<input type="hidden" name="task" value="shipping.getShipView" />
	<input type="hidden" name="view" value="shipping" />

	<!-- plugin related things -->
	<input type="hidden" name="plugview" value="default" />
	<input type="hidden" name="shipMethId" value="" />
	<input type="hidden" name="plugtask" value="" />
	<input type="hidden" name="storeid" value="" />
	<input type="hidden" name="storename" value="" />
	<input type="hidden" name="boxchecked" value="0" />
<!--	<input type="hidden" name="plugNextView" value="new" /> -->

	<?php echo JHtml::_( 'form.token' ); ?>

</form>
