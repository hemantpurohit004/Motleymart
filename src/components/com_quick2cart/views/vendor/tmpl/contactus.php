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

JHtml::_('behavior.framework');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation')

 ?>
 <style>
.contact-us-textarea {
    height:250px;
}

</style>

 <script type="text/javascript">

 function submitAction(action)
 {
	 var validateflag = document.formvalidator.isValid(document.adminForm);
	 console.log("validateflag" +validateflag);
 }
</script>
<div class='<?php echo Q2C_WRAPPER_CLASS; ?> container-fluid' >
	<form  name="adminForm" id="adminForm" class="form-validate" method="post">
		<div class="row">
			<legend><?php	echo JText::_( "QTC_CONTACT_TO_PRODUCT_OWNER"); ?> </legend>
				<div class="well col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="form-group">
						<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label">
							<?php echo  JText::_('QTC_ENTER_EMAIL') ?>
						</label>
						<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">

							<input type="text" name="cust_email" id="inputIcon" class="required  validate-email" style="width:233px" placeholder="<?php echo  JText::_('QTC_CONTCT_ENTER_EMAIL') ?>">
							<div class="input-group-addon"><i class="icon-envelope"></i></div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label">
							<?php echo  JText::_('QTC_EMAIL_BODY') ?>
						</label>
						<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
							<textarea name="message" id="message" class="contact-us-textarea  required col-lg-9 col-md-9 col-sm-9 col-xs-12" rows="15"></textarea>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-primary pull-right"><i class="icon-envelope <?php echo Q2C_ICON_WHITECOLOR; ?>"> &nbsp;</i><?php echo  JText::_('QTC_SEND') ?> </button>
					</div>
				</div>
	</div>


		<input type="hidden" name="option" value="com_quick2cart" />
		<input type="hidden" name="view" value="vendor" />
		<input type="hidden" name="task" value="vendor.contactUsEmail" />
		<input type="hidden" name="store_id" value="<?php echo $this->store_id;?>" />
		<input type="hidden" name="item_id" value="<?php echo $this->item_id;?>" />
</form>

</div>
