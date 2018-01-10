<?php defined('_JEXEC') or die('Restricted access');
$items = $this->items;
$isShippingEnabled = $this->params->get('shipping', 0);

?>
<div class="quick2cart-wrapper <?php echo Q2C_WRAPPER_CLASS; ?>">

	<form action="" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

		<?php
			$active = 'shipping';
			ob_start();
			include($this->toolbar_view_path);
			$html = ob_get_contents();
			ob_end_clean();
			echo $html;
		?>

		<legend><?php echo JText::_('COM_QUICK2CART_SHIPPING_METHODS'); ?></legend>
		<div class="alert alert-info">
			<?php echo JText::_('COM_QUICK2CART_SHIPPING_METHODS_HELP');?>
		</div>
		<?php
		// Shipping is diabled msg
		if ($isShippingEnabled == 0)
		{
			?>
			<div class="alert alert-danger">
				<?php echo JText::_('COM_QUICK2CART_U_HV_DISABLED_SHIPPING_OPTION_HELP_MSG'); ?>
			</div>
			<?php

		}?>
		<table class="  table table-striped">
			<tr>
				<td align="left" width="100%">

					<div class="filter-search btn-group pull-left">
						<input type="text" class="input-medium" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_QUICK2CART_SHIPPING_METHODS_SEARCH_FILTER'); ?>" value="<?php echo !empty($this->lists['search'])? $this->lists['search']:''; ?>" title="<?php echo JText::_('COM_QUICK2CART_SHIPPING_METHODS_SEARCH_FILTER'); ?>" />
					</div>

			<div class="btn-group pull-left">
				<button class="btn qtc-hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="<?php echo QTC_ICON_SEARCH; ?>"></i></button>
				<button class="btn qtc-hasTooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="<?php echo QTC_ICON_REMOVE; ?>"></i></button>
			</div>

	<!--
			<div>
				<input type="text" class="input-medium" name="search" id="search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo htmlspecialchars($this->lists['search']);?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
				<button class="btn btn-success" onclick="this.form.submit();">
					<?php echo JText::_('COM_QUICK2CART_FILTER_GO'); ?>
				</button>
				<button class="btn "
					onclick="document.getElementById('search').value='';this.form.submit();">
					<?php echo JText::_('COM_QUICK2CART_FILTER_RESET' ); ?>
				</button>
			</div> -->
				</td>
			</tr>

		</table>

		<table class="  table table-striped table-bordered" style="clear: both;">
			<thead>
				<tr>
					<th style="width: 5px;">
						<?php echo JText::_('COM_QUICK2CART_NUMBER'); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort',  'COM_QUICK2CART_SHIPPING_ID', 'tbl.id', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th style="text-align: left;">
						<?php echo JHtml::_('grid.sort',  'COM_QUICK2CART_SHIPPING_NAME', 'tbl.name', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th class="">
					</th>
				</tr>
			</thead>

			<tbody>
				<?php $i=0; $k=0; ?>
				<?php
				//print"<pre>"; print_r($items); die;
				foreach (@$items as $item)
				{ ?>

					<tr class='row<?php echo $k; ?>'>
						<td>
							<?php echo $i + 1; ?>
							<div style="display: none;">
							<input type="checkbox" onclick="isChecked(this.checked);" value="<?php echo $item->id; ?>" name="cid[]" id="cb<?php echo $i; ?>">
							</div>
						</td>
						<td>
							<?php echo $item->extension_id; ?>
						</td>

						<td>
							<?php echo JText::_($item->name); ?>
							<p class="help">
								<i>(<?php echo !empty($item->plugDescription) ? $item->plugDescription : ''; ?>)</i>
							</p>
						</td>

						<td>
							<?php
							$item->plugConfigLink = "index.php?option=com_quick2cart&task=shipping.getShipView&extension_id=".$item->extension_id;
							$comquick2cartHelper = new comquick2cartHelper;
							$CpItemid = $comquick2cartHelper->getItemId('index.php?option=com_quick2cart&view=vendor&layout=cp');
							$itemid = $comquick2cartHelper->getItemId($item->plugConfigLink);
							$redirect = JRoute::_($item->plugConfigLink . '&Itemid='.$itemid, false);

							?>
							<a class="btn btn-primary btn-small" target="" href="<?php echo !empty($item->plugConfigLink) ? $item->plugConfigLink : ''; ?>">
								<strong><?php echo JText::_('COM_QUICK2CART_SHIPPING_EDIT_PLUGIN_PARAMS');?></strong>
							</a>
						</td>
					</tr>
					<?php $i=$i+1; $k = (1 - $k); ?>
					<?php
				} ?>

				<?php if (!count($items)) : ?>
					<tr>
						<td colspan="10" align="center">
							<div class="alert">
							  <button type="button" class="close" data-dismiss="alert">×</button>
							 <?php echo JText::_('COM_QUICK2CART_NO_ITEMS_FOUND'); ?>
							</div>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if (JVERSION >= '3.0'): ?>
			<?php echo $this->pagination->getListFooter(); ?>
		<?php else: ?>
			<div class="pager">
				<?php echo $this->pagination->getListFooter(); ?>
			</div>
		<?php endif; ?>

		<input type="hidden" name="order_change" value="0" />
		<input type="hidden" name="id" value="" />
		<input type="hidden" name="task" id="task" value="" />
		<input type="hidden" name="boxchecked" value="" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_direction" value="<?php echo $this->lists['order_Dir']; ?>" />
		<?php echo JHtml::_('form.token' ); ?>
	</form>

</div>

