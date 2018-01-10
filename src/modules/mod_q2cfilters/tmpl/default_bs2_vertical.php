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
JHtml::_('bootstrap.framework');
$document = JFactory::getDocument();

$path = JUri::base() . 'modules/mod_q2cfilters/assets/css/bootstrap-slider.css';
$document->addStyleSheet($path);
$document->addScript(JUri::base() . 'modules/mod_q2cfilters/assets/js/bootstrap-slider.js');
$path = JUri::base() . 'modules/mod_q2cfilters/assets/css/q2cfilters.css';
$document->addStyleSheet($path);

$document->addStyleSheet(JUri::base().'components/com_quick2cart/assets/css/quick2cart.css');

$config = JFactory::getConfig();
$path = "/".$config->get( 'sitename' )."/media/system/js/mootools-core-uncompressed.js";

// Block mootools to load for filter module
unset($document->_scripts[$path]);

$selectedFilters = explode(',',JFactory::getApplication()->input->get('attributeoption', '', 'string'));
$jinput = JFactory::getApplication();
$baseurl = $jinput->input->server->get('REQUEST_URI', '', 'STRING');

// If price filters set then set min and max price
$min_price = $jinput->input->get('min_price', '0', 'int');
if (empty($min_price))
{
	$min_price = $priceRange['min'];
}

$max_price = $jinput->input->get('max_price', '0', 'int');
if (empty($max_price))
{
	$max_price = $priceRange['max'];
}

// If min price is less than max price then replace values
if ($min_price > $max_price)
{
	$temp = $min_price;
	$min_price = $max_price;
	$max_price = $temp;
}

// Get uRL base part and parameter part
$temp =  explode ('?', $baseurl);
$siteBase =  $temp[0];
$urlArray = array();

if (!empty($temp[1]))
{
	$urlArray = explode ('&',$temp[1]);
}


if (!empty($urlArray))
{
	foreach ($urlArray as $key => $url)
	{
		if (strpos($url, 'attributeoption') !== false)
		{
			unset($urlArray[$key]);
		}
		if (strpos($url, 'min_price') !== false)
		{
			unset($urlArray[$key]);
		}
		if (strpos($url, 'max_price') !== false)
		{
			unset($urlArray[$key]);
		}
	}
}

$baseurl = $siteBase  . "?" . implode('&', $urlArray);

?>
<?php
	if (isset($filters))
	{
	?>
		<?php
			$clearBtnPosition = $params->get('apply_clear_buttons_position', '');

			if($clearBtnPosition == "above" || $clearBtnPosition == "both")
			{ ?>
			<br>
				<div class="center">
					<a class="btn btn-small btn-info qtc-mobile-filter-apply" onclick='qtcfiltersubmitmobile()'><?php echo JText::_('MOD_Q2CFILTERS_APPLY_FILTERS');?></a>
					<a class="btn btn-small btn-info" onclick='q2c_clearfilters()'><?php echo JText::_('MOD_Q2CFILTERS_CLEAR_FILTERS');?></a>
				</div>
			<?php
			}
		?>

	<?php
	}
	?>
<?php

// if max and min price are same then do not show price range filter
if ($priceRange['max'] == $priceRange['min'])
{
	$priceRange['max'] = $priceRange['max'] + 1;
}
?>
	<b class="q2c-price-slider"><?php echo JText::_('MOD_Q2CFILTERS_PRICE_FILTER_RANGE');?></b>
	<br>
	<input id="q2c-price-filter-slider" type="text" style="width:100%"class="q2c-price-slider" value="" data-slider-min="<?php echo $priceRange['min'];?>" data-slider-max="<?php echo $priceRange['max'];?>" data-slider-step="5" data-slider-value="[<?php echo $min_price;?>, <?php echo $max_price +1;?>]"/><br><br>

<script type="text/javascript">
	techjoomla.jquery = jQuery.noConflict();
	techjoomla.jquery("#q2c-price-filter-slider").slider({});
	techjoomla.jquery("#q2c-price-filter-slider").on('slideStop', function (ev) {
	qtcfiltersubmit(1);
	});

	/* var optionStr = ""; */

	function qtcfiltersubmit(returnLinkFlag)
	{
		var optionStr = "";
		// Variable to get current filter values
		var filterValues = techjoomla.jquery('#q2c-price-filter-slider').val();
		var values = new Array();

		if (typeof(filterValues) != 'undefined')
		{
			values = filterValues.split(',');
		}

		var min_price = values[0];
		var max_price = values[1];

		var redirectlink = '<?php echo $baseurl;?>';

		if (typeof(min_price) != 'undefined')
		{
			if (redirectlink.indexOf('?') === -1)
			{
				optionStr += '?min_price='+min_price;
			}
			else
			{
				optionStr += '&min_price='+min_price;
			}
		}

		if (typeof(max_price) != 'undefined')
		{
			optionStr += '&max_price='+max_price;
		}

		optionStr += '&attributeoption=';

		// Flag to add comma in filter fields
		var flag = 0;

		techjoomla.jQuery(".qtcCheck:checked").each(function()
		{
			if (Number(flag) != 0)
			{
				optionStr += ",";
			}

			flag++;

			optionStr += techjoomla.jQuery(this).val();
		});

		if (techjoomla.jQuery(window).width() >= 768)
		{
			if (returnLinkFlag == 1)
			{
				window.location = redirectlink+optionStr;
			}
			else
			{
				return redirectlink + optionStr;
			}
		}
	}

	function qtcfiltersubmitmobile()
	{
		var optionStr = qtcfiltersubmit(returnLinkFlag);
		var redirectlink = '<?php echo $baseurl;?>';

		window.location = redirectlink+optionStr;
	}

	// Functions to clear all filters
	function q2c_clearfilters()
	{
		var redirectlink = '<?php echo $baseurl;?>';

		techjoomla.jQuery(".filter-fieldCheckbox:checked").each(function()
		{
			techjoomla.jQuery(this).attr('checked', false);
		});

		window.location = redirectlink;
	}

</script>
<?php

	$class = "";
	$filterStylings = "";

	if ($params->get('module_fix_size') == 1)
	{
		$filterStylings = 'overflow-y:auto; max-height:'.$params->get("module_size").'px; overflow-x:hidden;';
	}
?>

<div class="<?php echo Q2C_WRAPPER_CLASS . ' ' . $params->get('moduleclass_sfx');?>">
	<form action="" method="post" name="filterForm" id="filterForm">
	<div id="qtcFilterWrapperDiv ">
		<?php
		if (isset($filters))
		{
			foreach ($filters as $filterName => $filter)
			{
				$filter->style = $filterStylings;
				$filter->selectedFilters = $selectedFilters;

				if (!empty($filter))
				{
			?>
					<?php
					$layout = new JLayoutFile(str_replace('.php','',$filter->renderer), $basePath = JPATH_ROOT .'/components/com_quick2cart/layouts/globalattribute/renderer');
					$fieldHtml = $layout->render($filter);

					//$fieldHtml = $productHelper->getAttrFieldTypeHtml($data);
					?>
					<div>
						<?php echo $fieldHtml;?>
					</div>
					<?php
				}
			}
			?>
		<?php
		}
		?>
	</div>
		<?php
		if (isset($filters))
		{
		?>
			<?php
				$clearBtnPosition = $params->get('apply_clear_buttons_position', '');

				if($clearBtnPosition == "above" || $clearBtnPosition == "both")
				{ ?>
				<br>
					<div class="center">
						<a class="btn btn-small btn-info qtc-mobile-filter-apply" onclick='qtcfiltersubmitmobile()'><?php echo JText::_('MOD_Q2CFILTERS_APPLY_FILTERS');?></a>
						<a class="btn btn-small btn-info" onclick='q2c_clearfilters()'><?php echo JText::_('MOD_Q2CFILTERS_CLEAR_FILTERS');?></a>
					</div>
				<?php
				}
			?>

		<?php
		}
		?>
	</form>
</div>
