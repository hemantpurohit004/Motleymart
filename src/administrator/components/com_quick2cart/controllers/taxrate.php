<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

jimport('joomla.application.component.controllerform');

/**
 * Taxrate form controller class.
 *
 * @package     Quick2cart
 * @subpackage  com_quick2cart
 * @since       2.2
 */
class Quick2cartControllerTaxrate extends JControllerForm
{
	/**
	 * Class constructor.
	 *
	 * @since   1.6
	 */
	public function __construct()
	{
		$this->view_list = 'taxrates';
		parent::__construct();
	}
}
