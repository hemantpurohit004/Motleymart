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

$app = JFactory::getApplication();
$override = JPATH_BASE . '/templates' . DS . $app->getTemplate() . '/html/com_quick2cart/orders/default.php';

if (JFile::exists($override))
{
	$view = $override;
}
else
{
	$view = JPATH_SITE . "/components/com_quick2cart/views_bs2/site/orders/default.php";
}

ob_start();
include($view);
$html = ob_get_contents();
ob_end_clean();
echo $html;
