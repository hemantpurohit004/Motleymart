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

// Load backend reports model file as it is
JLoader::import('reports', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_quick2cart' . DS . 'models');
