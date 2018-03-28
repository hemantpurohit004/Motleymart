<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Quick2cartModelcartcheckout
 *
 * @package     Com_Quick2cart
 *
 * @subpackage  site
 *
 * @since       2.2
 */
class Quick2cartModelcartcheckout extends JModelLegacy
{
    /**
     * Function to fetch user data
     *
     * @return array
     */
    public function userdata()
    {
        $params   = JComponentHelper::getParams('com_quick2cart');
        $user     = JFactory::getUser();
        $userdata = array();
        $query    = "SELECT u.*
		FROM #__kart_users as u
		WHERE  u.user_id = " . $user->id . " order by u.id DESC LIMIT 0 , 2";

        /*$query=" SELECT u.* FROM #__kart_users AS u WHERE u.user_id =".$user->id." AND
        u.id=(select max(id) from #__kart_users)";*/

        $this->_db->setQuery($query);
        $result = $this->_db->loadObjectList();

        if (!empty($result)) {
            if ($result[0]->address_type == 'BT') {
                $userdata['BT'] = $result[0];

                if ($params->get('shipping') == '1' && !empty($result[1])) {
                    $userdata['ST'] = $result[1];
                }
            } elseif ($result[1]->address_type == 'BT') {
                $userdata['BT'] = $result[1];

                if ($params->get('shipping') == '1') {
                    $userdata['ST'] = $result[0];
                }
            }
        } else {
            $row             = new stdClass;
            $row->user_email = $user->email;
            $userdata['BT']  = $row;
            $userdata['ST']  = $row;
        }

        return $userdata;
    }

    /**
     * Function to get coupon. [Called while checking the coupon from cart view]
     *
     * @param   STRING  $c_code    coupon code
     * @param   INT     $user_id   user id
     * @param   STRING  $called    called from
     * @param   STRING  $order_id  order id
     *
     * @return coupon
     */
    public function getPromoCoupon($c_code = "", $user_id = "", $called = "cart", $order_id = 0)
    {
        $path = JPATH_SITE . '/components/com_quick2cart/helpers/promotion.php';

        if (!class_exists('PromotionHelper')) {
            JLoader::register('PromotionHelper', $path);
            JLoader::load('PromotionHelper');
        }

        $PromotionHelper = new PromotionHelper;

        // GETTING CART ITEMS
        JLoader::import('cart', JPATH_SITE . '/components/com_quick2cart/models');
        $cartCheckoutModel = new Quick2cartModelcartcheckout;
        $cart              = $cartCheckoutModel->getCheckoutCartitemsDetails();

        // Get valid and all rules for promotion (rules are not validted against cart detail)
        $data['coupon_code'] = $c_code;

        // 0 then get all promotion. If 1 then get only coupon based promotions
        $data['promoType'] = 1;
        $validPromotions   = $PromotionHelper->getValidatePromotions($data);

        if (empty($validPromotions)) {
            return false;
        }

        // TO validatat, the promotions rules, Format the cart data according to promo
        $formattedCartData = $PromotionHelper->getFormattedCartDetailForPromotion($cart);

        // Rules are not validted against cart detail)
        return $PromotionHelper->getApplicablePromotions($validPromotions, $formattedCartData);
    }

    /**
     * Function to get coupon
     *
     * @param   STRING  $c_code    coupon code
     * @param   INT     $user_id   user id
     * @param   STRING  $called    called from
     * @param   STRING  $order_id  order id
     *
     * @return coupon
     */
    public function getcoupon($c_code = "", $user_id = "", $called = "cart", $order_id = 0)
    {
        $jinput = JFactory::getApplication()->input;

        if ($user_id == "") {
            $user_id = JFactory::getUser()->id;
        }

        $db = JFactory::getDbo();

        if (!$c_code) {
            $c_code = $jinput->get('coupon_code');
        }

        // MULTIVENDOR ON THEN DONT ALLOW TO APPLY GLOBAL COUPON, store coupon
        $params             = JComponentHelper::getParams('com_quick2cart');
        $multivendor_enable = $params->get('multivendor');
        $noGlobalCop        = '';

        if (!empty($multivendor_enable) & empty($order_id)) {
            // NO GLOBAL COUPON
            $noGlobalCop = ' AND (cop.`store_id` IS NOT NULL  AND cop.`store_id` <> 0) ';

            // NO STORE RELEATED COUPON
            $noGlobalCop .= ' AND (cop.`item_id` IS NOT NULL ) ';
        }

        $query = "SELECT value,val_type,store_id,
		CASE WHEN store_id IS NOT NULL
		THEN CONCAT( item_id ,',',max_use,',', max_per_user)
		ELSE item_id
		END as item_use_per_user
				FROM #__kart_coupon as cop
				WHERE
				published = 1
				AND code=" . $db->quote($db->escape($c_code)) . "
				AND	 ( (CURDATE() BETWEEN from_date AND exp_date)   OR from_date = '0000-00-00 00:00:00')
				AND (max_use  > (SELECT COUNT(api.coupon_code) FROM #__kart_orders as api
				WHERE api.coupon_code =" . $db->quote($db->escape($c_code)) . ") OR max_use=0)
				AND (max_per_user > (SELECT COUNT(api.coupon_code)
				FROM #__kart_orders as api WHERE api.coupon_code = " . $db->quote($db->escape($c_code)) . " AND api.payee_id= " . $user_id . ") OR max_per_user=0)
				AND
					CASE WHEN user_id IS NOT NULL THEN user_id LIKE '%|" . $user_id . "|%'
					ELSE 1
					END
				" . $noGlobalCop;
        $db->setQuery($query);
        $count = $db->loadObjectList();

        if (!empty($count[0]) && strpos($count[0]->item_use_per_user, '|') !== false) {
            // Coupon is product related
            $count[0]->item_id = $this->getCop_item($count[0]->item_use_per_user, $c_code, $user_id, $called, $order_id);

            if (!empty($count[0]->item_id)) {
                return $count;
            } else {
                return array();
            }
        } elseif (!empty($count[0]) && empty($count[0]->item_use_per_user)) {
            $count[0]->item_id = array();
        } elseif (!empty($count[0]) && !empty($count[0]->store_id)) {
            // Coupon is store related
            $query = "SELECT i.item_id
			FROM #__kart_items as i
			WHERE i.store_id =" . $this->_db->escape($count[0]->store_id) . " AND  i.item_id IN (";

            if ($called == "cart") {
                $Quick2cartModelcart = new Quick2cartModelcart;
                $cartid              = $Quick2cartModelcart->getCartId();
                $query .= "SELECT kc.item_id
				FROM #__kart_cartitems as kc
				WHERE kc.cart_id =" . $cartid;
            } else {
                $query .= "SELECT oi.item_id
				FROM #__kart_order_item as oi
				WHERE oi.order_id =" . $order_id;
            }

            $query .= ")";
            $this->_db->setQuery($query);
            $in_cop_store = $this->_db->loadColumn();

            if (!empty($in_cop_store)) {
                $count[0]->item_id = $in_cop_store;

                return $count;
            } else {
                return array();
            }
        }

        return $count;
    }

    /**
     * function to get coupon items
     *
     * @param   INT     $item_use_per_user  count
     * @param   STRING  $cop_code           coupon code
     * @param   INT     $user_id            user id
     * @param   INT     $called             called from
     * @param   INT     $order_id           order id
     *
     * @return null
     */
    public function getCop_item($item_use_per_user, $cop_code, $user_id, $called = "cart", $order_id = 0)
    {
        $cart_item = array();

        if (isset($item_use_per_user)) {
            $item_use_per_user_array = explode(",", $item_use_per_user);
            $countitem_id            = substr($item_use_per_user_array[0], 1, -1);

            // Fetch all the item ids from coupon
            $cop_itemids = explode("||", $countitem_id);

            if ($called == "cart") {
                $Quick2cartModelcart = new Quick2cartModelcart;
                $cartid              = $Quick2cartModelcart->getCartId();
            }

            foreach ($cop_itemids as $cop_itemid) {
                // Run a loop on all item ids on order item table
                $cart_item_id = $max_use = $max_peruser = $in_cop_store = '';

                if ($called == "cart") {
                    $query = "SELECT kc.item_id
					FROM #__kart_cartitems as kc
					WHERE kc.cart_id =" . $cartid . " AND kc.item_id LIKE '" . $cop_itemid . "' ";
                } else {
                    $query = "SELECT oi.item_id
					FROM #__kart_order_item as oi
					WHERE oi.order_id =" . $order_id . " AND oi.item_id LIKE '" . $cop_itemid . "' ";
                }

                $this->_db->setQuery($query);
                $cart_item_id = $this->_db->loadResult();

                if ($item_use_per_user_array[1] != 0) {
                    $query = "SELECT COUNT(oi.params)
					FROM #__kart_order_item as oi
					WHERE oi.params LIKE '%" . $this->_db->escape($cop_code) . "%'
					AND oi.order_id=(SELECT o.id FROM #__kart_orders as o WHERE o.id= oi.order_id AND o.status='C')";
                    $this->_db->setQuery($query);
                    $max_use = $this->_db->loadResult();
                }

                if ($item_use_per_user_array[2] != 0) {
                    $query = "SELECT COUNT(oi.params)
					FROM #__kart_order_item as oi
					WHERE oi.params LIKE '%" . $this->_db->escape($cop_code) . "%'
					AND oi.order_id=(SELECT o.id FROM #__kart_orders as o WHERE o.id= oi.order_id AND o.status='C' AND o.payee_id =" . $user_id . ")";
                    $this->_db->setQuery($query);
                    $max_peruser = $this->_db->loadResult();
                }

                $cart_item_flag = 0;

                if ($cart_item_id && $item_use_per_user_array[1] > $max_use && $item_use_per_user_array[2] > $max_peruser) {
                    $cart_item_flag = 1;
                } else {
                    $cart_item_flag = 0;
                }

                if ($cart_item_flag) {
                    $cart_item[] = $cart_item_id;
                }
            }
        }

        return $cart_item;
    }

    /**
     * Function to get country name
     *
     * @param   INT  $countryId  id
     *
     * @return country name
     */
    public function getCountryName($countryId)
    {
        $db    = JFactory::getDbo();
        $query = "SELECT `country` FROM `#__tj_country` where id=" . $countryId;
        $db->setQuery($query);
        $rows = $db->loadResult();

        return $rows;
    }

    /**
     * Function to get state name
     *
     * @param   INT  $stateId  product data
     *
     * @return state name
     */
    public function getStateName($stateId)
    {
        $db    = JFactory::getDbo();
        $query = "SELECT `region` FROM `#__tj_region` where id=" . $stateId;
        $db->setQuery($query);
        $rows = $db->loadResult();

        return $rows;
    }

    /**
     * Function to set session
     *
     * @param   ARRAY  $data  product data
     *
     * @return status
     */
    public function setsession($data)
    {
        $session = JFactory::getSession();
        $session->set('order_id', $data['order_id']);
    }

    /**
     * function to get formated data
     *
     * @param   ARRAY  $data  product data
     *
     * @return recalculated data
     */
    public function getFormated_Data($data)
    {
        $amt    = 0;
        $detail = array();
        $i      = 0;

        foreach ($data['val'] as $k => $val) {
            $detail[$i]['val'] = $val;
            $detail[$i]['amt'] = $data['amt'][$k];
            $amt += $data['amt'][$k];
            $i++;
        }

        $result['detail'] = json_encode($detail);
        $result['val']    = $amt;

        return $result;
    }

    /**
     * This function stores order details in kart_order table
     *
     * @return status
     */
    public function store()
    {
        $user                = JFactory::getUser();
        $buildadsession      = JFactory::getSession();
        $jinput              = JFactory::getApplication()->input;
        $data                = $jinput->post;
        $comquick2cartHelper = new comquick2cartHelper;
        $helperPath          = JPATH_SITE . '/components/com_quick2cart/helpers/createorder.php';
        $createOrderHelper   = $comquick2cartHelper->loadqtcClass($helperPath, "CreateOrderHelper");

        // To check if data
        $data->set('allowToPlaceOrder', 1);
        $orderId = $data->get('order_id', '', "RAW");

        $Quick2cartModelcart = new Quick2cartModelcart;
        $cart_id             = $Quick2cartModelcart->getCartId();
        $cart_itemsdata      = $Quick2cartModelcart->getCartitems();

        $addressDetails = new stdclass;

        $modelsPath                 = JPATH_SITE . '/components/com_quick2cart/models/customer_addressform.php';
        $customer_addressform_model = $comquick2cartHelper->loadqtcClass($modelsPath, "Quick2cartModelCustomer_AddressForm");

        // GET BILLING AND SHIPPING ADDRESS - Start
        if (empty($user->id)) {
            $bill                    = (object) $data->get('bill', array(), "ARRAY");
            $addressDetails->billing = (object) $createOrderHelper->reMapUserAddress($bill);

            $bill     = (array) $bill;
            $ship_chk = $data->get('ship_chk', '0', "INT");

            if (empty($ship_chk)) {
                $ship                     = (object) $data->get('ship', array(), "ARRAY");
                $addressDetails->shipping = (object) $createOrderHelper->reMapUserAddress($ship);
                $ship                     = (array) $ship;
            } else {
                $addressDetails->shipping = $addressDetails->billing;
            }
        } else {
            $shipping = $data->get('shipping_address', '', 'INT');
            $billing  = $data->get('billing_address', '', 'INT');

            if (!empty($shipping)) {
                $shipping                 = $customer_addressform_model->getAddress($shipping);
                $addressDetails->shipping = $shipping;
            }

            if (!empty($billing)) {
                $billing                 = $customer_addressform_model->getAddress($billing);
                $addressDetails->billing = $billing;
            }
        }

        // GET BILLING AND SHIPPING ADDRESS - End

        $orderData          = new stdclass;
        $orderData->userId  = (!empty($user->id)) ? $user->id : '0';
        $orderData->address = $addressDetails;

        $orderData->products_data = array();

        foreach ($cart_itemsdata as $item) {
            $itemDetail                     = array();
            $itemDetail['store_id']         = $item['store_id'];
            $itemDetail['product_id']       = $item['item_id'];
            $itemDetail['product_quantity'] = $item['qty'];

            $cartDetail = $jinput->get("cartDetail", '', "ARRAY");

            if (!empty($item['product_attributes'])) {
                $attributes = explode(",", $item['product_attributes']);
                $attributes = array_filter($attributes);

                $att_option = array();

                foreach ($attributes as $attributeOption) {
                    $attInputOption = array();

                    $attributeId = $this->getAttributeId($attributeOption);

                    if (!empty($attributeOption)) {
                        // If input option is used
                        if (!empty($cartDetail[$item['id']]['attrDetail'][$attributeId]['type'])
                            && $cartDetail[$item['id']]['attrDetail'][$attributeId]['type'] == 'Textbox') {
                            $attInputOptionType  = $cartDetail[$item['id']]['attrDetail'][$attributeId]['type'];
                            $attInputOptionValue = $cartDetail[$item['id']]['attrDetail'][$attributeId]['value'];
                            $attInputOptionId    = $attributeOption;

                            $attInputOption['value']     = $attInputOptionValue;
                            $attInputOption['option_id'] = $attInputOptionId;

                            $att_option[$attributeId] = $attInputOption;
                        } else {
                            $attributeId              = $this->getAttributeId($attributeOption);
                            $att_option[$attributeId] = $attributeOption;
                        }
                    }
                }

                $itemDetail['att_option'] = $att_option;
            }

            $orderData->products_data[] = $itemDetail;
        }

        $orderStatus = array();

        $qtc_guest_regis = $data->get('qtc_guest_regis', '', "STRING");

        if (!$user->id) {
            $user->id = 0;

            // Register a new User if Checkout Method is Register
            if (!empty($qtc_guest_regis) && $qtc_guest_regis != "guest") {
                $regdata['user_name']  = $bill['email1'];
                $regdata['user_email'] = $bill['email1'];
                JLoader::import('registration', JPATH_SITE . '/components/com_quick2cart/models');

                $Quick2cartModelregistration = new Quick2cartModelregistration;
                $mesage                      = $Quick2cartModelregistration->store($regdata);

                if ($mesage) {
                    $user   = JFactory::getUser();
                    $userid = $user->id;

                    $bill = (object) $createOrderHelper->reMapUserAddress($bill);
                    $customer_addressform_model->save($bill);
                } else {
                    $orderStatus['success']     = 0;
                    $orderStatus['success_msg'] = JText::_('ERR_CONFIG_SAV_LOGIN');
                    $orderStatus['order_id']    = 0;

                    return $orderStatus;
                }
            }
        }

        $params                  = JComponentHelper::getParams('com_quick2cart');
        $isAllowedZeroPriceOrder = $params->get('orderWithZeroPrice', 0);

        $totalOrderAmount = 0;

        if (!empty($orderData->products_data)) {
            $totalOrderAmount = $createOrderHelper->calculateOrderPrice($orderData->products_data);
        }

        // Edit from 1page ckout
        if (empty($orderId)) {
            if (empty($isAllowedZeroPriceOrder)) {
                // If FINAL orderPRICE <=0 THEN DONT ALLOW FOR ORDER
                if ($totalOrderAmount <= 0) {
                    $orderStatus['success']     = 0;
                    $orderStatus['success_msg'] = JText::_('COM_QUICK2CART_CHECKOUT_ZERO_ORDER_IS_NOT_ALLOWED');
                    $orderStatus['order_id']    = 0;

                    return $orderStatus;
                }
            }
        }

        $path = JPATH_SITE . '/components/com_quick2cart/helpers/promotion.php';

        if (!class_exists('PromotionHelper')) {
            JLoader::register('PromotionHelper', $path);
            JLoader::load('PromotionHelper');
        }

        $PromotionHelper = new PromotionHelper;

        $coupon_code = $PromotionHelper->getSessionCoupon();

        $orderData->coupon_code = (!empty($coupon_code)) ? $coupon_code : '';

        // Place order
        $orderStatus = (array) $createOrderHelper->qtc_place_order($orderData);

        // Clear the seesion coupon
        $session = JFactory::getSession();
        $session->clear("coupon");

        return $orderStatus;
    }

    /**
     * function to store order items (Currently not used in quick2cart remove after jomsocial notification fix)
     *
     * @param   INT    $insert_order_id    order id
     * @param   ARRAY  $cart_itemsdata     item data
     * @param   ARRAY  $data               item data
     * @param   ARRAY  $updateOrderstatus  status
     *
     * @since   2.2
     * @return   status
     */
    public function addSaveOrderItems($insert_order_id, $cart_itemsdata, $data, $updateOrderstatus)
    {
        $productHelper = new productHelper;

        // GET BILLING AND SHIPPING ADDRESS
        $bill                = $data->get('bill', array(), "ARRAY");
        $ship                = $data->get('ship', array(), "ARRAY");
        $comquick2cartHelper = new comquick2cartHelper;

        // Row_id= last insert id
        $data->set('order_id', $insert_order_id);

        $store_info         = array();
        $itemsTaxDetail     = $data->get('itemsTaxDetail', array(), 'ARRAY');
        $itemShipMethDetail = $data->get('itemShipMethDetail', array(), 'ARRAY');

        foreach ($cart_itemsdata as $key => $cart_items) {
            $item_id    = $cart_items['item_id'];
            $taxdetail  = '';
            $shipdetail = '';

            // Get item tax detail
            if (!empty($itemsTaxDetail[$key])) {
                // Get current item tax detail
                $taxdetail = $itemsTaxDetail[$key];
            }

            // Get item ship detail
            if (!empty($itemShipMethDetail[$key])) {
                // Get current item tax detail
                $shipdetail = $itemShipMethDetail[$key];
            }

            $items                  = new stdClass;
            $items->order_id        = $insert_order_id;
            $items->item_id         = $item_id;
            $items->variant_item_id = $cart_items['variant_item_id'];

            // Getting store id from item_id
            $items->store_id                = $comquick2cartHelper->getSoreID($cart_items['item_id']);
            $items->product_attributes      = $cart_items['product_attributes'];
            $items->product_attribute_names = $cart_items['options'];
            $items->order_item_name         = $cart_items['title'];
            $items->product_quantity        = $cart_items['qty'];

            $items->product_item_price       = $cart_items['amt'];
            $items->product_attributes_price = $cart_items['opt_amt'];

            // This field store price without cop, tax,shipp etc

            // ~ $originalProdPrice = ($items->product_item_price + $items->product_attributes_price ) * $items->product_quantity;
            // ~ $items->original_price = isset($cart_items['original_price']) ? $cart_items['original_price'] : $originalProdPrice;
            $items->original_price   = $cart_items['original_price'];
            $items->item_tax         = !empty($taxdetail['taxAmount']) ? $taxdetail['taxAmount'] : 0;
            $items->item_tax_detail  = !empty($taxdetail) ? json_encode($taxdetail) : '';
            $items->item_shipcharges = !empty($shipdetail['totalShipCost']) ? $shipdetail['totalShipCost'] : 0;

            // Discount detail
            $items->discount        = 0;
            $items->discount_detail = json_encode(array());

            if (!empty($cart_items["discount"])) {
                $items->discount = $cart_items["discount"];

                if ($cart_items['discount_detail']) {
                    $promoDetail            = $cart_items['discount_detail'];
                    $items->coupon_code     = !empty($promoDetail['coupon_code']) ? $promoDetail['coupon_code'] : '';
                    $items->discount_detail = !empty($promoDetail) ? json_encode($promoDetail) : json_encode(array());
                }
            }

            $items->item_shipDetail     = !empty($shipdetail) ? json_encode($shipdetail) : '';
            $items->product_final_price = $cart_items['tamt'] + $items->item_tax + $items->item_shipcharges - $items->discount;

            $items->params = $cart_items['params'];
            $items->cdate  = date("Y-m-d H:i:s");
            $items->mdate  = date("Y-m-d H:i:s");
            $items->status = 'P';

            if (!$this->_db->insertObject('#__kart_order_item', $items, 'order_item_id')) {
                echo $this->_db->stderr();

                return 0;
            }

            // Add entry in order_itemattributes
            $query = "Select *
			 FROM #__kart_cartitemattributes
			 WHERE cart_item_id=" . (int) $cart_items['id'];

            // Cart_item_id as id
            $this->_db->setQuery($query);
            $cartresult = $this->_db->loadAssocList();

            if (!empty($cartresult)) {
                foreach ($cartresult as $key => $cart_itemopt) {
                    $items_opt                            = new stdClass;
                    $items_opt->order_item_id             = $items->order_item_id;
                    $items_opt->itemattributeoption_id    = $cart_itemopt['itemattributeoption_id'];
                    $items_opt->orderitemattribute_name   = $cart_itemopt['cartitemattribute_name'];
                    $attopprice                           = $this->getAttrOptionPrice($cart_itemopt['itemattributeoption_id']);
                    $items_opt->orderitemattribute_price  = $attopprice;
                    $items_opt->orderitemattribute_prefix = $cart_itemopt['cartitemattribute_prefix'];

                    if (!$this->_db->insertObject('#__kart_order_itemattributes', $items_opt, 'orderitemattribute_id')) {
                        echo $this->_db->stderr();

                        return 0;
                    }
                }
            }

            $params            = JComponentHelper::getParams('com_quick2cart');
            $socialintegration = $params->get('integrate_with', 'none');
            $streamBuyProd     = $params->get('streamBuyProd', 0);

            // $libclass = new activityintegrationstream();

            if ($streamBuyProd && $socialintegration != 'none') {
                // Adding msg in stream
                $user     = JFactory::getUser();
                $action   = 'buyproduct';
                $prodLink = '<a class="" href="' . $comquick2cartHelper->getProductLink($cart_items['item_id'], 'detailsLink', 1) .
                    '">' . $cart_items['title'] . '</a>';
                $store_info[$items->store_id] = $comquick2cartHelper->getSoreInfo($items->store_id);
                $s_lk                         = 'index.php?option=com_quick2cart&view=vendor&layout=store&store_id=';
                $storeLink                    = '<a class="" href="' . JUri::root() .
                substr(JRoute::_($s_lk . $items->store_id), strlen(JUri::base(true)) + 1) . '">' . $store_info[$items->store_id]['title'] . '</a>';
                $originalMsg = JText::sprintf('QTC_ACTIVITY_BUY_PROD', $prodLink, $storeLink);
                $title       = '{actor} ' . $originalMsg;

                // According to integration create social lib class obj.
                $libclass = $comquick2cartHelper->getQtcSocialLibObj();
                $libclass->pushActivity($user->id, $act_type = '', $act_subtype = '', $originalMsg, $act_link = '', $title = '', $act_access = '');
            }

            // @VM if social activity FOR  js IS SELECTED THEN ONY SHOW
            if (0) {
                // Add to JS stream
                if (JFile::exists(JPATH_SITE . '/components/com_community/libraries/core.php')) {
                    @$comquick2cartHelper->addJSstream($user->id, $user->id, $title, '', $action, 0);
                    require_once JPATH_SITE . '/components/com_community/libraries/core.php';
                    $userid = JFactory::getUser()->id;

                    if ($userid) {
                        $u_lk     = 'index.php?option=com_community&view=profile&userid=';
                        $u_nm     = JFactory::getUser()->name;
                        $userLink = '<a class="" href="' . JUri::root() . substr(CRoute::_($u_lk . $userid), strlen(JUri::base(true)) + 1) . '">' . $u_nm . '</a>';
                    } else {
                        $userLink = $bill['email1'];
                    }

                    // Get connected Users of logged in user
                    $jsuser         = CFactory::getUser($userid);
                    $connections_aa = $jsuser->getFriendIds();

                    if (!empty($connections_aa)) {
                        foreach ($connections_aa as $connections) {
                            $notification_subject = JText::sprintf('QTC_NOTIFIY_BUY_PROD_FRN', $userLink, $prodLink);
                            @$comquick2cartHelper->addJSnotify($userid, $connections, $notification_subject, 'notif_system_messaging', '0', '');
                        }
                    }

                    $groupIDs = explode(",", $jsuser->_groups);

                    if (empty($groupIDs)) {
                        $query = "SELECT groupid FROM #__community_groups_members " .
                            "WHERE memberid=" . $userid;
                        $this->_db->setQuery($query);
                        $groupIDs = $this->_db->loadColumn();
                    }

                    if (!empty($groupIDs)) {
                        foreach ($groupIDs as $groupID) {
                            if (!empty($groupID)) {
                                $query = "SELECT name FROM #__community_groups " .
                                    " WHERE id=" . $groupID;
                                $this->_db->setQuery($query);
                                $groupName = $this->_db->loadResult();
                                $query     = "SELECT memberid " .
                                    "FROM #__community_groups_members " .
                                    "WHERE groupid=" . $groupID . " AND approved=1 AND memberid<>" . $userid . "";
                                $this->_db->setQuery($query);
                                $group_ids = $this->_db->loadColumn();

                                if (!empty($group_ids)) {
                                    foreach ($group_ids as $group_id) {
                                        $notification_subject = JText::sprintf('QTC_NOTIFIY_BUY_PROD_GRP', $userLink, $groupName, $prodLink);
                                        $comquick2cartHelper->addJSnotify($userid, $group_id, $notification_subject, 'notif_system_messaging', '0', '');
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // REMOVED JS CODE
            /* end add to JS stream*/

            // ENd of cart item for each
        }

        /* For JS notification is sent */

        /*if (!empty($storeLink) && JFile::exists(JPATH_SITE . '/components/com_community/libraries/core.php'))
    {
    require_once JPATH_SITE . '/components/com_community/libraries/core.php';

    $commented_by_userid = JFactory::getUser()->id;

    if ($commented_by_userid)
    {
    $u_lnk = "index.php?option=com_community&view=profile&userid=";
    $u_nm = JFactory::getUser()->name;
    $base = JUri::root();
    $userLink = '<a class="" href="' . $base . substr(CRoute::_($u_lnk . $commented_by_userid), strlen(JUri::base(true)) + 1) . '">' . $u_nm . '</a>';
    }
    else
    {
    $userLink = $bill['email1'];
    }

    foreach ($store_info as $store_id => $storeinfo)
    {
    $s_lnk = "index.php?option=com_quick2cart&view=vendor&layout=store&store_id=";
    $s_tit = $storeinfo['title'];
    $storeLink = '<a class="" href="' . JUri::root() . substr(JRoute::_($s_lnk . $store_id), strlen(JUri::base(true)) + 1) . '">' . $s_tit . '</a>';

    $notification_subject = JText::sprintf('QTC_NOTIFIY_BUY_STORE', $userLink, $storeLink);
    @$comquick2cartHelper->addJSnotify($commented_by_userid, $storeinfo['owner'], $notification_subject, 'notif_system_messaging', '0', '');
    }
    }*/
    }

    /**
     * Gives country list.
     *
     * @since   2.2
     * @return   countryList
     */
    public function getCountry()
    {
        /*
        $db = JFactory::getDbo();
        $query="SELECT id,`country` FROM `#__tj_country` where com_quick2cart=1  ORDER BY ordering";
        $db->setQuery($query);
        $rows = $db->loadObjectList();*/

        require_once JPATH_SITE . '/components/com_tjfields/helpers/geo.php';
        $tjGeoHelper = TjGeoHelper::getInstance('TjGeoHelper');
        $rows        = (array) $tjGeoHelper->getCountryList('com_quick2cart');

        return $rows;
    }

    /**
     * function to get user state
     *
     * @param   integer  $country_id  country id
     *
     * @since   2.2
     * @return   user state
     */
    public function getuserState($country_id)
    {
        if (!empty($country_id)) {
            /*$db = JFactory::getDbo();
            $query="SELECT r.id,r.region FROM #__tj_region AS r LEFT JOIN #__tj_country as c
            ON r.country_id=c.id where c.id=" . $country_id;
            $db->setQuery($query);
            $rows = $db->loadAssocList();*/

            require_once JPATH_SITE . '/components/com_tjfields/helpers/geo.php';
            $tjGeoHelper = TjGeoHelper::getInstance('TjGeoHelper');
            $rows        = (array) $tjGeoHelper->getRegionList($country_id, 'com_quick2cart');

            return $rows;
        }
    }

    /**
     * Take itemattributeoption_id and fetch price according to currency
     *
     * @param   integer  $attop_id  attribute optio id
     *
     * @since   2.2
     * @return   returns the applicatble tax charges
     */
    public function getAttrOptionPrice($attop_id)
    {
        if ($attop_id) {
            $db                  = JFactory::getDbo();
            $comquick2cartHelper = new comquick2cartHelper;
            $currency            = $comquick2cartHelper->getCurrencySession();
            $query               = "SELECT price FROM `#__kart_option_currency` WHERE itemattributeoption_id= " . (int) $attop_id . " AND currency='$currency'";

            $db->setQuery($query);
            $result = $db->loadResult();

            return $result;
        }
    }

    /**
     * Gives applicable tax charges.
     *
     * @param   integer  $dis_totalamt  cart subtotal (after discounted amount )
     * @param   object   $vars          object with cartdetail,billing address and shipping adress details.
     *
     * @since   2.2
     * @return   returns the applicatble tax charges
     */
    public function afterTaxPrice($dis_totalamt, $vars)
    {
        $jinput = JFactory::getApplication()->input;

        // $post = $jinput->post;
        $params = JComponentHelper::getParams('com_quick2cart');

        // @TODO SET ITEM LEVEL AS DEF
        $shippingMode = $params->get('shippingMode', 'itemLevel');

        if ($shippingMode == "orderLeval") {
            $dispatcher = JDispatcher::getInstance();
            JPluginHelper::importPlugin('qtctax');
            $taxresults = $dispatcher->trigger('addTax', array($dis_totalamt, $vars));

            if (!empty($taxresults[0]['charges']) && is_numeric($taxresults[0]['charges'])) {
                $firstResult = $taxresults[0];
                $charges     = (float) $firstResult['charges'];

                $detail = json_encode($firstResult);

                $taxChargesDetails                    = new stdclass;
                $taxChargesDetails->charges           = $charges;
                $taxChargesDetails->order_tax_details = $detail;

                // $post->set("order_tax_details", $detail);

                return $taxChargesDetails;
            }
        } else {
            // GET BILLING AND SHIPPING ADDRESS
            $address = new stdClass;

            if (!empty($vars->billing_address)) {
                $address->billing_address = $vars->billing_address;
            }

            if (!empty($vars->shipping_address)) {
                $address->shipping_address = $vars->shipping_address;
            }

            $address->ship_chk = $vars->ship_chk;

            $totalTax                  = 0;
            $taxobject                 = new stdClass;
            $taxobject->totalAmount    = $dis_totalamt;
            $taxobject->cartdetails    = $vars->cartItemDetail;
            $taxobject->addressDetails = $address;

            $taxHelper  = new taxHelper;
            $taxresults = array();

            JPluginHelper::importPlugin('tjtaxation');
            $dispatcher = JDispatcher::getInstance();
            $taxresults = $dispatcher->trigger('tj_calculateTax', array($taxobject));

            $itemWiseTaxDetail = array();

            if (!empty($taxresults)) {
                $itemWiseTaxDetail = $taxresults[0];
            }

            if (!empty($itemWiseTaxDetail)) {
                // To set tax detail against order item row entry
                // $post->set('itemsTaxDetail', $itemWiseTaxDetail);

                // Get total of all item taxes
                foreach ($itemWiseTaxDetail as $prodTax) {
                    if (!empty($prodTax)) {
                        foreach ($prodTax['taxdetails'] as $prodVariantTax) {
                            if (!empty($prodVariantTax['amount'])) {
                                $totalTax += $prodVariantTax['amount'];
                            }

                            if (!empty($prodVariantTax['taxAmount'])) {
                                $totalTax += $prodVariantTax['taxAmount'];
                            }
                        }
                    }
                }

                $taxChargesDetails                 = new stdclass;
                $taxChargesDetails->charges        = $totalTax;
                $taxChargesDetails->orderTax       = $totalTax;
                $taxChargesDetails->itemsTaxDetail = $itemWiseTaxDetail;

                return $taxChargesDetails;
            }
        }
    }

    /**
     * This function updates coupon item
     *
     * @param   STRING  $coupon     coupon code
     * @param   INT     $cart_item  cart item
     * @param   STRING  $called     called
     * @param   INT     $order_id   order id
     *
     * @return price after discount
     */
    public function updateCop_item($coupon, $cart_item, $called = "cart", $order_id = 0)
    {
        $db = JFactory::getDbo();

        // $cart_item = coupon items;
        foreach ($cart_item as $cart_item_id) {
            if ($called == "cart") {
                // Called from cart
                $Quick2cartModelcart = new Quick2cartModelcart;
                $cartid              = $Quick2cartModelcart->getCartId();
                $query               = "Select cart_item_id as id ,item_id as item_id, product_final_price as tamt ,product_quantity as qty
			 FROM #__kart_cartitems
			 WHERE item_id='$cart_item_id' AND cart_id =" . $cartid . " order by `store_id`";
            } else {
                // Called from  order
                $query = "Select order_item_id as id , item_id as item_id, product_final_price as tamt ,product_quantity as qty
			 FROM #__kart_order_item
			 WHERE item_id='$cart_item_id' AND order_id =" . $order_id . " order by `store_id`";
            }

            $db->setQuery($query);
            $cartitems = $db->loadAssocList();

            foreach ($cartitems as $item) {
                if ($item['item_id'] == $cart_item_id) {
                    if ($coupon[0]->val_type == 1) {
                        $cval = ($coupon[0]->value / 100) * $item['tamt'];
                    } else {
                        $cval = $coupon[0]->value;

                        /* Multiply cop disc with qty*/
                        $cval = $cval * $item['qty'];
                    }

                    $camt = $item['tamt'] - $cval;

                    if ($camt <= 0) {
                        $camt = 0;
                    }

                    $dis_totalamt = ($camt) ? $camt : $item['tamt'];

                    $cart_item = new stdClass;

                    $cart_item->item_id             = $cart_item_id;
                    $cart_item->original_price      = $item['tamt'];
                    $cart_item->product_final_price = $dis_totalamt;

                    if ($called != "cart") {
                        $cart_item->discount = ($cval) ? $cval : '0';
                    }

                    $comquick2cartHelper = new comquick2cartHelper;
                    $db                  = JFactory::getDbo();

                    if ($called == "cart") {
                        $q                       = "SELECT  `params` FROM  `#__kart_cartitems` WHERE `cart_item_id` =" . $item['id'];
                        $cart_item->params       = $comquick2cartHelper->appendExtraFieldData($coupon[0]->cop_code, $q, 'coupon_code');
                        $cart_item->cart_item_id = $item['id'];
                        $sql                     = $db->updateObject("#__kart_cartitems", $cart_item, "cart_item_id");
                    } else {
                        $q                        = "SELECT  `params` FROM  `#__kart_order_item` WHERE `order_item_id` =" . $item['id'];
                        $cart_item->params        = $comquick2cartHelper->appendExtraFieldData($coupon[0]->cop_code, $q, 'coupon_code');
                        $cart_item->order_item_id = $item['id'];
                        $sql                      = $db->updateObject("#__kart_order_item", $cart_item, "order_item_id");
                    }

                    if (!$sql) {
                        echo $this->_db->stderr();

                        return -1;
                    }
                }
            }
        }
    }

    /**
     * This function returns price after applying discounts
     *
     * @param   ARRAY   $totalamt  total amount
     * @param   STRING  $c_code    coupon code
     * @param   INT     $user_id   userid
     * @param   STRING  $called    called
     * @param   INT     $order_id  order id
     *
     * @return price after discount
     */
    public function afterDiscountPrice($totalamt, $c_code, $user_id = "", $called = "cart", $order_id = 0)
    {
        $coupon       = $this->getcoupon($c_code, $user_id, $called, $order_id);
        $coupon       = $coupon ? $coupon : array();
        $dis_totalamt = $totalamt;

        // If user entered code is matched with dDb coupon code
        if (isset($coupon) && $coupon) {
            if (!empty($coupon[0]->item_id)) {
                // Item specific coupon
                $coupon[0]->cop_code = $c_code;
                $this->updateCop_item($coupon, $coupon[0]->item_id, $called, $order_id);
            } else {
                /*
                 * [0] => stdClass Object
                (
                [value] => 10.00
                [val_type] => 1  // 1 for percentag,  0 for flat coupon
                [store_id] => 0
                [item_use_per_user] => ,0,0
                )
                 */

                /*$returnData = array();
                $returnData['coupon_code'] = $c_code;
                $returnData['value'] = $coupon[0]->val_type;
                 */

                if ($coupon[0]->val_type == 1) {
                    // Percentage
                    $cval = ($coupon[0]->value / 100) * $totalamt;
                } else {
                    // Flat coupon
                    $cval = $coupon[0]->value;

                    // $returnData['coupon_type'] = 0;
                }

                $camt = $totalamt - $cval;

                if ($camt <= 0) {
                    $camt = 0;
                }

                $dis_totalamt = ($camt >= 0) ? $camt : $totalamt;

                // $returnData['discount_amount'] = $totalamt - $dis_totalamt;
            }
        }

        // @TODO @ANKUSH - while doing coupon change, $returnData['discount_amount'] return in specific format

        return $dis_totalamt;
    }

    /**
     * Gives applicable Shipping charges.
     *
     * @param   integer  $subtotal  cart subtotal (after discounted amount )
     * @param   object   $vars      object with cartdetail,billing address and shipping adress details.
     *
     * @since   2.2
     * @return   returns the applicatble shipping charges
     */
    public function afterShipPrice($subtotal, $vars)
    {
        $jinput = JFactory::getApplication()->input;
        $post   = $jinput->post;
        $params = JComponentHelper::getParams('com_quick2cart');

        // @TODO SET ITEM LEVEL AS DEF
        $shippingMode = $params->get('shippingMode', 'itemLevel');

        // $shippingMode = $params->get('shippingMode', 'orderLeval');

        if ($shippingMode == "orderLeval") {
            // Call the plugin and get the result
            $dispatcher = JDispatcher::getInstance();

            // @TODO:need to check plugim type..
            JPluginHelper::importPlugin('qtcshipping');
            $result = $dispatcher->trigger('qtcshipping', array($subtotal, $vars));

            $shipresults = array();

            if (!empty($result[0])) {
                $shipresults = $result[0];
            }

            if (isset($shipresults['allowToPlaceOrder']) && $shipresults['allowToPlaceOrder'] == 1) {
                $detail                                = json_encode($shipresults);
                $shipresults['order_shipping_details'] = $detail;

                // $post->set("order_shipping_details", $detail);

                return $shipresults;
            } else {
                return $shipresults;
            }
        } else {
            $shipChargesDetail                       = array();
            $shipChargesDetail['totCharges']         = 0;
            $shipChargesDetail['itemShipMethDetail'] = array();
            $qtcshiphelper                           = new qtcshiphelper;
            $allowToPlaceOrder                       = array();
            $ship_charge_Details_for_product         = array();

            foreach ($vars->cartItemDetail as $key => $itemDetail) {
                $item_id = $itemDetail['item_id'];

                // If item has shipping methods
                if (isset($vars->selectedItemshipMeth[$key][$item_id])) {
                    $shipMethIdForItem  = $vars->selectedItemshipMeth[$key][$item_id];
                    $itemShipMethDetail = $vars->itemsShipMethRateDetail[$shipMethIdForItem];

                    $shipMethod             = array();
                    $shipMethod['client']   = $itemShipMethDetail['client'];
                    $shipMethod['methodId'] = $itemShipMethDetail['methodId'];

                    $address                   = new stdClass;
                    $address->billing_address  = $vars->billing_address;
                    $address->shipping_address = $vars->shipping_address;
                    $address->ship_chk         = $vars->ship_chk;

                    // (Recalculate) Get selected shipping method detail.
                    $shipChargesDetail['itemShipMethDetail'] = $qtcshiphelper->getItemsShipMethods($item_id, $address, $itemDetail, $shipMethod);
                    $shippingChargeDetail                    = $shipChargesDetail['itemShipMethDetail'];

                    if (!empty($shippingChargeDetail['totalShipCost'])) {
                        $vars->cartItemDetail[$item_id]['itemShipCharges'] = $shippingChargeDetail['totalShipCost'];
                    } else {
                        $vars->cartItemDetail[$item_id]['itemShipCharges'] = $itemShipMethDetail['totalShipCost'];
                    }

                    $shipChargesDetail['totCharges'] += $vars->cartItemDetail[$item_id]['itemShipCharges'];
                }

                $ship_charge_Details_for_product[] = $shipChargesDetail['itemShipMethDetail'];
            }

            $qtcOrderShipcharges = 0;

            if (!empty($shipChargesDetail)) {
                // To add against item row entry

                // $post->set('itemShipMethDetail', $shipChargesDetail['itemShipMethDetail']);

                if ($shipChargesDetail['totCharges']) {
                    $qtcOrderShipcharges = $shipChargesDetail['totCharges'];

                    // $shipval = $comquick2cartHelper->calamt($shipval, $shipChargesDetail['totCharges']);
                }
            }

            // @TODO: change allowToPlaceOrder according to condition. Each plugin must return this parameter
            $shippingData['allowToPlaceOrder']  = 1;
            $shippingData['charges']            = $qtcOrderShipcharges;
            $shippingData['totalShipCost']      = $qtcOrderShipcharges;
            $shippingData['itemShipMethDetail'] = $ship_charge_Details_for_product;

            return $shippingData;
        }
    }

    /**
     * function to add tax
     *
     * @param   INT  $dis_totalamt  dis_totalamt
     * @param   INT  $tax           tax
     *
     * @return recalculated data
     */
    public function addtax($dis_totalamt, $tax)
    {
        return $dis_totalamt + $tax;
    }

    /**
     * function to get shipping price
     *
     * @param   STRING  $keyname   keyname
     * @param   STRING  $keyvalue  keyvalue
     *
     * @return shipping price
     */
    public function getShippingPrice($keyname, $keyvalue)
    {
        $comquick2cartHelper = new comquick2cartHelper;
        $shipid              = $comquick2cartHelper->getShippingManagerId($keyname, $keyvalue);

        if (!empty($shipid)) {
            $shipval = $comquick2cartHelper->getShipCurrencyPrice($shipid);

            return $shipval;
        }

        return 0;
    }

    /**
     * data should in format
     * stdClass Object ([totalprice] => 174.375 [country] => Bangladesh [state] => Dhaka [city] => xyz)
     *
     * */
    /*function getFinalShipPrice($data)
    {
    $shipcharges=0;
    $totalamt=$data->totalprice;
    $comquick2cartHelper = new comquick2cartHelper;
    // use onBeforShipManagerApply
    $finalshipdata=$comquick2cartHelper->applyShipManegertrigger($totalamt,$shipcharges,"onBeforShipManagerApply");
    $totalamt = $comquick2cartHelper->calamt($finalshipdata['totalamt'],$finalshipdata['charges']);
    $ship_for='';
    if ($data->country && $data->region && $data->city)
    {
    $shipcharges=$this->getShippingPrice('city',$data->city);
    $ship_for=(empty($shipcharges)) ? "":"city";
    if (empty($shipcharges))
    {
    $shipcharges=$this->getShippingPrice('region',$data->region);
    $ship_for=(empty($shipcharges)) ? "":"region";
    if (empty($shipcharges))
    {
    $shipcharges=$this->getShippingPrice('country',$data->country);
    $ship_for=(empty($shipcharges)) ? "":"country";
    }
    }
    }
    $finalshipdata=$comquick2cartHelper->applyShipManegertrigger($totalamt,$shipcharges,"onAfterShipManagerApply");

    $finalshipdata['totalamt']=$comquick2cartHelper->calamt($finalshipdata['totalamt'],$finalshipdata['charges']);
    $finalshipdata['shipkey']=$ship_for;
    return $finalshipdata;
    }*/

    /**
     * FUnctio to ccheck bill mail if exists
     *
     * @param   INT  $mail  mail
     *
     * @return status
     */
    public function checkbillMailExists($mail)
    {
        $mailexist = 0;
        $query     = "SELECT id FROM #__users where email  LIKE '" . $mail . "'";
        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();

        if ($result) {
            $mailexist = 1;
        } else {
            $mailexist = 0;
        }

        return $mailexist;
    }

    /**
     * This function gives plugin name from plugin parameter
     *
     * @param   INT  $plgname  plugin name
     *
     * @return array
     */
    public function getPluginName($plgname)
    {
        $plugin  = JPluginHelper::getPlugin('payment', $plgname);
        @$params = json_decode($plugin->params);

        return @$params->plugin_name;
    }

    /**
     * Get details of checkout cart items
     *
     * @return array
     */
    public function getCheckoutCartItemsDetails()
    {
        // GETTING CART ITEMS
        JLoader::import('cart', JPATH_SITE . '/components/com_quick2cart/models');
        $cartmodel = new Quick2cartModelcart;
        $cart      = $cartmodel->getCartitems();

        if (!empty($cart)) {
            foreach ($cart as $key => $rec) {
                JLoader::import('product', JPATH_SITE . '/components/com_quick2cart/models');
                $quick2cartModelProduct    = new quick2cartModelProduct;
                $cart[$key]['item_images'] = $quick2cartModelProduct->getProdutImages($rec['item_id']);

                $productHelper = new productHelper;

                // Get cart items attribute details
                $cart[$key]['prodAttributeDetails'] = $productHelper->getItemCompleteAttrDetail($rec['item_id']);

                $product_attributes = rtrim($cart[$key]['product_attributes'], ",");

                if (!empty($product_attributes)) {
                    // Get Cart Item attribute seleted value
                    if ($cart[$key]['product_attributes']) {
                        $db    = JFactory::getDbo();
                        $query = $db->getQuery(true);
                        $query->select("`cartitemattribute_id`, `itemattributeoption_id`, `cartitemattribute_name`");
                        $query->from('#__kart_cartitemattributes');
                        $query->where("itemattributeoption_id IN(" . $product_attributes . ")");
                        $query->where(" cart_item_id = " . $cart[$key]['id']);
                        $db->setQuery($query);
                        $cart[$key]['product_attributes_values'] = $db->loadObjectList('itemattributeoption_id');
                    }
                }
            }
        }

        return $cart;
    }

    /**
     * Find applicable promotion discount. Substract the applicable discount from item's total amount.
     *
     * @param   Array  &$cart        cart detail
     * @param   Array  $coupon_code  coupon code
     *
     * @return coupon
     */
    public function afterPromotionDiscount(&$cart, $coupon_code = '')
    {
        $path = JPATH_SITE . '/components/com_quick2cart/helpers/promotion.php';

        if (!class_exists('PromotionHelper')) {
            JLoader::register('PromotionHelper', $path);
            JLoader::load('PromotionHelper');
        }

        $PromotionHelper        = new PromotionHelper;
        $getAllApplicablePromos = $PromotionHelper->getPromotionDiscount($cart, $coupon_code);

        if (empty($getAllApplicablePromos)) {
            return array();
        }

        $maximumDiscount = 0;
        $maxDisPromo     = new stdClass;

        // Get Maximum discount promotion
        foreach ($getAllApplicablePromos as $key => $promo) {
            if ($maximumDiscount < $promo->applicableMaxDiscount) {
                $maximumDiscount = $promo->applicableMaxDiscount;
                $maxDisPromo     = $promo;
            }
        }

        // Now distribute amount in shares
        $totalPrice             = 0;
        $cartitemPriceArray     = array();
        $cartitemIdDiscoutArray = array();

        if (!empty($maxDisPromo->applicableItemDetail)) {
            $promoDetail                = array();
            $promoDetail['id']          = $maxDisPromo->id;
            $promoDetail['name']        = $maxDisPromo->name;
            $promoDetail['coupon_code'] = ($maxDisPromo->coupon_required == 1) ? $maxDisPromo->coupon_code : '';

            /* Get applicable item's total price and item->price array {$cartItemId is unique identifier to identify the item row.}
            - Not need to
             */
            foreach ($maxDisPromo->applicableItemDetail as $key => $citem) {
                $totalPrice               = $totalPrice + $citem['tamt'];
                $cartitemPriceArray[$key] = $citem['tamt'];
            }

            // Sort by asc item price  and distribute share
            arsort($cartitemPriceArray);

            $HighestPriceCartItemId = -1;
            $itemDiscounts          = 0;

            // Find discount share except highest item priceone
            foreach ($cartitemPriceArray as $key => $pamout) {
                if ($HighestPriceCartItemId == -1) {
                    // For highest item price item, we will add the share later
                    $HighestPriceCartItemId = $key;
                    continue;
                }

                if (empty($pamout)) {
                    $cartitemIdDiscoutArray[$key] = 0;
                } else {
                    $cartitemIdDiscoutArray[$key] = round(($pamout / $totalPrice) * $maximumDiscount);
                    $itemDiscounts                = $itemDiscounts + $cartitemIdDiscoutArray[$key];
                }
            }

            $cartitemIdDiscoutArray[$HighestPriceCartItemId] = $maximumDiscount - $itemDiscounts;
        }

        // Update the discount in cart variable
        if (!empty($cartitemIdDiscoutArray)) {
            foreach ($cart as $key => $citem) {
                $cart_id = $citem['id'];

                if (!empty($cartitemIdDiscoutArray[$cart_id])) {
                    $cart[$key]['tamt']            = $citem['tamt'] - $cartitemIdDiscoutArray[$cart_id];
                    $cart[$key]['discount']        = $cartitemIdDiscoutArray[$cart_id];
                    $cart[$key]['discount_detail'] = $promoDetail;
                }
            }
        }
    }

    /**
     * Get item attribute id
     *
     * @param   Array  $attributeOptionId  attribute option id
     *
     * @return attribute id
     */
    public function getAttributeId($attributeOptionId)
    {
        if (!empty($attributeOptionId)) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select($db->quoteName("itemattribute_id"));
            $query->from($db->quoteName('#__kart_itemattributeoptions'));
            $query->where($db->quoteName('itemattributeoption_id') . " = " . $attributeOptionId);
            $db->setQuery($query);
            $attributeId = $db->loadResult();
        }

        return $attributeId;
    }

    /* amol changes
     * Function: updatecart updates the cart and also calculates the tax and
     * shipping charges Parameters: none Returns: json:
     */
/*  As not required (pls refere update_cart_item function from same controller file)
 *     function update_cart_item()
{
$input = JFactory::getApplication()->input;;
$post = $input->post;

$cart_id =  $post->get('cart_id', '', 'INT');
$item_id =  $post->get('item_id', '', 'INT');

/* Get cartitemattribute_ids to update
$query = $this->_db->getQuery(true);
$query->select("`cartitemattribute_id`");
$query->from('#__kart_cartitemattributes');
$query->where(" cart_item_id = ".$cart_id);
$this->_db->setQuery($query);
$cartitemattribute_ids = $this->_db->loadColumn();

/* Get parsed form data
parse_str($post->get('formData', '' ,'STRING'), $formData);

/*print_r($formData);die;

$itemattributeoption_ids = array();
$product_attribute_names = array();

/* Update item cart attribute
foreach($cartitemattribute_ids as $cartitemattribute_id)
{
try
{
$obj = new stdclass;
$obj->cartitemattribute_id = $cartitemattribute_id;
/*
Now there are two attribute type
 * 1=> select & 2=> text
 * So get the value from one of them

e.g post data
[qtcTextboxField_45] => AA
[attri_option_46] => 67
[attri_option_47] => 59*/

    /* For TextboxField field
    if( !empty( $formData['qtcTextboxField_'.$cartitemattribute_id]) )
    {
    $obj->cartitemattribute_name = $formData['qtcTextboxField_'.$cartitemattribute_id];

    $query = $this->_db->getQuery(true);
    $query->select("ci.itemattributeoption_id, ao.itemattributeoption_name");
    $query->from($this->_db->quoteName('#__kart_cartitemattributes', 'ci'));
    $query->join("LEFT",
     * $this->_db->quoteName("#__kart_itemattributeoptions" , "ao") ."
     * ON (" . $this->_db->quoteName('ci.itemattributeoption_id') . " = " . $this->_db->quoteName('ao.itemattributeoption_id') . ")" );
    $query->where("ci.cartitemattribute_id = " . $cartitemattribute_id);
    $this->_db->setQuery($query);

    $result = $this->_db->loadObject();
    /*print_r($result);

    $itemattributeoption_ids[] = $result->itemattributeoption_id;
    $product_attribute_names[] = $result-> itemattributeoption_name . ": ". $obj->cartitemattribute_name;

    }
    else
    {
    $obj->itemattributeoption_id = $formData['attri_option_'.$cartitemattribute_id];
    $itemattributeoption_ids[] = $obj->itemattributeoption_id;

    /* Get the details to update itemattributeoptions
    $query = $this->_db->getQuery(true);
    $query->select("`itemattributeoption_name`, `itemattributeoption_price`, `itemattributeoption_prefix`");
    $query->from('#__kart_itemattributeoptions');
    $query->where(" itemattributeoption_id = " . $obj->itemattributeoption_id);
    $this->_db->setQuery($query);
    $itemattributeoption = $this->_db->loadObject();

    /* Get itemattributeoptions
    $obj->cartitemattribute_name = $itemattributeoption->itemattributeoption_name;
    $obj->cartitemattribute_price = $itemattributeoption->itemattributeoption_price;
    $obj->cartitemattribute_prefix = $itemattributeoption->itemattributeoption_prefix;

    /* Get the attribute name & value to update product_attribute_names in cart_items table
    $query = $this->_db->getQuery(true);
    $query->select("ci.itemattributeoption_id, ao.itemattributeoption_name, ai.itemattribute_name");
    $query->from($this->_db->quoteName('#__kart_cartitemattributes', 'ci'));

    $query->join("LEFT",
     * $this->_db->quoteName("#__kart_itemattributeoptions" , "ao") ."
     * ON (" . $this->_db->quoteName('ci.itemattributeoption_id') . " = " . $this->_db->quoteName('ao.itemattributeoption_id') . ")" );

    $query->join("LEFT",
     * $this->_db->quoteName("#__kart_itemattributes" , "ai") ." ON (" . $this->_db->quoteName('ao.itemattribute_id') . " = " .
     * $this->_db->quoteName('ai.itemattribute_id') . ")" );

    $query->where("ci.cartitemattribute_id = " . $cartitemattribute_id);
    $this->_db->setQuery($query);
    $result = $this->_db->loadObject();

    $product_attribute_names[] = $result-> itemattribute_name . ": ". $obj->cartitemattribute_name;

    }

    /* Update Table
    $this->_db->updateObject("#__kart_cartitemattributes", $obj, "cartitemattribute_id");

    }
    catch(Exception $e)
    {
    $this->setError($e->getMessage());
    return false;
    }
    }

    try
    {
    /* Update cart Item table
    $cartItem =  new stdclass;
    $cartItem->cart_item_id = $cart_id;
    $cartItem->item_id = $item_id;

    /* One ',' is extra in existing structure of Q@C*/
    /*
$cartItem->product_attributes = implode(',', $itemattributeoption_ids) . ',';
$cartItem->product_attribute_names = implode(',', $product_attribute_names) . ',';
$cartItem->mdate = date("Y-m-d H:i:s");
$this->_db->updateObject('#__kart_cartitems', $cartItem, "cart_item_id");

}
catch (Exception $e)
{
$this->setError($e->getMessage());
return false;
}

return true;

}
 */
}
