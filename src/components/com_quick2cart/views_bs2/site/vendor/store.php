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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.framework');
JHtml::_('behavior.modal');

// check for store
if (empty($this->store_id))
{
	?>
	<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
		<div>
			<div class="alert alert-error">
				<span>
					<?php echo JText::_('QTC_ILLEGAL_PARAMETARS'); ?>
				</span>
				<?php
				$qtc_back = Q2C_ICON_ARROW_RIGHT;
				?>
				<button type="button"  title="<?php echo JText::_( 'QTC_DEL' ); ?>" class="btn btn-mini btn-primary pull-right" onclick="javascript:history.back();" >
					<i class="<?php echo $qtc_back;?> <?php echo Q2C_ICON_WHITECOLOR; ?>"></i>&nbsp; <?php echo JText::_( 'QTC_BACK_BTN');?>
				</button>

			</div>
		</div>
	</div>
	<?php
	return false;
}

//load style sheet
$document = JFactory::getDocument();

// for featured and top seller product
$product_path = JPATH_SITE . '/components/com_quick2cart/helpers/product.php';

if (!class_exists('productHelper'))
{
	JLoader::register('productHelper', $product_path );
	JLoader::load('productHelper');
}

$productHelper = new productHelper();
$comquick2cartHelper = new comquick2cartHelper;
$store_id = $this->store_id;
$params = JComponentHelper::getParams('com_quick2cart');
$layout_to_load = $params->get('layout_to_load','','string');
$pinHeight  = $params->get('fix_pin_height','200','int');
$noOfPin_lg = $params->get('pin_for_lg','3','int');
?>

<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
	<form name="adminForm" id="adminForm" class="form-validate" method="post">
		<div class="row-fluid">
			<div class="span9">
				<!-- START ::for store info  -->
				<?php
				if (!empty($this->storeDetailInfo))
				{
					$sinfo = $this->storeDetailInfo;
				}
				?>

				<legend align="center">
					<?php echo JText::sprintf('QTC_WECOME_TO_STORE',$sinfo['title']) ;?>
				</legend>

				<?php
				// Show store info is category is not selected
				if (empty($this->change_prod_cat))
				{
					$view = $comquick2cartHelper->getViewpath('vendor', 'storeinfo');
					ob_start();
					include($view);
					$html = ob_get_contents();
					ob_end_clean();
					echo $html;
				}
				?>
				<!-- END ::for store info  -->

				<?php
				// featured prod and top seller should be shown only if categoty is not selected
				if (empty($this->change_prod_cat))
				{
					?>
					<!-- START ::for featured product  -->
					<?php
					// 	GETTING ALL FEATURED PRODUCT
					$params = JComponentHelper::getParams('com_quick2cart');
					$featured_limit = $params->get('featured_limit');
					$target_data = $productHelper->getAllFeturedProducts($store_id, $this->change_prod_cat, $featured_limit);

					if (!empty($target_data))
					{
						?>
						<div class="row-fluid">
							<div class="span12" >
								<legend align="center">
									<?php echo JText::_('QTC_FEATURED_PRODUCTS') ;?>
								</legend>
								<?php $random_container = 'q2c_pc_featured';?>
								<div id="q2c_pc_featured">
									<?php
									$Fixed_pin_classes = "";

									if ($layout_to_load == "fixed_layout")
									{
										$Fixed_pin_classes = " qtc-prod-pin span" . $noOfPin_lg . " ";
									}
									// REDERING FEATURED PRODUCT
									foreach($target_data as $data)
									{
										?>
										<div class='q2c_pin_item_<?php echo $random_container . $Fixed_pin_classes;?>'>
										<?php
										$path = JPATH_SITE . '/components/com_quick2cart/views/product/tmpl/product.php';

										ob_start();
										include($path);
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
										var pin_container_<?php echo $random_container; ?> = 'q2c_pc_featured'
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
					<!-- END ::for featured product  -->

					<?php
					// GETTING ALL Top  seller PRODUCT
					$topSeller_limit = $params->get('topSeller_limit');
					$target_data = $productHelper->getTopSellerProducts($store_id, $this->change_prod_cat, $topSeller_limit, "com_quick2cart");

					if (!empty($target_data))
					{
						?>
						<!-- START ::for top seller  -->
						<div class="row-fluid">
							<div class="span12" >
								<legend align="center">
									<?php echo JText::_('QTC_TOP_SELLER_STORE_PRODUCTS') ;?>
								</legend>
								<?php $random_container = 'q2c_pc_top_seller';?>
								<div id="q2c_pc_top_seller">
									<?php
									$Fixed_pin_classes = "";

									if ($layout_to_load == "fixed_layout")
									{
										$Fixed_pin_classes = " qtc-prod-pin span" . $noOfPin_lg . " ";
									}
									// REDERING Top  seller  PRODUCT
									foreach($target_data as $data)
									{
										?>
										<div class='q2c_pin_item_<?php echo $random_container . $Fixed_pin_classes;?>'>
										<?php
										$path = JPATH_SITE . DS . 'components' . DS . 'com_quick2cart' . DS . 'views' . DS . 'product' . DS . 'tmpl' . DS . 'product.php';
										ob_start();
										include($path);
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
										var pin_container_<?php echo $random_container; ?> = 'q2c_pc_top_seller'
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

				}
				// end of empty($this->change_prod_cat)
				?>

				<!-- All products frm store -->
				<?php

				if (!empty($this->allStoreProd))
				{
					?>
					<!-- START ::for top seller  -->
					<div class="row-fluid">
						<div class="span12" >
							<legend align="center">
								<?php echo JText::_('QTC_PROD_FROM_THIS_STORE_PRODUCTS') ;?>
							</legend>
							<?php $random_container = 'q2c_pc_store_products';?>
							<div id="q2c_pc_store_products">
								<?php
								$Fixed_pin_classes = "";

								if ($layout_to_load == "fixed_layout")
								{
									$Fixed_pin_classes = " qtc-prod-pin span" . $noOfPin_lg . " ";
								}
								// REDERING Top  seller  PRODUCT
								foreach($this->allStoreProd as $data)
								{
									?>
									<div class='q2c_pin_item_<?php echo $random_container . $Fixed_pin_classes;?>'>
									<?php
									$data=(array)$data;
									$path = JPATH_SITE . '/components/com_quick2cart/views/product/tmpl/product.php';
									ob_start();
									include($path);
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
								var pin_container_<?php echo $random_container; ?> = 'q2c_pc_store_products'
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

							<?php
					//if (!empty($this->change_prod_cat))
					{
						?>
						<div class="pager" style="margin:0px;">
							<?php echo $this->pagination->getPagesLinks(); ?>
						</div>
						<?php
					}
					?>
						</div>
					</div>

					<?php

				}
				?>
				<!-- END ALL PRODU FRM store -->
			</div>

			<div class="span3">
				<div class="row-fluid">
					<?php
					if (!empty($this->cats))
					{
						/*$defaultcat=$this->change_prod_cat;
						// first for "select cat ". show only if catas >= 2 cat
						if (count($this->cats) >= 2)
						{
							$default=!empty($this->itemDetail)?$this->itemDetail['category']:0;
							echo JHtml::_('select.genericlist',$this->cats,'prod_cat','class="required"  onchange="document.adminForm.submit();" ','value','text',$defaultcat);
						}*/
						//echo $this->cats;
					}
					?>
					<!-- for category list-->

					<?php
					// DECLARE STORE RELEATED PARAMS
					$qtc_catname = "store_cat";
					$qtc_view = "vendor";
					$qtc_layout = "store";
					$qtc_store_id = $this->store_id;

					//GETTING STORE RELEATED CATEGORIES
					$storeHelper = new storeHelper();
					$storeHomePage = 1;
					$viewReleated_cats = $storeHelper->getStoreCats($this->store_id, '', '', '', '', 0);
					// getStoreCats($store_id,$catid='',$onchangeSubmitForm=1,$name='store_cat',$class='',$givedropdown=1)
					$catListHeader = JText::_('COM_QUICK2CART_STOREHOME_CATLIST_HEADER');
					$view = $comquick2cartHelper->getViewpath('category', 'categorylist');
					ob_start();
					include($view);
					$html = ob_get_contents();
					ob_end_clean();
					echo $html;
					?>
				</div>
			</div>
		</div>
		<!-- FIRST ROW-FLOUID DIV-->

		<input type="hidden" name="option" value="com_quick2cart" />
		<input type="hidden" name="view" value="vendor" />
		<input type="hidden" name="task" value="refreshStoreView" />
		<input type="hidden" name="controller" value="vendor" />
	</form>
</div>
