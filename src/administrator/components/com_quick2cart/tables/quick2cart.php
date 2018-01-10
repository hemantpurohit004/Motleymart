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

// import Joomla table library
jimport('joomla.database.table');

/**
* Cart Table class
*/
class TableCart extends JTable
{
	/**
	* Constructor
	*
	* @param object Database connector object
	*/
	function __construct(&$db)
	{
		parent::__construct('#__kart_cart', 'id', $db);
	}
}

/**
* Cart Items Table class
*/
class TableCartitems extends JTable
{
	/**
	* Constructor
	*
	* @param object Database connector object
	*/
	function __construct(&$db)
	{
		parent::__construct('#__kart_cartitems', 'id', $db);
	}
}
