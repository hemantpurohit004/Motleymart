<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2016. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
$comquick2cartHelper = new Comquick2cartHelper();
$view = JPATH_SITE . '/components/com_quick2cart/views_bs2/site/promotion/edit.php';
ob_start();
include($view);
$html = ob_get_contents();
ob_end_clean();
echo $html;
