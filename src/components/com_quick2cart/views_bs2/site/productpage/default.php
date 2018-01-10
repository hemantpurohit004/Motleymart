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

//JHtml::_('behavior.framework');
JHtml::_('behavior.modal');

// Load style sheet
if (empty($this->itemdetail))
{
	?>
	<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
		<div class="well well small">
			<div class="alert alert-error">
				<span><?php echo JText::_('QTC_PROD_INFO_NOT_FOUND'); ?> </span>
			</div>
		</div>
	</div>
	<?php
	return false;
}

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root(true).'/components/com_quick2cart/assets/css/swipebox.min.css');

// Here if min and max qty is not present then we assign it to min=1 and max=999
$min_qty = (! empty($this->itemdetail->min_quantity)) ? $this->itemdetail->min_quantity : 1;
$max_qty = (! empty($this->itemdetail->min_quantity)) ? $this->itemdetail->max_quantity : 999;
$slab = (! empty($this->itemdetail->slab)) ? $this->itemdetail->slab : 1 ;
$client = $this->client;

$comquick2cartHelper = new comquick2cartHelper;
$q2cbaseUrl = $comquick2cartHelper->quick2CartRoute('index.php?option=com_quick2cart&view=category&layout=default');
$productHelper = new productHelper;
require_once (JPATH_SITE . '/components/com_quick2cart/helpers/media.php');
$media = new qtc_mediaHelper();

$prodViewPath = $comquick2cartHelper->getViewpath('product', 'product');
$pepoleViewPath = $comquick2cartHelper->getViewpath('product', 'pepole');
$params = JComponentHelper::getParams('com_quick2cart');
$on_editor = $params->get('enable_editor', 0);

// Pin height for fixes pin layout
$layout_to_load = $params->get('layout_to_load','','string');
$pinHeight = $params->get('fix_pin_height', '', 'int');
$productDetailsUrl = 'index.php?option=com_quick2cart&view=productpage&layout=default&item_id=' . $this->item_id;
$productDetailsUrl = JUri::root() . substr(JRoute::_($productDetailsUrl, false), strlen(JUri::base(true)) + 1);
?>
<script>
	function showDescription(id)
	{
		techjoomla.jQuery("#promotionDesc"+id).toggle();
	}
</script>
<script type="text/javascript">
	techjoomla.jQuery(function()
	{
		var update_prodImg = function(){
			/*var old_src=techjoomla.jQuery("#qtc_prod_image").attr("src");*/
			var imgsrc=this.src;

			/*imgsrc = imgsrc.replace(/_S./i, '_M.');*/
			/*comment by vm /JSdemo/images/quick2cart/mfti_M.irt3_s385977223_S.jpg*/

			imgsrc = imgsrc.replace("_S.", "_L.");
			/*old_src = old_src.replace("_L.", "_S.");*/

			techjoomla.jQuery("#qtc_prod_image").attr("src", imgsrc);
			/*this.src=old_src;*/
		};

		techjoomla.jQuery(".qtc_prod_slider_image")
			/*.click(update_prodImg)*/
			.hover(update_prodImg);

		techjoomla.jQuery(".qtcpromotiondescription").hide();
	});

	function getlimit(limit,pid,parent,min_qtc,max_qtc)
	{
		var lim=limit.trim();

		if (lim=='min')
		{
			return min_qtc;
		}
		else/*if (lim=='max')*/
		{
			return max_qtc;
		}

		return returndata;
	}

	function qtc_increment(input_field,pid,parent,slab,min_qtc,max_qtc)
	{
		var limit=getlimit('max',pid,parent,min_qtc,max_qtc);
		var qty_el = document.getElementById(input_field);
		var qty = qty_el.value;

		if(!isNaN(qty) && qty < limit)
		{
			qty_el.value = parseInt(qty_el.value) + parseInt(slab);
		}

		return false;
	}

	function qtc_decrement(input_field,pid,parent,slab,min_qtc,max_qtc)
	{
		var limit=getlimit('min',pid,parent,min_qtc,max_qtc);
		var qty_el = document.getElementById(input_field);
		var qty = qty_el.value;

		if(!isNaN(qty) && qty > limit)
		{
			qty_el.value = parseInt(qty_el.value) - parseInt(slab);
		}

		return false;
	}

	function checkforalphaLimit(el,pid,parent,slab,min_qtc,max_qtc)
	{
		var textval=Number(el.value);
		var minlim=getlimit('min',pid,parent,min_qtc,max_qtc)

		if (textval < minlim)
		{
			alert("<?php echo JText::_('QTC_MIN_LIMIT_MSG'); ?>"+minlim);
			el.value = minlim;

			return false;
		}

		var maxlim=getlimit('max',pid,parent,min_qtc,max_qtc)

		if (textval>maxlim)
		{
			alert("<?php echo JText::_('QTC_MAX_LIMIT_MSG'); ?> "+maxlim);
			el.value =maxlim;

			return false;
		}

		var slabquantity=textval%slab;

		if(slabquantity != 0)
		{
			/* @TODO add jtext  */
			alert("Enter in multiples of " + slab);
			el.value = el.defaultValue;
			return false;
		}

		return true;
	}
</script>
<?php $itemstate=$this->itemdetail->state; ?>

<div class="<?php echo Q2C_WRAPPER_CLASS; ?>" id="qtcProductPage">
<!--
	<form action="" name="" id="" class="form-validate" method="post">
-->
		<?php
		// FOR STORE OWNER SHOW MENU TOOLBAR
		if (! empty($this->store_role_list))
		{
			$this->store_role_list = ""; // As no need of store list on product page
			$active = 'productpage';
			$view = $comquick2cartHelper->getViewpath('vendor', 'toolbar');
			ob_start();
			include ($view);
			$html = ob_get_contents();
			ob_end_clean();
			echo $html;
		}
		// END OF TOOLBAR
		?>

		<div class="row-fluid" itemscope itemtype="http://schema.org/Product">
			<div class="span12">
				<div class="span12">
					<div class="row-fluid">
						<div class="span12">
							<!-- GETTING prod NAME & fetured icon-->
							<div class="row-fluid">
								<div class="span12">
									<div>
									<?php
									if ($this->itemdetail->featured=='1')
									{
										?>
										<span class="pull-left">
											<img title="<?php echo JText::_('QTC_FEATURED_PROD')?>"
												src="<?php echo JUri::base().'components/com_quick2cart/assets/images/featured.png'; ?>"/> &nbsp;
										</span>
										<?php
									}
									?>

										<h2 itemprop="name"><?php echo $this->itemdetail->name;?></h2>
										<?php
											if (!empty($this->productRating))
											{
												echo $this->productRating;
											}?>
									</div>
									<div class="clearfix"></div>
									<hr class="hr hr-condensed"/>
								</div>
							</div>

						<?php
						$store_owner = '';
						$store_list = $this->store_list;

						if (! empty($store_list) && ! empty($this->itemdetail->store_id))
						{
							if (in_array($this->itemdetail->store_id, $store_list))
							{
								// $store_owner=$data['store_id'];
								$store_owner = 1;
							}
						}
						?>

						<!-- Show category, store name -->
						<div class="row-fluid">
							<div class="span8">
								<?php
								if (! empty($this->itemdetail->category))
								{
									?>
									<div>
										<?php
										$storeHelper = new storeHelper();

										if (! empty($this->itemdetail->category))
										{
											echo JText::_('QTC_CATEGORY') . ":&nbsp;";
										}

										echo $storeHelper->getCatHierarchyLink($this->itemdetail->category, 'com_quick2cart');
										?>
									</div>
									<?php
								}

								$multivendor_enable = $params->get('multivendor');

								if (! empty($this->itemdetail->store_id) && ! empty($multivendor_enable))
								{
									?>
										<!--  STORE NAME -->
										<div class="" itemprop="brand" itemscope
											itemtype="http://schema.org/Brand">
											<span>
												<?php
												$storeinfo = $comquick2cartHelper->getSoreInfo($this->itemdetail->store_id);
												$storeHelper = new storeHelper();
												$storeLink = $storeHelper->getStoreLink($this->itemdetail->store_id);
												$contact_ink = JUri::base() . 'index.php?option=com_quick2cart&view=vendor&layout=contactus&store_id=' .
												$this->itemdetail->store_id . '&item_id=' . $this->item_id . '&tmpl=component';
												?>
												<?php echo JText::_('QTC_STORE_NAME')?>:&nbsp;
												<a href="<?php echo $storeLink;?>">
													<span itemprop="name"><?php echo $storeinfo['title'];?></span>
												</a>
											</span> &nbsp;

											<a title="<?php echo JText::_('QTC_CONTACT_STORE_OWN')?>"
												rel="{handler: 'iframe', size: {x: window.innerWidth-350, y: window.innerHeight-150}, onClose: function(){}}"
												class="modal qtc_modal qtcModal qtcContacStoreOwner"
												href="<?php echo $contact_ink;?>">
												<i class="<?php echo Q2C_ICON_ENVELOPE; ?> <?php echo Q2C_ICON_WHITECOLOR; ?>"></i>
											</a>
										</div>
									<?php
								}
								?>
							</div>

							<?php
							// AS we are using the sepereate plugin for "jlike for quick2cart" plugin
							if (!empty($this->addLikeButtons) )
							{
								?>
								<div class="qtcJlikeBtn span4">
									<?php  echo $this->addLikeButtons; ?>
								</div>
								<?php
							}
							?>
						</div>

						<div class="row-fluid">
							<?php
							if($params->get('social_sharing'))
							{
								if($params->get('social_shring_type')=='addthis')
								{
									$publisher_id = $params->get('addthis_publishid', '');
									$add_this_js='http://s7.addthis.com/js/300/addthis_widget.js';
									$document->addScript($add_this_js);

									$add_this_share='
									<!-- AddThis Button BEGIN -->
									<div class="addthis_toolbox addthis_default_style">
									<a class="addthis_button_facebook_like" fb:like:layout="button_count" class="addthis_button" addthis:url="'.$productDetailsUrl.'"></a>
									<a class="addthis_button_google_plusone" g:plusone:size="medium" class="addthis_button" addthis:url="'.$productDetailsUrl.'"></a>
									<a class="addthis_button_tweet" class="addthis_button" addthis:url="'.$productDetailsUrl.'"></a>
									<a class="addthis_button_pinterest_pinit" class="addthis_button" addthis:url="'.$productDetailsUrl.'"></a>
									<a class="addthis_counter addthis_pill_style" class="addthis_button" addthis:url="'.$productDetailsUrl.'"></a>
									</div>
									<script type="text/javascript">
										var addthis_config ={ pubid: "'.$publisher_id.'"};
									</script>
									<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid="' . $publisher_id .'"></script>
									<!-- AddThis Button END -->' ;

									//$integrationsHelper=new integrationsHelper();
									//$integrationsHelper->loadScriptOnce($add_this_js);
									//output all social sharing buttons
									echo' <div id="rr" style="">
										<div class="social_share_container">
										<div class="social_share_container_inner">'.
											$add_this_share.
										'</div>
									</div>
									</div>
									';
								}
								else
								{
									echo '<div id="fb-root"></div>';
									$fblike_tweet = JUri::root(true) . '/components/com_quick2cart/assets/js/fblike.js';
									echo "<script type='text/javascript' src='".$fblike_tweet."'></script>";

									echo '<div class="q2c_horizontal_social_buttons">';
									echo '<div class="pull-left">
											<div class="fb-like" data-href="'.$productDetailsUrl.'" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true"></div>
										</div>';
									echo '<div class="pull-left">
											&nbsp; <div class="g-plus" data-action="share" data-annotation="bubble" data-href="'.$productDetailsUrl.'"></div>
										</div>';
									echo '<div class="pull-left">
											&nbsp; <a href="https://twitter.com/share" class="twitter-share-button" data-url="'.$productDetailsUrl.'" data-counturl="'.$productDetailsUrl.'"  data-lang="en">Tweet</a>
										</div>';
									echo '</div>
										<div class="clearfix"></div>';
								}
							}
							?>
						</div>
						<div class="clearfix"></div>
						<hr class="hr hr-condensed"/>
					</div>

				<?php
				$img_divSize =  " span6 ";
				$prod_divSize = " span6 ";

				//~ if (empty($this->itemdetail->video_link))
				//~ {
					//~ $img_divSize =  " span5 ";
					//~ $prod_divSize = " span7 ";
				//~ }

				$images = (! empty($this->itemdetail->images)) ? json_decode($this->itemdetail->images, true) : array();

				// Start OG tag support.
				$config = JFactory::getConfig();

				if (JVERSION >= '3.0')
				{
					$site_name = $config->get('sitename');
				}
				else
				{
					$site_name = $config->getvalue('config.sitename');
				}

				// https://moz.com/blog/meta-data-templates-123
				$document->addCustomTag('<meta property="og:title" content="' . $this->itemdetail->name . '" />');
				$document->addCustomTag('<meta property="og:url" content="' . $productDetailsUrl . '" />');
				$document->addCustomTag('<meta property="og:description" content="' . strip_tags($this->itemdetail->description) . '" />');
				$document->addCustomTag('<meta property="og:site_name" content="' . $site_name . '" />');

				// End OG tag support.

				?>

				<div class="row-fluid qtc_bottom">
					<!-- FOR PROD IMG -->
					<div class="<?php echo $img_divSize;?>">
						<!-- Show main image-->
						<div class="row-fluid">
							<div class='span12 qtcMarginBotton'>
								<!-- LM- Product Carousel Start-->
								<?php
								$ogImg = '';

								if (!empty($images)  && count($images) > 1)
								{
								?>
								<div class="row-fluid q2cProdCarousel">
									<!-- myCarousel div start-->
									<div id="myCarousel" class="carousel slide" data-ride="carousel" data-interval="false">

									<!-- Indicators Start-->
										<ol class="carousel-indicators">
											<?php
											$i=0;
											foreach ($images as $image)
											{
												//~ $file_name_without_extension = $media->get_media_file_name_without_extension($image);
												//~ $media_extension = $media->get_media_extension($image);
												//~ $img = $comquick2cartHelper->isValidImg($file_name_without_extension . '_S.' . $media_extension);
												//~ $img_big = $comquick2cartHelper->isValidImg($file_name_without_extension . '.' . $media_extension);
												?>
												<li data-target="#myCarousel" data-slide-to="<?php echo $i ; ?>" <?php if ($i == 0) {echo "class='active'";} ?> class="qtcCarouselIndicator"></li>
												<?php
												$i++;
											}
											?>
										</ol>
										<!-- Indicators End-->

										<!-- Wrapper for slides Start-->
										<div class="carousel-inner q2cProdImgWrapper" role="listbox">
											<?php
											$i=0;
											foreach ($images as $image)
											{
												$file_name_without_extension = $media->get_media_file_name_without_extension($image);

												$media_extension = $media->get_media_extension($image);
												//$img = $comquick2cartHelper->isValidImg($file_name_without_extension . '_S.' . $media_extension);

												$img_big = $comquick2cartHelper->isValidImg($file_name_without_extension . '_L.' . $media_extension);

												if (empty($img_big))
												{
													$img_big = JUri::base() . 'components/com_quick2cart/assets/images/default_product.jpg';
												}

												$ogImg = $img_big;
												?>

												<div class="item <?php if ($i == 0) {echo active;} ?>">
<!--
													<img itemprop="image" src="<?php echo $img_big ;?>" title="<?php echo $this->itemdetail->name; ?>" alt="<?php echo $this->itemdetail->name; ?>"  id="<?php echo 'q2cProdImg'.$i ; ?>" class="q2cProdImg <?php echo 'q2cProdImg'.$i ; ?>">
-->

													<div itemprop="image" class="qtc-prod-img q2cProdImgWrapper" title="<?php echo $this->itemdetail->name; ?>"  alt="<?php echo $this->itemdetail->name; ?>"  id="<?php echo 'q2cProdImg'.$i ; ?>" style="background-image: url('<?php echo htmlentities($img_big); ?>'); ">
													</div>
												</div>
												<?php
												$i++;
											}
											?>
										</div>
										<!-- Wrapper for slides End-->

											<!-- Left and right controls Start -->
											<!--qtcCarouselControlIcon is only for BS2 views -->
											<a class="left carousel-control qtcCarouselControlIcon"  href="#myCarousel" role="button" data-slide="prev">
												<i class="<?php echo Q2C_ICON_ARROW_CHEVRON_LEFT ; ?> qtcPreIcon icon22-white22" aria-hidden="true"></i>
											</a>
											<a class="right carousel-control qtcCarouselControlIcon" href="#myCarousel" role="button" data-slide="next">
												<i class="<?php echo Q2C_ICON_ARROW_CHEVRON_RIGH ; ?> qtcNextIcon icon22-white22" aria-hidden="true"></i>
											</a>
											<!-- Left and right controls End-->
										</div>


									</div>
									<!-- row div end-->
									<?php
									}
									else
									{
										$firstKey = 0;
										foreach ($images as $key=>$img)
										{
											$firstKey = $key;
											break;
										}

									// Only one image
									$image = $images[$firstKey];
									$file_name_without_extension = $media->get_media_file_name_without_extension($image);

									$media_extension = $media->get_media_extension($image);
									$img_big = $comquick2cartHelper->isValidImg($file_name_without_extension . '.' . $media_extension);

									if (empty($img_big))
									{
										$img_big = JUri::base() . 'components/com_quick2cart/assets/images/default_product.jpg';
									}
									$ogImg = $img_big;
									?>
									<div class="q2cProdImgWrapper">
										<div itemprop="image" class="qtc-prod-img q2cProdImgWrapper" style="background-image: url('<?php echo htmlentities($img_big); ?>'); ">
										</div>
									 </div>
									<?php
								}
								$document->addCustomTag('<meta property="og:image" content="' . $ogImg . '" />');
								?>
								<!-- if condition end-->
								<!-- LM- Product Carousel end-->
							</div>
							</div>
							<!--END ::100 X 100 image -->

						</div>
						<!-- END:: FOR PROD IMG -->

						<!-- FOR PROD name att, option etc -->
					<div class="<?php echo $prod_divSize;?> qtc_prod_blog_page">
						<!-- FOR FORM HORIZANTAL -->
						<div class="form-horizontal" id="<?php echo $this->item_id;?>_item" style="width: auto;">
							<?php
							$discount_present = ($params->get('usedisc') && isset($this->price['discount_price']) && $this->price['discount_price'] != 0) ? 1 : 0;
							?>
							<div class="control-group">
								<label class="control-label">
									<strong><?php echo JText::_('QTC_ITEM_AMT')?></strong>
								</label>
								<div class="controls qtc_controls_text">
									<span id="<?php echo ( (isset($this->price['price'])) ? $this->product_id.'_price' :'' );?>">
										<?php
										echo ($discount_present == 1) ? '<del>' . $comquick2cartHelper->getFromattedPrice($this->price['price']) . '</del>' : $comquick2cartHelper->getFromattedPrice($this->price['price']);
										?>
									</span>
								</div>
								</div>

							<?php
							if ( $discount_present)
							{
								?>
								<div class="control-group">
									<label class="control-label">
										<strong><?php echo JText::_('QTC_ITEM_DIS_AMT')?></strong>
									</label>
									<div class="controls qtc_controls_text" itemprop="offers"
										itemscope itemtype="http://schema.org/Offer">
										<span itemprop="price" id="<?php echo $this->product_id;?>_price">
											<?php echo $comquick2cartHelper->getFromattedPrice($this->price['discount_price']);  ?>
										</span>
									</div>
								</div>
								<?php
							}

							if ($this->attributes)
							{
								foreach ($this->attributes as $attribute)
								{
									?>
									<div class="control-group">
										<label class="control-label ">
											<strong><?php echo $attribute->itemattribute_name; ?></strong>
										</label>
										<?php
										$productHelper = new productHelper();
										$data['itemattribute_id'] = $attribute->itemattribute_id;
										$data['fieldType'] = $attribute->attributeFieldType;
										$data['parent'] = $this->itemdetail->parent;
										$data['product_id'] = $this->item_id;
										$data['attribute_compulsary'] = $attribute->attribute_compulsary;
										$data['attributeDetail'] = $attribute;

										// Rendor layout
										$layout = new JLayoutFile('productpage.attribute_option_display', null, array('client' => 0, 'component' => 'com_quick2cart'));
										$fieldHtml = $layout->render($data);
										?>
										<div class="controls">
											<?php echo $fieldHtml;?>
										</div>
									</div>
								<?php
								}
							}
							?>

							<!-- free download links-->
							<?php
							if (! empty($this->mediaFiles))
								{
									?>
									<div class="control-group">
										<label class="control-label ">
											<strong><?php echo JText::_( "COM_QUICK2CART_PROD_FREE_DOWNLOAD"); ?></strong>
										</label>
										<div class="controls qtc_padding_class_attributes">
											<?php
											$productHelper = new productHelper();

											foreach ($this->mediaFiles as $mediaFile)
											{
												$linkData = array();
												$linkData['linkName'] = $mediaFile['file_display_name'];
												$linkData['href'] = $productHelper->getMediaDownloadLinkHref($mediaFile['file_id']);
												$linkData['event'] = '';
												$linkData['functionName'] = '';
												$linkData['fnParam'] = '';
												echo $productHelper->showMediaDownloadLink($linkData) . "<br/>";
											}
											?>
											<br/>
										</div>
									</div>
									<?php
								}
								?>
								<!-- END free download links-->

								<?php
								$showqty_style = "";
								$showqty = $params->get('qty_buynow', 1);
								if (empty($showqty))
								{
									$showqty_style = "display:none;";
								}

								if ($this->showBuyNowBtn)
								{
									$data = $this->itemdetail;

									if (is_numeric($data->stock) && $data->stock < $data->max_quantity)
									{
										$data->max_quantity = $data->stock;
									}

									$textboxid = $data->parent . '-' . $data->product_id . "_itemcount";
									$parent = $data->parent;
									$slab = $data->slab;
									$limits = $data->min_quantity . "," . $data->max_quantity;
									$arg = "'" . $textboxid . "','" . $data->product_id . "','" . $parent . "'," . $slab . ',' . $limits;
									//$arg = "'" . $textboxid . "','" . $pid . "','" . $parent . "','" . $slab . "'," . $limits;
									$min_msg = JText::_('QTC_MIN_LIMIT_MSG');
									$max_msg = JText::_('QTC_MAX_LIMIT_MSG');
									$fun_param = $parent . '-' . $data->product_id;
									// added by aniket
									$entered_numerics = "'" . JText::_('QTC_ENTER_NUMERICS') . "'";
									?>
									<div class="control-group" >
										<label class="control-label" style="<?php echo $showqty_style; ?>" for="<?php echo $textboxid;?>">
											<strong><?php echo JText::_('QTC_ITEM_QTY'); ?></strong>
										</label>
										<div class="controls">


											<input id="<?php echo $textboxid;?>"
												name="<?php echo $data->product_id;?>_itemcount"
												class="qtc_count" type="text"
												value="<?php echo $data->min_quantity;?>"
												maxlength="3"
												onblur="checkforalphaLimit(this,'<?php echo $data->product_id;?>','<?php echo $parent;?>','<?php echo $slab;?>',<?php echo $limits;?>,'<?php echo $min_msg;?>','<?php echo $max_msg;?>');"
												Onkeyup="checkforalpha(this,'',<?php echo $entered_numerics?>)" style="<?php echo $showqty_style; ?>"/>
											<span class="qtc_itemcount">
												<input type="button"
													onclick="qtc_increment(<?php echo $arg;?>);"
													class="qtc_icon-qtcplus" style="<?php echo $showqty_style; ?>"/>
												<input type="button"
													onclick="qtc_decrement(<?php echo $arg;?>);"
													class="qtc_icon-qtcminus" style="<?php echo $showqty_style; ?>"/>
											</span>
										</div>
									</div>

									<div class="control-group" >
									 <div class="controls"><button class="btn btn-success " type="button"
												onclick="qtc_addtocart('<?php echo $fun_param; ?>');">
													<i class="<?php echo QTC_ICON_CART;?>"></i> <?php echo JText::_('QTC_ITEM_BUY');?>
											</button>
										</div>
									</div>
								<div>
									<?php
									$popup_buynow = $this->params->get('popup_buynow', 1);
									if ($popup_buynow == 2)
									{
										$checkout = 'index.php?option=com_quick2cart&view=cart';
										$itemid = $comquick2cartHelper->getitemid($checkout);
										$action_link = JUri::root() . substr(JRoute::_('index.php?option=com_quick2cart&view=cartcheckout&Itemid=' . $itemid, false), strlen(JUri::base(true)) + 1);
										?>

										<div class="cart-popup" id="<?php echo $fun_param; ?>_popup" style="display: none;">
											<div class="message"></div>
											<div class="cart_link">
												<a class="btn btn-success" href="<?php echo $action_link; ?>">
													<?php echo JText::_('COM_QUICK2CART_VIEW_CART')?>
												</a>
												<a class="btn btn-primary" href="<?php echo $q2cbaseUrl; ?>">
													<?php echo JText::_('QTC_BACK');?>
												</a>
											</div>
											<i class="<?php echo QTC_ICON_REMOVE; ?> cart-popup_close" onclick="techjoomla.jQuery(this).parent().slideUp().hide();"></i>
										</div>
										<?php
									}

										if (!empty($this->applicablePromotions))
										{
									?>
											<h4><?php echo JText::_("COM_QUICK2CART_AVAILABLE_OFFERS");?></h4>
											<div class="qtc-applicable-promotions-wrapper">
											<?php
											$flag = 1;
											$count = count($this->applicablePromotions);

											foreach ($this->applicablePromotions as $promotion)
											{
												$flag++;
												?>
												<div class="qtc-applicable-promotion-wrapper">
<!--
													<?php
													if (!empty($promotion->coupon_code))
													{
													?>
														<h5><?php echo JText::_("QTC_CUPCODE") . " : ";?><span class="qtc-applicable-promotions"><?php //echo $promotion->coupon_code;?></span></h5>
													<?php
													}
													?>
-->
													<div><strong><?php echo $promotion->name;?></strong></div>
													<div><a class="qtcHandPointer" onclick="showDescription('<?php echo $promotion->id;?>')"><?php echo JText::_("COM_QUICK2CART_PROMOTION_DETAILS");?></a></div>
													<div class="qtcpromotiondescription" id="promotionDesc<?php echo $promotion->id;?>"><?php echo $promotion->description;?></div>
													<?php
													if ($flag <= $count)
													{
													?>
														<hr>
													<?php
													}
													?>
												</div>
												<?php
											}
											?>
											</div>
											<?php
										}
									?>
								</div>
									<div class="form-group">
									<?php if ($this->getPincodeCheckAvailability[0] === true):?>
										<div class="col-xs-9 col-xs-offset-3">
											<div class="">
												<input type="text" name="pincode" id="pincode"
												placeholder="<?php echo JText::_('enter pincode'); ?>"
												class="input input-small"/>
											</div>
											<div class="">
												<a class="btn btn-default" onclick="checkPincode(<?php echo $this->item_id;?>)">Check</a>
											</div>
										</div>
										<div class="availabilitystatus"></div>
									<?php endif;?>
									</div>
									<?php
									$dispatcher = JDispatcher::getInstance();
									JPluginHelper::importPlugin('discounts');
									$shareButtonHtml = $dispatcher->trigger('getDiscountHtml',array($productDetailsUrl));
									echo $shareButtonHtml[0];
								}
								else
								{
									?>
									<div class="alert alert-warning">
										<button type="button" class="close" data-dismiss="alert"></button>
										<strong><?php echo JText::_('QTC_WARNING'); ?></strong><?php echo JText::_('QTC_OUT_OF_STOCK_MSG'); ?>
									</div>
									<?php
								}

								/*if (empty($showqty) )
								{
									?>
									<div class="controls">
										<button class="btn btn-small btn-success qtc_buyBtn_style" type="button"
											onclick="qtc_addtocart('<?php echo $fun_param; ?>');">
												<i class="<?php echo QTC_ICON_CART;?>"></i> <?php echo JText::_('QTC_ITEM_BUY');?>
										</button>
									</div>
									<?php
								}*/
							?>
							</div>
							<!-- END FORM HORIZANTAL -->
						</div>
						<!-- END:: PROD name att, option etc -->
					</div>

					<!-- FOR PROD video -->
					<?php
					if (! empty($this->itemdetail->video_link))
					{
						?>
						<div class="row-fluid">
							<div class="span12">
								<?php
								$url = (! empty($this->itemdetail->video_link)) ? ($this->itemdetail->video_link) : '';
								// $url
								// ='https://www.youtube.com/watch?v=FyvtOM8DMuA&feature=g-high-esi';
								preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url, $matches);
								$id = $matches[1];
								$srclink = "https://www.youtube.com/embed/" . $id;

								?>
								<div class="q2c-videoWrapper">
									<iframe width="100%" height="350" src="<?php echo $srclink;?>" frameborder="0" allowfullscreen>
									</iframe>
								</div>
							</div>

							<div class="clearfix"></div>
							<hr class="hr hr-condensed"/>
						</div>
						<?php
					}
					?>
					<!-- END:: FOR PROD video -->

					<!-- PROD DESCRIPTION-->
					<?php
			    	// Trigger content plugins in description of product
			    	$this->itemdetail->description = JHtml::_('content.prepare', $this->itemdetail->description);
			    	$other_details_class = (!empty($this->itemdetail->description))? '':'active';

					if (!empty($this->itemdetail->description) || !empty($this->extraData))
					{
						?>
						<div class="clearfix"></div>
						<div class="qtcClearBoth"></div>
						<div class="row-fluid">
							<div class="span12">
								<ul class="nav nav-tabs">
									<?php if (!empty($this->itemdetail->description))
									{?>
									<li class="active">
										<a href="#description_data" data-toggle="tab">
											<?php echo JText::_('COM_QUICK2CART_PROD_DESC'); ?>
										</a>
									</li>
									<?php
									}?>
									<?php if (!empty($this->extraData))
									{?>
									<li class="<?php echo $other_details_class;?>">
										<a href="#other_details_data" data-toggle="tab">
											<?php echo JText::_('COM_QUICK2CART_PROD_OTHER_DETAIL'); ?>
										</a>
									</li>
									<?php
									}?>
								</ul>
								<div class="tab-content">
									<div class="tab-pane active" id="description_data">
										<div class="clearfix"></div>
											<div>
												<?php
												if (!$on_editor)
												{
													// Do nl2br when editor is OFF
													$prodDes = (!empty($this->itemdetail->description)) ? nl2br($this->itemdetail->description) : '';
													$prodDes = str_replace('  ', '&nbsp;&nbsp;', $prodDes);
													?>
													<p><?php echo $prodDes;?></p>
												<?php
												}
												else
												{
													$prodDes = (!empty($this->itemdetail->description)) ? $this->itemdetail->description : '';?>
													<p><?php echo $prodDes;?></p>
												<?php
												}
												?>
											</div>
									</div>
									<div class="tab-pane" id="other_details_data">
										<div class="clearfix"></div>
										<?php
										if($this->extraData)
										{
											if(count($this->extraData)): ?>
												<table class="table table-striped table-bordered table-hover">
													<?php foreach($this->extraData as $f):?>
														<?php if(!empty($f->value))
														{?>
															<tr>
																<td>
																	<strong><?php echo $f->label;?></strong>
																</td>
																<td>
																	<?php if (!is_array($f->value)): ?>
																		<?php echo $f->value; ?>
																	<?php else: ?>
																		<?php foreach($f->value as $option): ?>
																				<?php echo $option->options; ?>
																			<br/>
																		<?php endforeach; ?>
																	<?php endif; ?>
																</td>
															</tr>
														<?php
														}?>
													<?php endforeach; ?>
												</table>
											<?php endif;
										}?>
									</div>
								</div>
							</div>
						</div>
						<?php
					}?>
					<!-- END :: PROD DESCRIPTION-->

				<?php
				// Create span4 DIV if any one of peopleAlsoBought & prodFromSameStore DATA FOUND
				if (! empty($this->peopleAlsoBought) || ! empty($this->prodFromSameStore))
				{
					?>
					<div class="">
						<!-- PEOPLE ALSO BOUGHT -->
						<?php
						if (! empty($this->peopleAlsoBought))
						{
							?>
							<div class="row-fluid">
								<div class="span12">

									<h4 class="sectionTitle"><?php echo JText::_('QTC_PEOPLE_ALSO_BOUGHT_PRODUCTS');?></h4>

									<?php
									$random_container = 'q2c_pc_people_also_bought';

									// We are defining pin width here itself, bcoz this will be shown on side
									$pin_width_defined = '3';
									?>

									<style type="text/css">
										.q2c_pin_item_<?php echo $random_container;?> <?php
										if ($layout_to_load == "flexible_layout")
										{
											echo "{width: 160px !important; margin-bottom: 3px !important;}";
										}
										?>
									</style>

									<div id="q2c_pc_people_also_bought">
										<?php
										//LM added variables
										$Fixed_pin_classes = "";
										$noOfPin_xs = 3;

										if ($layout_to_load == "fixed_layout")
										{
											$Fixed_pin_classes = " qtc-prod-pin span" . $noOfPin_xs . " ";
										}

										foreach ($this->peopleAlsoBought as $data)
										{
											?>
											<div class="q2c_pin_item_<?php echo $random_container . $Fixed_pin_classes; ?>">
											<?php
											// not used in product.php //$prodclass = 'span12';
											ob_start();
											include ($prodViewPath);
											$html = ob_get_contents();
											ob_end_clean();
											echo $html;
											$prodclass = '';
											?>
											</div>
											<?php
										}
										?>
									</div>
									<?php
									if ($layout_to_load == "flexible_layout")
									{
									?>
										<!-- setup pin layout script-->
										<script type="text/javascript">
											var pin_container_<?php echo $random_container; ?> = 'q2c_pc_people_also_bought';
										</script>

										<?php
										$view = $comquick2cartHelper->getViewpath('product', 'pinsetup');
										ob_start();
										include($view);
										$html = ob_get_contents();
										ob_end_clean();
										echo $html;
									}
									?>
								</div>
							</div>
							<?php
						}
						?>
						<!-- END :: PEOPLE ALSO BOUGHT -->

						<!-- OTHER PRODUCT FROM SAME STORE -->
						<?php
						if (! empty($this->prodFromSameStore))
						{
							?>
							<div class="row-fluid">
								<div class="span12 ">
									<h4 class="sectionTitle"><?php echo JText::_('QTC_PRODUCTS_FROM_SAME_STORE');?></h4>
									<?php
									$random_container = 'q2c_pc_products_from_same_store';

									// We are defining pin width here itself, bcoz this will be shown on side
									$pin_width_defined = '3';
									?>

									<style type="text/css">
										.q2c_pin_item_<?php echo $random_container;?> <?php
										if ($layout_to_load == "flexible_layout")
										{
											echo "{width: 160px !important; margin-bottom: 3px !important;}";
										}
										?>
									</style>

									<div id="q2c_pc_products_from_same_store">
										<?php

										$Fixed_pin_classes = "";
										$noOfPin_xs = 3;

										if ($layout_to_load == "fixed_layout")
										{
											$Fixed_pin_classes = " qtc-prod-pin span" . $noOfPin_xs . " ";
										}

										// REDERING
										foreach($this->prodFromSameStore as $data)
										{
										?>
											<div class='q2c_pin_item_<?php echo $random_container . $Fixed_pin_classes . " ";?>'>
											<?php
											//$prodclass = 'span12';
											ob_start();
											include($prodViewPath);
											$html = ob_get_contents();
											ob_end_clean();
											echo $html;
											//$prodclass = '';
											?>
											</div>
											<?php
										}
										?>
									</div>

									<?php
									if ($layout_to_load == "flexible_layout")
									{
									?>
										<!-- setup pin layout script-->
										<script type="text/javascript">
											var pin_container_<?php echo $random_container; ?> = 'q2c_pc_products_from_same_store';
										</script>

										<?php
										$view = $comquick2cartHelper->getViewpath('product', 'pinsetup');
										ob_start();
										include($view);
										$html = ob_get_contents();
										ob_end_clean();
										echo $html;
									}
									?>
								</div>
							</div>
							<?php
						}
						?>
						<!-- END :: OTHER PRODUCT FROM SAME STORE -->

					</div>
					<?php
				}
				// END OF SPAN3 DIV IF LOOP
				?>

				<!-- RELEATED  PRODUCT FROM SAME CAT-->
				<?php
				if ($this->prodFromCat)
				{
					$prodCatName = '';

					if (! empty($this->itemdetail->category))
					{
						$prodCatName = $comquick2cartHelper->getCatName($this->itemdetail->category);
					}
					?>
				<div class="row-fluid">
					<div class="span12 ">
					<h4 class="sectionTitle"><?php echo JText::sprintf('QTC_SIMILAR_CAT_PRODUCTS', JText::_(trim($prodCatName))); ?></h4>

					<?php
					$random_container = 'q2c_pc_similar_products';

					// We are defining pin width here itself, bcoz this will be shown on side
					//$pin_width_defined = '1';
					?>

<!--
					<style type="text/css">
						.q2c_pin_item_<?php echo $random_container;?> {width: 160px !important; margin-bottom: 3px !important;}
					</style>
-->

					<div id="q2c_pc_similar_products">
						<?php
						//LM added variables
						$noOfPin_xs = 3;
							$Fixed_pin_classes = "";

						if ($layout_to_load == "fixed_layout")
						{
							$Fixed_pin_classes = " qtc-prod-pin span" . $noOfPin_xs . " ";
							}

							foreach ($this->prodFromCat as $data)
							{
							?>
								<div class='q2c_pin_item_<?php echo $random_container . $Fixed_pin_classes . " ";?>'>
							<?php
								// @TODO condition vise mod o/p
								ob_start();
								include ($prodViewPath);
								$html = ob_get_contents();
								ob_end_clean();
								echo $html;
							?>
								</div>
							<?php
							}
								?>
							</div>
							<?php
							if ($layout_to_load == "flexible_layout")
							{
							?>
								<!-- setup pin layout script-->
								<script type="text/javascript">
									var pin_container_<?php echo $random_container; ?> = 'q2c_pc_similar_products';
								</script>

								<?php
								$view = $comquick2cartHelper->getViewpath('product', 'pinsetup');
								ob_start();
								include($view);
								$html = ob_get_contents();
								ob_end_clean();
								echo $html;
							}
							?>
						</div>
					</div>
				<?php
				}
				?>
				<!-- END :: RELEATED  PRODUCT FROM SAME CAT-->

			<div class="clearfix"></div>
			<!-- Pepole Who bought this -->
			<div class="clearfix"></div>
			<?php

			if (!empty($this->peopleWhoBought) && $this->socialintegration != 'none')
			{
				$who_bought_limit = 10; //$params->get('who_bought_limit', 8);
				$WhoBought_style = ($this->who_bought == 1) ? "display:block" : "display:none";
				?>
				<div class="" style="<?php echo $WhoBought_style; ?>">
					<h4 class="sectionTitle"><?php echo JText::_('COM_QUICK2CART_WHO_BOUGHT');?></h4>
					<ul class="thumbnails qtc_ForLiStyle">
						<?php
						$i = 0;
						$libclass = $this->comquick2cartHelper->getQtcSocialLibObj();

						foreach ($this->peopleWhoBought as $data)
						{
							$usertable  = JUser::getTable();
							$buyed_user_id = intval( $data->id );

							if($usertable->load( $buyed_user_id ))
							{
							$i ++;
							?>
							<li>
								<a href="<?php echo $libclass->getProfileUrl(JFactory::getUser($data->id));?>">
									<img title="<?php echo $data->name;?>" alt="<?php echo $data->name;?>"
										src="<?php echo $libclass->getAvatar(JFactory::getUser($data->id));?>"
										class="user-bought img-rounded q2c_image" />
								</a>
							</li>

							<?php
							}

							if ($i == $who_bought_limit)
							{
								echo "</ul>";
								echo '<a href="index.php?option=com_quick2cart&view=productpage&layout=users&itemid=' . $this->item_id . '&tmpl=component" class="modal qtc_modal" rel="{size: {x: 700, y: 500}, handler:\'iframe\'}">' . JText::_('COM_QUICK2CART_SHOW_MORE') . '</a>';
								break;
							}
						}
						?>
					</ul>
				</div>

				<?php
			}
			?>
			<!-- END :: Pepole Who bought this -->
				</div>

			</div>
			<!--span12 end -->
		</div>
		<!--row end -->

<!--
		<input type="hidden" name="option" value="com_quick2cart" />
		<input type="hidden" name="view" value="category" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="controller" value="" />
	</form>
-->

<?php
if (!empty($this->afterProductDisplay))
{
	?>
	<div >
		<?php echo $this->afterProductDisplay; ?>
	</div>
	<?php
}
?>
</div></div>
<!-- Below code is used for slide show-->
<script type="text/javascript">
	//~ techjoomla.jQuery(document).ready(function (){
		//~ techjoomla.jQuery('.swipebox').swipebox();
	//~ });
</script>

<!--jQuery and mootool conflict resolved for carousel -- start-->
<script type="text/javascript">
	/*
if (typeof techjoomla.jQuery != 'undefined') {
	(
	function($) {
		techjoomla.jQuery(document).ready(function(){
			techjoomla.jQuery('.carousel').each(function(index, element) {
				techjoomla.jQuery(this)[index].slide = null;
			});
		});
	})(techjoomla.jQuery)
};*/
	if (typeof jQuery != 'undefined') {
		(function($) {
			$(document).ready(function(){
				$('.carousel').each(function(index, element) {
					$(this)[index].slide = null;
				});
			});
		})(jQuery);
	}

</script>
<!--jQuery and mootool conflict resolved for carousel -- end-->
