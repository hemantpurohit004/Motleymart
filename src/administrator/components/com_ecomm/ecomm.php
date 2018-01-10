<?php

/**
 * @package    Com_Tjnotification
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

JHtml::_('jquery.framework');

require_once JPATH_ADMINISTRATOR . '/components/com_ecomm/helpers/ecomm.php';

$controller = JControllerLegacy::getInstance('ecomm');

// Perform the Request task
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
