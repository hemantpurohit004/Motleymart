<?php
/**
 * @package	API
 * @version 2.5
 * @author 	Nitesh Kesarkar
 * @link 	http://www.shivnerisystems.com
 * @copyright Copyright (C)2012 GNU General private License v2. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgAPIEcomm extends ApiPlugin
{
	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 */
	public function __construct(&$subject, $config)
	{
		JResponse::setHeader('Access-Control-Allow-Origin','*');
		JResponse::setHeader('Access-Control-Allow-Methods','POST');
		JResponse::setHeader('Access-Control-Max-Age','1000');
		parent::__construct($subject, $config);
		ApiResource::addIncludePath(dirname(__FILE__).'/ecomm');

		$this->setResourceAccess('ecommGetAllCategories', 'private', 'post');
		$this->setResourceAccess('ecommSearch', 'private', 'post');
		$this->setResourceAccess('ecommSignup', 'private', 'post');
		$this->setResourceAccess('ecommVerifyMobileNoAndOtp', 'private', 'post');
		$this->setResourceAccess('ecommVerifyMobileNoAndOtp', 'private', 'post');
		$this->setResourceAccess('ecommRegister', 'private', 'post');
		$this->setResourceAccess('ecommSaveAddress', 'private', 'post');
		$this->setResourceAccess('login', 'public', 'post');
		$this->setResourceAccess('login', 'public', 'get');
		$this->setResourceAccess('ecommGetPaymentMethods', 'private', 'post');
		$this->setResourceAccess('ecommGetBannerImages', 'private', 'post');
		$this->setResourceAccess('ecommGetStatesForCountry', 'private', 'post');
		$this->setResourceAccess('ecommGetHashKey', 'private', 'post');
		$this->setResourceAccess('ecommSendSms', 'private', 'post');


		$this->setResourceAccess('ecommGetCategoriesByLevel', 'private', 'post');
		$this->setResourceAccess('ecommAddToCart', 'private', 'post');
		$this->setResourceAccess('ecommGetCartDetails', 'private', 'post');
		$this->setResourceAccess('ecommGetShippingDetails', 'private', 'post');

		$this->setResourceAccess('ecommUpdateUserDetails', 'private', 'post');
		$this->setResourceAccess('ecommGetAllShopsForCategory', 'private', 'post');

		$this->setResourceAccess('ecommGetProductsForShopAndCategory', 'private', 'post');
		$this->setResourceAccess('ecommGetShopCategories', 'private', 'post');
		$this->setResourceAccess('ecommGetShopOffers', 'private', 'post');
		$this->setResourceAccess('ecommGetSingleProductDetails', 'private', 'post');
		$this->setResourceAccess('ecommDeleteFromCart', 'private', 'post');
		$this->setResourceAccess('ecommUpdateCartDetails', 'private', 'post');
		$this->setResourceAccess('ecommEmptyCart', 'private', 'post');
		$this->setResourceAccess('ecommSaveCustomerAddress', 'private', 'post');
		$this->setResourceAccess('ecommGetUserAddressList', 'private', 'post');
		$this->setResourceAccess('ecommDeleteCustomerAddress', 'private', 'post');
		$this->setResourceAccess('ecommSaveCustomerAddress', 'private', 'post');
		$this->setResourceAccess('ecommGetSingleCustomerAddressDetails', 'private', 'post');
		$this->setResourceAccess('ecommGetSingleOrderDetails', 'private', 'post');
		$this->setResourceAccess('ecommGetReorderDetails', 'private', 'post');
		$this->setResourceAccess('ecommCreateOrder', 'private', 'post');
		$this->setResourceAccess('ecommUpdateCartDetails', 'private', 'post');
		$this->setResourceAccess('ecommGetCartDetails', 'private', 'post');
		$this->setResourceAccess('ecommGetOrdersForUser', 'private', 'post');
		$this->setResourceAccess('ecommGetOtp', 'private', 'post');
		$this->setResourceAccess('ecommVerifyOtp', 'private', 'post');
		$this->setResourceAccess('getEnvironmentDetails', 'private', 'post');
		$this->setResourceAccess('ecommApplyCouponCode', 'private', 'post');
		$this->setResourceAccess('ecommCancelOrder', 'private', 'post');
		$this->setResourceAccess('ecommSendOrderNotification', 'private', 'post');
		$this->setResourceAccess('ecommSaveFeedback', 'private', 'post');
		$this->setResourceAccess('ecommGetShopsNearMe', 'private', 'post');
		$this->setResourceAccess('ecommGetSingleShopDetails', 'private', 'post');
		$this->setResourceAccess('ecommGetUserAddressListWithShopId', 'private', 'post');
		$this->setResourceAccess('ecommGetStoreTotalSale', 'private', 'post');
	}
}
