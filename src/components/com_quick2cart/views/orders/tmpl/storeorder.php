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

$comquick2cartHelper = new comquick2cartHelper;
$view=$comquick2cartHelper->getViewpath('orders');
ob_start();
	include($view);
	$html = ob_get_contents();
ob_end_clean();
echo $html;
?>

