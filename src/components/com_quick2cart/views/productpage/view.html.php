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

jimport('joomla.application.component.view');

/**
 * View class for Product detail page.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartViewProductpage extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->comquick2cartHelper = new comquick2cartHelper;
		$input               = JFactory::getApplication()->input;
		$layout              = $input->get('layout', 'default');
		$option              = $input->get('option', '');

		$this->params = JFactory::getApplication()->getParams('com_quick2cart');
		$this->socialintegration = $this->params->get('integrate_with', 'none');
		$this->who_bought = $this->params->get('who_bought', 0);

		// Load helper file
		$product_path = JPATH_SITE . '/components/com_quick2cart/helpers/product.php';

		if (!class_exists('productHelper'))
		{
			JLoader::register('productHelper', $product_path);
			JLoader::load('productHelper');
		}

		$productHelper = new productHelper;
		$this->item_id = $item_id = $input->get('item_id', '');
		JLoader::import('cart', JPATH_SITE . '/components/com_quick2cart/models');
		$model = new Quick2cartModelcart;

		// Getting stock min max,cat,store_id
		$this->itemdetail = $model->getItemRec($item_id);

		if ($layout == 'default')
		{
			// DECLARATION SECTION
			$this->client  = $client = "com_quick2cart";
			$this->pid     = 0;

			if (empty($item_id))
			{
				return false;
			}

			// Retrun store_id,role etc with order by role,store_id
			$this->store_role_list = $this->comquick2cartHelper->getStoreIds();

			// GETTING AUTHORIZED STORE ID
			$storeHelper      = new storeHelper;
			$this->store_list = $storeHelper->getuserStoreList();

			// GETTING PRICE @TODO : DONT SHOW THE PRODUCT WHEN DOESN'T FOUND PRICE FOR CURRENT CURRENCY
			$this->price = $price = $model->getPrice($item_id, 1);

			// Getting Extra Fields Data
			$this->extraData = $this->get('DataExtra');

			// Getting stock min max,cat,store_id
			$this->itemdetail = $model->getItemRec($item_id);

			JLoader::import('promotion', JPATH_SITE . '/components/com_quick2cart/helpers');
			$promotionHelper = new PromotionHelper;

			// Get applicable promotions
			if (!empty($this->itemdetail->item_id) && !empty($this->itemdetail->category) && !empty($this->itemdetail->store_id))
			{
				$this->applicablePromotions = $promotionHelper->getApplicablePromotionsForProduct(
				$this->itemdetail->item_id, $this->itemdetail->category, $this->itemdetail->store_id
				);
			}

			if (!empty($this->itemdetail))
			{
				// Get attributes
				// $this->attributes = $model->getAttributes($item_id);
				$this->attributes = $productHelper->getItemCompleteAttrDetail($item_id);

				if (!empty($this->attributes))
				{
					$this->itemdetail->itemAttributes = $this->attributes;
				}

				$this->showBuyNowBtn = $productHelper->isInStockProduct($this->itemdetail);

				// Get free products media file
				$this->mediaFiles = $productHelper->getProdmediaFiles($item_id);

				$this->prodFromCat       = $productHelper->getSimilarProdFromCat($this->itemdetail->category, $this->item_id, "com_quick2cart");

				// $this->prodFromSameStore = $productHelper->prodFromSameStore($this->itemdetail->store_id, $this->item_id, "com_quick2cart");
				$this->peopleAlsoBought  = $productHelper->peopleAlsoBought($this->item_id);
				$this->peopleWhoBought   = $productHelper->peopleWhoBought($this->item_id, 10);

				$social_options = '';
				$route          = $this->comquick2cartHelper->getProductLink($this->item_id);

				// Trigger data
				$triggerData               = array();
				$triggerData['context']    = "com_quick2cart.productpage";
				$triggerData['itemDetail'] = $this->itemdetail;

				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('content');
				$this->afterProductDisplay = $dispatcher->trigger('onQ2cAfterProductDisplay', array($triggerData));

				if (!empty($this->afterProductDisplay))
				{
					$this->afterProductDisplay = trim(implode("\n", $this->afterProductDisplay));
				}

				// Get avg rating html
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('content');
				$this->productRating = $dispatcher->trigger('onQ2cProductAvgRating', array($triggerData));

				if (!empty($this->productRating))
				{
					$this->productRating = trim(implode("\n", $this->productRating));
				}

				// Trigger for like dislike buttons
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('content');
				$this->addLikeButtons = $dispatcher->trigger('onQ2cAddLikeButtons', array($triggerData));

				// Trigger for pincode check

				/*			$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('tjshipping');
				$this->getPincodeCheckAvailability = $dispatcher->trigger('getPincodeCheckAvailability');
				*/

				if (!empty($this->addLikeButtons))
				{
					$this->addLikeButtons = trim(implode("\n", $this->addLikeButtons));
				}
			}
		}
		elseif ($layout == 'popupslide')
		{
			$this->item_id = $item_id = $input->get('qtc_prod_id', '');
			JLoader::import('cart', JPATH_SITE . '/components/com_quick2cart/models');
			$model = new Quick2cartModelcart;

			if (empty($item_id))
			{
				return false;
			}

			$this->itemdetail = $model->getItemRec($item_id);
			$this->item = $this->itemdetail;
		}
		elseif ($layout == 'users')
		{
			$this->peopleWhoBought   = $productHelper->peopleWhoBought($this->item_id, 100);
		}

		$this->item = $this->itemdetail;
		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		/* Because the application sets a default page title,
		 we need to get it from the menu item itself
		 @TODO Need to uncomment this when a menu for single product item can be created.
		 */

		/*
		$menu = $menus->getActive();

		if($menu)
		{
		$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
		$this->params->def('page_heading', JText::_('QTC_PRODUCTPAGE_PAGE'));
		}

		$title = $this->params->get('page_title', '');
		*/

		// @TODO Need to comment this if when a menu for single product item can be created.
		if (empty($title))
		{
			$title = $this->itemdetail->name . ' - ' . JText::_('QTC_PRODUCTPAGE_PAGE');
		}

		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif (!$this->item->metadesc && $this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}
		elseif (!$this->item->metakey && $this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
