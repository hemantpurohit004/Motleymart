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

require_once JPATH_SITE . '/administrator/components/com_quick2cart/models/attributesetmapping.php';
require_once JPATH_SITE . '/administrator/components/com_quick2cart/models/attributeset.php';

jimport('joomla.filesystem.file');
$input = JFactory::getApplication()->input;
$displayLayout = $params->get('module_layout', 'vertical', 'STRING');
$bsVersion = $params->get('bs_version', 'bs3', 'STRING');

if (JFile::exists(JPATH_SITE . '/components/com_quick2cart/quick2cart.php'))
{
	$path = JPATH_SITE . '/components/com_quick2cart/helper.php';

	if (!class_exists('comquick2cartHelper'))
	{
		JLoader::register('comquick2cartHelper', $path);
		JLoader::load('comquick2cartHelper');
	}

	// Load assets
	comquick2cartHelper::loadQuicartAssetFiles();
	$Comquick2cartHelper = new Comquick2cartHelper;
	$productHelper = new productHelper;
	$priceRange = $Comquick2cartHelper->getFilterPriceRange();
	$componentEnabled = $Comquick2cartHelper->isComponentEnabled('quick2cart');

	// LOAD LANGUAGE FILES
	$doc  = JFactory::getDocument();
	$lang = JFactory::getLanguage();
	$lang->load('mod_q2cfilter', JPATH_SITE);

	if ($input->get('option', '', 'string') == 'com_quick2cart' && $input->get('view', '', 'string') == 'category' && $componentEnabled == 1)
	{
		// Get cat from URL
		$prod_cat = $input->get('prod_cat', '', 'int');

		// Get all Quick2cart categorys in array
		$all_categorys = JHtml::_('category.options', 'com_quick2cart', array('filter.published' => array(1)));

		foreach ($all_categorys as $cats)
		{
			$all_cats[] = $cats->value;
		}

		$app       = JFactory::getApplication();

		// Load the JMenuSite Object
		$menu      = $app->getMenu();

		// Load the Active Menu Item as an stdClass Object
		$activeMenuItem    = $menu->getActive();

		// If product category not found in URL then assign product category according menu
		if (empty($prod_cat) && !empty($activeMenuItem))
		{
			$prod_cat = $activeMenuItem->params->get('defaultCatId', '', 'INT');
		}

		if (in_array($prod_cat, $all_cats))
		{
			// Product count for category (consider count for sub categorys)
			$productCount = $productHelper->getCategoryProductsCount(0, 1);

			$count = !empty($productCount[$prod_cat]['count'])?$productCount[$prod_cat]['count']:0;

			// If there are no products in category then dont show filter module
			if (!empty($count))
			{
				// Get aattribute set id mapped with categorys
				$attributeSetIds = array();
				$attributesetMappingModel = new Quick2cartModelAttributesetMapping;
				$attributeSetId = $attributesetMappingModel->getAttributeSetId($prod_cat);

				$attributesetModel = new Quick2cartModelAttributeset;
				$globalAttributeDetails = $attributesetModel->getAttributeListInAttributeSet($attributeSetId, 1);

				foreach ($globalAttributeDetails as $attribute)
				{
					if (!empty($attribute))
					{
						$filtersData = new stdclass;
						$display_name = $attribute['display_name'];
						$filtersData->$display_name = $attributesetModel->getOptionsListInAttribute($attribute['id'], $prod_cat);
						$filtersData->renderer = $attribute['renderer'];
						$filters[$attribute['display_name']] = $filtersData;
					}
				}

				require JModuleHelper::getLayoutPath('mod_q2cfilters', 'default_' . $bsVersion . "_" . $displayLayout);
			}
		}
	}
}
