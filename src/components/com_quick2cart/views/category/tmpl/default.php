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

jimport('joomla.filesystem.file');

//JHtml::_('behavior.framework');
//JHtml::_('behavior.modal');
//JHtml::_('behavior.keepalive');
//JHtml::_('behavior.tooltip');

$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');

$categoryPage = $this->categoryPage;

// For featured and top seller product
$product_path = JPATH_SITE . '/components/com_quick2cart/helpers/product.php';

if (!class_exists('productHelper'))
{
	JLoader::register('productHelper', $product_path );
	JLoader::load('productHelper');
}

$productHelper =  new productHelper();
$comquick2cartHelper = new comquick2cartHelper;
$store_id=0;//$this->store_id;

$layout_to_load = $this->params->get('layout_to_load', 'flexible_layout', 'string');
$pinHeight = $this->params->get('fix_pin_height','200','int');
$noOfPin_lg = $this->params->get('pin_for_lg','3','int');
$noOfPin_md = $this->params->get('pin_for_md','3','int');
$noOfPin_sm = $this->params->get('pin_for_sm','4','int');
$noOfPin_xs = $this->params->get('pin_for_xs','2','int');

// Get if quick2cart model is published on position tj-filters-mod-pos
$document = JFactory::getDocument();
$renderer = $document->loadRenderer('module');
$com_params   = JComponentHelper::getParams('com_quick2cart');
$modules  = JModuleHelper::getModules($com_params->get('product_filter', 'tj-filters-mod-pos'));
?>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?> container-fluid qtc-cat-prod">
	<form name="adminForm" id="adminForm" class="form-validate" method="post">
		<?php
		$input = JFactory::getApplication()->input;
		$option = $input->get('option', '', 'STRING' );
		$storeOwner = $input->get( 'qtcStoreOwner', 0, 'INTEGER');

		if (!empty($this->store_role_list) && $storeOwner==1)
		{
			$active = 'products';
			$view = $comquick2cartHelper->getViewpath('vendor', 'toolbar');
			ob_start();
			include($view);
			$html = ob_get_contents();
			ob_end_clean();
			echo $html;
		}
		?>

		<div class="row qtc_productblog container-fluid">
			<?php
			$gridClass = "col-lg-9 col-md-9 col-sm-12 col-xs-12" ;

			if ($this->qtcShowCatStoreList == 0)
			{
				$gridClass = "col-lg-12 col-md-12 col-sm-12 col-xs-12" ;
			}
			?>

			<div class="<?php echo $gridClass; ?>">
				<div class="row">
					<div class="">
						<legend>
							<?php
							echo JText::_($this->productPageTitle);
							?>
						</legend>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-5 col-md-5 col-sm-6 col-xs-12">
						<div class="input-group">
							<!--
							Dont remove searchbox, use hide class to hide
							-->
							<input type="text"
								placeholder="<?php echo JText::_('COM_QUICK2CART_FILTER_SEARCH_DESC_PRODUCTS'); ?>"
								name="filter_search"
								id="filter_search"
								value="<?php echo $this->escape($this->searchkey); ?>"
								class="form-control"
								 />

							<span class="input-group-btn">
								<button type="button" onclick="this.form.submit();" class="btn btn-default "  title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
									<i class="<?php echo QTC_ICON_SEARCH; ?>"></i>
								</button>
<!--
								<button onclick="document.id('filter_search').value='';this.form.submit();" type="button" class="btn btn-default"  title="<?php echo JText::_('JSEARCH_FILTER_CLEAR');?>">
									<i class="<?php echo QTC_ICON_REMOVE; ?>"></i>
								</button>
-->
							</span>
						</div>
					</div>
					<?php
					if ($modules)
					{
						?>
						<i class="fa fa-filter fa-2x" aria-hidden="true" onclick="q2cShowFilter()"></i>
						<?php
					}
					?>
					<div class="col-md-offset-5 col-lg-2 col-md-2 col-sm-12 col-xs-12">
						<div class="btn-group pull-right">
							<?php
							echo JHtml::_('select.genericlist', $this->product_sorting, "sort_products", 'class="" onchange="document.adminForm.submit();" name="sort_products"', "value", "text", $this->state->get('sort_products'));
							?>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>

				<div class="clearfix"></div>
				<div class="qtcClearBoth">&nbsp;</div>

				<!--
					Code to show filters
				-->
				<div id="q2chorizontallayout" style="display:none;" class="q2c-filter-horizontal-div">
				<?php
					if ($modules)
					{
						$moduleParams = new JRegistry($modules['0']->params);
						$params   = array();

						if ($moduleParams->get('client_type') == "com_quick2cart.product")
						{
							foreach ($modules as $module)
							{
								echo $renderer->render($module, $params);
							}
						}
						else
						{
							echo JText::_('COM_QUICK2CART_NO_FILTERS');
						}
					}
				?>
				</div>

				<div class="row">
						<?php
						// GETTING ALL FEATURED PRODUCT
						$target_data = ($this->products);
						$prodivsize = "category_product_div_size";

						if (empty($target_data))
						{
							?>
								<div class="alert alert-danger">
									<span><?php echo JText::_('QTC_NO_PRODUCTS_FOUND'); ?></span>
								</div>
							<?php
						}
						else
						{
							?>
							<?php $random_container = 'q2c_pc_category';?>
							<div id="q2c_pc_category" class="q2c_pin_container"> <!-- LM Added Classes row q2c_pin_container-->
								<?php
								// REDERING FEATURED PRODUCT

								foreach($target_data as $data)
								{
									$Fixed_pin_classes = "";

									if ($layout_to_load == "fixed_layout")
									{
										$Fixed_pin_classes = " qtc-prod-pin col-xs-" . $noOfPin_xs . " col-sm-" . $noOfPin_sm . " col-md-" . $noOfPin_md. " col-lg-" . $noOfPin_lg . " ";
									}

									?>
									<div class="q2c_pin_item_<?php echo $random_container . $Fixed_pin_classes;?>">
	<!-- LM removed classes q2c_pin_item_<?php echo $random_container;?> and added qtc-prod-pin col-xs- col-sm- col-md- -->
									<?php
									// converting to array
									$data=(array)$data;
									$path=$comquick2cartHelper->getViewpath('product','product');
									$store_list= !empty($this->store_list)?$this->store_list:array();
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
								<div class="clearfix"></div>
								<div class="qtcClearBoth">&nbsp;</div>
							</div>
							<!-- setup pin layout script-->
							<?php
							if ($layout_to_load == "flexible_layout")
							{
							?>
								<script type="text/javascript">
									var pin_container_<?php echo $random_container; ?> = 'q2c_pc_category'
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
						}
						?>
				</div>
				<!-- END ::for featured product  -->
			</div>

			<?php

			if ($this->qtcShowCatStoreList == 1)
			{
			?>
			<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
				<!-- for category list-->
				<?php
				$view=$comquick2cartHelper->getViewpath('category','categorylist');
				ob_start();
				include($view);
				$html = ob_get_contents();
				ob_end_clean();
				echo $html;
				?>
				<hr class="hr hr-condensed">
				<?php
				if($this->params->get('multivendor')):?>
					<!-- for store list-->
					<?php
					$storeHelper=new storeHelper();
					$options=$storeHelper->getStoreList();
					$view=$comquick2cartHelper->getViewpath('vendor','storelist');
					ob_start();
					include($view);
					$html = ob_get_contents();
					ob_end_clean();
					echo $html;
					?>
				<?php endif;?>
			</div>
			<?php
			}
			?>
		</div>
		<!-- FIRST ROW-FLOUID DIV-->

		<?php if (JVERSION >= '3.0'): ?>
			<?php echo $this->pagination->getListFooter(); ?>
		<?php else: ?>
			<div class="pager">
				<?php echo $this->pagination->getListFooter(); ?>
			</div>
		<?php endif; ?>

		<input type="hidden" name="option" value="com_quick2cart" />
		<input type="hidden" name="view" value="category" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="controller" value="" />
	</form>
</div>
