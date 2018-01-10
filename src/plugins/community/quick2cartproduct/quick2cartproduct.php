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

if (!defined('DS'))
{
	define('DS', '/');
}


require_once JPATH_ROOT .'/components/com_community/libraries/core.php';

$lang = JFactory::getLanguage();
$lang->load('plg_community_quick2cartproduct', JPATH_ADMINISTRATOR);

require_once JPATH_SITE . '/components/com_quick2cart/defines.php';

class plgCommunityQuick2cartproduct extends CApplications
{
	var $name = "Quick2cartproduct";
	var $_name	= 'quick2cartproduct';

	function onProfileDisplay()
	{
		$cache = JFactory::getCache('community');
		$callback = array($this, '_getquick2cartproductHTML');

		$content = $cache->call($callback);
		return $content;
	}

	function _getquick2cartproductHTML()
	{
		jimport('joomla.filesystem.file');

		if (JFile::exists(JPATH_SITE . '/components/com_quick2cart/quick2cart.php'))
		{
			$lang = JFactory::getLanguage();
			$lang->load('com_quick2cart', JPATH_SITE);
			$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

			if(!class_exists('comquick2cartHelper'))
			{
				JLoader::register('comquick2cartHelper', $path );
				JLoader::load('comquick2cartHelper');
			}

			// Load assets
			comquick2cartHelper::loadQuicartAssetFiles();

			$product_path = JPATH_SITE . '/components/com_quick2cart/helpers/product.php';

			if (!class_exists('productHelper'))
			{
				JLoader::register('productHelper', $product_path );
				JLoader::load('productHelper');
			}

			$params = $this->params;
			$no_of_prod = $params->get('no_of_prod','2');

			// Get profile id
			$user= CFactory::getRequestUser();
			$model =  new productHelper();
			$target_data = $model->getUserProducts($user->_userid,$no_of_prod);

			if (!empty($target_data))
			{
				$random_container = 'q2c_pc_js_my_products';
				$html = "
					<div class='" . Q2C_WRAPPER_CLASS . "' >
						<div class=''>
							<div id='q2c_pc_js_my_products'>";

								$comParams = JComponentHelper::getParams('com_quick2cart');
								$layout_to_load = $params->get('layout_to_load','flexible_layout','string');
								$pinHeight  = $params->get('fix_pin_height','200','int');
								$noOfPin_lg = $params->get('pin_for_lg','3','int');
								$noOfPin_md = $params->get('pin_for_md','3','int');
								$noOfPin_sm = $params->get('pin_for_sm','4','int');
								$noOfPin_xs = $params->get('pin_for_xs','2','int');
								$currentBSViews = $comParams->get('currentBSViews','bs2','string');

								$Fixed_pin_classes = "";

								if ($layout_to_load == "fixed_layout")
								{
									if ($currentBSViews == "bs2")
									{
										$Fixed_pin_classes = " qtc-prod-pin span" . $noOfPin_lg . " ";
									}
									else
									{
										$Fixed_pin_classes = " qtc-prod-pin col-xs-" . $noOfPin_xs . " col-sm-" . $noOfPin_sm . " col-md-" . $noOfPin_md. " col-lg-" . $noOfPin_lg . " ";
									}
								}

								foreach($target_data as $data)
								{
									$html .= "<div class='q2c_pin_item_". $random_container . $Fixed_pin_classes."'>";

									$path = JPATH_SITE . '/components/com_quick2cart/views/product/tmpl/product.php';
									ob_start();
									include($path);
									$html.= ob_get_contents();
									ob_end_clean();
									$html .= "</div>";
								}

				$html .="
						</div>
					</div>
				</div>";

				if ($layout_to_load == "flexible_layout")
				{
					ob_start();
					?>
					<?php
					// Get pin width
					$pin_width = $params->get('pin_width');

					if (empty($pin_width))
					{
						$pin_width = 170;
					}

					// Get pin padding
					$pin_padding = $params->get('pin_padding');

					if (empty($pin_padding))
					{
						$pin_padding = 7;
					}

					// Calulate columnWidth (columnWidth = pin_width+pin_padding)
					$columnWidth = $pin_width + $pin_padding;
					?>

					<style type="text/css">
						.q2c_pin_item_<?php echo $random_container;?> { width: <?php echo $pin_width . 'px'; ?> !important; }
					</style>

					<script type="text/javascript">
							var pin_container_<?php echo $random_container; ?> = 'q2c_pc_js_my_products';
							//var random_containerId = pin_container_<?php echo $random_container; ?>;
							var columnWidth_<?php echo $random_container; ?> = <?php echo $columnWidth; ?>;
							var random_container_<?php echo $random_container; ?> = '.q2c_pin_item_<?php echo $random_container;?>';
							var pin_padding_<?php echo $random_container; ?> = <?php echo $pin_padding; ?>;

							techjoomla.jQuery(document).ready(function()
							{
								techjoomla.jQuery(".joms-tab__bar a").bind("click", function(){

									// Get actual data div id
									var divID = techjoomla.jQuery(this).attr("href");

									// If q2c-wrapper class exist in this tab
									if (techjoomla.jQuery( divID + "  q2c-wrapper"))
									{
										QttPinArrange(pin_container_<?php echo $random_container; ?>, columnWidth_<?php echo $random_container; ?>, random_container_<?php echo $random_container; ?>, pin_padding_<?php echo $random_container; ?>);
										setTimeout(function() { QttPinArrange(pin_container_<?php echo $random_container; ?>, columnWidth_<?php echo $random_container; ?>, random_container_<?php echo $random_container; ?>, pin_padding_<?php echo $random_container; ?>); }, 100);

									}
								});

								QttPinArrange(pin_container_<?php echo $random_container; ?>, columnWidth_<?php echo $random_container; ?>, random_container_<?php echo $random_container; ?>, pin_padding_<?php echo $random_container; ?>);

								setTimeout(function() { QttPinArrange(pin_container_<?php echo $random_container; ?>, columnWidth_<?php echo $random_container; ?>, random_container_<?php echo $random_container; ?>, pin_padding_<?php echo $random_container; ?>); }, 100);

								setTimeout(function() { QttPinArrange(pin_container_<?php echo $random_container; ?>, columnWidth_<?php echo $random_container; ?>, random_container_<?php echo $random_container; ?>, pin_padding_<?php echo $random_container; ?>); }, 1000);

								setTimeout(function() { QttPinArrange(pin_container_<?php echo $random_container; ?>, columnWidth_<?php echo $random_container; ?>, random_container_<?php echo $random_container; ?>, pin_padding_<?php echo $random_container; ?>); }, 2000);

								setTimeout(function() { QttPinArrange(pin_container_<?php echo $random_container; ?>, columnWidth_<?php echo $random_container; ?>, random_container_<?php echo $random_container; ?>, pin_padding_<?php echo $random_container; ?>); }, 3000);

								setTimeout(function() { QttPinArrange(pin_container_<?php echo $random_container; ?>, columnWidth_<?php echo $random_container; ?>, random_container_<?php echo $random_container; ?>, pin_padding_<?php echo $random_container; ?>); }, 4000);

								setTimeout(function() { QttPinArrange(pin_container_<?php echo $random_container; ?>, columnWidth_<?php echo $random_container; ?>, random_container_<?php echo $random_container; ?>, pin_padding_<?php echo $random_container; ?>); }, 5000);

								setTimeout(function() { QttPinArrange(pin_container_<?php echo $random_container; ?>, columnWidth_<?php echo $random_container; ?>, random_container_<?php echo $random_container; ?>, pin_padding_<?php echo $random_container; ?>); }, 5000);

								setTimeout(function() { QttPinArrange(pin_container_<?php echo $random_container; ?>, columnWidth_<?php echo $random_container; ?>, random_container_<?php echo $random_container; ?>, pin_padding_<?php echo $random_container; ?>); }, 8000);

								setTimeout(function() { QttPinArrange(pin_container_<?php echo $random_container; ?>, columnWidth_<?php echo $random_container; ?>, random_container_<?php echo $random_container; ?>, pin_padding_<?php echo $random_container; ?>); }, 10000);

							});
						</script>
					<?php

					$pin_html .= ob_get_contents();
					ob_end_clean();

					$html .= $pin_html;
				}

				return $html;
			}
		}
	}
}
