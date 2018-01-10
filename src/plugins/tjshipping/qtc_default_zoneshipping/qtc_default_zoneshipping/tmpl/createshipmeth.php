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

jimport( 'joomla.form.formvalidator' );
jimport('joomla.html.pane');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.tooltip');
JHtmlBehavior::framework();
jimport( 'joomla.html.parameter' );
$comquick2cartHelper = new comquick2cartHelper;
$qtczoneShipHelper = new qtczoneShipHelper;

$path = JPATH_SITE . '/components/com_quick2cart/helpers/storeHelper.php';
if(!class_exists('storeHelper'))
{
	//require_once $path;
	 JLoader::register('storeHelper', $path );
	 JLoader::load('storeHelper');
}
$zoneHelper = new zoneHelper;
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$extension_id = $jinput->get('extension_id', 0);

?>
<script type="text/javascript">

function myValidate(f)
{
	var msg = "<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_NOT_ACCEPTABLE_FORM")?>";
	//vm PRODUCT LAEVAL COP COMPULSORY ::coupon exist then only allow to save & check for other validation

	if (document.formvalidator.isValid(f)) {
		f.check.value='<?php echo JSession::getFormToken(); ?>';

		return true;
	}
	else {

		//alert(msg);
	}

	return false;
}
function qtcShipSubmitAction(action)
{
	var form = document.qtcshipform;
	if (action == 'saveshipmethod' || action == 'shipMethodSaveAndClose')
	{
		switch(action)
		{
			case 'saveshipmethod':
				var submit_status = myValidate(form);
				if (!submit_status)
				{
					return false;
				}
				//form.plugview.value='saveshipmethod';

				// current processing view. Added url param according to this.
				form.plugview.value='createshipmeth';

				// Task to call.
				form.plugtask.value='qtcshipMethodSave';
			break;
			case 'shipMethodSaveAndClose':
				form.plugview.value='createshipmeth';

				// Task to call.
				form.plugtask.value = 'qtcshipMethodSaveAndClose';
			break;
			case 'cancel':
				form.plugview.value='createshipmeth';

				// Task to call.
				form.plugtask.value = 'cancel';
			break;
		}
	}
	else
	{
		window.location = '';
	}

	// Submit form
	form.submit();
	return;

 }

function getFieldHtmlForShippingType(shipping_type)
{
	var data = {
			fieldData :
			{
				shipping_type : shipping_type,

			},
			plugtask : 'getFieldHtmlForShippingType',
		};

	var extension_id = <?php echo $extension_id; ?>;


	techjoomla.jQuery.ajax({
		type : "POST",
		url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&task=shipping.qtcHandleShipAjaxCall&plugview=createshipmeth&extension_id=" +extension_id +'&tmpl=component',
		data : data,
		dataType: 'json',
		beforeSend: function() {},
		success : function(response)
		{
			if (response)
			{
				// No error
				techjoomla.jQuery('#qtcCreateMethMinField').html(response.minFieldHtml);
				techjoomla.jQuery('#qtcCreateMethMaxField').html(response.maxFieldHtml);

				// Change lable
				techjoomla.jQuery('#qtcCreateMethMinFieldLable').html(response.minFieldLable);
				techjoomla.jQuery('#qtcCreateMethMaxFieldLable').html(response.maxFieldLable);

			}
		}
	});
}
</script>

<?php

if (!empty($shipFormData['methodId']))
{
	$status = $comquick2cartHelper->store_authorize('', $shipFormData['store_id']);

	if (!$status)
	{
		$zoneHelper->showUnauthorizedMsg();
		return false;
	}
}
?>
<form name="qtcshipform" id="adminForm" class="form-validate form-horizontal container-fluid" method="post" onSubmit="return myValidate(this);" >
	<input type="hidden" name="check" value="post"/>
	<div class="row">

		<legend id="qtc_shipmethodInfo" ><?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_SEL_CREATE_SHIPMETHO')?>&nbsp;<small><?php //echo JText::_('QTC_BILLIN_DESC')?></small></legend>

		<div class="form-group">
			<label  for="name" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label" title="<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_METH_NAME_TITLE'); ?>">
				<?php echo "* ". JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_METH_NAME'); ?>
			</label>
			<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
				<input id="methodId" name="shipForm[methodId]" class="" type="hidden" value="<?php echo !empty($shipFormData['methodId']) ? $shipFormData['methodId'] : ''; ?>">

				<input id="name" name="shipForm[name]"
				class="required validate-name"
				placeholder="<?php echo Jtext::_('PLG_QTC_DEFAULT_ZONESHIPPING_METH_NAME_TOOLTIP');?>"
				type="text" value="<?php echo !empty($shipFormData['name']) ? $shipFormData['name'] : ''; ?>">
			</div>
		</div>

		<!-- STORE LIST -->
		<?php
			// Getting user accessible store ids
			$storeList = $comquick2cartHelper->getStoreIds();
			$defaultSstore_id = !empty($shipFormData['store_id']) ? $shipFormData['store_id'] : '';
			$options = array();
			$options[] = JHtml::_('select.option', "", JText::_('PLG_QTC_DEFAULT_SELECT_STORE'));

			foreach ($storeList as $store)
			{
				$storename = ucfirst($store['title']);
				$options[] = JHtml::_('select.option', $store['store_id'], $storename);
			}

		?>
		<div class="form-group">
			<label for="qtcShipMethStoreId" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label" title="<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_STORE_NAME_DESC'); ?>">
				<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_STORE_NAME'); ?>
			</label>
			<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
				<?php

				echo JHtml::_('select.genericlist',  $options, "shipForm[store_id]", 'class="inputbox required"  size="1" required="required" ', 'value', 'text', $defaultSstore_id, 'qtcShipMethStoreId');
				?>
				<!--
				<p class="text-info"><?php// echo  JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_SHIPTAXPROFILE_HELP'); ?></p> -->
			</div>
		</div>

		<!-- Tax profile -->
		<?php
			$default = $shipFormData['taxprofileId'];//((!empty($shipFormData['taxprofiles']))?$shipFormData['taxprofiles']:'a');
			$options = array();
			$options[] = JHtml::_('select.option', "", JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_SEL_TAXPROFILE'));
			$options[] = JHtml::_('select.option', "0", JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_TAXPROFILE_NONE'));

			foreach ($shipFormData['taxprofiles'] as $taxprofile)
			{
				$profileText = '';
				$profileText = $taxprofile['name'] . ' [' . JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_STORE') . ':' . $taxprofile['title'] . ' ] ';
				$options[] = JHtml::_('select.option', $taxprofile['id'], $profileText);
			}
		?>
		<div class="form-group">
			<label  for="taxprofileId" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label" title="<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_SHIPTAXPROFILE_HELP_TITLE'); ?>">
				<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_TAX_PROFILE'); ?>
			</label>
			<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
				<?php echo  $this->dropdown = JHtml::_('select.genericlist',$options,"shipForm[taxprofileId]",'class=""   aria-invalid="false" size="1" required="required" ','value','text',$default,'taxprofileId');
				?>
				<p class="text-info"><?php echo  JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_SHIPTAXPROFILE_HELP'); ?></p>
			</div>
		</div>


		<!-- Publish/unpublish -->
		<?php
			$default = empty($shipFormData['state']) ? 0 : 1;
			$options = array();
			$options[] = JHtml::_('select.option', "1", JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_PUBLISH'));
			$options[] = JHtml::_('select.option', "0", JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_UNPUBLISH'));
		?>
		<div class="form-group">
			<label  for="shipstate" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label" title="<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_STATE_TITLE'); ?>">
				<?php echo "* ". JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_STATE"); ?>
			</label>
			<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
				<?php echo  $this->dropdown = JHtml::_('select.genericlist',$options,"shipForm[state]",'class=""  required="required" aria-invalid="false" size="1" ','value','text',$default,'shipstate');
				?>
			</div>
		</div>

		<!-- Method type -->
		<?php
			//FRPSI  = Flat rate per store item
			//FRPSI  = Flat rate per store item
			$default = !empty($shipFormData['shipping_type']) ? $shipFormData['shipping_type'] : 1;;
			$shipping_typeoptions =  array();

			$shipping_typeoptions[] = JHtml::_('select.option', "1", JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_STORE_QTY'));
			$shipping_typeoptions[] = JHtml::_('select.option', "2", JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_STORE_WEIGHT'));
			$shipping_typeoptions[] = JHtml::_('select.option', "3", JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_STORE_ITEM'));
			//$shipping_typeoptions[] = JHtml::_('select.option', "4", JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_UNPUBLISH'));
		?>
		<div class="form-group" >
			<label  for="shipping_type" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label" title="<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_SHIPTYPE_TITLE'); ?>">
				<?php echo "* ". JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_SHIPTYPE"); ?>
			</label>
			<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
				<?php echo  JHtml::_('select.genericlist',$shipping_typeoptions,"shipForm[shipping_type]",'class=""  required="required" onChange="getFieldHtmlForShippingType(this.value)" aria-invalid="false" size="1" ','value','text',$default,'shipping_type');
				?>
			</div>
		</div>
		<!-- min/ max or curr fields -->
		<?php
		$fieldData = array();
		// Price based shipping
		if (!empty($shipFormData['methodId']) && $shipFormData['shipping_type'] == 3)
		{
			// Edit  method
			$comParams = JComponentHelper::getParams('com_quick2cart');

			// Get Currencies
			$currencies = $comParams->get('addcurrency');
			$curr = explode(',', $currencies);

			$currFieldValues = array();


			// Create template field with default value
			foreach ($curr as $key=>$currName)
			{
				$currFieldValues['min'][$currName] = 0;
				$currFieldValues['max'][$currName] = -1;
			}

			// Create default currency value array for fields

			if (!empty($shipFormData['shipMethCurr']))
			{
				foreach ($shipFormData['shipMethCurr'] as $rec)
				{
					$dbcurr = $rec['currency'];
					$currFieldValues['min'][$dbcurr] = $rec['min_value'];
					$currFieldValues['max'][$dbcurr] = $rec['max_value'];
				}
			}

			// Default curr field valuy
			$fieldData['DefFieldValues'] = $currFieldValues;
			$fieldData['shipping_type'] = 3;
		}
		else
		{
			// New method
			$fieldData['shipping_type'] = 1;
			$fieldData['minFieldAmt'] = !empty($shipFormData['min_value']) ? $shipFormData['min_value'] : '';
			$fieldData['maxFieldAmt'] = !empty($shipFormData['max_value']) ? $shipFormData['max_value'] : '';

		}

		$fieldHtml = $qtczoneShipHelper->getFieldHtmlForShippingType($fieldData);
		$fieldHtml = json_decode($fieldHtml, 1);

		?>
		<div class="form-group">
			<label  for="qtcMinAmount" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label" title="<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_MINIMUM_AMT_TITLE'); ?>" id="qtcCreateMethMinFieldLable">
				<?php echo "* ". $fieldHtml['minFieldLable'] ?>
			</label>
			<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12" id="qtcCreateMethMinField">
				<!--
				<input id="qtcMinAmount" name="shipForm[min_value]" class=" required validate-name" type="text" value="<?php echo !empty($shipFormData['min_value']) ? $shipFormData['min_value'] : '0'; ?>">
				-->
				<?php
				echo !empty($fieldHtml['minFieldHtml'])?$fieldHtml['minFieldHtml'] : '';
				?>
			</div>
		</div>

		<div class="form-group">
			<label  for="qtcMaxAmount" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label"  title="<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_MAXIMUM_AMT_TITLE'); ?>" id="qtcCreateMethMaxFieldLable">
				<?php echo "* ". $fieldHtml['maxFieldLable'] ?>
			</label>
			<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12" id="qtcCreateMethMaxField">
				<!--
				<input id="qtcMaxAmount" name="shipForm[max_value]" class=" required validate-name" type="text" value="<?php echo !empty($shipFormData['max_value']) ? $shipFormData['max_value'] : '-1'; ?>">
				<p class="text-info"><?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_MAXIMUM_AMT_HELP"); ?></p>
				-->
				<?php
					echo !empty($fieldHtml['maxFieldHtml'])?$fieldHtml['maxFieldHtml'] : '';
				?>
			</div>
		</div>

		<div class="form-actions ">
			<div class="qtc_action_button filter-search ">
				<button type="button" class="btn btn-success " title="<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_SAVE'); ?>" onclick="qtcShipSubmitAction('saveshipmethod');">
					<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_SAVE'); ?>
				</button>

				<button type="button" class="btn btn-default" title="<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_S_SAVE_CLOSE'); ?>" onclick="qtcShipSubmitAction('shipMethodSaveAndClose');">
					<?php echo JText::_('PLG_QTC_DEFAULT_ZONESHIPPING_S_SAVE_CLOSE'); ?>
				</button>

				<button type="button" class="btn btn-default " title="<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_CANCEL"); ?>" onclick="qtcShipSubmitAction('cancel');">
					<?php echo JText::_("PLG_QTC_DEFAULT_ZONESHIPPING_CANCEL"); ?>
				</button>
			</div>
		</div>

	<!-- Component related things -->
	<input type="hidden" name="com_quick2cart" value="shipping" />
	<input type="hidden" name="task" value="shipping.getShipView" />
	<input type="hidden" name="view" value="shipping" />

	<!-- plugin related things -->
	<input type="hidden" name="plugview" value="" />
	<input type="hidden" name="plugtask" value="save" />
<!--	<input type="hidden" name="plugNextView" value="new" /> -->

	<?php echo JHtml::_( 'form.token' ); ?>
</div> <!-- End row fluid-->
</form>
