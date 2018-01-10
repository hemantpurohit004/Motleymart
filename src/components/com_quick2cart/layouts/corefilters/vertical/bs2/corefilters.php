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

$comquick2cartHelper = new Comquick2cartHelper;
$removeParamOnchangeCat = $comquick2cartHelper->getParameterToRemoveOnChangeOfCategory();
$compSpecificFilterHtml = $comquick2cartHelper->getComponentSpecificFilterHtml();
echo $compSpecificFilterHtml;
