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

$lang = JFactory::getLanguage();
$lang->load('com_quick2cart', JPATH_SITE);
$helperobj = new comquick2cartHelper;
$curr = $helperobj->getCurrencySession();

$path = JPATH_SITE . '/components/com_quick2cart/models/attributes.php';

if (!class_exists('quick2cartModelAttributes'))
{
	JLoader::register('quick2cartModelAttributes', $path);
	JLoader::load('quick2cartModelAttributes');
}

$quick2cartModelAttributes = new quick2cartModelAttributes;
$item_id = (is_object($data)) ? $data->item_id : $data['item_id'];
$productHelper = new productHelper;

$itemDetailObj = (object)$data;

// For attribute based stock get attribute details
$completeAttrDetail= $productHelper->getItemCompleteAttrDetail($itemDetailObj->item_id);

if (!empty($completeAttrDetail))
{
	$itemDetailObj->itemAttributes = $completeAttrDetail;
}

// Check whether product is allowd to buy or not. ( our of stock)
$qtcTeaserShowBuyNowBtn = $productHelper->isInStockProduct($itemDetailObj);
$prodAttDetails = $productHelper->getProdPriceWithDefltAttributePrice($item_id);
$it_price = $prodAttDetails; //$quick2cartModelAttributes->getCurrenciesvalue('0',$curr,'com_quick2cart',$item_id);

if (isset($it_price['itemdetail']))
{
	$item_price = $it_price['itemdetail'];
}

// STORE OWNER IS LOGGED IN
$store_owner = '';

if (!empty($store_list))
{

	if (in_array($data['store_id'], $store_list))
	{
		//$store_owner=$data['store_id'];
		$store_owner = 1;
	}
}
// class for publish and unpublish icon --- used further

if (version_compare(JVERSION, '3.0', 'lt'))
{
	$publish = QTC_ICON_CHECKMARK;
	$unpublish = QTC_ICON_REMOVE;
}
else
{
	// for joomla3.0
	$publish = QTC_ICON_CHECKMARK;
	$unpublish = QTC_ICON_REMOVE;
}

if (!empty($store_owner))
{
	$itemstate = $data['state'];
}

// GETTING ALL PRODUCTS ATTRIBURES
$attribure_option_ids = $prodAttDetails['attrDetail']['attrOptionIds'];
$tot_att_price = $prodAttDetails['attrDetail']['tot_att_price'];
$classes = !empty($classes) ? $classes : '';
$prodivsize = !empty($prodivsize) ? $prodivsize : 'default_product_div_size';
$com_params = JComponentHelper::getParams('com_quick2cart');
$img_width = $com_params->get('medium_width', 120);
// Getting item id
//$catpage_Itemid = $helperobj->getitemid('index.php?option=com_quick2cart&view=category&layout=default&item_id' . $data['item_id']);

?>

	<!-- LM removed classes q2c_pin_item_<?php echo $random_container;?> and added qtc-prod-pin col-xs- col-sm- col-md- -->
	<div class="qtc-prod-pin-inner">
		<!-- LM removed classes q2c_pin_wrapper and added qtc-prod-pininner -->

			<div class="qtc-prod-pin-header">
				<?php
				// p_link:: if product has attribute then use plink to open product page
				//$p_link = 'index.php?option=com_quick2cart&view=productpage&layout=default&item_id=' . $data['item_id'] . '&Itemid=' . $catpage_Itemid;
				$product_link = $helperobj->getProductLink($data['item_id'], 'detailsLink');

				if (isset($data['featured']))
				{
					/*if ($data['featured']=='1')
					{
						?>
						<img title="<?php echo JText::_('QTC_FEATURED_PROD');?>"
							 src="<?php echo JUri::base().'components/com_quick2cart/assets/images/featured.png'; ?>" />
						<?php
					}*/
					?>
					<div class="qtc-prod-tag-cover <?php if ($data['featured']=='1') {echo 'qtc-feat-prod-visible';} ?>">
							<span href="#" class="qtc-prod-tag" title="<?php echo  JText::_('COM_QUICK2CART_FEATURED_PRODUCT') ?>"><?php echo  JText::_('COM_QUICK2CART_FEATURED_PRODUCT') ?></span>
							<div class="clear-fix"></div>
					</div>
				<?php
				}

				// GETTIN PRODUCT TITLE LIMIT
				$prodTitleLimit = $com_params->get('ProductTitleLimit', 15);
				$prodname = $data['name'];

				//~ if (strlen($data['name']) > $prodTitleLimit)
				//~ {
					//~ $prodname = substr($data['name'], 0, $prodTitleLimit). '...';
				//~ }
				?>

				<!--LM-->

			</div>

			<?php
			$images = json_decode($data['images'], true);
			$img = JUri::base().'components/com_quick2cart/assets/images/default_product.jpg';

			if (!empty($images))
			{
				// Get first key
				$firstKey = 0;
				foreach ($images as $key=>$img)
				{
					$firstKey = $key;

					break;
				}

				require_once(JPATH_SITE . '/components/com_quick2cart/helpers/media.php');

				// create object of media helper class
				$media = new qtc_mediaHelper();
				$file_name_without_extension = $media->get_media_file_name_without_extension($images[$firstKey]);
				$media_extension = $media->get_media_extension($images[$firstKey]);
				$img = $helperobj->isValidImg($file_name_without_extension.'_L.'.$media_extension);

				if (empty($img))
				{
					$img = JUri::base().'components/com_quick2cart/assets/images/default_product.jpg';
				}
			}
			?>

			<div class="qtc-prod-img-cover <?php if (empty($qtcTeaserShowBuyNowBtn)){ echo 'poos';} ?>">
				<a title="<?php echo htmlentities($data['name']);?>" href="<?php echo $product_link; ?>">
				<?php
				if ($layout_to_load == "fixed_layout")
				{
				?>
					<div class="qtc-prod-img" style="background-image: url('<?php echo htmlentities($img) ; ?>'); height:<?php echo !empty($pinHeight) ? $pinHeight : 200;?>px">
					</div>

				<?php
				}
				else
				{
					?>
					<img class=' img-rounded q2c_pin_image'
						src="<?php echo $img;?>"
						alt="<?php echo  JText::_('QTC_IMG_NOT_FOUND') ?>"
						title="<?php echo $data['name'];?>" />
				<?php
				} ?>
				</a>
			</div>

			<div class="qtc-prod-footer-cover">
				<div class="qtc-prod-name-cover">
					<strong>
					<a title="<?php echo htmlentities($data['name']);?>" href="<?php echo $product_link; ?>" class="qtc-cv-prod-name">
						<?php echo $prodname;?>
					</a>
					</strong>
				</div>
				<div class="qtc-prod-price-cover">
					<?php
						$discount_percent = (100 - (($item_price['discount_price'] / $item_price['price']) * 100));
						$discount_present = ($com_params->get('usedisc') && isset($item_price['discount_price']) && $item_price['discount_price'] != 0) ? 1 : 0;
						$p_price = (!empty($item_price['discount_price']) && ceil($item_price['discount_price'])) ? $item_price['discount_price'] : $item_price['price'];
					?>
					<?php
						if ($discount_present == 1)
						{
						?>
								<span class="qtc-offer-price">
									<small><del><?php echo $helperobj->getFromattedPrice($item_price['price']);?></del></small>
								</span>
						<?php
						}
						?>
						<span class='qtcproductprice'>
							<strong>
								<?php echo $helperobj->getFromattedPrice($p_price + $tot_att_price);?>
							</strong>
						</span>
						<?php
						if ($discount_present == 1)
						{
						?>
						<span class='qtcproductdiscount' title= "<?php echo JText::sprintf('QTC_PERCENT_OFF',$discount_percent."%");?>">
								<b>
								<?php echo JText::_('COM_QUICK2CART_DISC_PRE'); ?> <?php echo round($discount_percent) . " %";?> <?php echo JText::_('COM_QUICK2CART_DISC_POST'); ?>
								</b>
						</span>
						<?php
						}
						?>
				</div>
<!--
				<hr class=""/>
-->
				<?php
				$textboxid = $data['parent'] . '-' . $item_id . "_itemcount";
				$parent = $data['parent'];
				$slab = !empty($data['slab']) ? $data['slab'] : 1;
				$limits = $data['min_quantity'] . "," . $data['max_quantity'];
				$arg= "'" . $textboxid."','" . $item_id."','" . $parent . "','" . $slab . "'," . $limits;

				$min_msg = JText::_('QTC_MIN_LIMIT_MSG');
				$max_msg = JText::_('QTC_MAX_LIMIT_MSG');
				$fun_param = $parent . '-' . $data['product_id'];
				//com_content-31_itemcount
				$qty_buynow = $com_params->get('qty_buynow', 1);
				$qtyDivStyle = "";
				$qtyDivSpan = "span6";
				$buyBtnSpan = "span6";
				$buyBtnClass = " pull-left ";

				if (empty($qty_buynow))
				{
					// Dont show quantity
					$qtyDivStyle = "display:none";
					$qtyDivSpan = "";
					$buyBtnSpan = "span12";
					$buyBtnClass = "";
				}
				?>

				<div class="clearfix"></div>
					<?php
					$options_str = implode(',', $attribure_option_ids);
					?>

				</div>
				<div class="qtc-prod-oos <?php if (empty($qtcTeaserShowBuyNowBtn)){ echo 'oos';} ?>">
						<span class="label label-grey "><?php echo JText::_('QTC_OUT_OF_STOCK_MSG'); ?></span></div>
				</div>
				<div class="clearfix"></div>
