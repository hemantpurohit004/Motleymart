<?php

// No direct access to this file
defined('_JEXEC') or die();

$lang = JFactory::getLanguage();
$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);

JHTML::_('behavior.modal', 'a.modal');
$html = '';
$client = "com_zoo";

$jinput = JFactory::getApplication()->input;
$itemid = $jinput->get('cid', array(), "ARRAY");
$pid = $itemid[0];

// load helper file if not exist

if (! class_exists('comquick2cartHelper'))
{
	// require_once $path;
	$path = JPATH_SITE . DS . 'components' . DS . 'com_quick2cart' . DS . 'helper.php';
	JLoader::register('comquick2cartHelper', $path);
	JLoader::load('comquick2cartHelper');
}

$comquick2cartHelper = new comquick2cartHelper();
$path = $comquick2cartHelper->getViewpath('attributes', '', "ADMIN", "SITE");
ob_start();

include $path;
$html = ob_get_contents();
ob_end_clean();

echo $html;

