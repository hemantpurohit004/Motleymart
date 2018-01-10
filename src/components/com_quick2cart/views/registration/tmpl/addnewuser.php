<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$document = JFactory::getDocument();
?>

<script type="text/javascript">
	function adduser()
	{
		var name = document.getElementById('name').value;
		var username = document.getElementById('username').value;
		var password1 = document.getElementById('password1').value;
		var password2 = document.getElementById('password2').value;
		var emailid = document.getElementById('emailid').value;
		var pattern = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;

		if(name && username && password1 && password2 && emailid)
		{
			if (pattern.test(emailid))
			{
				techjoomla.jQuery.ajax({
					url:'<?php echo JUri::root();?>index.php?option=com_quick2cart&task=registration.newUser&tmpl=component',
					type:'POST',
					dataType:'json',
					data:
					{
						name:name,
						username:username,
						password1:password1,
						password2:password2,
						emailid:emailid
					},
					success:function(data)
					{

					}
				});

				var msg = "<?php echo JText::_('COM_QUICK2CART_NEW_USER_CREATED_SUCCESSFULLY'); ?>";
				alert(msg);
				window.parent.document.location.reload();
				window.close();
			}
			else
			{
				var msg = "<?php echo JText::_('COM_QUICK2CART_INVALID_EMAILID')?>";
				alert(msg);
				return false;
			}
		}
		else
		{
			var msg = "<?php echo JText::_('COM_QUICK2CART_FILL_MANDATORY_FIELD')?>";
			alert(msg);
			return false;
		}
	}
</script>
<form name="addnewuser" id="addnewuser" method="post" class="form-validate" action="" enctype="multipart/form-data" >
<legend><?php echo JText::_( "COM_QUICK2CART_CREATE_NEW_USER");?></legend>
	<div class="form-horizontal">
		<div class="control-group">
			<label class="control-label" for="name">
				<?php echo  JText::_("COM_QUICK2CART_CREATE_NEW_USER_NAME") . ' * '; ?>
			</label>
			<div class="controls">
				<input type="text" id="name" name="name"  class="form-control" placeholder="<?php echo  JText::_('COM_QUICK2CART_CREATE_NEW_USER_NAME'); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="username">
				<?php echo  JText::_("COM_QUICK2CART_CREATE_NEW_USER_LOGIN_NAME") . ' * '; ?>
			</label>
			<div class="controls">
				<input type="text" id="username" name="username"  class="form-control" placeholder="<?php echo  JText::_('COM_QUICK2CART_CREATE_NEW_USER_LOGIN_NAME'); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="password1">
				<?php echo  JText::_("COM_QUICK2CART_CREATE_NEW_USER_PASSWORD") . ' * '; ?>
			</label>
			<div class="controls">
				<input type="password" id="password1" name="password1"  class="form-control" placeholder="<?php echo  JText::_('COM_QUICK2CART_CREATE_NEW_USER_PASSWORD'); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="password2">
				<?php echo  JText::_("COM_QUICK2CART_CREATE_NEW_USER_CONFIRM_PASSWORD") . ' * '; ?>
			</label>
			<div class="controls">
				<input type="password" id="password2" name="password2"  class="form-control" placeholder="<?php echo  JText::_('COM_QUICK2CART_CREATE_NEW_USER_CONFIRM_PASSWORD'); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="emailid">
				<?php echo  JText::_("COM_QUICK2CART_CREATE_NEW_USER_EMAIL") . ' * '; ?>
			</label>
			<div class="controls">
				<input type="text" id="emailid"
				name="emailid"  class="form-control"
				placeholder="<?php echo  JText::_('COM_QUICK2CART_CREATE_NEW_USER_EMAIL'); ?>">
			</div>
		</div>
		<div class="row">
			<div class="span11">
				<button id="viewMoreRec" class="btn btn-primary validate pull-right" type="button" onclick="adduser()">
					<?php
						echo JText::_('COM_QUICK2CART_REGISTRE');
					?>
				</button>
			</div>
			<div class="span1">
				<a class="btn btn-default pull-right" onclick="window.parent.SqueezeBox.close();" title="<?php echo JText::_('JCANCEL'); ?>">
				<?php echo JText::_('COM_QUICK2CART_REGISTRE_CANCEL'); ?>
				</a>
			</div>
		</div>

		<input type="hidden" name="option" value="com_quick2cart">
		<input type="hidden" name="task" value="">

	</div>
</form>


