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
?>

<form method="post" name="adminForm" class="" id="adminForm">
<div class="<?php echo Q2C_WRAPPER_CLASS; ?>" >


	<input type="hidden" name="option" value="com_quick2cart">
	<input type="hidden" id="task" name="task" value="cartcheckout.processFreeOrder">
	<input type="hidden" name="orderid" value="<?php echo $order_id; ?>">

	<div class="form-actions qtc_formActionAlign" >
			<!--<a class="btn btn-success bth-large" href="<?php //echo $link?>" >
			<?php echo JText::_('QTC_CONFORM_ORDER'); ?>
			</a>  -->
			<input type="submit" class="btn btn-success btn-large" value="<?php echo JText::_('QTC_CONFORM_ORDER'); ?>">
	</div >


</div>
</form>
