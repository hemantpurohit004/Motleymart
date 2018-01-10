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

jimport('joomla.filter.output');
jimport( 'joomla.utilities.date');

$document=JFactory::getDocument();

$user=JFactory::getUser();

$jsScript ="Joomla.submitbutton = function(prm)
{
	if (prm=='reports.add')
	{
		window.location = 'index.php?option=com_quick2cart&view=reports&task=save&layout=edit_payout';
	}
	elseif (prm=='delete')
	{
		document.getElementById('controller').value='report';
		document.getElementById('task').value='delete';
		document.adminForm.submit();
	}
	elseif (prm=='csvexport')
	{
		document.getElementById('controller').value='reports';
		document.getElementById('task').value='csvexport';
		document.adminForm.submit();
	}
	else
	{
		window.location = 'index.php?option=com_quick2cart';
	}
}";

$document->addScriptDeclaration($jsScript);
?>

<div class="<?php echo Q2C_WRAPPER_CLASS;?>">
	<form action="index.php" method="post" name="adminForm"	id="adminForm">
		<?php
		if (JVERSION >= '3.0'):
			if (!empty( $this->sidebar)) : ?>
				<div id="j-sidebar-container" class="span2">
					<?php echo $this->sidebar; ?>
				</div>
				<div id="j-main-container" class="span10">
			<?php else : ?>
				<div id="j-main-container">
			<?php endif;
		endif;
		?>

		<?php
		if (empty($this->payouts))
		{
		?>
			<div class="well">
				<div class="alert alert-info">
					<?php echo JText::_('COM_QUICK2CART_NO_DATA'); ?>
				</div>
			</div>
		<?php
		}
		?>

		<div class="clearifx"></div>

		<table class="table table-striped">
			<thead>
				<tr>
					<th width="1%">
						<!--By Sneha to check all, Bug id:26182 -->
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
					<th>
						<?php echo JHtml::_( 'grid.sort', 'COM_QUICK2CART_PAYOUT_ID','id', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th>
						<?php echo JHtml::_( 'grid.sort', 'COM_QUICK2CART_PAYEE_NAME','payee_name', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th>
						<?php echo JHtml::_( 'grid.sort', 'COM_QUICK2CART_PAYPAL_EMAIL','email_id', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th>
						<?php echo JHtml::_( 'grid.sort', 'COM_QUICK2CART_TRANSACTION_ID','transaction_id', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th>
						<?php echo JHtml::_( 'grid.sort', 'COM_QUICK2CART_PAYOUT_DATE','date', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th>
						<?php echo JHtml::_( 'grid.sort', 'COM_QUICK2CART_STATUS','status', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th>
						<?php echo JHtml::_( 'grid.sort', 'COM_QUICK2CART_CASHBACK_AMOUNT','amount', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php
				$i = 0;

				foreach ($this->payouts as $payout)
				{
				?>
					<tr class="row<?php echo $i % 2;?>">
						<td align="center">
							<?php echo JHtml::_('grid.id',$i,$payout->id);?>
						</td>

						<td>
							<a href="<?php
							if (strlen($payout->id) <= 6)
							{
								$append = '';

								for ($z=0; $z < (6-strlen($payout->id)); $z++)
								{
									$append .= '0';
								}

								$payout->id = $append . $payout->id;
							}
							echo 'index.php?option=com_quick2cart&view=reports&layout=edit_payout&task=edit_pay&payout_id=' . $payout->id; ?>"
							title="<?php echo JText::_('COM_QUICK2CART_PAYOUT_ID_TOOLTIP');?>">
								<?php echo $payout->id;
							?>
							</a>
						</td>

						<td><?php echo $payout->payee_name;?></td>

						<td><?php echo $payout->email_id;?></td>

						<td><?php echo $payout->transaction_id;?></td>

						<td>
							<?php echo JHtml::_('date', $payout->date, "Y-m-d");?>
						</td>

						<td>
							<?php
							if ($payout->status==1)
							{
								echo JText::_('COM_QUICK2CART_PAID');
							}
							else
							{
								echo JText::_('COM_QUICK2CART_NOT_PAID');
							}
							?>
						</td>

						<td><?php echo $payout->amount; ?></td>
					</tr>
				<?php
				$i++;
				}
				?>
			</tbody>

			<?php if (JVERSION < '3.0'): ?>
			<tfoot>
				<tr>
					<td colspan="9">
						<div class="pager pull-left">
							<?php echo $this->pagination->getListFooter(); ?>
						</div>
					</td>
				</tr>
			</tfoot>
			<?php endif; ?>

		</table>

		<?php if (JVERSION >= '3.0'): ?>
			<?php echo $this->pagination->getListFooter(); ?>
		<?php endif; ?>

		<input type="hidden" name="option" value="com_quick2cart" />
		<input type="hidden" name="view" value="reports" />
		<input type="hidden" name="layout" value="payouts" />
		<!--
		<input type="hidden" id="controller" name="controller" value="" />
		-->
		<input type="hidden" id="task" name="task" value="" />

		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

		<?php echo JHtml::_( 'form.token' ); ?>
	</form>
</div>
