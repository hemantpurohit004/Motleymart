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

//jimport('joomla.form.formvalidator');
//jimport('joomla.html.parameter');
//jimport('joomla.html.pane');
//JHtmlBehavior::framework();

//JHtml::_('behavior.formvalidation');
//JHtml::_('behavior.tooltip');

//JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

// Added by aniket
$entered_numerics= "'".JText::_('QTC_ENTER_NUMERICS')."'";
?>

<!--
<script src="<?php //echo JUri::root().'administrator/components/com_quick2cart/assets/js/geo/jquery-1.7.2.js'?>"></script>
<script src="<?php //echo JUri::root().'administrator/components/com_quick2cart/assets/js/geo/jquery.ui.core.js'?>"></script>
<script src="<?php //echo JUri::root().'administrator/components/com_quick2cart/assets/js/geo/jquery.ui.widget.js'?>"></script>
<script src="<?php //echo JUri::root().'administrator/components/com_quick2cart/assets/js/geo/jquery.ui.position.js'?>"></script>
<script src="<?php //echo JUri::root().'administrator/components/com_quick2cart/assets/js/geo/jquery.ui.autocomplete.js'?>"></script>
<script src="<?php //echo JUri::root().'components/com_quick2cart/assets/js/auto.js'?>"></script>
-->

<link rel="stylesheet" href="<?php echo JUri::root(true) . '/components/com_quick2cart/assets/css/geo/geo.css';?>">
<link rel="stylesheet" href="<?php echo JUri::root(true) . '/components/com_quick2cart/assets/css/geo/smoothness/jquery-ui-1.10.4.custom.min.css';?>">

<?php
$document = JFactory::getDocument();
/*$jinput = JFactory::getApplication()->input;
$cid = $jinput->get('cid','0');

if(!$cid)
{
	$this->coupons=array();
}

if($this->coupons)
	$published 	= $this->item->published;
else
	$published 	= 0;

$this->lists['published'] = JHtml::_('select.booleanlist',  'published', '', $published );
*/

$js_key="
function checkfornum(el)
{
	var i =0 ;
	for(i=0;i<el.value.length;i++)
	{
		if(el.value.charCodeAt(i) > 47 && el.value.charCodeAt(i) < 58)
		{
			alert('Numerics Not Allowed');
			el.value = el.value.substring(0,i); break;
		}
	}
}

";
$document->addScriptDeclaration($js_key);
$customValidation = "

	window.addEvent('domready', function()
	{
		var fvalidator = document.formvalidator;
		if(fvalidator)
		{
			document.formvalidator.setHandler('verifydate', function(value)
			{
				regex=/^\d{4}(-\d{2}){2}$/;

				return regex.test(value);
			});
		}
	});



window.addEvent('domready', function()
	{
		var fvalidator = document.formvalidator;
		if(fvalidator)
		{
			fvalidator.setHandler('name', function (value)
			{
				if(value<=0)
				{
					alert('" .JText::_('COM_QUICK2CART_COUPON_GRT', true) . "');

					return false;
				}
				else if(value == ' ')
				{
					alert('" .JText::_('COM_QUICK2CART_COUPON_BLANK', true) . "');

					return false;
				}
				else
				{
					return true;
				}
			});
		}

	});

";

$document->addScriptDeclaration($customValidation);
?>

<style>
	/*+ manoj*/
	.q2c-wrapper .q2c-margin-zero { margin:0 0 0 0 !important; }
</style>

<script type="text/javascript">
	/*sanjivani*/
	/*techjoomla.jQuery(document).ready(function()
	{
		// +manoj
		var coupon_view_name = 'couponform';

		techjoomla.jQuery("#store_ID").val(techjoomla.jQuery("#store_ID option:selected").val());
		techjoomla.jQuery("select").change(function()
		{
			var no = techjoomla.jQuery("#store_ID option:selected").val();
			techjoomla.jQuery("#current_store_id").val(no);
		});
	});*/



	var validcode1=0;

	function checkcode()
	{
		var selectedcode=document.getElementById('code').value;
		var cid=<?php if(isset($this->item->id)) echo $this->item->id; else echo "0"; ?>;

		if(parseInt(cid)==0)
		{
			var url = "index.php?option=com_quick2cart&task=couponform.getcode&selectedcode="+selectedcode;
		}
		else
		{
			var url = "index.php?option=com_quick2cart&task=couponform.getselectcode&couponid="+cid+"&selectedcode="+selectedcode;
		}

		techjoomla.jQuery.ajax({
			url:url,
			type: 'GET',
			success: function(response)
			{
				cid=<?php if (isset($this->item->id)) echo $this->item->id;else echo "0"; ?>;

				if(parseInt(cid)==0)
				{
					if(parseInt(response)!=0)
					{
						alert("<?php echo JText::_('COM_QUICK2CART_COUPON_EXIST')?>");
						validcode1=0;

						return 0;
					}
					else
					{
						validcode1=1;

						return 1;
					}
				}
				else
				{
					if(parseInt(response)!=0)
					{
						alert("<?php echo JText::_('COM_QUICK2CART_COUPON_EXIST')?>");
						validcode1=0;

						return 0;
					}
					else
					{
						validcode1=1;

						return 1;
					}
				}
			}
		});
	}

	Joomla.submitbutton = function(task)
	{
		if (task == 'couponform.cancel')
		{
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
		else if(task=='couponform.apply' || task=='couponform.save' )
		{
			var validateflag = document.formvalidator.isValid(document.adminForm);

			if (validateflag)
			{
				if (techjoomla.jQuery('#item_id_hidden').val() === undefined || techjoomla.jQuery('#item_id_hidden').val() == '')
				{
					alert("<?php echo JText::_('COM_QUICK2CART_SELECT_PRODUCT_FOR_COUPON'); ?>");
					techjoomla.jQuery('#item_id').focus();

					return false;
				}
				techjoomla.jQuery(document).ready(function()
				{
					if (typeof qtc_base_url != 'undefined')
					{
						qtc_base_url = "<?php echo JUri::root() ?>";
					}
					var cid=<?php if (isset($this->item->id)) echo $this->item->id; else echo "0"; ?>;

					if (parseInt(cid)==0)
					{
						var selectedcode=document.getElementById('code').value;
						/*selectedcode=addslashes(selectedcode);*/
						var url = "index.php?option=com_quick2cart&task=couponform.getcode&selectedcode="+selectedcode;
					}
					else
					{
						var selectedcode=document.getElementById('code').value;
						/*selectedcode=addslashes(selectedcode);*/
						var url = "index.php?option=com_quick2cart&task=couponform.getselectcode&couponid="+cid+"&selectedcode="+selectedcode;
					}

					var a = new Request({
						url:qtc_base_url+url,
						method: 'get',
						onComplete: function(response)
						{
							var cid=<?php if (isset($this->item->id)) echo $this->item->id;else echo "0"; ?>;

							if (parseInt(cid)==0)
							{
								if (parseInt(response)!=0)
								{
									alert("<?php echo JText::_('COM_QUICK2CART_COUPON_EXIST')?>");
									validcode1=0;

									return false;
								}
								else
								{
									Joomla.submitform(task, document.getElementById('adminForm'));

									return true;
								}
							}
							else
							{
								if(parseInt(response)!=0)
								{
									alert("<?php echo JText::_('COM_QUICK2CART_COUPON_EXIST')?>");
									validcode1=0;

									return false;
								}
								else
								{
									Joomla.submitform(task, document.getElementById('adminForm'));

									return true;
								}
							}
						}
					}).send();
				});
			}
			/* end of if validate flag*/
			else
			{
				return false;
			}
		}
		/*end of if task=save*/
		else
		{
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	}

	/* this function allow only numberic and specified char (at 0th position)
	// ascii (code 43 for +) (48 for 0 ) (57 for 9)  (45 for  - (negative sign))
		(code 46 for .)
		@param el :: html element
		@param allowed_ascii::ascii code that shold allow

	*/
	function checkforalpha(el, allowed_ascii,entered_numericsMsg )
	{
		// by defau
		allowed_ascii= (typeof allowed_ascii === "undefined") ? "" : allowed_ascii;
		var i =0 ;
		for(i=0;i<el.value.length;i++){
		  if((el.value.charCodeAt(i) < 48 || el.value.charCodeAt(i) >= 58) || (el.value.charCodeAt(i) == 45 ))
		  {
			if(allowed_ascii ==el.value.charCodeAt(i))   // && i==0)  // + allowing for phone no at first char
			{
				var temp=1;
			}
			else
			{
					alert(entered_numericsMsg);
					el.value = el.value.substring(0,i);
					return false;
			}


		  }
		}
		return true;
	}
</script>

<div class="<?php echo Q2C_WRAPPER_CLASS;?> coupon-form">
	<form name="adminForm" id="adminForm" class="form-horizontal form-validate"
		method="post" onSubmit="" >
		<?php
		$active = 'my_coupons';
		ob_start();
		include($this->toolbar_view_path);
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		?>
		<legend><?php echo JText::_( "COM_QUICK2CART_COUPON_INFO"); ?></legend>
		<div>
			<div class="form-group">
				<label for="coupon_name" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label">
					<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_COUPON_NAME_TOOLTIP'), JText::_('COM_QUICK2CART_COUPON_NAME'), '', JText::_('COM_QUICK2CART_COUPON_NAME')) . ' *';?>
				</label>
				<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
					<input type="text" name="coupon_name" id="coupon_name"
						class="required validate-name"
						value="<?php if($this->item){ echo stripslashes($this->item->name);}?>"/>
				</div>
			</div>

			<div class="form-group">
				<label for="code" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label">
					<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_COUPAN_CODE_TOOLTIP'), JText::_('COM_QUICK2CART_COUPON_CODE'), '', JText::_('COM_QUICK2CART_COUPON_CODE') . ' *');?>
				</label>
				<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
					<input type="text" name="code" id="code"
						class="required validate-name"
						value="<?php if($this->item){ echo $this->escape(stripslashes($this->item->code));}?>"/>
				</div>
			</div>

			<?php

			if (!empty($this->item->published))
			{
				$published = " checked='checked' ";
				$unpublished = "";

			}
			else
			{
				$published = "";
				$unpublished = " checked='checked' ";
			}
			?>
			<div class="form-group">
				<label for="published" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label">
					<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_ENABLED_TOOLTIP'), JText::_('COM_QUICK2CART_COUPON_ENABLED'), '', JText::_('COM_QUICK2CART_COUPON_ENABLED'));?>
				</label>
<!--
				<?php if (JVERSION < '3.0') : ?>
					<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
				<?php endif; ?>
						<?php
						echo JHtml::_('select.booleanlist',  'published', '', (isset($this->item->id)) ? $this->item->published : 0);
						?>
				<?php if (JVERSION < '3.0') : ?>
					</div>
				<?php endif; ?>
-->
					<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
						<label class="radio-inline">
						  <input type="radio" class="" <?php echo $published;?> value="1" name="published" >
							<?php echo JText::_('COM_QUICK2CART_YES');?>
						</label>
						<label class="radio-inline">
						  <input type="radio" class="" <?php echo $unpublished;?> value="0" name="published" >
							<?php echo JText::_('COM_QUICK2CART_NO');?>
						</label>
					</div>
			</div>

			<!-- SELECT STORE -->
			 <?php
			//made by sanjivani
			$this->store_role_list = $store_role_list = $this->comquick2cartHelper->getAllStoreIds();

			//JLoader::import('managecoupon', JPATH_SITE . DS . 'components' . DS . 'com_quick2cart' . DS . 'models');
			if ($this->item)
			{
				//$model = new quick2cartModelManagecoupon();// ^manoj //@todo check
				//$this->item = $model->Editlist($this->item->id);// ^manoj //@todo check
			}

			$multivendor_enable = $this->params->get('multivendor');
			//sanjivani end


			if ($multivendor_enable == '1')
			{
				?>
				<div class="form-group">
					<label for="qtc_store" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label">
						<?php echo JHtml::tooltip(JText::_('QTC_PROD_SELECT_STORE_DES'), JText::_('QTC_PROD_SELECT_STORE'), '', JText::_('QTC_PROD_SELECT_STORE'));?>
					</label>
					<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
						<?php
						$default = !empty($this->item->store_id) ? $this->item->store_id : (!empty($store_role_list[0]['id']) ? $store_role_list[0]['id'] : '');
						$options = array();
						$options[] = JHtml::_('select.option', '0', JText::_('COM_QUICK2CART_COUPONFORM_STORE_SELECT'));//submitAction('deletecoupon');

						foreach($this->store_role_list as $key=>$value)
						{
							$options[] = JHtml::_('select.option', $value["id"],$value['title']);//submitAction('deletecoupon');
						}

						echo $this->dropdown = JHtml::_('select.genericlist',$options,'store_ID','class=" qtc_putmargintop10px required" ','value','text',$default,'store_ID');
						?>
					</div>
				</div>
				<?php
			}
			// sj end
			?>

			<div class="form-group">
				<label for="value" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label"><?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_COUPAN_VALUE_TOOLTIP'), JText::_('COM_QUICK2CART_COUPON_VALUE'), '', JText::_('COM_QUICK2CART_COUPON_VALUE') . ' *');?></label>
				<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
					<input class="required validate-name" type="text" name="value" id="value" Onkeyup= "checkforalpha(this,46,<?php echo $entered_numerics; ?>);"  value="<?php if($this->item){ echo $this->item->value; } ?>" />
				</div>
			</div>

			<div class="form-group">
				<label for="val_type" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label">
					<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_COUPON_VALUE_TYPE_TOOLTIP'), JText::_('COM_QUICK2CART_COUPON_VALUE_TYPE'), '', JText::_('COM_QUICK2CART_COUPON_VALUE_TYPE'));?>
				</label>


				<?php
/*				if ($this->item)
				{
					$val_type 	= $this->item->val_type;
				}
				else
				{
					$val_type 	= 0;
				}

				$val_type1[] = JHtml::_('select.option', '0', JText::_("COM_QUICK2CART_COUPON_FLAT"));
				$val_type1[] = JHtml::_('select.option', '1', JText::_("COM_QUICK2CART_COUPON_PER")); // first parameter is value, second is text
				$lists['val_type'] = JHtml::_('select.radiolist', $val_type1, 'val_type', ' ', 'value', 'text', $val_type, 'val_type');

				if(JVERSION < '3.0')
				{
					?>
					<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
						<?php echo $lists['val_type']; ?>
					</div>
					<?php
				}
				else
				{
					echo $lists['val_type'];
				}
*/
				if(!empty($this->item->val_type))
				{
					// Percentage
					if ($this->item->val_type)
					{
						$flatCop = "";
						$perCop = " checked='checked' ";

					}
					else
					{
						$flatCop = " checked='checked' ";
						$perCop = "";
					}
				}
				else
				{
					// Flat
					$flatCop = "checked='checked'";
					$perCop = "";
				}
				?>
				<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
					<label class="radio-inline">
					  <input type="radio" class="" <?php echo $flatCop;?> value="0" name="val_type" >
						<?php echo JText::_('COM_QUICK2CART_COUPON_FLAT');?>
					</label>
					<label class="radio-inline">
					  <input type="radio" class="" <?php echo $perCop;?> value="1" name="val_type" >
						<?php echo JText::_('COM_QUICK2CART_COUPON_PER');?>
					</label>
				</div>
			</div>

			<?php
			if($multivendor_enable == '1')   // sj change
			{
				?>
				<!-- -sj change -->
				<div class="form-group">
					<label for="item_id" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label qtc_product_cop_txtbox_lable">
						<?php echo JHtml::tooltip(JText::_('COUPAN_ITEMID_TOOLTIP'), JText::_('COUPAN_ITEMID'), '', JText::_('COUPAN_ITEMID')) . ' *';?>
					</label>

					<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
						<ul class='selections q2c-margin-zero' id='selections.item_id'>
							<input type="text" id="item_id" class="auto_fields validate-item_id_hidden qtc_product_cop_txtbox"
								value="<?php echo ($this->item) ? $this->item->item_id : JText::_('ITEMID_START_TYP_MSG'); ?>"  />
							<input type="hidden" class="auto_fields_hidden" name="item_id" id="item_id_hidden" value="" autocomplete='off' />
						</ul>

						<input type="hidden" class="" id="item_id_hiddenname" value="<?php echo (isset($this->item->item_id_name)) ? $this->item->item_id_name : '' ;?>" autocomplete='off' />

						<!--
						<input type="hidden" name="store_ID" id="store_ID" value="<?php echo $default; ?>" /> -->
					</div>
				</div>
				<?php
			}
			?>

			<div class="form-group">
				<label for="max_use" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label"><?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_COUPON_MAXUSES_TOOLTIP'), JText::_('COM_QUICK2CART_COUPON_MAXUSES'), '', JText::_('COM_QUICK2CART_COUPON_MAXUSES'));?></label>
				<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
					<input type="text" name="max_use" id="max_use"  Onkeyup= "checkforalpha(this,'',<?php echo $entered_numerics; ?>);"  value="<?php if($this->item){ echo $this->item->max_use; } ?>"  />
				</div>
			</div>

			<div class="form-group">
				<label for="max_per_user" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label"><?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_COUPON_MAXUSES_PERUSER_TOOLTIP'), JText::_('COM_QUICK2CART_COUPON_MAXUSES_PERUSER'), '', JText::_('COM_QUICK2CART_COUPON_MAXUSES_PERUSER'));?></label>
				<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
					<input type="text" name="max_per_user" id="max_per_user"  Onkeyup= "checkforalpha(this,'',<?php echo $entered_numerics; ?>);"  value="<?php if($this->item){  echo $this->item->max_per_user; } ?>"  />
				</div>
			</div>

			<div class="form-group">
				<label for="from_date" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label"><?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_VALID_FROM_TOOLTIP'), JText::_('COM_QUICK2CART_COUPON_VALID_FROM'), '', JText::_('COM_QUICK2CART_COUPON_VALID_FROM'));?></label>
				<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12 ">
					<?php
					if ($this->item)
					{
						if (isset($this->item->from_date) && $this->item->from_date !== '0000-00-00 00:00:00')
						{
							$date_from = trim(date(JText::_('COM_QUICK2CART_DATE_FORMAT_SHOW_SHORT'), strtotime($this->item->from_date)));
						}
						else
						{
							$date_from = JFactory::getDate($this->item->from_date)->Format(JText::_('COM_QUICK2CART_DATE_FORMAT_SHOW_SHORT'));
						}
					}
					else
					{
						$date_from = JFactory::getDate()->Format(JText::_('COM_QUICK2CART_DATE_FORMAT_SHOW_SHORT'));
					}

					//echo JHtml::_('calendar', $date_from, 'from_date', 'from_date', JText::_('COM_QUICK2CART_DATE_FORMAT_CALENDER'));
					?>
					<?php
					//static calendar($value, $name, $id, $format= '%Y-%m-%d', $attribs=null)
					echo $calender = JHtml::_('calendar', $date_from, 'from_date', 'from_date', JText::_('COM_QUICK2CART_DATE_FORMAT_CALENDER'), array('class'=>'col-xs-4'));
				?>
				</div>

			</div>

			<div class="form-group">
				<label for="exp_date" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label"><?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_COUPON_EXPIRES_ON_TOOLTIP'), JText::_('COM_QUICK2CART_COUPON_EXPIRES_ON'), '', JText::_('COM_QUICK2CART_COUPON_EXPIRES_ON'));?></label>
				<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
					<?php
					if ($this->item)
					{
						if (isset($this->item->exp_date) && $this->item->exp_date !== '0000-00-00 00:00:00')
						{
							$date_exp = trim(date(JText::_('COM_QUICK2CART_DATE_FORMAT_SHOW_SHORT'), strtotime($this->item->exp_date)));
						}
						else
						{
							$date_exp = JFactory::getDate($this->item->exp_date)->Format(JText::_('COM_QUICK2CART_DATE_FORMAT_SHOW_SHORT'));
						}
					}
					else
					{
						$date_exp = JFactory::getDate()->Format(JText::_('COM_QUICK2CART_DATE_FORMAT_SHOW_SHORT'));
					}

					echo JHtml::_('calendar', $date_exp, 'exp_date', 'exp_date', JText::_('COM_QUICK2CART_DATE_FORMAT_CALENDER'), array('class'=>'col-lg-5 col-md-5 col-sm-5 col-xs-4'));

					?>
				</div>
			</div>

			<div class="form-group">
				<label for="description" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label"><?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_COUPON_DESCRIPTION_TOOLTIP'), JText::_('COM_QUICK2CART_COUPON_DESCRIPTION'), '', JText::_('COM_QUICK2CART_COUPON_DESCRIPTION'));?></label>
				<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
					<textarea rows="3" name="description" id="description"  ><?php if($this->item){  echo trim($this->item->description); } ?></textarea>
				</div>
			</div>

			<div class="form-group">
				<label for="params" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label"><?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_COUPON_PARAMETERS_TOOLTIP'), JText::_('COM_QUICK2CART_COUPON_PARAMETERS'), '', JText::_('COM_QUICK2CART_COUPON_PARAMETERS'));?></label>
				<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
					<textarea   rows="3" name="params" id="params"  ><?php if($this->item){  echo trim($this->item->extra_params); } ?></textarea>
				</div>
			</div>

		</div>

		<!--sj change -->
		<div class="">
			<input type="button" class="btn btn-success" value="<?php echo JText::_('QTC_COUPON_SAVE');?>" onclick="Joomla.submitbutton('couponform.save');"/>
			<input type="button" class="btn btn-default" value="<?php echo JText::_('QTC_COUPON_CANCEL');?>" onclick="Joomla.submitbutton('couponform.cancel');"/>
		</div>
		<input type="hidden" name="coupon_id" id="coupon_id" value="<?php if($this->item){ echo $this->item->id; } ?>" />
		<input type="hidden" name="id1" id="id1" value="<?php if($this->item){ echo $this->item->id; } ?>" />
		<label for="id1" ></label>

		<input type="hidden" name="option" value="com_quick2cart" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="view" value="coupon" />

		<input type="hidden" name="check" value="post"/>

		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
