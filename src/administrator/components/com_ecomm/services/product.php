<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Ecomm
 * @author     Shivneri <shivnerisystems.com>
 * @copyright  Copyright (c) 2017-2020 shivnerisystems
 * @license    GNU General Public License version 2, or later
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Ecomm Product service class.
 *
 * @since  1.0
 */

class EcommProductService
{
    public function __construct()
    {
        $this->db                    = JFactory::getDbo();
        $this->returnData            = array();
        $this->returnData['success'] = 'false';
    }

    /* - PRODUCT
     * Function to Search product by its title and category
     */
    public function ecommSearch($search, $shopId = 0)
    {
        if (empty($shopId) || $shopId <= 0) {
            return array('success' => 'false', 'message' => 'Please select shop first');
        }

        try
        {
            // Get the query instance
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('DISTINCT' . ' *');
            $query->from($this->db->quoteName('#__kart_items') . 'AS k');
            $query->where('(' . $this->db->quoteName('k.name') . 'like' . "'%$search%'" . 'OR' . $this->db->quoteName('c.title') . 'like' . "'%$search%'" . ')', 'AND');
            $query->where($this->db->quoteName('k.state') . ' = ' . $this->db->quote('1'));
            $query->where($this->db->quoteName('k.store_id') . ' = ' . $this->db->quote($shopId));

            $query->JOIN('LEFT', '`#__categories` AS c ON k.category=c.id');
            $query->JOIN('INNER', '`#__kart_base_currency` AS bc ON bc.item_id=k.item_id');

            // Set the query and get the result
            $this->db->setQuery($query);
            $products = $this->db->loadAssocList();

            // If products found
            if (!empty($products)) {
                $productsDetails = array();

                // Iterate over each product and get details
                foreach ($products as $product) {
                    // id, name, price, image, stock, rating
                    $singleProduct               = array();
                    $singleProduct['name']       = $product['name'];
                    $singleProduct['product_id'] = $product['product_id'];
                    $singleProduct['price']      = $product['price'];
                    $singleProduct['stock']      = $product['stock'];

                    // change by hemant for selling price
                    if ($product['discount_price'] != null) {
                        $singleProduct['sellingPrice'] = $product['discount_price'];
                    } else {
                        $singleProduct['sellingPrice'] = $product['price'];
                    }
                    // end selling price change

                    $singleProduct['store_id']    = $product['store_id'];
                    $singleProduct['category_id'] = $product['category'];
                    $singleProduct['level']       = $product['level'];

                    // TODO - Get the product ratings
                    $singleProduct['ratings'] = '';

                    // Get the products available in options
                    $singleProduct['availableIn'] = $this->ecommGetAvailableUnitsForProduct($product['product_id'], $product['price'], $singleProduct['sellingPrice']);

                    // Get all the images
                    $images = json_decode($product['images']);

                    // Get the valid images
                    $images   = $this->getValidImages($images);
                    $imgArray = array();

                    if (isset($images[0]) && !empty($images[0])) {
                        $imgArray['image0'] = $images[0];
                    }

                    $singleProduct['images'][] = $imgArray;

                    $productsDetails[] = $singleProduct;
                }

                $this->returnData['success']  = 'true';
                $this->returnData['products'] = $productsDetails;
            } else {
                $this->returnData['message'] = 'No products found';
            }

            return $this->returnData;
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
    }

    /* TODO - PRODUCT
     * Function to get the ratings for the given productId
     * return array containig status as true and the shop payment options
     */
    public function ecommGetProductRating($productId)
    {
        $productRating = '';
        try
        {
            // Get the new query instance
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('*')
                ->from($this->db->quoteName('#__ecomm_ratings'))
                ->where($this->db->quoteName('product_id') . " = " . (int) $productId);

            // Set the query and load the result as associative array
            $this->db->setQuery($query);
            $ratings = $this->db->loadAssocList();
        } catch (Exception $e) {
            return "";
        }

        // If there are rating present
        if (!empty($ratings)) {
            // Get the no of ratings given
            $sumOfAllRatings = 0;
            $noOfRatings     = count($ratings);

            // Iterate over each rating and get the sumation of all rating
            foreach ($ratings as $rating) {
                $sumOfAllRatings += $rating['rating'];
            }

            // Find the average rating and roundup the value upto 1 decimal places
            $productRating = round(($sumOfAllRatings / $noOfRatings), 1);
        }

        return (string) $productRating;
    }

    /* - PRODUCT
     * Function to get available units for product
     * return array of available units for product
     */
    public function ecommGetAvailableUnitsForProduct($productId, $productPrice, $sellingPrice)
    {
        JLoader::register('ProductHelper', JPATH_SITE . '/components/com_quick2cart/helpers');
        $productHelper = new ProductHelper;
        $result        = $productHelper->getItemCompleteAttrDetail($productId);
        $attributeData = array('isAvailable' => 'false');

        if (!empty($result)) {
            foreach ($result as $attribute) {
                $attributeData['fieldName']  = $attribute->itemattribute_name;
                $attributeData['compulsory'] = ($attribute->attribute_compulsary) ? 'true' : 'false';

                if (!empty($attribute->optionDetails)) {
                    $optionData = array();

                    foreach ($attribute->optionDetails as $option) {
                        $availableOption['optionId']    = $option->itemattributeoption_id;
                        $availableOption['optionName']  = $option->itemattributeoption_name;
                        $availableOption['optionMRP']   = (string) $option->itemattributeoption_price_mrp;
                        $availableOption['optionPrice'] = (string) $option->itemattributeoption_price;
                        $optionData[]                   = $availableOption;
                    }
                    $attributeData['isAvailable'] = 'true';
                    $attributeData['options']     = $optionData;
                }
            }

        }

        return $attributeData;
    }

    /* - PRODUCT
     * Function to get images of the product in cart
     * return array containig images of the product in cart
     */
    public function ecommGetProductImages($productId)
    {
        // Get the product details
        $modelCart      = new Quick2cartModelcart;
        $productDetails = $modelCart->getItemRec($productId);

        // Get all the images
        $images = json_decode($productDetails->images);

        // Get the valid images
        $images = $this->getValidImages($images);

        $productImages = array();

        if (isset($images[0]) && !empty($images[0])) {
            $productImages['Img0'] = $images[0];
        }

        return $productImages;
    }

    /* - PRODUCT
     */
    public function getValidImages($images)
    {
        JLoader::register('comquick2cartHelper', JPATH_SITE . '/components/com_quick2cart/helpers');
        $comquick2cartHelper = new comquick2cartHelper;
        // Initialise variable
        $validImages = array();

        // Iterate over each image
        foreach ($images as $image) {
            // Check all images are valid and present
            $image = $comquick2cartHelper->isValidImg($image);

            // If image is valid
            if (!empty($image)) {
                $validImages[] = $image;
            }
        }

        return $validImages;
    }

    /* - PRODUCT
     * Function to get single product details along with the applicable offers
     * return array containig status as true and the product details and the offers if applicatable
     */
    public function ecommGetSingleProductDetails($productId, $categoryId, $shopId)
    {
        // Load the cart model
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_quick2cart/models');
        $modelCart      = JModelLegacy::getInstance('cart', 'Quick2cartModel');
        $productDetails = $modelCart->getItemRec($productId);

        // Get the selling price
        $sellingPrice                 = $modelCart->getPrice($productDetails->product_id, 1)['discount_price'];
        $productDetails->sellingPrice = (empty($sellingPrice)) ? '' : $sellingPrice;

        // If productDetails exits
        if (!empty($productDetails)) {
            // Get all the images
            $images = json_decode($productDetails->images);

            // Get the valid images
            $images = $this->getValidImages($images);

            $productDetails->images = array();

            if (isset($images[0])) {
                $productDetails->images[]['path'] = $images[0];
            }

            // Get the products available in options
            $productDetails->availableIn = $this->ecommGetAvailableUnitsForProduct($productId, $productDetails->price, $productDetails->sellingPrice);

            // remove unneccessary data
            unset($productDetails->featured);
            unset($productDetails->item_length);
            unset($productDetails->item_width);
            unset($productDetails->item_height);
            unset($productDetails->item_length_class_id);
            unset($productDetails->display_in_product_catlog);
            unset($productDetails->item_weight_class_id);
            unset($productDetails->item_weight);
            unset($productDetails->ordering);
            unset($productDetails->params);
            unset($productDetails->parent);
            unset($productDetails->parent_id);
            unset($productDetails->cdate);
            unset($productDetails->mdate);
            unset($productDetails->state);
            unset($productDetails->sku);
            unset($productDetails->alias);
            unset($productDetails->item_id);
            unset($productDetails->product_type);
            unset($productDetails->min_quantity);
            unset($productDetails->max_quantity);
            unset($productDetails->video_link);
            unset($productDetails->metakey);
            unset($productDetails->metadesc);
            unset($productDetails->slab);
            unset($productDetails->taxprofile_id);
            unset($productDetails->shipProfileId);

            // Build the data to be return
            $this->returnData['success']        = 'true';
            $this->returnData['productDetails'] = $productDetails;
        } else {
            $this->returnData['message'] = 'Product details not found.';
        }

        return $this->returnData;
    }

    /* - PRODUCT
     * Function to get the products of given sub-category and shop
     * return array containig status as true and the shop products
     */
    public function ecommGetProductsForShopAndCategory($shopId, $categoryId, $filter = array())
    {
        // Load the cart model
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_quick2cart/models');
        $model = JModelLegacy::getInstance('Category', 'Quick2cartModel');

        try
        {
            // Create db and query object
            $query = $this->db->getQuery(true);

            // Select the required fields from the table.
            $query->select($model->getState('list.select', 'a.*'));
            $query->select('CASE WHEN bc.discount_price IS NOT NULL THEN bc.discount_price
                            ELSE a.price
                            END as fprice');
            $query->from('`#__kart_items` AS a');
            $query->where('`category` = ' . $categoryId);
            $query->where('`store_id` = ' . $shopId);
            $query->where('`state` = ' . 1);

            // Adding the filter
            if (!empty($filter)) {
                $search = $filter['keyword'];
                $search = $this->db->Quote('%' . $this->db->escape($search, true) . '%');
                $query->where('( a.name LIKE ' . $search . ' )');
            }

            $query->JOIN('LEFT', '`#__categories` AS c ON c.id=a.category');
            $query->JOIN('INNER', '`#__kart_base_currency` AS bc ON bc.item_id=a.item_id');
            $this->db->setQuery($query);

            // Load the list of stores found
            $products = $this->db->loadAssocList();
        } catch (Exception $e) {
            $this->returnData['message'] = $e->getMessage();
            return $this->returnData;
        }
        // If products found
        if (!empty($products)) {
            $productsDetails = array();
            $singleProduct   = array();

            // Iterate over each product and get details
            foreach ($products as $product) {
                // Get the products available in options
                $productsDetails[] = $this->ecommGetSingleProductDetails($product['product_id'], $$shopId, $categoryId)['productDetails'];
            }

            $this->returnData             = array();
            $this->returnData['success']  = 'true';
            $this->returnData['products'] = $productsDetails;
        } else {
            $this->returnData['message'] = 'No products found.';
        }

        return $this->returnData;
    }

    /* User - PRODUCT
     * Function to get single order details for given orderId and shopId
     * return array containig status as true and the order details
     */
    public function ecommGetCategoryIdAndStoreId($productId)
    {
        try
        {
            // Create db and query object
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select('product_id, category, store_id')
                ->from($this->db->quoteName('#__kart_items'))
                ->where($this->db->quoteName('product_id') . " = " . (int) $productId);

            $this->db->setQuery($query);

            // Load the list of users found
            $product = $this->db->loadAssoc();

            if (!empty($product)) {
                return $product;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /* VENDOR - PRODUCT
     * Function to save new product
     * return array containig status as true and the message
     */
    public function ecommSaveProduct($productData)
    {

        // Clear the previous responses
        $this->returnData            = array();
        $this->returnData['success'] = 'false';

        $input     = new JInput();
        $token     = JHtml::_('form.token');
        $token     = JSession::getFormToken();
        $productId = empty($productData['productId']) ? 0 : $productData['productId'];

        $input->set('pid', $productId);
        $input->set('item_name', $productData['productName']);
        $input->set('item_alias', $productData['productAlias']);
        $input->set('qtc_product_type', 1);
        $input->set('prod_cat', $productData['productCategory']);
        $input->set('description', array('data' => $productData['productDescription']));
        $input->set('sku', $productData['productSku']);
        $input->set('stock', $productData['stock']);
        $input->set('state', $productData['state']);
        $input->set('store_id', $productData['storeId']);
        $input->set('multi_cur', array('INR' => $productData['productPrice']));
        $input->set('multi_dis_cur', array('INR' => $productData['discountPrice']));
        $input->set('option', 'com_quick2cart');
        $input->set('client', 'com_quick2cart');
        $input->set('task', 'product.save');
        $input->set('view', 'product');
        $input->set('check', 'post');
        $input->set($token, '1');
        $input->set('att_detail', $this->getAttributeData($productData));

        /* Changes by Hemant for proct save*/
        /* For Image upload*/
        $input->set('qtc_prodImg',array($productData['productImages']));
        $input->set('youtube_link', '');
        $input->set('item_slab', '0');
        $input->set('min_item', '1');
        $input->set('max_item', '100');
        $input->set('metadesc', '');
        $input->set('metakey', '');
        $input->set('taxprofile_id', '');
        $input->set('qtc_item_length', '0');
        $input->set('qtc_item_width', '0');
        $input->set('qtc_item_height', '0');
        $input->set('length_class_id', '1');
        $input->set('qtc_item_weight', '0');
        $input->set('weigth_class_id', '1');
        $input->set('qtc_shipProfile', '');
        $input->set('saveAttri', '1');
        $input->set('saveMedia', '1');
        /*End Of change by hemant*/

        // Require helper file
        JLoader::register('comquick2cartHelper', JPATH_SITE . '/components/com_quick2cart/helpers');
        $comquick2cartHelper = new comquick2cartHelper;
        $productId           = $comquick2cartHelper->saveProduct($input);

        // If Product not saved
        if ($productId <= 0) {
            $this->returnData['message'] = 'Failed to save the product details';
            return $this->returnData;
        }

        // If image is not provided in case of edit.
        if(!isset($productData['productImages']) || empty($productData['productImages'])){
            $this->returnData['success'] = 'true';
            $this->returnData['message'] = 'Product details saved successfully';
            return $this->returnData;
            }

        /*Store image to quick to cart folder*/
        $tempPath = JPATH_SITE . '/tmp/' . $productData['productImages'];

        if (file_exists($tempPath) && !empty($productData['productImages'])) {

            $destinationPath      = JPATH_SITE . '/images/quick2cart/'.$productId.'-'.$productData['productImages'];
            // Move Image from temp to image folder.
            rename($tempPath, $destinationPath);
            $this->returnData['success'] = 'true';
            $this->returnData['message'] = 'Product details saved successfully';
                }
        else{
            $this->returnData['message'] = 'Failed to save product image';
        }

        return $this->returnData;
    }

    /* VENDOR - PRODUCT
     * Function to get the product attributes from the inputdata
     * return product attributes
     */
    public function getAttributeData($inputData) {
        $attributeDetails =array();

        $firstIndex = array(
            "global_attribute_set" => 0,
            "attri_name" =>"",
            "attri_id" =>"",
            "global_atrri_id" =>"",
            "fieldType" => "Select",
            "attri_opt" => array
            ( array(
                "id" =>"",
                "globalOptionId" =>"",
                "child_product_item_id" =>"",
                "name" =>"",
                "state" => 1,
                "sku" => "",
                "stock" =>"",
                "prefix" => "+",
                "mrp" => array( "INR" => 0 ),
                "currency" => array ( "INR" =>"" ),
                "order" => "1"
                )
            )
        );

        $attributeId = $inputData['attributeId'];
        $attributeOptions = $inputData['attributeDetails'];
        $attributesArray = array();


        foreach ($attributeOptions as $attribute) {
            $singleAttr = array();
            $singleAttr['id'] = $attribute['id'];
            $singleAttr['globalOptionId'] = '';
            $singleAttr['child_product_item_id'] = '';
            $singleAttr['name'] = $attribute['name'];
            $singleAttr['state'] = 1;
            $singleAttr['stock'] = '';
            $singleAttr['sku'] = '';
            $singleAttr['prefix'] = '+';
            $singleAttr['mrp'] = array( 'INR' => $attribute['mrp']);
            $singleAttr['currency'] = array( 'INR' => $attribute['price']);
            $singleAttr['name'] = $attribute['name'];
            $singleAttr['order'] = $attribute['order'];

            $attributesArray[] = $singleAttr;
        }

        $attributesArray[] = array(
            "id" =>"",
            "globalOptionId" =>"",
            "child_product_item_id" =>"",
            "name" =>"",
            "state" => 1,
            "sku" => "",
            "stock" =>"",
            "prefix" => "+",
            "mrp" => array( "INR" => 0 ),
            "currency" => array ( "INR" =>"" ),
            "order" => "1"
            );

        $secondIndex = array(
            "global_attribute_set" => 0,
            "attri_name" =>" Available In (Units)",
            "attri_id" => $attributeId,
            "global_atrri_id" => 0,
            "fieldType" => "Select",
            "attri_opt" => $attributesArray
        );

        $attributeDetails[] = $firstIndex;
        $attributeDetails[] = $secondIndex;

        return $attributeDetails;
    }
    /* VENDOR - PRODUCT
     * Function to get product list
     * return array containig status as true and the product list
     */
    public function ecommGetProductsList($shopId, $categoryId, $status = -1)
    {
        try
        {
            // Create db and query object
            $query = $this->db->getQuery(true);

            // Build the query
            $query->select($this->db->quoteName(array('product_id', 'store_id', 'category', 'name', 'price', 'stock', 'images', 'description', 'state')))
                ->from($this->db->quoteName('#__kart_items'))
                ->where($this->db->quoteName('store_id') . " = " . (int) $shopId);

            // If status is other than -1
            if ($status != -1) {
                $query->where($this->db->quoteName('state') . " = " . (int) $status);
            }

            // If categoryId is provided
            if ($categoryId) {
                $query->where($this->db->quoteName('category') . " = " . (int) $categoryId);
            }

            // Set the query and get the result
            $this->db->setQuery($query);
            $products = $this->db->loadAssocList();

            // If products are present
            if (!empty($products)) {
                // Load the cart model
                JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_quick2cart/models');
                $modelCart = JModelLegacy::getInstance('cart', 'Quick2cartModel');

                // Itterate over each product
                for ($i = 0; $i < count($products); $i++) {
                    // Get the selling price
                    $sellingPrice                 = $modelCart->getPrice($products[$i]['product_id'], 1)['discount_price'];
                    $products[$i]['sellingPrice'] = (empty($sellingPrice)) ? '0' : $sellingPrice;

                    // Get the products available in options
                    $products[$i]['availableIn'] = $this->ecommGetAvailableUnitsForProduct($products[$i]['product_id'], $products[$i]['price'], $products[$i]['sellingPrice']);

                    // Get all the images
                    $images = json_decode($products[$i]['images']);

                    // Get the valid images
                    $images = $this->getValidImages($images);

                    $products[$i]['images'] = array();

                    if (isset($images[0])) {
                        $products[$i]['images'][]['path'] = $images[0];
                    }
                }

                $this->returnData['success']  = 'true';
                $this->returnData['products'] = $products;
            } else {
                $this->returnData['message'] = 'Products not found';
            }

            return $this->returnData;
        } catch (Exception $e) {
            return $this->returnData;
        }
    }
}
