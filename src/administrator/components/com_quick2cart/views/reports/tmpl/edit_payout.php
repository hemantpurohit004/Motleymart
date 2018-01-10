<?php
/**
 *  @package    Quick2Cart
 *  @copyright  Copyright (c) 2009-2013 TechJoomla. All rights reserved.
 *  @license    GNU General Public License version 2, or later
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.application.component.helper');
$document=JFactory::getDocument();

//load validation scripts
JHtml::_('behavior.formvalidation');

// load report HELPER
require(JPATH_SITE.DS."components".DS."com_quick2cart".DS."helpers".DS."reports.php");
$js="
function fill_me(el)
{
	//console.log(el);
	jQuery('#payee_name').val(jQuery('#payee_options option:selected').text());
	jQuery('#user_id').val(jQuery('#payee_options option:selected').val());
	jQuery('#payment_amount').val(user_amount_map[jQuery('#payee_options option:selected').val()]);
	jQuery('#paypal_email').val(user_email_map[jQuery('#payee_options option:selected').val()]);
}
var user_amount_map=new Array();";


foreach($this->getPayoutFormData as $payout)
{
	//print_r($payout); die('asdas');
	//@TODO remove this function call somewhere else
	$reportsHelper=new reportsHelper();
	//(float)$totalpaidamount=$reportsHelper->getTotalPaidOutAmount($payout->user_id);
	// TOTATL AMOUT = totl prod price - paid payout sum
	$amt=(float)$payout->total_amount - $payout->fee ;
	$js.="user_amount_map[".$payout->user_id."]=".$amt.";";
}

$js.="var user_email_map=new Array();";
foreach($this->getPayoutFormData as $payout){
	$js.="user_email_map[".$payout->user_id."]='".$payout->email."';";
}

//echo $js;

$document->addScriptDeclaration($js);

$js_joomla15="function submitbutton(task)
{
		if (task == '')
		{
				return false;
		}
		else
		{
				var isValid=true;
				var action = task.split('.');
				if (action[1] != 'cancel' && action[1] != 'close')
				{
						var forms = $$('form.form-validate');
						for (var i=0;i<forms.length;i++)
						{
								if (!document.formvalidator.isValid(forms[i]))
								{
										isValid = false;
										break;
								}
						}
				}

				if (isValid)
				{
						/*Joomla.submitform(task);*/
					  //  document.adminForm.submit();
					   // return true;
				}
				else
				{
					  //  alert(Joomla.JText._('COM_QUICK2CART_ERROR_UNACCEPTABLE','Some values are unacceptable'));
					   // return false;
				}
				  document.adminForm.submit();
		}
}
";

$js_joomla16="
Joomla.submitbutton = function(task)
{                console.log(task);

		if (task == '')
		{
				return false;
		}
		else
		{
				var isValid=true;
				var action = task.split('.');
				if (action[1] != 'cancel' && action[1] != 'close')
				{
						var forms = $$('form.form-validate');
						for (var i=0;i<forms.length;i++)
						{
								if (!document.formvalidator.isValid(forms[i]))
								{
										isValid = false;
										break;
								}
						}
				}

				if (isValid)
				{
						/*Joomla.submitform(task);*/
					   document.adminForm.submit();
						return true;
				}
				else
				{
					   alert(Joomla.JText._('COM_QUICK2CART_ERROR_UNACCEPTABLE','Some values are unacceptable'));
						return false;
				}

			//    document.adminForm.submit();

		}
}
";

if(JVERSION >= '1.6.0')
	$document->addScriptDeclaration($js_joomla16);
else
	$document->addScriptDeclaration($js_joomla15);

//override active menu class to remove active class from other submenu
$menuCssOverrideJs="jQuery(document).ready(function(){
	jQuery('ul>li> a[href$=\"index.php?option=com_quick2cart&view=reports\"]:last').removeClass('active');
});";
$document->addScriptDeclaration($menuCssOverrideJs);
//$type=$this->type_data;
?>

<div class="techjoomla-bootstrap">
	<form method="post" name="adminForm" id="adminForm" enctype="multipart/form-data"
		class="form-horizontal form-validate">

		<div class="control-group">
			<label class="control-label" for="payee_name">
				<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_PAYEE_NAME_TOOLTIP'), JText::_('COM_QUICK2CART_PAYEE_NAME'), '', JText::_('COM_QUICK2CART_PAYEE_NAME'));?>
			</label>
			<div class="controls">
				<input type="text" id="payee_name" name="payee_name"
					class="required" maxlength="250"
					placeholder="<?php echo JText::_('COM_QUICK2CART_PAYEE_NAME');?>"
					value="<?php if(isset($this->payout_data->payee_name)) echo $this->payout_data->payee_name;?>" />

				<?php
					echo JHtml::_('select.genericlist', $this->payee_options, "payee_options", 'class="" size="1"
					onchange="fill_me(this);" name="payee_options"', "value", "text", '');
				?>

				<i><?php echo JText::_('COM_QUICK2CART_PAYOUT_SEL_PAYEENAME');?></i>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="user_id">
				<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_USER_ID_TOOLTIP'), JText::_('COM_QUICK2CART_USER_ID'), '', JText::_('COM_QUICK2CART_USER_ID'));?>
			</label>
			<div class="controls">
				<input type="text" id="user_id" name="user_id"
					class="required validate-numeric" maxlength="250"
					placeholder="<?php echo JText::_('COM_QUICK2CART_USER_ID');?>"
					value="<?php if(isset($this->payout_data->user_id)) echo $this->payout_data->user_id;?>" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="paypal_email">
				<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_PAYPAL_EMAIL_TOOLTIP'), JText::_('COM_QUICK2CART_PAYPAL_EMAIL'), '', JText::_('COM_QUICK2CART_PAYPAL_EMAIL'));?>
			</label>
			<div class="controls">
				<input type="text" id="paypal_email" name="paypal_email"
					class="required validate-email" maxlength="250"
					placeholder="<?php echo JText::_('COM_QUICK2CART_PAYPAL_EMAIL');?>"
					value="<?php if(isset($this->payout_data->email_id)) echo $this->payout_data->email_id;?>" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="transaction_id">
				<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_TRANSACTION_ID_TOOLTIP'), JText::_('COM_QUICK2CART_TRANSACTION_ID'), '', JText::_('COM_QUICK2CART_TRANSACTION_ID'));?>
			</label>
			<div class="controls">
				<input type="text" id="transaction_id" name="transaction_id"
					class="required" maxlength="250"
					placeholder="<?php echo JText::_('COM_QUICK2CART_TRANSACTION_ID');?>"
					value="<?php if(isset($this->payout_data->transaction_id)) echo $this->payout_data->transaction_id;?>" />
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="payout_date">
				<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_PAYOUT_DATE_TOOLTIP'), JText::_('COM_QUICK2CART_PAYOUT_DATE'), '', JText::_('COM_QUICK2CART_PAYOUT_DATE'));?>
			</label>
			<div class="controls">
				<?php
				// Set date to blank
				$date = date('');

				if (isset($this->payout_data->date))
				{
					$date = $this->payout_data->date;
				}

				//echo JHtml::_('calendar', $date, 'payout_date', 'payout_date', JText::_('%Y-%m-%d '), "class='required'"); //@TODO use jtext for date format
				echo JHtml::_('calendar', date('Y-m-d'), 'payout_date', 'payout_date', '%Y-%m-%d'); ?>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="payment_amount">
				<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_PAYOUT_AMOUNT_TOOLTIP'), JText::_('COM_QUICK2CART_PAYOUT_AMOUNT'), '', JText::_('COM_QUICK2CART_PAYOUT_AMOUNT'));?>
			</label>
			<div class="controls">
				<div class="input-append">
					<input type="text" id="payment_amount" name="payment_amount"
						class="required validate-numeric" maxlength="11"
						placeholder="<?php echo JText::_('COM_QUICK2CART_PAYOUT_AMOUNT');?>"
						value="<?php if(isset($this->payout_data->amount)) echo $this->payout_data->amount;?>" />
				</div>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="payment_comment">
				<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_PAYOUT_COMMENT_TOOLTIP'), JText::_('COM_QUICK2CART_PAYOUT_COMMENT'), '', JText::_('COM_QUICK2CART_PAYOUT_COMMENT'));?>
			</label>
			<div class="controls">
				<textarea id="payment_comment" class="input-medium bill inputbox required"
					name="payment_comment" maxlength="250" rows="3"
					title="Enter Address" aria-required="true" required="required"
					style="background-color: transparent; white-space: pre-wrap; z-index: auto; position: relative; line-height: 20px; font-size: 14px; -webkit-transition: none; overflow: auto;"
					spellcheck="false"><?php if(isset($this->payout_data->comment)) echo $this->payout_data->comment;?></textarea>
			</div>
		</div>

		<?php
		$status1 = $status2 = '';

		if (isset($this->payout_data->status))
		{
			if ($this->payout_data->status)
			{
				$status1 = 'checked';
			}
			else
			{
				$status2 = 'checked';
			}
		}
		else
		{
			$status2 = 'checked';
		}
		?>

		<div class="control-group">
			<label class="control-label" for="status">
				<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_STATUS_TOOLTIP'), JText::_('COM_QUICK2CART_STATUS'), '', JText::_('COM_QUICK2CART_STATUS'));?>
			</label>
			<div class="controls">
				<label class="radio inline">
					<input type="radio" name="status" id="status1"
						value="1" <?php echo $status1;?> />
						<?php echo JText::_('COM_QUICK2CART_PAID');?>
				</label>
				<label class="radio inline">
					<input type="radio" name="status" id="status2"
						value="0" <?php echo $status2;?> />
						<?php echo JText::_('COM_QUICK2CART_NOT_PAID');?>
				</label>
			</div>
		</div>

		<input type="hidden" name="option" value="com_quick2cart" />
		<!--
		<input type="hidden" name="controller" value="reports" />
		-->
		<input type="hidden" name="task" value="<?php echo $this->task;?>" />

		<?php
		if($this->task=='edit_pay')
		{
			?>
			<input type="hidden" name="edit_id" value="<?php echo $this->payout_data->id;?>" />
			<?php
		}
		?>

		<?php echo JHtml::_( 'form.token' ); ?>

	</form>
</div>
