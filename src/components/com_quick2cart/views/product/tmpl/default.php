<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.form.formvalidator');
jimport('joomla.html.pane');
jimport('joomla.html.parameter');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');

JHtmlBehavior::framework();

$strapperClass = Q2C_WRAPPER_CLASS;
$jinput = JFactory::getApplication()->input;

// Variable is set in plugin
$qtc_product_name = $jinput->get('qtc_article_name','','STRING');
$lang = JFactory::getLanguage();


// Load helper file if not exist
if (!class_exists('comquick2cartHelper'))
{
	$path = JPATH_SITE . '/components/com_quick2cart/helper.php';
	JLoader::register('comquick2cartHelper', $path);
	JLoader::load('comquick2cartHelper');
}

$comquick2cartHelper = new comquick2cartHelper;
$qtcshiphelper = new qtcshiphelper;
$productHelper =  new productHelper;
$params = JComponentHelper::getParams('com_quick2cart');
$currencies=$params->get('addcurrency');

$qtc_shipping_opt_status = $this->params->get('shipping', 0);
$isTaxationEnabled = $this->params->get('enableTaxtion', 0);
$document = JFactory::getDocument();

$qtc_base_url = JUri::root();
JLoader::import('attributes', JPATH_SITE . '/components/com_quick2cart/models');
$quick2cartModelAttributes =  new quick2cartModelAttributes();
$currencies = $params->get('addcurrency');
$curr = explode(',', $currencies);
$currencies_sym = $params->get('addcurrency_sym');

if (!empty($currencies_sym))
{
	$curr_syms = explode(',', $currencies_sym);
}

$curr = explode(',', $currencies);
?>

<script type="text/javascript">
	function myValidate(f)
	{
		if (document.formvalidator.isValid(f))
		{
			f.check.value='<?php echo JSession::getFormToken(); ?>';

			return true;
		}
		else
		{
			var msg = "<?php echo JText::_('COM_QUICK2CART_FORM_INVALID');?>";
			alert(msg);
		}

		return false;
	}
/*
	window.addEvent("domready", function()
	{
		document.formvalidator.setHandler('name', function (value);
		{
			if (value<=0)
			{
				alert( "<?php echo JText::_( 'VAL_GRT')?>", true);
				return false;
			}
			else if (value == ' ')
			{
				alert("<?php echo JText::_( 'NO_BLANK', true)?>" );
				return false;
			}
			else
			{
				return true;
			}
		});
	});

	window.addEvent("domready", function()
	{
		document.formvalidator.setHandler('verifydate', function(value);
		{
			regex=/^\d{4}(-\d{2}){2}$/;
			return regex.test(value);
		});
	});
*/
	function submitTask(action)
	{
		if (action=='save'  || action=="saveAndClose")
		{
			/* New product and not edit task */
			var submit_status=myValidate(document.qtcAddProdForm);
			if (!submit_status)
			{
				return false;
			}

			if( techjoomla.jQuery('input[name="qtc_prodImg[]"]')){
				var checkedNum = techjoomla.jQuery('.qtc_img_checkbox:checked').length;
				if (!checkedNum && !techjoomla.jQuery('input[name="prod_img"]').val()) {
					alert("<?php echo JText::_('COM_QUICK2CARET_ATLEAST_ONE_IMAGE_SELECT')?>");
					return false;
				}
			}

			// Check for slab condition
			var slabvalue=techjoomla.jQuery('#item_slab').val();
			if(slabvalue==0)
			{
				alert("<?php echo JText::_('COM_QUICK2CARET_LOT_VALUE_SHOULDNOT_BE_ZERO', true)?>" );
				return false;
			}

			if(slabvalue!=1 && slabvalue!=0)
			{
				var minval=techjoomla.jQuery('#min_item').val();
				var minvaluecheck=minval%slabvalue;

				if (minval<slabvalue || minvaluecheck != 0)
				{
					alert("<?php echo JText::_('QTC_SLAB_MIN_QTY')?>");
					return false;
				}
			}

			if (action=='save')
			{
				document.qtcAddProdForm.task.value='product.save';
			}
			else
			{
				document.qtcAddProdForm.task.value='product.saveAndClose';
			}
		}
		else if (action="cancel")
		{
			document.qtcAddProdForm.task.value='product.cancel';
		}

		document.qtcAddProdForm.submit();
	}
</script>

<?php
// if catagories are not presnt then show appropriate msg
if (empty($this->cats))
{
	?>
	<div class="<?php echo $strapperClass; ?>" >
		<div class="well well-small" >
			<div class="alert alert-danger">
				<span><?php echo JText::_('QTC_NO_FOUND_CONTACT_TO_ADMIN'); ?> </span>
			</div>
		</div>
	</div>
	<?php
	return;
}
?>

<div class='<?php echo $strapperClass; ?> qtc_addInvalidate_border qtc_addProduct' >
	<form name="qtcAddProdForm" id="qtcAddProdForm" class="form-validate "
		method="post" enctype="multipart/form-data" onSubmit="return myValidate(this);" >

		<?php
		$active = 'add_product';
		$comquick2cartHelper = new comquick2cartHelper;
		$view=$comquick2cartHelper->getViewpath('vendor','toolbar');
		ob_start();
		include($view);
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		?>

		<?php
		if (!$this->store_id)
		{
			?>
			<div class="alert alert-danger">
				<button type="button" class="close" data-dismiss="alert"></button>
				<?php echo JText::_('QTC_NO_STORE'); ?>
			</div>
			<!--
			</div>
			-->
			<!--@TODO check if above commented div is needed -->
			<?php
		}
		else
		{
			?>
			<legend>
				<?php
				if (!empty($this->itemDetail))
				{
					echo JText::_( "QTC_EDIT_PRODUCT");
				}
				else
				{
					echo JText::_( "QTC_ADD_PRODUCT");
				}
				?>
			</legend>

			<!-- CODE FOR TABS START-->
			<!-- Only required for left/right tabs -->
			<div class="tabbable">
				<ul class="nav nav-pills">
					<li id="tab1id" class="active">
						<a href="#qtctab1" data-toggle="tab"><?php echo JText::_( "QTC_PRODUCTS_BASIC_DETAIL"); ?></a>
					</li>
					<li id="tab2id">
						<a href="#qtctab2" data-toggle="tab"><?php echo JText::_( "QTC_PROD_ATTRI_INFO"); ?></a>
					</li>
					<?php
					$eProdSupport = $params->get('eProdSupport',0);

					// If esupport
					if ($eProdSupport)
					{
						?>
						<li id="tab3id" class="">
							<a href="#qtcMediatab" data-toggle="tab"><?php echo JText::_( "QTC_PROD_MEDIA_DETAILS"); ?></a>
						</li>
						<?php
					}

					// If taxation and shippping is enabled
					if ($isTaxationEnabled  || $qtc_shipping_opt_status)
					{
						?>
						<li id="taxshipTabId" class="">
							<a href="#taxshipTab" data-toggle="tab"><?php echo JText::_( "COM_QUICK2CART_TAX_ND_SHIPPING_TAB"); ?></a>
						</li>
						<?php
					}
					?>

					<li id="tab5id" class="">
						<a href="#qtcextrafieldsTab" data-toggle="tab"><?php echo JText::_( "QTC_PRODUCT_ADDITIONAL_INFO"); ?></a>
					</li>
				</ul>

				<div class="tab-content">
					<div class="clearfix">&nbsp;</div>
					<div class="tab-pane active" id="qtctab1">
						<?php
						// Check for view override
						$att_list_path = $comquick2cartHelper->getViewpath('product', 'options', "SITE", "SITE");
						ob_start();
						include($att_list_path);
						$item_options = ob_get_contents();
						ob_end_clean();
						echo $item_options;
						?>
					</div>
					<!-- tab 1 end -->

					<div class="tab-pane" id="qtctab2">
						<?php

						$canDisplayAttriContent = empty($this->item_id) ? 0 : 1;
						?>
						<div id="qtcAttributeTabContent" style="<?php echo ($canDisplayAttriContent == 0) ? 'display:none;' : ''; ?>">
							<?php
							// Check for view override
							$att_list_path = $comquick2cartHelper->getViewpath('product', 'attribute', "SITE", "SITE");
							ob_start();
							include($att_list_path);
							$html_attri = ob_get_contents();
							ob_end_clean();
							echo $html_attri;
						?>
						</div>
						<div id="qtcAttributeTabContentHideMsg" style="<?php echo ($canDisplayAttriContent == 1) ? 'display:none;' : ''; ?>">
							<div class="alert alert-info">
								<?php echo JText::_('COM_QUICK2CART_PRODUCT_SAVE_PROD_TO_ADD_ATTRI_MSG');?>
							</div>
						</div>
					</div>

					<?php
					if ($eProdSupport)
					{
						?>
						<div class="tab-pane" id="qtcMediatab">
							<?php
							// Check for view override
							$mediaDetail = $comquick2cartHelper->getViewpath('product', 'medialist', "SITE", "SITE");
							ob_start();
							include($mediaDetail);
							$mediaDetail = ob_get_contents();
							ob_end_clean();
							echo $mediaDetail;
							?>
						</div>
						<?php
					}
					?>
					<?php

					// If taxation and shippping is enabled
					if ($isTaxationEnabled  || $qtc_shipping_opt_status)
					{
						?>
						<div class="tab-pane" id="taxshipTab">
							<?php
							// Check for view override
							$taxshipPath = $comquick2cartHelper->getViewpath('product', 'taxship', "SITE", "SITE");
							ob_start();
							include($taxshipPath);
							$taxshipDetail = ob_get_contents();
							ob_end_clean();
							echo $taxshipDetail;
							?>
						</div>
						<?php
					}
					?>

					<div class="tab-pane" id="qtcextrafieldsTab">
						<?php
						if (empty($this->item_id))
						{
						?>
						<div class="alert alert-info">
							<?php echo JText::_('COM_QUICK2CART_PRODUCT_OTHER_DETAILS_SAVE_PROD_MSG');?>
						</div>
						<?php
						}
						else
						{
							if (!empty($this->form_extra))
							{
							?>
								<?php echo $this->loadTemplate('extrafields'); ?>
							<?php
							}
						}
						?>
					</div>
				</div>
			</div>
			<!-- END OF tabbable  DIV -->
			<!-- CODE FOR TABS END -->

			<div class="clearfix">&nbsp;</div>
			<hr/>
			<div class="">

				<button type="button" class="btn btn-success" title="<?php echo JText::_('QTC_COUPON_SAVE')?>" onclick="submitTask('save')">
					<?php echo JText::_('QTC_COUPON_SAVE')?>
				</button>

				<button type="button" class="btn btn-primary" title="<?php echo JText::_('QTC_PROD_SVCLOSE')?>" onclick="submitTask('saveAndClose')">
					<?php echo JText::_('QTC_PROD_SVCLOSE')?>
				</button>

				<button type="button" class="btn btn-default" title="<?php echo JText::_('QTC_COUPON_CANCEL')?>" onclick="submitTask('cancel')">
					<?php echo JText::_('QTC_COUPON_CANCEL')?>
				</button>
			</div>
			<!-- End of form actions-->
			<input type="hidden" name="option" value="com_quick2cart" />
			<input type="hidden" name="task" value="save" />
			<input type="hidden" name="view" value="product" />
			<input type="hidden" name="controller" value="product" />
			<!-- @TODO check - ^sanjivani -->
			<input type="hidden" name="pid" value="<?php echo $this->item_id;?>" />
			<input type="hidden" name="client" value="com_quick2cart" />
			<!-- @TODO check - ^sanjivani -->
			<input type="hidden" name="check" value="post"/>

		<?php
		}
		?>
	</form>
</div>
<!-- end of techjoomla-->
