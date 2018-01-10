<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();
$params = JComponentHelper::getParams('com_quick2cart');
$currentBSViews = $params->get("currentBSViews","bs2","STRING");
$comquick2cartHelper = new comquick2cartHelper;
$q2cbaseUrl = $comquick2cartHelper->quick2CartRoute('index.php?option=com_quick2cart&view=category&layout=default');
$checkout = JUri::root().substr($comquick2cartHelper->quick2CartRoute('index.php?option=com_quick2cart&view=cartcheckout',false),strlen(JUri::base(true))+1);

$data = new stdclass;
$data->cart = $this->cart;

if(empty($data->cart))
{
?>
<div class="well" >
	<div class="alert alert-danger">
		<span ><?php echo JText::_('QTC_EMPTY_CART'); ?> </span>
	</div>
</div>
<?php
	return false;
}

$data->showoptioncol = 0;
$data->coupon = $this->coupon;

foreach ($this->cart as $citem)
{
	if (!empty($citem['options']))
	{
		// Atleast one found then show
		$data->showoptioncol = 1;
		break;
	}
}
?>
<div class=" <?php echo Q2C_WRAPPER_CLASS; ?> ">
	<div class="">
		<h2><?php echo JText::_('QTC_CART')?></h2>
	</div>
	<form method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-horizontal form-validate" onsubmit="return validateForm();">
		<?php
		$layoutName = "cartcheckout." . $currentBSViews . ".cart_checkout";
		$layout = new JLayoutFile($layoutName);
		$data->promotions = !empty($this->promotions) ? $this->promotions : array();
		$response = $layout->render($data);
		echo $response;
		?>
		<hr>
		<div class="form-actions" id="qtc_formactions">
			<a class="btn btn-success" onclick="window.parent.document.location.href='<?php echo $checkout; ?>';" ><?php echo JText::_('QTC_CHKOUT'); ?></a>
			<a class="btn btn-primary" onclick="qtcCartContinueBtn('<?php echo $q2cbaseUrl;?>')" ><?php echo JText::_('QTC_BACK'); ?></a>
		</div>
		<input type="hidden" name="task" id="task" value="cartcheckout.qtc_autoSave" />
	</form>
</div>
<?php

// To change to Continue shipping URL to site specific URL.
$AllProductItemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=category');
$allProdLink = JUri::root() . substr(JRoute::_('index.php?option=com_quick2cart&view=category&Itemid=' . $AllProductItemid, false), strlen(JUri::base(true)) + 1);
?>
<script>
	function qtcCartContinueBtn(q2cBaseUrl)
	{
		var popup = true;
		try
		{
			// IF popup.
			popup = (window.self === window.top);
		}
		catch (e)
		{
			popup = true;
		}

		if (popup == true)
		{
			/* qtc_base_url - Defined in asset loader plugin*/
			window.location.assign(qtc_base_url);

			/* To change to Continue shipping URL to site specific URL. */
			/*window.location.assign("<?php echo $allProdLink;?>"); */
		}
		else
		{
			window.parent.location = q2cBaseUrl;
			window.parent.SqueezeBox.close();
		}
	}
</script>
