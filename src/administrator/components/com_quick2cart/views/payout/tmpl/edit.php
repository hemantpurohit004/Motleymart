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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_quick2cart/assets/css/quick2cart.css');

$js="
function fill_me(el)
{
	jQuery('#payee_name').val(jQuery('#payee_options option:selected').text());
	jQuery('#user_id').val(jQuery('#payee_options option:selected').val());
	jQuery('#payment_amount').val(user_amount_map[jQuery('#payee_options option:selected').val()]);
	jQuery('#paypal_email').val(user_email_map[jQuery('#payee_options option:selected').val()]);
}

var user_amount_map=new Array();";

foreach($this->getPayoutFormData as $payout)
{
	// TOTATL AMOUT = totl prod price - paid payout sum
	$amt = (float)$payout->total_amount - $payout->fee;
	$js .= "user_amount_map[".$payout->user_id."]=".$amt.";";
}

$js .= "var user_email_map=new Array();";

foreach ($this->getPayoutFormData as $payout)
{
	$js .= "user_email_map[".$payout->user_id."]='".$payout->email."';";
}

//echo $js; die;

$document->addScriptDeclaration($js);
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'payout.cancel')
		{
			Joomla.submitform(task, document.getElementById('payout-form'));
		}
		else
		{
			if (task != 'payout.cancel' && document.formvalidator.isValid(document.id('payout-form')))
			{
				Joomla.submitform(task, document.getElementById('payout-form'));
			}
			else
			{
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<?php if (JVERSION < '3.0'): ?>
<div class="techjoomla-bootstrap">
<?php endif; ?>

	<form
		action=""
		method="post" enctype="multipart/form-data" name="adminForm" id="payout-form" class="form-validate">

		<div class="form-horizontal">
			<div class="row-fluid">
				<div class="span12 form-horizontal">
					<fieldset class="adminform">

						<div class="control-group">
							<label class="control-label" for="payee_name">
								<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_PAYEE_NAME_TOOLTIP'), JText::_('COM_QUICK2CART_PAYEE_NAME'), '', '* ' . JText::_('COM_QUICK2CART_PAYEE_NAME'));?>
							</label>
							<div class="controls">
								<input type="text" id="payee_name" name="payee_name"
									class="required" maxlength="250"
									placeholder="<?php echo JText::_('COM_QUICK2CART_PAYEE_NAME');?>"
									value="<?php if(isset($this->item->payee_name)) echo $this->item->payee_name;?>" />

								<?php
									echo JHtml::_('select.genericlist', $this->payee_options, "payee_options", 'class="" size="1"
									onchange="fill_me(this);" name="payee_options"', "value", "text", '');
								?>

								<i><?php echo JText::_('COM_QUICK2CART_PAYOUT_SEL_PAYEENAME');?></i>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="user_id">
								<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_USER_ID_TOOLTIP'), JText::_('COM_QUICK2CART_USER_ID'), '', '* ' . JText::_('COM_QUICK2CART_USER_ID'));?>
							</label>
							<div class="controls">
								<input type="text" id="user_id" name="user_id"
									class="required validate-numeric" maxlength="250"
									placeholder="<?php echo JText::_('COM_QUICK2CART_USER_ID');?>"
									value="<?php if(isset($this->item->user_id)) echo $this->item->user_id;?>" />
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="paypal_email">
								<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_PAYPAL_EMAIL_TOOLTIP'), JText::_('COM_QUICK2CART_PAYPAL_EMAIL'), '', '* ' . JText::_('COM_QUICK2CART_PAYPAL_EMAIL'));?>
							</label>
							<div class="controls">
								<input type="text" id="paypal_email" name="paypal_email"
									class="required validate-email" maxlength="250"
									placeholder="<?php echo JText::_('COM_QUICK2CART_PAYPAL_EMAIL');?>"
									value="<?php if(isset($this->item->email_id)) echo $this->item->email_id;?>" />
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="transaction_id">
								<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_TRANSACTION_ID_TOOLTIP'), JText::_('COM_QUICK2CART_TRANSACTION_ID'), '', '* ' . JText::_('COM_QUICK2CART_TRANSACTION_ID'));?>
							</label>
							<div class="controls">
								<input type="text" id="transaction_id" name="transaction_id"
									class="required" maxlength="250"
									placeholder="<?php echo JText::_('COM_QUICK2CART_TRANSACTION_ID');?>"
									value="<?php if(isset($this->item->transaction_id)) echo $this->item->transaction_id;?>" />
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="payout_date">
								<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_PAYOUT_DATE_TOOLTIP'), JText::_('COM_QUICK2CART_PAYOUT_DATE'), '', '* ' . JText::_('COM_QUICK2CART_PAYOUT_DATE'));?>
							</label>
							<div class="controls">
								<?php
								// Set date to blank
								$date = date('');

								if (isset($this->item->date))
								{
									$date = $this->item->date;
								}

								//echo JHtml::_('calendar', $date, 'payout_date', 'payout_date', JText::_('%Y-%m-%d '), "class='required'"); //@TODO use jtext for date format
								echo JHtml::_('calendar', date('Y-m-d'), 'payout_date', 'payout_date', '%Y-%m-%d'); ?>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="payment_amount">
								<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_PAYOUT_AMOUNT_TOOLTIP'), JText::_('COM_QUICK2CART_PAYOUT_AMOUNT'), '', '* ' . JText::_('COM_QUICK2CART_PAYOUT_AMOUNT'));?>
							</label>
							<div class="controls">
								<div class="input-append">
									<input type="text" id="payment_amount" name="payment_amount"
										class="required validate-numeric" maxlength="11"
										placeholder="<?php echo JText::_('COM_QUICK2CART_PAYOUT_AMOUNT');?>"
										value="<?php if(isset($this->item->amount)) echo $this->item->amount;?>" />
								</div>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="payment_comment">
								<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_PAYOUT_COMMENT_TOOLTIP'), JText::_('COM_QUICK2CART_PAYOUT_COMMENT'), '', '* ' . JText::_('COM_QUICK2CART_PAYOUT_COMMENT'));?>
							</label>
							<div class="controls">
								<textarea id="payment_comment" class="input-medium bill inputbox required"
									name="payment_comment" maxlength="250" rows="3"
									title="Enter Address" aria-required="true" required="required"
									style="background-color: transparent; white-space: pre-wrap; z-index: auto; position: relative; line-height: 20px; font-size: 14px; -webkit-transition: none; overflow: auto;"
									spellcheck="false"><?php if(isset($this->item->comment)) echo $this->item->comment;?></textarea>
							</div>
						</div>

						<?php
						$status1 = $status2 = '';

						if (isset($this->item->status))
						{
							if ($this->item->status)
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
								<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_STATUS_TOOLTIP'), JText::_('COM_QUICK2CART_STATUS'), '', '* ' . JText::_('COM_QUICK2CART_STATUS'));?>
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

						<input type="hidden" name="id" value="<?php echo $this->item->id;?>" />

						<?php echo JHtml::_( 'form.token' ); ?>

					</fieldset>
				</div>
			</div>

			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>

<?php if (JVERSION < '3.0'): ?>
</div>
<?php endif; ?>
