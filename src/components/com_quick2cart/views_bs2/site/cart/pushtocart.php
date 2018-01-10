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
$session = JFactory::getSession();
JHtml::_('behavior.modal', 'a.modal');

// Define wrapper class
//~ if (!defined('Q2C_WRAPPER_CLASS'))
//~ {
	//~ if (JVERSION < '3.0')
	//~ {
		//~ define('Q2C_WRAPPER_CLASS', "q2c-wrapper techjoomla-bootstrap");
	//~ }
	//~ else
	//~ {
		//~ define('Q2C_WRAPPER_CLASS', "q2c-wrapper");
	//~ }
//~ }

$document = JFactory::getDocument();
$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

if (!class_exists('comquick2cartHelper'))
{
  // Require_once $path;
   JLoader::register('comquick2cartHelper', $path);
   JLoader::load('comquick2cartHelper');
}

$comquick2cartHelper = new comquick2cartHelper;
$productHelper = new productHelper;

// Load component models
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_activitystream/models');
$Quick2cartModelcart = JModelLegacy::getInstance('cart', 'Quick2CartModel');

// Load Assets which are require for quick2cart.
$comquick2cartHelper->loadQuicartAssetFiles();

$pid = $this->product_id;
$parent = $this->parent;
$stock = $this->stock;
$entered_numerics= '"' . JText::_('QTC_ENTER_NUMERICS') . '"';

//  Here if min  and max qty is not present then we  assign it to min=1 and max=999
$slab = (!empty($this->slab)) ? $this->slab : 1;
$min_qty = (!empty($this->min_quantity))?$this->min_quantity:1;
$max_qty = (!empty($this->max_quantity))?$this->max_quantity:999;
$this->product_id = $this->parent."-".$this->product_id;

require_once JPATH_SITE . '/components/com_quick2cart/defines.php';
?>
<?php
	$params = JComponentHelper::getParams('com_quick2cart');
 /*	$usestock = $params->get('usestock');
	$outofstock_allowship = $params->get('outofstock_allowship');
	$buybutton_status = 1;

	if ($usestock==1 && $outofstock_allowship==0)
	{
		if ($stock != NULL || $stock!= '')
		{
			$max_qty = min($stock,$max_qty);
		}

		// 0  and not equal to NULL
		if ($stock == 0 &&  $stock != "")
		{
			$buybutton_status = 0;
		}
		elseif ($stock==NULL)
		{
			// STOCK=NULL mean not entered or not require of stock (e-artical)
			$buybutton_status=1;
		}
	}*/
?>

<?php
if ($this->showBuyNowBtn)
{
?>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?>" >
	<div class="form-horizontal qtc_buynow" id="<?php echo $this->product_id;?>_item" style="width:auto;">
		<?php
		$discount_present=($params->get('usedisc') && isset($this->price['discount_price']) && $this->price['discount_price']!=0) ? 1 :0;

		if (empty($this->qtcExtraParam['hideOriginalPrice']))
		{
		?>
			<div class="control-group">
				<label class="control-label qtc-label-original_price"><strong><?php echo JText::_('QTC_ITEM_AMT')?></strong></label>
				<div class="controls qtc_controls_text qtc-field-original_price"><span id="<?php echo ((isset($this->price['discount_price'])) ? $this->product_id.'_price' :'');?>" >
				<?php //echo (($params->get('usedisc'))?((isset($this->price['discount_price']))  ? '<del>'.$this->price['price'].'</del>':$this->price['price']):$this->price);
				$pprice = (($discount_present==1)  ? '<del>'.$comquick2cartHelper->getFromattedPrice($this->price['price']).'</del>':$comquick2cartHelper->getFromattedPrice($this->price['price']));
					echo	$pprice;
				?></span></div>
			</div>
		<?php
		}

		if ($discount_present && empty($this->qtcExtraParam['hideDiscountPrice']))
		{ ?>
			<div class="control-group">

				<label class="control-label qtc-label-discount_price"><strong><?php echo JText::_('QTC_ITEM_DIS_AMT')?></strong></label>
				<div class="controls qtc_controls_text qtc-field-discount_price">
					<span id="<?php echo $this->product_id;?>_price" >
						<?php
							echo	$comquick2cartHelper->getFromattedPrice($this->price['discount_price']);
						?>
						</span>
					</div>
			</div>
			<?php
		} ?>
		<?php
		// Display Attributes
		if ($this->attributes && empty($this->qtcExtraParam['hideDiscountPrice']))
		{
			foreach($this->attributes as $attribute)
			{ ?>
				<div class="control-group">
					<label class="control-label qtc-label-attribute"><strong><?php echo $attribute->itemattribute_name; ?></strong></label>
					<?php
						$productHelper = new productHelper ;
						$data['itemattribute_id'] = $attribute->itemattribute_id;
						$data['fieldType'] = $attribute->attributeFieldType;
						$data['parent'] = $parent;
						$data['product_id'] = $pid;
						$data['attribute_compulsary'] = $attribute->attribute_compulsary;
						$data['attributeDetail'] = $attribute;

						//$fieldHtml = $productHelper->getAttrFieldTypeHtml($data);
						$layout = new JLayoutFile('productpage.attribute_option_display', null, array('component' => 'com_quick2cart'));
						$fieldHtml = $layout->render($data);
				?>
				<div class="controls qtc-field-attribute" >
					<?php  echo $fieldHtml; ?>
				</div>
			</div>
			<?php
			}
		}

		// Don't Show media file if you found qtcFreeDdownloads=true.
		if (!empty($this->mediaFiles))
		{
			$hideAtt = !empty($this->qtcExtraParam['hideAttributes']) ? 'qtc_hideEle' : '' ;
		?>
			<div class="control-group <?php echo $hideAtt ?>" >
				<div class="control-label qtc-label-free_download"><strong><?php echo JText::_("COM_QUICK2CART_PROD_FREE_DOWNLOAD"); ?>		</strong></div>
				<div class="controls qtc_padding_class_attributes qtc-field-free_download">
				<?php
				$productHelper = new productHelper;

				foreach($this->mediaFiles as $mediaFile)
				{
					$linkData = array();
					$linkData['linkName'] = $mediaFile['file_display_name'];
					$linkData['href'] =$productHelper->getMediaDownloadLinkHref($mediaFile['file_id']);
					$linkData['event'] = '';
					$linkData['functionName'] = '';
					$linkData['fnParam'] = '';
					echo $productHelper->showMediaDownloadLink($linkData) ."<br>";
				}
				?>
				</br>
				</div>
			</div>
		<?php
		}

		$showqty_style = "";
		$showqty = $params->get('qty_buynow',1);

		// Check whether you have to show quantity or not
		if (empty($showqty))
		{
			$showqty_style = "display:none;";
		}
		?>
		<div class="control-group" style="<?php echo $showqty_style; ?>">
			<label class="control-label qtc-label-itemcount"><strong><?php echo JText::_('QTC_ITEM_QTY'); ?></strong></label>
			<div class="controls qtc-field-itemcount">
			<?php
				$textboxid=$this->product_id."_itemcount" ;

				if (is_numeric($stock) && $stock < $max_qty )
				{
					$max_qty = $stock;
				}

				$limits=$min_qty .",".$max_qty ;
				$arg = "'" . $textboxid . "','" . $pid . "','" . $parent . "','" . $slab . "'," . $limits;
				$min_msg=JText::_('QTC_MIN_LIMIT_MSG');
				$max_msg=JText::_('QTC_MAX_LIMIT_MSG');
			?>
			<input id="<?php echo $textboxid;?>" name="<?php echo $this->product_id;?>_itemcount" class="input input-mini qtc_count" type="text" value="<?php echo $min_qty;?>"  maxlength="3" onblur="checkforalphaLimit(this,'<?php echo $pid;?>','<?php echo $parent;?>',<?php echo $slab;?>,<?php echo $limits;?>,'<?php echo $min_msg;?>','<?php echo $max_msg;?>');"  Onkeyup="checkforalpha(this,'',<?php echo $entered_numerics; ?>)">

			<span class="qtc_itemcount" >
				<input type="button" onclick="qtc_increment(<?php echo $arg;?>)"  class="qtc_icon-qtcplus">

				<input type="button" onclick="qtc_decrement(<?php echo $arg;?>)" class="qtc_icon-qtcminus">
			</span>

			<button class="btn btn-small btn-success qtc_buyBtn_style" type="button" onclick="qtc_addtocart('<?php echo $this->product_id; ?>');"><i class="<?php echo QTC_ICON_CART;?>"></i> <?php echo JText::_('QTC_ITEM_BUY');?></button>
		</div>
		</div>

		<?php
		if (empty($showqty))
		{  ?>
			<div class="controls">
				<button class="btn btn-small btn-primary qtc_buyBtn_style" type="button" onclick="qtc_addtocart('<?php echo $this->product_id; ?>');"><i class="<?php echo QTC_ICON_CART;?>"></i> <?php echo JText::_('QTC_ITEM_BUY') ;?></button>
			</div>
		<?php
		}

		// Get pop up style
		$popup_buynow = $params->get('popup_buynow', 1);

		if ($popup_buynow == 2)
		{
			$checkout='index.php?option=com_quick2cart&view=cart';
			$itemid=$comquick2cartHelper->getitemid($checkout);
			$action_link=JUri::root().substr(JRoute::_('index.php?option=com_quick2cart&view=cartcheckout&Itemid='.$itemid,false),strlen(JUri::base(true))+1);
			?>
			<div class="cart-popup" id="<?php echo $this->product_id; ?>_popup" style="display: none;">
				<div class="message"></div>
				<div class="cart_link"><a class="btn btn-success" href="<?php echo $action_link; ?>"><?php echo JText::_('COM_QUICK2CART_VIEW_CART')?></a></div>
				<i class="<?php echo QTC_ICON_REMOVE; ?> cart-popup_close" onclick="techjoomla.jQuery(this).parent().slideUp().hide();"></i>
			</div>
			<?php
		}?>

		<?php

		if(isset($this->extra_field_data) && count($this->extra_field_data))
		{ ?>
			<div>
			<?php foreach($this->extra_field_data as $f)
			{?>
				<?php if(!empty($f->value))
				{?>
					<div class="control-group" style="<?php echo $showqty_style; ?>">
						<label class="control-label qtc-label-itemcount">
							<strong><?php echo $f->label;?></strong>
						</label>

						<?php if (!is_array($f->value))
						{?>
							<div class="span2">
								<span>
									<?php echo $f->value;?>
								</span>
							</div>
						<?php
						}
						else
						{ ?>
							<?php foreach($f->value as $option)
							{ ?>
								<div class="span2">
									<span>
										<?php echo $option->options;?>
									</span>
								</div>
								<br/>
							<?php
							} ?>
						<?php
						} ?>
					</div>
				<?php
				}?>
			<?php
			} ?>
			</div>
		<?php
		}
		
		// For cck products
		if (empty($productDetailsUrl))
		{
			$item = array();
			$item['id'] = $pid;
			$item['parent'] = $this->parent;
			$item['count'] = '';
			$item['options'] = '';
			
			$prod_details = $Quick2cartModelcart->getProd($item);
			$item_id = $prod_details[0]['item_id'];
			
			if (!empty($item_id))
			{
				$productDetailsUrl = $comquick2cartHelper->getProductLink($item_id);
			}
		}

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('discounts');
		$shareButtonHtml = $dispatcher->trigger('getDiscountHtml',array($productDetailsUrl));
		echo $shareButtonHtml[0];
		?>
	</div>
</div>

<?php
}
else
{
?>
	<div class="<?php echo Q2C_WRAPPER_CLASS; ?>" >
		<div class="alert alert-warning">
		  <button type="button" class="close" data-dismiss="alert"></button>
		  <strong><?php echo JText::_('QTC_WARNING'); ?></strong><?php echo JText::_('QTC_OUT_OF_STOCK_MSG'); ?>
		</div>
	</div>
<?php
}
?>
