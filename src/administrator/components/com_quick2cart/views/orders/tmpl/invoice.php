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

// @TODO use GET view path funtion
ob_start();
include(JPATH_SITE . '/components/com_quick2cart/views_bs2/site/orders/invoice.php');
$html = ob_get_contents();
ob_end_clean();
echo $html;
