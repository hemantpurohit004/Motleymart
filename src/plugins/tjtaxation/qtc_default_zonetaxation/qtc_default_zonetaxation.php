<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
$lang = JFactory::getLanguage();
$lang->load('plg_tjtaxation_qtc_default_zonetaxation', JPATH_ADMINISTRATOR);

/**
 * PlgPaymentPaypal
 *
 * @package     Qtc_Default_Zonetaxation
 * @subpackage  site
 * @since       1.0
 */
class PlgTjtaxationQtc_Default_Zonetaxation extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   string  &$subject  subject
	 *
	 * @param   string  $config    config
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		// Set the language in the class
		$config = JFactory::getConfig();
	}

	/**
	 * Used to Build List of taxation with respective of component Components.
	 *
	 * @param   object  $config  plugins config.
	 *
	 * @since   1.0
	 * @return   null
	 */
	public function onTP_GetInfo($config)
	{
		if (!in_array($this->_name, $config))
		{
			return;
		}

		$obj 		= new stdClass;
		$obj->name	= $this->params->get('plugin_name');
		$obj->id	= $this->_name;

		return $obj;
	}

	/**
	 * Method provides itemwise tax details.
	 *
	 * @param   object  $vars  Data needed for tax plugins.
	 *
	 * @since   1.0
	 * @return   null
	 */
	public function tj_calculateTax($vars)
	{
		$itemTaxDetails = array();
		$cartitems = $vars->cartdetails;
		$dis_totalamt = $vars->totalAmount;
		$address = $vars->addressDetails;

		// Load helper files
		$this->_TjloadTaxHelperFiles();

		$itemTaxDetails = array();
		$taxHelper = new taxHelper;

		foreach ($cartitems as $citem)
		{
			if (!empty($citem['item_id']))
			{
				$item_id = $citem['item_id'];

				// Get Current item tax details
				$itemTaxDetail = $taxHelper->getItemTax($citem['product_final_price'], $citem['item_id'], $address);

				$citemProduct_attri_ifo = array();

				if (!empty($citem['product_attributes']))
				{
					$citemProduct_attri_ifo['product_attributes'] = $citem['product_attributes'];

					if (!empty($citem['product_attribute_names']))
					{
						$citemProduct_attri_ifo['product_attribute_names'] = $citem['product_attribute_names'];
					}

					$itemTaxDetails[] = array_merge($itemTaxDetail, $citemProduct_attri_ifo);
				}
				else
				{
					$itemTaxDetails[] = $itemTaxDetail;
				}
			}
		}

		/* ItemTaxDetails will be like below
		[41] => Array // 41 is item_id
		(
			[taxdetails] => Array
				(
				 add tax detail here
				)

			[taxAmount] => 1.2
		)*/

		return $itemTaxDetails;
	}

	/**
	 * Method provides load helper file needed
	 *
	 * @since   1.0
	 * @return   null
	 */
	public function _TjloadTaxHelperFiles()
	{
		/*$path = JPATH_SITE.DS.'components'.DS.'com_quick2cart'.DS.'helper.php';
		if(!class_exists('comquick2cartHelper'))
		{
			require_once $path;
			 JLoader::register('comquick2cartHelper', $path );
			 JLoader::load('comquick2cartHelper');
		}
				 LOAD STORE HELPER
		$path = JPATH_SITE.DS.'components'.DS.'com_quick2cart'.DS.'helpers'.DS.'storeHelper.php';
		if(!class_exists('storeHelper'))
		{
			require_once $path;
			 JLoader::register('storeHelper', $path );
			 JLoader::load('storeHelper');
		}*/

		// LOAD tax helper
		$path = JPATH_SITE . '/components/com_quick2cart/helpers/taxHelper.php';

		if (!class_exists('productHelper'))
		{
			// Require_once $path;
			JLoader::register('taxHelper', $path);
			JLoader::load('taxHelper');
		}
	}
}
