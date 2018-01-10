<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
$data = $displayData;
$params = JComponentHelper::getParams('com_quick2cart');
?>
<?php
if (!empty($data))
{
	if ($params->get('currentBSViews') == "bs2")
	{
		$bs_classes = "span4 qtc_address_pin_wrapper ";
	}
	else
	{
		$bs_classes = "col-xs-12 col-md-6 col-lg-4 ";
	}
?>
<div class="<?php echo $bs_classes;?>qtc-address<?php echo $data->id;?>">
	<div class="qtc_address_pin">
		<div class="qtc_address_pin_margin">
			<div class="q2c_address_header">
				<div class="q2c_address_name">
				<b>
				<?php
					echo ucfirst($data->firstname) . " " . ucfirst($data->lastname);
				?>
				</b>
				</div>
				<div class="pull-right">
				<span class="" onclick="editAddress('<?php echo $data->id;?>')" title="<?php echo JText::_('QTC_EDIT');?>">
					<i class="fa fa-pencil-square-o " aria-hidden="true"></i>
				</span>
				<span class="" onclick="deleteAddress('<?php echo $data->id;?>')" title="<?php echo JText::_('QTC_DEL');?>">
				<i class="fa fa-trash-o " aria-hidden="true"></i>
				</span>
			</div>
			<div class="clearfix"></div>

			</div>
			<hr class="hr-condensed"/>
			<address>
			<!--<h3>
			<?php
			// Add address title here
			//echo JText::_('QTC_BILLIN_ADDR');
			?>
			</h3>-->
			<div class="qtc_address_div">
					<?php
						if (!empty($data->address)){
							echo "<div>". $data->address . "</div>";
						}
						if (!empty($data->land_mark)){
							echo "<div>". $data->land_mark . "</div>";
						}
						echo "<div><strong>". $data->city . "</strong></div>";
						echo "<div>";
						if (!empty($data->state_name)){
							echo $data->state_name . ", " ;
						}
						echo $data->country_name . "</div>";
						echo "<div>".$data->zipcode ."</div>";
					?>
			</div>
			</address>
			<hr class="hr-condensed" />
			<div>
				<b>
				<?php
					echo JText::_('COM_QUICK2CART_USE_ADDRESS_AS');
				?>
				</b>
			</div>
			<div>
				<?php
				if ($params->get('shipping'))
				{
					$ship_checked = (!empty($data->last_used_for_shipping))?'checked="true"':'';
				?>
				<span class="form-inline">
					<input type="checkbox" class="addressship qtcHandPointer" <?php echo $ship_checked;?> onclick="selectShip('<?php echo $data->id;?>')" id="shipping_address<?php echo $data->id;?>" name="shipping_address" value="<?php echo $data->id;?>">
					<label class="qtcHandPointer" for="shipping_address<?php echo $data->id;?>"><?php echo JText::_('COM_QUICK2CART_CUSTOMER_SHIPPING_ADDRESS');?></label>
				</span>
				<?php
				}
					$bill_checked = (!empty($data->last_used_for_billing))?'checked="true"':'';
				?>
				<span class="form-inline">
					<input type="checkbox" class="qtcHandPointer addressbill" <?php echo $bill_checked;?> onclick="selectBill('<?php echo $data->id;?>')" id="billing_address<?php echo $data->id;?>" name="billing_address" value="<?php echo $data->id;?>">
					<label class="qtcHandPointer" for="billing_address<?php echo $data->id;?>"><?php echo JText::_('COM_QUICK2CART_CUSTOMER_BILLING_ADDRESS');?></label>
				</span>
			</div>
			<br>
		</div>
	</div>
</div>
<?php
}
?>
