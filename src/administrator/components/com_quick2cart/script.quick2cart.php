<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.application.component.controller');

if (!defined('DS'))
{
	define('DS', '/');
}


class com_quick2cartInstallerScript
{
	/** @var array The list of extra modules and plugins to install */
	private $oldversion="";

	// used to identify new install or update
	private $componentStatus="install";

	/** @var array Obsolete files and folders to remove*/
	private $removeFilesAndFolders = array(
		'files'	=> array(
			// Removed since 2.2

			'administrator/components/com_quick2cart/views/vendor/tmpl/approvestore.php',
			'administrator/components/com_quick2cart/views/vendor/tmpl/default.php',
			'administrator/components/com_quick2cart/views/vendor/tmpl/newvender.php',
			'components/com_quick2cart/views/vendor/tmpl/default.php',
			'components/com_quick2cart/views/vendor/tmpl/default.xml',
			'components/com_quick2cart/views/managecoupon/metadata.xml',
			'components/com_quick2cart/views/reports/metadata.xml',
			'components/com_quick2cart/views/reports/tmpl/mypayouts.xml',

			/* version 2.3.1*/
			'components/com_quick2cart/views/zones/tmpl/default2.php',
			'com_quick2cart/productpage/popupslide.php',

			/* version 2.5.1*/
			'components/com_quick2cart/assets/css/bootstrap-slider.css',
			'components/com_quick2cart/assets/js/bootstrap-slider.js',

			/* version 2.6*/
			'components/com_quick2cart/views/orders/tmpl/default_store_cartdetail.php',
			'components/com_quick2cart/helpers/user.php',

			/* Removed in version 2.8*/
			'components/com_quick2cart/views/coupons/metadata.xml',
			'components/com_quick2cart/views/productpage/tmpl/users.php',
			'modules/mod_quick2cart/tmpl/default_itemrow.php',
			'modules/mod_quick2cart/tmpl/default_itemshort.php',
			'components/com_quick2cart/views/cartcheckout/tmpl/default_cartdetail.php',
			'administrator/components/com_quick2cart/defines.php'
		),
		'folders' => array(
			// Removed since 2.2

			/*
			 * // Uncomment this in version 2.2.1
			'administrator/components/com_quick2cart/views/managecoupon',
			'administrator/components/com_quick2cart/views/reports',
			'components/com_quick2cart/bootstrap',
			'components/com_quick2cart/css',
			'components/com_quick2cart/images',
			'components/com_quick2cart/js'
			'components/com_quick2cart/views/managecoupon',
			'components/com_quick2cart/views/reports',
			*/
			/* Version 2.3.1*/
			'components/com_quick2cart/views/managecoupon',
			'components/com_quick2cart/views/reports',
			'components/com_quick2cart/assets/font-awesome'
		)
	);

	/**
	 * Removes obsolete files and folders
	 *
	 * @param array $removeFilesAndFolders
	 */
	private function _removeObsoleteFilesAndFolders($removeFilesAndFolders)
	{
		// Remove files
		jimport('joomla.filesystem.file');
		if (!empty($removeFilesAndFolders['files'])) foreach ($removeFilesAndFolders['files'] as $file) {
			$f = JPATH_ROOT.'/'.$file;
			if (!JFile::exists($f)) continue;
			JFile::delete($f);
		}

		// Remove folders
		jimport('joomla.filesystem.file');
		if (!empty($removeFilesAndFolders['folders'])) foreach ($removeFilesAndFolders['folders'] as $folder) {
			$f = JPATH_ROOT.'/'.$folder;
			if (!JFolder::exists($f)) continue;
			JFolder::delete($f);
		}
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		// Only allow to install on Joomla! 2.5.0 or later
		//return version_compare(JVERSION, '2.5.0', 'ge');
	}

	/**
	 * Runs after install, update or discover_update
	 * @param string $type install, update or discover_update
	 * @param JInstaller $parent
	 */
	function postflight( $type, $parent )
	{
		$lang =  JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_SITE);

		$msgBox=array();

		/* AS We are loading the strapper from com_tjfields first
		 * $straperStatus = $this->_installStraper($parent);
		 * */

		// Remove obsolete files and folders
		$removeFilesAndFolders = $this->removeFilesAndFolders;
		$this->_removeObsoleteFilesAndFolders($removeFilesAndFolders);

		// Add Uncategorised __categories in #__categories table
		$this->addUncategorisedCat();

		//create default store
		$storeMsg=$this->createSuperuserstore();
		if (!empty($storeMsg))
		{// not msg return mean not create
			$msgBox['Stores']=$storeMsg;
		}

		//ADD STORE DASHBOARD MENU IN MAIN MENU
		$menusMsg=$this->addMenuItems();

		if (!empty($storeMsg))
		{// not msg return mean not create
			$msgBox['Menus']=$menusMsg;
		}

		// Since version 2.2
		$this->fix_menus_on_update();
		//$this->migrateCountryRelatedFields();

		//ADD QUICK2CART MENUES IN JS TOOLBAR
		//$this->addDefaultToolbarMenus();

		if (!JFolder::exists(JPATH_ROOT . '/images/quick2cart'))
		{
			JFolder::create(JPATH_ROOT . '/images/quick2cart');
		}

		// Load bootstrap and jquery for installation screen
		$document = JFactory::getDocument();
		$document->addScript(JUri::root(true) . '/media/techjoomla_strapper/js/akeebajq.js' );

		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			$document = JFactory::getDocument();
			$document->addStyleSheet(JUri::root(true) . '/media/techjoomla_strapper/css/bootstrap.min.css' );
		}


		// Do all releated Tag line/ logo etc
		$this->taglinMsg();

		// Migrate country and region table
		$this->migrateDbfix();

		// Add default permissions
		$this->permissionsFix();

		$this->migrateTaxShipDetails();
		$this->migrateCouponChangesTO28version();
	}

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent)
	{
		// $parent is the class calling this method
		$this->installSqlFiles($parent);

		// On new installation - keep bootstrap 3 layouts as default
		//$this->changeBSViews();
		//$this->migrateTables();
	}
	function installSqlFiles($parent)
	{
		$db = JFactory::getDBO();
		// Obviously you may have to change the path and name if your installation SQL file ;)
		if (method_exists($parent, 'extension_root')) {
			$sqlfile = $parent->getPath('extension_root')  . '/admin/sql/install.sql';
		} else {
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/sql/install.sql';
		}
		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);
		if ($buffer !== false) {
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);
			if (count($queries) != 0) {
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query{0} != '#') {
						$db->setQuery($query);
						if (!$db->execute()) {
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
							return false;
						}
					}
				}
			}
		}
		$config=JFactory::getConfig();
		if (JVERSION>=3.0)
		{
			$dbname=$config->get( 'db' );
         $dbprefix=$config->get( 'dbprefix' );

		}
        else
         {
			$dbname=$config->getValue( 'config.db' );
		    $dbprefix=$config->getvalue( 'config.dbprefix' );
	  	}

		//install country table(#__tj_country) if it does not exists
		/*$query="SELECT table_name
		FROM information_schema.tables
		WHERE table_schema='".$dbname."'
		AND table_name='".$dbprefix."tj_country'";


		$db->setQuery($query);
		$check=$db->loadResult();
		if (!$check){
			//Lets create the table
			$this->runSQL($parent,'country.sql');
		}

		//install region table(#__tj_region) if it does not exists
		$query="SELECT table_name
		FROM information_schema.tables
		WHERE table_schema='".$dbname."'
		AND table_name='".$dbprefix."tj_region'";
		$db->setQuery($query);
		$check=$db->loadResult();
		if (!$check){
			//Lets create the table
			$this->runSQL($parent,'region.sql');
		}
		//install city table(#__tj_city) if it does not exists
	 $query="SELECT table_name
		FROM information_schema.tables
		WHERE table_schema='".$dbname."'
		AND table_name='".$dbprefix."tj_city'";

		$db->setQuery($query);
		$check=$db->loadResult();
		if (!$check){
			//Lets create the table
			$this->runSQL($parent,'city.sql');
		}*/

		//install kart_lengths table(#kart_lengths) if it does not exists
		$query="SELECT table_name
		FROM information_schema.tables
		WHERE table_schema='".$dbname."'
		AND table_name='".$dbprefix."kart_lengths'";


		$db->setQuery($query);
		$check=$db->loadResult();
		if (!$check){
			//Lets create the table
			$this->runSQL($parent,'lengths.sql');
		}

		//install kart_weights table(#kart_weights) if it does not exists
		$query="SELECT table_name
		FROM information_schema.tables
		WHERE table_schema='".$dbname."'
		AND table_name='".$dbprefix."kart_weights'";


		$db->setQuery($query);
		$check=$db->loadResult();
		if (!$check){
			//Lets create the table
			$this->runSQL($parent,'weights.sql');

		}
}


	/**
	 * Runs on uninstallation
	 *
	 * @param JInstaller $parent
	 */
	function uninstall($parent)
	{
		//~ $status = $this->_uninstallSubextensions($parent);

		// Show the post-uninstallation page
		//~ $this->_renderPostUninstallation($status, $parent);
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	 function update($parent) {
	 	$this->componentStatus="update";
	 	$this->installSqlFiles($parent);
		$this->fix_db_on_update();
	} // end of update

	function runSQL($parent,$sqlfile)
	{
		$db = JFactory::getDBO();
		// Obviously you may have to change the path and name if your installation SQL file ;)
		if (method_exists($parent, 'extension_root')) {
			$sqlfile = $parent->getPath('extension_root') . '/admin/sql/' . sqlfile;
		} else {
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/sql'.DS.$sqlfile;
		}
		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);
		if ($buffer !== false) {
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);
			if (count($queries) != 0) {
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query{0} != '#') {
						$db->setQuery($query);
						if (!$db->execute()) {
							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
							return false;
						}
					}
				}
			}
		}
	}//end run sql

	//since version 1.0.2
	function fix_db_on_update()
	{
		$db = JFactory::getDBO();
		// alter kart_orders table
		$this->alterOrderTb();

		$this->qtc_AlterUpdate();

		$this->migrateTables();

		// Db changes for version 2.1 and above
		$this->mediaRelatedTables();

		// Db changes for version 2.1 and above for zone,tax,ship
		$this->zoneRelatedTables();

		// Db changes for version 2.5 and above for global attributes
		$this->globalAttributeRelatedTables();

		// Db changes for version 2.8 and above for promotion rules
		$this->promotionRelatedTables();

	}// end of fix_db_on_updates

	function zoneRelatedTables()
	{
		// Add zone table
		$db = JFactory::getDBO();
		$field_array = array();
		$query="CREATE TABLE IF NOT EXISTS `#__kart_zone` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL,
			  `store_id` int(11) NOT NULL,
			  `state` tinyint(1) NOT NULL,
			  `ordering` int(11) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		$db->setQuery($query);
		$db->execute();

		// Add zone rule table
		$db = JFactory::getDBO();
		$field_array = array();
		$query="CREATE TABLE IF NOT EXISTS `#__kart_zonerules` (
				  `zonerule_id` int(11) NOT NULL AUTO_INCREMENT,
				  `zone_id` int(11) NOT NULL,
				  `country_id` int(11) NOT NULL,
				  `region_id` int(11) NOT NULL,
				  `ordering` int(11) NOT NULL,
				  PRIMARY KEY (`zonerule_id`)
				)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		$db->execute();

		// Add tax rates eg vat,service tax table
		$db = JFactory::getDBO();
		$field_array = array();
		$query="CREATE TABLE IF NOT EXISTS `#__kart_taxrates` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `name` varchar(255) NOT NULL,
				  `percentage` decimal(11,3) NOT NULL,
				  `zone_id` int(11) NOT NULL,
				  `state` tinyint(1) NOT NULL,
				  `ordering` int(11) NOT NULL,
				  `created_by` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1  ; ";
		$db->setQuery($query);
		$db->execute();

		// Add tax Tax profile
		$db = JFactory::getDBO();
		$field_array = array();
		$query="CREATE TABLE IF NOT EXISTS `#__kart_taxprofiles` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `name` varchar(255) NOT NULL,
				  `store_id` int(11) NOT NULL,
				  `state` tinyint(1) NOT NULL,
				  `ordering` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";
		$db->setQuery($query);
		$db->execute();

		// Add tax Tax rules
		$db = JFactory::getDBO();
		$field_array = array();
		$query="CREATE TABLE IF NOT EXISTS `#__kart_taxrules` (
				  `taxrule_id` int(11) NOT NULL AUTO_INCREMENT,
				  `taxprofile_id` int(11) NOT NULL,
				  `taxrate_id` int(11) NOT NULL,
				  `address` varchar(255) NOT NULL COMMENT 'Which address should be used to apply taxrates. Eg billin, shipping or store address',
				  `ordering` int(11) NOT NULL,
				  `state` int(11) NOT NULL,
				  PRIMARY KEY (`taxrule_id`)
				)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; ";
		$db->setQuery($query);
		$db->execute();

		// Add __kart_zoneShipMethod profile (Plugin specific)
		$db = JFactory::getDBO();
		$field_array = array();
		$query = "CREATE TABLE IF NOT EXISTS `#__kart_zoneShipMethods` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL,
			  `store_id` int(11) NOT NULL DEFAULT '1',
			  `taxprofileId` int(11) NOT NULL,
			  `state` tinyint(3) NOT NULL,
			  `shipping_type` int(11) NOT NULL COMMENT 'It is type of shipping method eg. weight based, quantity based etc',
			  `min_value` decimal(15,5) NOT NULL,
			  `max_value` decimal(15,5) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";
		$db->setQuery($query);
		$db->execute();

		// Add __kart_zoneShipMethodRates profile (Plugin specific)
		$db = JFactory::getDBO();
		$field_array = array();
		$query = "CREATE TABLE IF NOT EXISTS `#__kart_zoneShipMethodRates` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `methodId` int(11) NOT NULL COMMENT 'This is primary key of #__kart_shipMethods table',
				  `zone_id` int(11) NOT NULL COMMENT 'This is primary key of #__kart_zones table.',
				  `rangeFrom` int(11) DEFAULT '0',
				  `rangeTo` int(11) DEFAULT '99999',
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
		$db->setQuery($query);
		$db->execute();

		$db = JFactory::getDBO();
		$field_array = array();
		$query = "
				CREATE TABLE IF NOT EXISTS `#__kart_zoneShipMethodCurr` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `methodId` int(11) NOT NULL,
				  `currency` varchar(16) NOT NULL,
				  `min_value` decimal(15,5) NOT NULL,
				  `maxAmount` decimal(15,5) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		$db->execute();



		// Add __kart_zoneShipMethod Rates currencies profile (Plugin specific)
		$db = JFactory::getDBO();
		$field_array = array();
		$query = "CREATE TABLE IF NOT EXISTS `#__kart_zoneShipMethodRateCurr` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `rateId` int(11) NOT NULL COMMENT 'This is primary key of #__kart_zoneShipMethodRates table.',
				  `shipCost` float(15,8) NOT NULL DEFAULT '0.00000000',
				  `handleCost` float(15,8) NOT NULL DEFAULT '0.00000000',
				  `currency` varchar(16) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		$db->execute();

		// Add shipping  profile.
		$db = JFactory::getDBO();
		$field_array = array();
		$query = "CREATE TABLE IF NOT EXISTS `#__kart_shipprofile` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `name` varchar(255) NOT NULL,
				  `store_id` int(11) NOT NULL,
				  `state` tinyint(1) NOT NULL,
				  `ordering` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		$db->execute();

		// Add shipping  profile methods.
		$db = JFactory::getDBO();
		$field_array = array();
		$query = "CREATE TABLE IF NOT EXISTS `#__kart_shipProfileMethods` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `shipprofile_id` int(11) NOT NULL,
				  `client` varchar(255) NOT NULL COMMENT 'Shipping Plugin Name',
				  `methodId` int(11) NOT NULL COMMENT 'Extension specific, Shipping Plugin Method id.',
				  `ordering` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1; ";
		$db->setQuery($query);
		$db->execute();
	}

	function mediaRelatedTables()
	{
		$db = JFactory::getDBO();
		$field_array = array();
		$query="CREATE TABLE IF NOT EXISTS `#__kart_itemfiles` (
			  `file_id` int(11) NOT NULL AUTO_INCREMENT,
			  `file_display_name` varchar(255) NOT NULL,
			  `item_id` int(11) DEFAULT NULL,
			  `purchase_required` tinyint(1) DEFAULT '1',
			  `state` tinyint(1) DEFAULT '1',
			  `download_limit` int(11) DEFAULT NULL,
			  `filePath` varchar(255) DEFAULT NULL,
			  `version` varchar(16) DEFAULT NULL,
			  `expiry_mode` tinyint(1) DEFAULT '1' COMMENT '1 for months',
			  `expiry_in` int(11) NOT NULL,
			  `cdate` datetime NOT NULL,
			  `mdate` datetime NOT NULL,
			  PRIMARY KEY (`file_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		$db->setQuery($query);
		$db->execute();

		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_itemfiles`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$field_array[] = $columns[$i]->Field;
		}

		if (in_array('expiry_mode', $field_array) ) {
			$query = "ALTER TABLE `#__kart_itemfiles` CHANGE `expiry_mode` `expiry_mode` TINYINT( 1 ) NULL DEFAULT '1' COMMENT '1 for months'";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_itemfiles table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// Promotion table changes - start
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_promotions`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$field_array[] = $columns[$i]->Field;
		}

		if (in_array('keyValue', $field_array))
		{
			$query = "ALTER TABLE `#__kart_promotions` CHANGE `key` `store_id` int(11) NOT NULL COMMENT 'store id', CHANGE `operation` `name` varchar(255) NOT NULL , CHANGE `keyValue` `description` varchar(2500), CHANGE `discountApplied` `coupon_required` tinyint(4) DEFAULT '0', CHANGE `discuntValType` `discount_type` varchar(100) COMMENT 'flat, percentage, quanity_discount', CHANGE `message` `extra_params` text, CHANGE `fromDate` `from_date` datetime DEFAULT NULL, CHANGE `toDate` `exp_date` datetime DEFAULT NULL, ADD `coupon_code` varchar(100), ADD `max_use` int(11), ADD `terms_and_conditions` varchar(700) DEFAULT NULL, ADD `max_per_user` int(11)";

			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_promotions table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		// Promotion table changes - end

		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_global_attribute`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('renderer', $field_array) ) {
			$query = "ALTER TABLE `#__kart_global_attribute` ADD `renderer` VARCHAR(50) NULL DEFAULT 'filters_checkbox'";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_global_attribute table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		$query="CREATE TABLE IF NOT EXISTS `#__kart_orderItemFiles` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `product_file_id` int(11) DEFAULT NULL,
			  `order_item_id` int(11) DEFAULT NULL,
			  `download_count` int(11) DEFAULT '0',
			  `cdate` datetime NOT NULL,
			  `expirary_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `download_limit` int(11) DEFAULT NULL,
			    `expiration_mode` varchar(50) DEFAULT NULL COMMENT 'This is component option value for expiration mode eg Max Download, Date Expirary or both',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";
			$db->setQuery($query);
      		if (JVERSION < 3.0)
				$db->execute();
			else
				$db->execute();
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_orderItemFiles`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++) {
			$field_array[] = $columns[$i]->Field;
		}

		if (in_array('expirary_date', $field_array) ) {
			$query = "ALTER TABLE  `#__kart_orderItemFiles` CHANGE  `expirary_date`  `expirary_date` TIMESTAMP NOT NULL DEFAULT  '0000-00-00 00:00:00'";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_orderItemFiles table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// Add expiration mode column.


		if (!in_array('expiration_mode', $field_array) )
		{
			$query = "ALTER TABLE  `#__kart_orderItemFiles` ADD  `expiration_mode` VARCHAR( 50 ) NULL COMMENT 'This is component option value for expiration mode eg Max Download, Date Expirary or both' AFTER  `download_limit`";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_orderItemFiles table').$BR;
				echo $db->getErrorMsg();
				return false;
			}

			// Get component option

			$params = JComponentHelper::getParams('com_quick2cart');
			$expiration_mode = 'epMaxDownload'; //$params->get('eProdUExpiryMode', 'epMaxDownload');
			// Set component option value to db
			$query = "UPDATE  `#__kart_orderItemFiles` SET  `expiration_mode` =  '" . $expiration_mode . "' WHERE  `expiration_mode` = '' OR `expiration_mode` IS NULL";
			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_orderItemFiles table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
	}
	//end by aniket..

	function alterOrderTb()
	{
		$db =  JFactory::getDBO();

		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_orders`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++) {
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('order_tax', $field_array)) {
			$query = "ALTER TABLE `#__kart_orders`
						ADD `order_tax` float(10,2) default NULL,
						ADD COLUMN `order_tax_details` text NOT NULL,
						ADD COLUMN `order_shipping` float(10,2) default NULL,
						ADD COLUMN `order_shipping_details` text default NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_orders table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// For adding created_by column in kart_orders table
		if (!in_array('created_by', $field_array)) {
			$query = "ALTER TABLE `#__kart_orders`
						ADD `created_by` int(11) default NULL COMMENT 'user id of vendor who have created manual order for customer' after `user_info_id`";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_orders table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (!in_array('coupon_discount', $field_array)) {
			$query = "ALTER TABLE `#__kart_orders`
						ADD `coupon_discount` float(10,5) default 0.000";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_orders table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (!in_array('currency', $field_array)) {
			$query = "ALTER TABLE `#__kart_orders`
						ADD `currency` varchar(16) default NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_orders table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array('customer_note', $field_array)) {
			$query = "ALTER TABLE `#__kart_orders`
						ADD `customer_note` text default NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_orders table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array('prefix', $field_array)) {
			$query = "ALTER TABLE `#__kart_orders`
						ADD `prefix` VARCHAR( 23 ) NOT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_orders table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array('couponDetails', $field_array)) {
			$query = "ALTER TABLE `#__kart_orders`
						ADD `couponDetails`  text COMMENT 'Coupon price and used price flag- MRP price or field price etc is stored'";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to ADD couponDetails column to kart_orders table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (!in_array('orderRuleDetails', $field_array))
		{
			$query = "ALTER TABLE `#__kart_orders`
						ADD `orderRuleDetails`  text ";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to ADD orderRuleDetails column to kart_orders table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}


		if (!in_array('payment_note', $field_array))
		{
			$query = "ALTER TABLE `#__kart_orders` ADD `payment_note` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT'Payment gateway form comment detail goes here' AFTER `customer_note` ;";
			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to ADD payment_note column to kart_orders table') . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}

	}// end of alterOrderTb

	function qtc_AlterUpdate()
	{
		$db = JFactory::getDbo();
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_order_item`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++) {
			$field_array[] = $columns[$i]->Field;
		}

		/* if (!in_array('parent', $field_array)) {
			$query = "ALTER TABLE `#__kart_order_item`
						ADD `parent` varchar(255) default NULL";

			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_order_item table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}*/

		if (!in_array('product_attributes', $field_array))
		{
			$query = "ALTER TABLE `#__kart_order_item`
						ADD `product_attributes` text NOT NULL COMMENT 'A CSV of itemattributeoption_id values, always in numerical order',
						ADD COLUMN `product_attribute_names` text NOT NULL COMMENT 'A CSV of itemattributeoption_name values',
						ADD COLUMN `product_attributes_price` varchar(64) NOT NULL COMMENT 'The increase or decrease in price per item as a result of attributes. Includes + or - sign'";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_order_item table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (!in_array('discount', $field_array))
		{
			$query = "ALTER TABLE `#__kart_order_item`
						ADD `discount` decimal(15,5) NOT NULL DEFAULT '0.00000' AFTER `original_price`";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_order_item table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (!in_array('discount_detail', $field_array))
		{
			$query = "ALTER TABLE `#__kart_order_item`
						ADD `discount_detail` text AFTER `discount`";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_order_item table. (ADD : discount_detail)').$BR;
				echo $db->getErrorMsg();
				return false;
			}

		}
		if (!in_array('coupon_code', $field_array))
		{
			$query = "ALTER TABLE `#__kart_order_item`
						ADD `coupon_code` VARCHAR( 100 ) NULL AFTER `discount_detail` ";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_order_item table. (ADD : coupon_code)').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

/* @TODO discuss if to keep the cdate,mdate column in items table*/
			$query = "ALTER TABLE `#__kart_order_item`
						MODIFY COLUMN `cdate` datetime DEFAULT NULL, MODIFY COLUMN `mdate` datetime DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_order_item table').$BR;
				echo $db->getErrorMsg();
				return false;
			}

		// Add taxation related field such as order_item_tax
		if (!in_array('item_tax', $field_array))
		{
			$query = "ALTER TABLE  `#__kart_order_item` ADD  `item_tax` DECIMAL( 15, 5 ) NOT NULL DEFAULT  '0.00000' AFTER  `originalBasePrice`";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to ADD item_tax in #__kart_order_item table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// Add taxation related field such as order_item_tax
		if (!in_array('item_tax_detail', $field_array))
		{
			$query = "ALTER TABLE  `#__kart_order_item` ADD  `item_tax_detail` TEXT NOT NULL COMMENT  'item tax charges detail' AFTER  `item_tax`";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to ADD item_tax in #__kart_order_item table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (!in_array('item_shipcharges', $field_array))
		{
			$query = "ALTER TABLE  `#__kart_order_item` ADD  `item_shipcharges` DECIMAL( 15, 5 ) NOT NULL DEFAULT  '0.00000' AFTER  `item_tax_detail`";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to ADD item_tax in #__kart_order_item table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array('item_shipDetail', $field_array))
		{
			$query = "ALTER TABLE  `#__kart_order_item` ADD  `item_shipDetail` TEXT NOT NULL COMMENT  'item shipping charges detail' AFTER  `item_shipcharges`";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to ADD item_tax in #__kart_order_item table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_cartitems`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++) {
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('product_attributes', $field_array)) {
			$query = "ALTER TABLE `#__kart_cartitems`
						ADD `product_attributes` text NOT NULL COMMENT 'A CSV of itemattributeoption_id values, always in numerical order',
						ADD COLUMN `product_attribute_names` text NOT NULL COMMENT 'A CSV of itemattributeoption_name values',
						ADD COLUMN `product_attributes_price` varchar(64) NOT NULL COMMENT 'The increase or decrease in price per item as a result of attributes. Includes + or - sign'";

			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_cartitems table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		// added variant_item_id in 2.5
		if (!in_array('variant_item_id', $field_array)) {
			$query = "ALTER TABLE `#__kart_cartitems`
						ADD `variant_item_id` int(11) default '0' AFTER `item_id`";

			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_cartitems table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// changing countrycode and statecode SIZE to 50
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_users`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++) {
			$field_array[] = $columns[$i]->Field;
		}
		// Since 2.5
		if (!in_array('land_mark', $field_array)) {
			$query = "ALTER TABLE `#__kart_users`
						ADD `land_mark` varchar(50) NULL DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_users table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array('middlename', $field_array)) {
			$query = " ALTER TABLE `#__kart_users` ADD middlename varchar(250)";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_users table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (in_array('country_code', $field_array)) {
			$query = "ALTER TABLE `#__kart_users`
						MODIFY `country_code` varchar(50)";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_users table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (in_array('state_code', $field_array)) {
			$query = "ALTER TABLE `#__kart_users`
						MODIFY `state_code` varchar(50)";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_users table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array('order_id', $field_array)) /* if order_id not present then add*/
		{
			$query = " ALTER TABLE `#__kart_users` ADD order_id int(11) ";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter `#__kart_users` table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		/* if not present then add stock,min,max column to kart_items table */
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_items`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++) {
			$field_array[] = $columns[$i]->Field;
		}
		if (!in_array('stock', $field_array) && !in_array('min_quantity', $field_array) && !in_array('max_quantity', $field_array)) {
			$query = "Alter table `#__kart_items`
					ADD  stock INT(11) DEFAULT NULL ,
					 ADD  min_quantity INT(5) DEFAULT NULL ,
					 ADD  max_quantity INT(5) DEFAULT NULL ";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_items table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// added product_type in 2.5
		if (!in_array('product_type', $field_array) && !in_array('display_in_product_catlog', $field_array) && !in_array('parent_id', $field_array)) {
			$query = "Alter table `#__kart_items`
					ADD product_type INT(11) DEFAULT '1' AFTER `product_id`,
					ADD parent_id INT(11) NOT NULL DEFAULT '0' AFTER `parent`,
					ADD display_in_product_catlog TINYINT(3) NOT NULL DEFAULT '1'";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_items table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (!in_array('slab', $field_array) ) {
			$query = "Alter table `#__kart_items`
					ADD  slab INT(11) NOT NULL DEFAULT '1'";
			$db->setQuery($query);
			if(!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_items table').$BR;
				echo $db->getErrorMsg();
				return FALSE;
			}
		}

		if (!in_array('ordering', $field_array)) {
			$query = "Alter table `#__kart_items`
					ADD ordering INT(11) NOT NULL DEFAULT '1' AFTER `display_in_product_catlog`";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_items table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		 ;


		if (!in_array('params', $field_array)) {
			$query = "ALTER TABLE  `#__kart_items` ADD  `params` TEXT NULL COMMENT  'Store extra data in json format. In key value pair' AFTER  `ordering`";
			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_items table for param coulmn').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (!in_array('alias', $field_array))
		{
			$query = "ALTER TABLE  `#__kart_items` ADD  `alias` varchar(255) NOT NULL AFTER  `name`";

			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_items table for alias coulmn').$BR;
				echo $db->getErrorMsg();
				return false;
			}
			else
			{
				$this->migrateAlias();
			}
		}

		/* if not present then add discount_price to __kart_base_currency table */
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_base_currency`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++) {
			$field_array[] = $columns[$i]->Field;
		}
		if (!in_array('discount_price', $field_array) ) {
			$query = "Alter table `#__kart_base_currency`
					ADD `discount_price` FLOAT( 10, 2 ) DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter base_currency table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		/* if not present then add attribute_compulsary to __kart_itemattributes table */
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_itemattributes`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++) {
			$field_array[] = $columns[$i]->Field;
		}
		if (!in_array('attribute_compulsary', $field_array) ) {
			$query = "ALTER TABLE `#__kart_itemattributes` 	ADD `attribute_compulsary` BOOLEAN NOT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_itemattributes table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// Added product_type in 2.5
		if (!in_array('global_attribute_id', $field_array) && !in_array('is_stock_keeping', $field_array)) {
			$query = "ALTER TABLE `#__kart_itemattributes` ADD `global_attribute_id` INT(11) DEFAULT '0', ADD `is_stock_keeping` TINYINT(3) DEFAULT '0'";

			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_itemattributes table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// FOR USER FIELD CANGE
		if (!in_array('attributeFieldType', $field_array) ) {
			$query = "ALTER TABLE `#__kart_itemattributes`	ADD `attributeFieldType` varchar(20) DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to add attributeFieldType in #__kart_itemattributes table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		// 	#__kart_cartitems changes START
		$colarray=$this->getColumns("#__kart_cartitems");
		if (!in_array('currency', $colarray) ) {
			$query = "ALTER TABLE `#__kart_cartitems`	ADD `currency` varchar(16) NOT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_cartitems table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}


		if (!in_array('store_id', $colarray) ) {
			$query = "ALTER TABLE `#__kart_cartitems`	ADD  `store_id` int(11) default NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_cartitems table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array('original_price', $colarray) ) {
			$query = "ALTER TABLE `#__kart_cartitems`	ADD  `original_price` float(10,2) NOT NULL ";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_cartitems table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array('params', $colarray) ) {
			$query = "ALTER TABLE `#__kart_cartitems`	ADD    `params` text default NULL ";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_cartitems table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		// 	#__kart_payouts changes START
		$colarray=$this->getColumns("#__kart_payouts");
		if (in_array('transaction_id', $colarray) ) {
			$query = "ALTER TABLE  `#__kart_payouts` CHANGE  `transaction_id`  `transaction_id` VARCHAR( 20) DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_payouts table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// change coupon value field from int to float
		$colarray=$this->getColumns("#__kart_coupon");
		if (in_array('value', $colarray) ) {
			$query = "ALTER TABLE `#__kart_coupon`	MODIFY `value`  float(10,2) ";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_coupon table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		$colarray=$this->getColumns("#__kart_coupon");
		if (in_array('item_id', $colarray) ) {
			$query = "ALTER TABLE  `#__kart_coupon` CHANGE  `item_id`  `item_id` VARCHAR(50) DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_coupon table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// Since 2.2
		$colarray=$this->getColumns("#__kart_coupon");
		if (in_array('params', $colarray))
		{
			$query = "ALTER TABLE  `#__kart_coupon` CHANGE `params` `extra_params` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $img_ERROR . JText::_('Unable to Alter #__kart_coupon table') . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}
		// Since 2.2.1
		elseif (!in_array('extra_params', $colarray))
		{
			$query = "ALTER TABLE  `#__kart_coupon` ADD `extra_params` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $img_ERROR . JText::_('Unable to Alter #__kart_coupon table for column extra_params.') . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}

		$colarray=$this->getColumns("#__kart_coupon");

		if (in_array('user_id', $colarray) ) {
			$query = "ALTER TABLE  `#__kart_coupon` CHANGE  `user_id`  `user_id` VARCHAR(50) DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_coupon table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		$colarray=$this->getColumns("#__kart_coupon");
		if (in_array('user_id', $colarray) ) {
			$query = "ALTER TABLE  `#__kart_coupon` CHANGE  `user_id`  `user_id` VARCHAR(50) DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_coupon table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array('store_id', $colarray) ) {
			$query = "ALTER TABLE  `#__kart_coupon` ADD `store_id` int(11) NOT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_coupon table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array('item_id', $colarray) ) {
			$query = "ALTER TABLE  `#__kart_coupon` ADD `item_id`  VARCHAR(50)  NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_coupon table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array('user_id', $colarray) ) {
			$query = "ALTER TABLE  `#__kart_coupon` ADD `user_id` VARCHAR(50) NOT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_coupon table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		// 	END #__kart_coupon changes
		// START ::lter #__kart_order_item tb:: change `original_price`and `param`  *** START ***
		$colarray=$this->getColumns("#__kart_order_item");

		if (!in_array("original_price", $colarray) ) {
			$query = "ALTER TABLE  `#__kart_order_item` ADD  original_price float(10,2) NOT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Alter #__kart_order_item table for original price and param").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array("params", $colarray) ) {
			$query = "ALTER TABLE  `#__kart_order_item` ADD  `params` text default NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Alter #__kart_order_item table for original price and param").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array("store_id", $colarray) ) {
			$query = "ALTER TABLE  `#__kart_order_item` ADD  `store_id` int(11) NOT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Alter #__kart_order_item table for original price and param").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (in_array("original_price", $colarray) ) {
			$query = "ALTER TABLE  `#__kart_order_item` CHANGE  `original_price` `original_price` float(10,2) NOT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Alter #__kart_order_item table for original price and param").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (in_array("params", $colarray) ) {
			$query = "ALTER TABLE  `#__kart_order_item` CHANGE  `params`  `params` text default NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Alter #__kart_order_item table for original price and param").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// Added in 2.5
		if (!in_array("variant_item_id", $colarray) ) {
			$query = "ALTER TABLE `#__kart_order_item`	ADD `variant_item_id` int(11) default '0' AFTER `item_id`";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add status column in #__kart_order_item table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		// ** END alter #__kart_order_item tb:: change `original_price`and `param`  *** END ***
		// ** START ::lter #__kart_cartitems tb:: change `original_price`and `param`  *** START ***
		$colarray=$this->getColumns("#__kart_cartitems");
		if (in_array("original_price", $colarray) ) {
			$query = "ALTER TABLE  `#__kart_cartitems` CHANGE  `original_price` `original_price` float(10,2) NOT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Alter #__kart_cartitems table for original price and param").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (in_array("params", $colarray) ) {
			$query = "ALTER TABLE  `#__kart_cartitems` CHANGE  `params` `params` text default NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Alter #__kart_cartitems table for original price and param").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		// END alter #__kart_cartitems tb:: change `original_price`and `param`  *** END ***

		$colarray=$this->getColumns("#__kart_order_item");
		if (!in_array("status", $colarray) ) {
			$query = "ALTER TABLE `#__kart_order_item`	ADD `status` varchar(100) default NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add status column in #__kart_order_item table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		//`originalBasePrice` float(10,2) NOT NULL COMMENT 'Product price without field discount'
		if (!in_array("originalBasePrice", $colarray) ) {
			$query = "ALTER TABLE `#__kart_order_item`	ADD `originalBasePrice` float(10,2) NOT NULL COMMENT 'Product price without field discount'";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add originalBasePrice column in #__kart_order_item table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		//ALTER TABLE `m6n2y_kart_itemattributes` ADD `attribute_compulsary` BOOLEAN NOT NULL

		// KART_ITEMS :: add category ,sku,images,description, video_link ,cdate,mdate columns in kart_items tables
		$colarray=$this->getColumns("#__kart_items");
		if (!in_array("category", $colarray) ) {
			$query = "ALTER TABLE `#__kart_items` ADD `category`  varchar(200) DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add category column in #__kart_items table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array("sku", $colarray) ) {
			$query = "ALTER TABLE `#__kart_items` ADD `sku`  varchar(200) DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add sku column in #__kart_items table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array("images", $colarray) ) {
			$query = "ALTER TABLE `#__kart_items` ADD `images`  varchar(200) DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add images column in #__kart_items table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array("description", $colarray) ) {
			$query = "ALTER TABLE `#__kart_items` ADD `description`  text DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add description column in #__kart_items table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// if present vedio_link then chage to video_lnk
		if (in_array("vedio_link", $colarray) ) {
			$query = "ALTER TABLE `#__kart_items` CHANGE `vedio_link` `video_link`  varchar(200) DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add vedio_link column in #__kart_items table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		// if not present then add
		$colarray=$this->getColumns("#__kart_items"); /*by vbmundhe dont remove */
		if (!in_array("video_link", $colarray) ) {
			$query = "ALTER TABLE `#__kart_items` ADD `video_link`  varchar(200) DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add video_link column in #__kart_items table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (!in_array("cdate", $colarray) ) {
			$query = "ALTER TABLE `#__kart_items` ADD `cdate` datetime DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add cdate column in #__kart_items table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array("mdate", $colarray) ) {
			$query = "ALTER TABLE `#__kart_items` ADD `mdate` datetime DEFAULT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add mdate column in #__kart_items table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array("state", $colarray) ) {
			$query = "ALTER TABLE `#__kart_items` ADD  `state` tinyint(3) NOT NULL DEFAULT '1'";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add state column in #__kart_items table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array("featured", $colarray) ) {
			$query = "ALTER TABLE `#__kart_items` ADD  `featured` tinyint(3) NOT NULL DEFAULT '0'";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add featured column in #__kart_items table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array("store_id", $colarray) ) {
			$query = "ALTER TABLE `#__kart_items` ADD  `store_id` int(11) NOT NULL";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add featured column in #__kart_items table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// Since 2.2.
		if (!in_array("metakey", $colarray))
		{
			$query = "ALTER TABLE `#__kart_items` ADD `metakey` text NOT NULL";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $img_ERROR . JText::_("Unable to Add metakey column in #__kart_items table") . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}

		// Since 2.2.
		if (!in_array("metadesc", $colarray))
		{
			$query = "ALTER TABLE `#__kart_items` ADD `metadesc` text NOT NULL";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $img_ERROR . JText::_("Unable to Add metadesc column in #__kart_items table") . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}

		// Since 2.2.
		if (!in_array("item_length", $colarray))
		{
			$query = "ALTER TABLE `#__kart_items` ADD `item_length` decimal(15,8) NOT NULL";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $img_ERROR . JText::_("Unable to Add item length column in #__kart_items table") . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}

		// Since 2.2.
		if (!in_array("item_length", $colarray))
		{
			$query = "ALTER TABLE `#__kart_items` ADD `item_width` decimal(15,8) NOT NULL";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $img_ERROR . JText::_("Unable to Add item width column in #__kart_items table") . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}

		// Since 2.2.
		if (!in_array("item_length", $colarray))
		{
			$query = "ALTER TABLE `#__kart_items` ADD `item_height` decimal(15,8) NOT NULL";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $img_ERROR . JText::_("Unable to Add item height column in #__kart_items table") . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}

		// Since 2.2.
		if (!in_array("item_length", $colarray))
		{
			$query = "ALTER TABLE `#__kart_items` ADD `item_length_class_id` int(11) NOT NULL";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $img_ERROR . JText::_("Unable to Add item length class column in #__kart_items table") . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}

		// Since 2.2.
		if (!in_array("item_length", $colarray))
		{
			$query = "ALTER TABLE `#__kart_items` ADD `item_weight` decimal(15,8) NOT NULL";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $img_ERROR . JText::_("Unable to Add item weight column in #__kart_items table") . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}

		// Since 2.2.
		if (!in_array("item_length", $colarray))
		{
			$query = "ALTER TABLE `#__kart_items` ADD `item_weight_class_id` int(11) NOT NULL";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $img_ERROR . JText::_("Unable to Add item weight class column in #__kart_items table") . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}
		// Since 2.2.
		if (!in_array("taxprofile_id", $colarray))
		{
			$query = "ALTER TABLE `#__kart_items` ADD `taxprofile_id` int(11) NOT NULL";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $img_ERROR . JText::_("Unable to Add item taxprofile_id column in #__kart_items table") . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}

		// Since 2.2.
		if (!in_array("shipProfileId", $colarray))
		{
			$query = "ALTER TABLE `#__kart_items` ADD `shipProfileId` int(11) NOT NULL";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $img_ERROR . JText::_("Unable to Add item taxprofile_id column in #__kart_items table") . $BR;
				echo $db->getErrorMsg();

				return false;
			}
		}

		// END KART_ITEMS.

		// Since 2.2 Kart_store changes start
		$colarray=$this->getColumns("#__kart_store");

		if (!in_array("length_id", $colarray) ) {
			$query = "ALTER TABLE  `#__kart_store` ADD  `length_id` INT( 11 ) NULL DEFAULT NULL COMMENT 'This will be default length unite for store. Primary key of kart_lengths table'";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add length_id column in #__kart_store table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// Added in 2.5
		if (!in_array("pincode", $colarray) ) {
			$query = "ALTER TABLE  `#__kart_store` ADD  `pincode` INT( 11 ) NOT NULL DEFAULT '0' AFTER `address`";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add length_id column in #__kart_store table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		// Added in 2.5
		if (!in_array("city", $colarray) ) {
			$query = "ALTER TABLE `#__kart_store` ADD `city` varchar(255) NULL DEFAULT NULL AFTER `address`, ADD `land_mark` varchar(255) NULL DEFAULT NULL AFTER `city`, ADD `country` int(11) NULL DEFAULT NULL AFTER `city`, ADD `region` int(11) NULL DEFAULT NULL AFTER `city`";
			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add length_id column in #__kart_store table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (!in_array("weight_id", $colarray) ) {
			$query = "ALTER TABLE  `#__kart_store` ADD  `weight_id` INT( 11 ) NULL DEFAULT NULL COMMENT 'This will be default weight unite for store. Primary key of kart_weights table' ";

			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add weight_id column in #__kart_store table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		if (!in_array("taxprofile_id", $colarray) ) {
			$query = "ALTER TABLE  `#__kart_store` ADD  `taxprofile_id` INT(11) NULL DEFAULT NULL COMMENT 'This will be default ship profile id for store. Primary key of kart_shipprofile table table'";

			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add taxprofile_id column in #__kart_store table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (!in_array("shipprofile_id", $colarray) ) {

			$query = "ALTER TABLE  `#__kart_store` ADD  `shipprofile_id` INT( 11 ) NULL DEFAULT NULL COMMENT 'This will be default ship profile id for store. Primary key of _kart_shipprofile table table'";

			$db->setQuery($query);
			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_("Unable to Add shipprofile_id column in #__kart_store table").$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		/* since 2.5 for kart_itemattributeoptions*/
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_itemattributeoptions`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++) {
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('global_option_id', $field_array))
		{
			$query = "ALTER TABLE `#__kart_itemattributeoptions` ADD `global_option_id` INT(11) DEFAULT '0' AFTER `itemattributeoption_id`, ADD `child_product_item_id` INT(11) DEFAULT '0' AFTER `itemattribute_id`";
			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_itemattributes table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (!in_array('state', $field_array))
		{
			$query = "ALTER TABLE `#__kart_itemattributeoptions` ADD `state` TINYINT(11) DEFAULT '1' AFTER `ordering`";
			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_itemattributes table. [State Column]').$BR;
				echo $db->getErrorMsg();

				return false;
			}
		}

		// Add colum in table kart_customer_address
		$db =  JFactory::getDBO();
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_customer_address`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$field_array[] = $columns[$i]->Field;
		}

		if (!in_array('last_used_for_billing', $field_array) && !in_array('last_used_for_shipping', $field_array))
		{
			$query = "ALTER TABLE `#__kart_customer_address`
						ADD `last_used_for_billing` tinyint(4) NOT NULL DEFAULT '0',
						ADD COLUMN `last_used_for_shipping` tinyint(4) NOT NULL DEFAULT '0'";
			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_customer_address table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
			else
			{
				// DB changes for version 2.8 and above
				$this->migrateCustomerAddresses();
			}
		}

		if (in_array('middlename', $field_array))
		{
			$query = "ALTER TABLE `#__kart_customer_address`
						CHANGE `middlename` `middlename` varchar(250) DEFAULT NULL";
			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_customer_address table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (in_array('zipcode', $field_array))
		{
			$query = "ALTER TABLE `#__kart_customer_address`
						CHANGE `zipcode` `zipcode` varchar(255) DEFAULT NULL";
			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter kart_customer_address table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		$query="CREATE TABLE IF NOT EXISTS `#__kart_orders_history` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `order_id` int(11) NOT NULL,
				  `order_item_id` int(11) NOT NULL,
				  `creater_id` int(11) DEFAULT NULL,
				  `mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `customer_notified` tinyint(1) NOT NULL,
				  `order_item_status` varchar(100) DEFAULT NULL,
				  `note` text,
				  PRIMARY KEY (`id`)
				)";
		$db->setQuery($query);
		$db->execute();

		$query="CREATE TABLE IF NOT EXISTS `#__kart_orders_xref` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`order_id` int(11) NOT NULL,
					`mdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					`extra` text,
					PRIMARY KEY (`id`)
				)";
		$db->setQuery($query);
		$db->execute();

		$query = "CREATE TABLE IF NOT EXISTS `#__kart_customer_address` (
				`id` int(11) NOT NULL auto_increment,
				`user_id` int(11),
				`firstname` varchar(150),
				`middlename` varchar(250) DEFAULT NULL,
				`lastname` varchar(150),
				`vat_number` varchar(50),
				`phone` varchar(15),
				`address_title` varchar(250),
				`user_email` varchar(250),
				`address` varchar(250),
				`land_mark` varchar(250),
				`zipcode` varchar(255) DEFAULT NULL,
				`country_code` int(10),
				`state_code` int(10),
				`city` varchar(150),
				`last_used_for_billing` tinyint(4) NOT NULL DEFAULT '0',
				`last_used_for_shipping` tinyint(4) NOT NULL DEFAULT '0',
				PRIMARY KEY  (`id`)
			)";

		$db->setQuery($query);
		$db->execute();

		$db = JFactory::getDbo();
		$field_array = array();
		$query = "SHOW COLUMNS FROM `#__kart_zoneShipMethodRates`";
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++)
		{
			$field_array[] = $columns[$i]->Field;
		}

		if (in_array('rangeFrom', $field_array))
		{
			$query = "ALTER TABLE `#__kart_zoneShipMethodRates` MODIFY COLUMN `rangeFrom` float(11,4) DEFAULT '0'";

			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_zoneShipMethodRates table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}

		if (in_array('rangeTo', $field_array))
		{
			$query = "ALTER TABLE `#__kart_zoneShipMethodRates` MODIFY COLUMN `rangeTo` float(11,4) DEFAULT '9999'";

			$db->setQuery($query);

			if (!$db->execute() )
			{
				echo $img_ERROR.JText::_('Unable to Alter #__kart_zoneShipMethodRates table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
	}// end of qtc_alter andupdate

	/*	Tag line, version etc
	 *
	 *
	 * */
	function taglinMsg()
	{

		echo JText::_(	"<h4>Thank you for installing Quick2Cart by Techjoomla, the powerful ecommerce component for Joomla! </h4>");
	//	echo JText::_('<br/><h1>Quick2Cart - Quick, Simple Ecommerce For Joomla</h1>	');

		/// version check
		$path=JPATH_ROOT . '/administrator/components/com_quick2cart/quick2cart.xml';
		$xml =JFactory::getXML($path );
		$oldversion=$xml->version;

		require_once(JPATH_ROOT . '/components/com_quick2cart/helper.php');
		$helperobj= new comquick2cartHelper;
		$latestversion=$helperobj->getVersion();

		if (version_compare($oldversion, $latestversion, 'lt'))
		{
			echo "<span id='NewVersion' style='padding-top: 5px; color: red; font-weight: bold; padding-left: 5px;'>". JText::_("It seems that you have installed an older version. Latest Version is : "). $latestversion ."</span>";
		}

		echo "<br/>";
	} // end of tagline msg

	/*
	 * @return : this function return array of column names
	 * */
	function getColumns($table)
	{
		$db = JFactory::getDBO();

		$field_array = array();
		$query = "SHOW COLUMNS FROM ".$table;
		$db->setQuery($query);
		$columns = $db->loadobjectlist();

		for ($i = 0; $i < count($columns); $i++) {
			$field_array[] = $columns[$i]->Field;
		}
		return $field_array;
	}
	/* Migration start (user item_id insted of product_id,parent) */

	function migrateTables()
	{
		$tablearray= array();
			$table['name']="#__kart_itemattributes";
			$table['key']="itemattribute_id";
		$tablearray[]=$table;
			$table['name']="#__kart_order_item";
			$table['key']="order_item_id";
		$tablearray[]=$table;
			$table['name']="#__kart_cartitems";
			$table['key']="cart_item_id";
		$tablearray[]=$table;


		$db =  JFactory::getDBO();

		foreach ($tablearray as $tb)
		{
			$colarray='';
			$colarray=$this->getColumns($tb['name']);

			if (!in_array('item_id', $colarray))
			{
				$query = "ALTER TABLE `".$tb['name']."`
							ADD `item_id` int(11) NOT NULL";

				$db->setQuery($query);
				if (!$db->execute() )
				{
					echo $img_ERROR.JText::_('Unable to Alter kart_itemattributes table').$BR;
					echo $db->getErrorMsg();
					return false;
				}
				$this->migrateData($tb['name'],$tb['key']);
			}
		}
	}

	function migrateData($tbname,$primarykey)
	{
		$db =  JFactory::getDBO();
		$query="select ".$primarykey." ,parent, product_id from ".$tbname;
		$db->setQuery($query);
		$rawdata = $db->loadAssocList();
		foreach ($rawdata as $rec)
		{
			$item_id=0;
			$item_id=$this->getitemid($rec['product_id'],$rec['parent']);
			//1 update item id againt primary key
			$row = new stdClass;
			$row->$primarykey=$rec[$primarykey];
			$row->item_id=$item_id;
			if (!$db->updateObject($tbname, $row, $primarykey))
			{
				echo $this->_db->stderr();
				return false;
			}

		}

		//2.delete product_id,parent coloum
			$colarray='';
			$colarray=$this->getColumns($tbname);
			if (in_array('product_id', $colarray))
			{
			$query = "ALTER TABLE `".$tbname."` DROP column `product_id`";
			$db->setQuery($query);
			$db->execute();
			}
			if (in_array('parent', $colarray))
			{
			$query = "ALTER TABLE `".$tbname."` DROP column `parent`";
			$db->setQuery($query);
			$db->execute();
			}

	}

	function updateItemId($tbname,$primarykey,$item_id)
	{
		$db =  JFactory::getDBO();
		$row = new stdClass;
		$row->$primarykey=$primarykey;

			if (!$db->updateObject($tbname, $row, $primarykey))
			{
				echo $this->_db->stderr();
				return false;
			}
	}
	function getitemid($product_id,$client)
	{
		$db = JFactory::getDBO();
		$query = "SELECT `item_id` FROM `#__kart_items`  where `product_id`=".(int)$product_id. " AND parent='$client'";

		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;
	}

	/**
	 * Add Uncategorised __categories in #__categories table
	 * */
	function addUncategorisedCat()
	{
			$db = JFactory::getDBO();
			$query  = 'SELECT `id` FROM `#__categories` WHERE `extension` = \'com_quick2cart\' AND `title`=\'Uncategorised\'' ;
			$db->setQuery($query);
			$result = $db->loadResult();
			if (empty($result))
			{
				$catobj=new stdClass;
				$catobj->title = 'Uncategorised';
				$catobj->alias = 'uncategorised';;
				$catobj->extension="com_quick2cart";
				$catobj->path=" uncategorised";
				$catobj->parent_id=1;
				$catobj->level=1;

				$paramdata=array();
				$paramdata['category_layout']='';
				$paramdata['image']='';
				$catobj->params=json_encode($paramdata);

				//LOGGED user id
				$user = JFactory::getUser();
				$catobj->created_user_id=$user->id;
				$catobj->language="*";
				//$catobj->description = $category->description;

				$catobj->published = 1;
				$catobj->access = 1;
				if (!$db->insertObject('#__categories',$catobj,'id'))
				{
					echo $db->stderr();
					return false;
				}
			}
	}
		/**
		 * Create default store on install
		 * */
	function createSuperuserstore()
  {
		$storeMsg=array();
		// CHECK EXISTANCE OF DEFAULT STORE

		$db=JFactory::getDBO();
		$query = "SELECT `id`,`extra` FROM `#__kart_store` WHERE `extra` IS NOT NULL";
		$db->setQuery($query);
		$storedata = $db->loadAssocList();
		$default_store=0;
		foreach ($storedata as $data)
		{
			$extraField=json_decode($data['extra'],1);
			if (!empty($extraField['default']))
			{
				$default_store=$data['id'];
				break;
			}
		}
		// if no default store is found then create
		if (empty($default_store))
		{
				$product_path = JPATH_SITE . '/components/com_quick2cart/models/vendor.php';
				if (!class_exists('quick2cartModelVendor'))
				{
					//require_once $path;
					 JLoader::register('quick2cartModelVendor', $product_path );
					 JLoader::load('quick2cartModelVendor');
				}

				$user=JFactory::getUser();
				global $mainframe;
				$mainframe = JFactory::getApplication();
				$jinput=$mainframe->input;
				$post=$jinput->post;
				//$post=array();
				$sitename=$mainframe->getCfg('sitename');
				$post->set('title',$sitename);
				$post->set('description','');
				$post->set('companyname','');
				$post->set('address','');
				$post->set('phone','');
				$post->set('email',$mainframe->getCfg('mailfrom'));
				$post->set('paymentMode',1);
				$post->set('otherPayMethod',$mainframe->getCfg('mailfrom'));
				$post->set('storeVanityUrl','');

				$extraArray=array();
				$extraArray['default']=1;
				$extraArray=json_encode($extraArray);
				$post->set('extra',$extraArray);
				$avtar_path='components/com_quick2cart/images/no_user.png';
				$post->set('avatar',$avtar_path);
				$storeheader_path='components/com_quick2cart/images/header_default2.jpg';
				$post->set('storeheader',$storeheader_path);
				$quick2cartModelVendor=new quick2cartModelVendor();
				$quick2cartModelVendor->store($post);

				$default_store=$sitename." ( Default Store ) ";
				$storeMsg[$default_store]="Created"; //JText::_("COM_QUICK2CART_ADDED_STORE_ON_INSTALL_MSG");;

				return $storeMsg;
		}

	}
	/**
	 * This function add dashboard menu entry in mainmainu
	 * */
	function addMenuItems()
	{
		$addedMenuMsg=array();
		$lang = JFactory::getLanguage();
		$lang->load('com_quick2cart', JPATH_ADMINISTRATOR);
		$db = JFactory::getDBO();

		// Get new component id.
		$component    = JComponentHelper::getComponent('com_quick2cart');
		$component_id = 0;

		if (is_object($component) && isset($component->id))
		{
			$component_id = $component->id;
		}
/*
		$column_name = JOOMLA_MENU_NAME;
		$column_cid  = JOOMLA_MENU_COMPONENT_ID;*/

		// Get the default menu type
		// 2 Joomla bugs occur in /Administrator mode
		// Bug 1: JFactory::getApplication('site') failed. It always return id = 'administrator'.
		// Bug 2: JMenu::getDefault('*') failed. JAdministrator::getLanguageFilter() doesn't exist.
		// If these 2 bugs are fixed, we can call the following syntax:
		// $defaultMenuType	= JFactory::getApplication('sites')->getMenu()->getDefault()->menutype;
		jimport('joomla.application.application');
		$defaultMenuType = JApplication::getInstance('site')->getMenu()->getDefault('workaround_joomla_bug')->menutype;

		// Update the existing menu items.
		$row				= JTable::getInstance ( 'menu', 'JTable' );
		$row->menutype		= $defaultMenuType;
		$row->title	=JText::_('COM_QUICK2CART_DASHBOARDMENU');
		$row->alias			='vendor-dashboard';
		$row->path			='vendor-dashboard';
		$row->access		= 1;
		$row->link			= 'index.php?option=com_quick2cart&view=vendor&layout=cp';
		$row->type			= 'component';
		$row->published		= '0';
		$row->component_id	= $component_id;
		$row->id			= null; //new item
		$row->language		= '*';


		$row->check();

		$ispresent=$this->isMenuItemPresent($row->link);

		if (empty($ispresent))
		{
			// ADD MENU
			$var=$row->store();
			// UPDATE MENU
			$query = 'UPDATE '. $db->quoteName( '#__menu' )
				 . ' SET `parent_id` = ' .$db->quote(1)
				 . ', `level` = ' . $db->quote(1)
				 . ' WHERE `id` = ' . $db->quote($row->id) ;
			$db->setQuery( $query );
			$db->execute();

			if ($db->getErrorNum())
			{
				return false;
			}

			// AS ADDD DASHBOARD MENU. Use it for display
			$menuIndex_Name=$row->title . " MENU ";//JText::_("COM_QUICK2CART_MENU_ON_INSTALL");
			$addedMenuMsg[$menuIndex_Name]=JText::_("COM_QUICK2CART_ADDED_MENU_ON_INSTALL");
		}

		$row				= JTable::getInstance ( 'menu', 'JTable' );
		$row->menutype		= $defaultMenuType;
		$row->title	=JText::_('COM_QUICK2CART_ALLPRODUCTSMENU');
		$row->alias			='all-products';
		$row->path			='all-products';
		$row->access		= 1;
		$row->link			= 'index.php?option=com_quick2cart&view=category&layout=default';
		$row->type			= 'component';
		$row->published		= '0';
		$row->component_id	= $component_id;
		$row->id			= null; //new item
		$row->language		= '*';

		$row->check();

		$ispresent=$this->isMenuItemPresent($row->link);

		if (empty($ispresent))
		{
			// ADD new MENU
			$var=$row->store();
			// UPDATE
			$query = 'UPDATE '. $db->quoteName( '#__menu' )
				 . ' SET `parent_id` = ' .$db->quote(1)
				 . ', `level` = ' . $db->quote(1)
				 . ' WHERE `id` = ' . $db->quote($row->id) ;
			$db->setQuery( $query );
			$db->execute();

			if ($db->getErrorNum())
			{
				// IF ERROR FOR SECOND MENU THEN RETRUN FIRST MENU DETAILS
				// AS ADDD ALL PRODUCT MENU. Use it for display
				$menuIndex_Name=$menuIndex_Name=$row->title ." " . JText::_("COM_QUICK2CART_MENU_ON_INSTALL");
				$addedMenuMsg[$menuIndex_Name]=JText::_("COM_QUICK2CART_ADDED_MENU_ON_INSTALL");
				//return false;
			}

			// AS ADDD ALL PRODUCT MENU. Use it for display
			$menuIndex_Name=$row->title . " MENU ";//JText::_("COM_QUICK2CART_MENU_ON_INSTALL");
			$addedMenuMsg[$menuIndex_Name]=JText::_("COM_QUICK2CART_ADDED_MENU_ON_INSTALL");;
			return $addedMenuMsg;
			//return true;
		}
	}
	/*chk whether menu for link is resent or not*/
	function isMenuItemPresent($link,$menutype='mainmenu')
	{
		$db = JFactory::getDBO();
		$query = 'SELECT `id` from `#__menu` where `link` LIKE '."\"%$link%\" ";

		$db->setQuery( $query );
		return $db->loadResult();
	}
		/**
	 * Add default toolbar  menu
	 */
	function addDefaultToolbarMenus()
	{

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$target = JPATH_ROOT . '/administrator/components/com_community';
		if (JFolder::exists($target))
		{
				// GETTING COMPONENT ID
			$component    = JComponentHelper::getComponent('com_quick2cart');
			$component_id = 0;

			if (is_object($component) && isset($component->id))
			{
				$component_id = $component->id;
			}

			$db				= JFactory::getDBO();
			$file			= JPATH_ROOT . '/administrator/components/com_quick2cart/toolbar.xml';
			$menu_name		= "title";
			$menu_parent	= "parent_id";
			$menu_level		= "level";
			$items			= new SimpleXMLElement( $file , NULL , true );
			$items			= $items->items;

			$i	= 1;

			foreach ($items->children() as $item )
			{			// each menu
				$obj				= new stdClass();
				$obj->$menu_name	=(string) $item->name;
				$obj->$menu_name=JText::_($obj->$menu_name);

				$obj->alias			= (string) $item->alias;
				$obj->path			= (string) $item->alias;
				$obj->link			= (string) $item->link;
				$obj->access		= (string) $item->access;
				$obj->menutype		= 'jomsocial';
				$obj->type			= 'component';
				$obj->published		= 1;
				$obj->$menu_parent	= 1;
				$obj->level	= 1;
				$obj->language		= '*';
				$obj->component_id=$component_id;

				//GETTING CHILDS
				$childs	= $item->childs;
				$manuid=$this->menuExists($obj->link,$obj->menutype);

				if (!empty($manuid))
				{// update menu
					$parentId= $obj->id= $manuid;
					if (!$db->updateObject('#__menu', $obj, 'id'))
					{
						echo $this->_db->stderr();
						return false;
					}
				}
				else
				{
							$query 	= 'SELECT ' . $db->quoteName( 'rgt' ) . ' '
					. 'FROM ' . $db->quoteName( '#__menu' ) . ' '
						. 'ORDER BY ' . $db->quoteName( 'rgt' ) . ' DESC LIMIT 1';

					$db->setQuery( $query );
					$obj->lft 	= $db->loadResult() + 1;
					$totalchild = $childs?count($childs->children()):0;
					$obj->rgt	= $obj->lft + $totalchild * 2 + 1;
					// insert
					$db->insertObject( '#__menu' , $obj );
					//J1.6: menu item ordering follow lft and rgt
					if ($db->getErrorNum()) {
						return false;
					}
					$parentId		= $db->insertid();
				}

				// CHECK FOR CHILDS
				if ( $childs )
				{
					$x	= 1;
					foreach ($childs->children() as $child )
					{
						$childObj		= new stdClass();
						$childObj->$menu_name	= (string) $child->name;
						$childObj->$menu_name=JText::_($childObj->$menu_name);

						$childObj->alias		= (string) $child->alias;
						$childObj->path=$item->alias.'/'.$childObj->alias;

						$childObj->link			= (string) $child->link;
						$childObj->access		= (string) $item->access;
						$childObj->menutype		= 'jomsocial';
						$childObj->type			= 'component';
						$childObj->published	= 1;
						$childObj->$menu_parent	= $parentId;
						$childObj->$menu_level	= 1 + 1;
						$childObj->language		= '*';
						$childObj->component_id=$component_id;

						$childMenuId=$this->menuExists($childObj->link,$childObj->menutype);

						if (!empty($childMenuId))
						{
										// update CHILD menu
								$childObj->id= $childMenuId;
								if (!$db->updateObject('#__menu', $childObj, 'id'))
								{
									echo $this->_db->stderr();
									return false;
								}
						}
						else
						{
							//J1.6: menu item ordering follow lft and rgt
							$childObj->lft			= $obj->lft + ($x - 1)* 2 + 1;
							$childObj->rgt			= $childObj->lft + 1;

							$db->insertObject( '#__menu' , $childObj );
							if ($db->getErrorNum()) {
								return false;
							}
						}
						$x++;
					}
				}
				$i++;
			}
			return true;
		}
	}

	function menuExists($link, $menutype=null)
	{
		$db = JFactory::getDBO();
		$query = 'SELECT `id`
		 FROM `#__menu`
		 WHERE `link` LIKE "%' . $link . '%"';

		if ($menutype != null)
		{
			$query .= ' AND `menutype`="' . $menutype .'"';
		}

		$db->setQuery( $query );
		return $db->loadResult();
	}

	function fix_menus_on_update()
	{
		// since 2.2
		// Fix jomsocial menus
		$childMenuId = $this->menuExists('index.php?option=com_quick2cart&view=managecoupon', 'jomsocial');
		$db = JFactory::getDBO();

		if (!empty($childMenuId))
		{
			$childObj = new stdClass();
			$childObj->link = 'index.php?option=com_quick2cart&view=coupons&layout=my';
			$childObj->id = $childMenuId;

			if (!$db->updateObject('#__menu', $childObj, 'id'))
			{
				echo $this->_db->stderr();
				return false;
			}
		}

		// Fix q2c menus
		$changedMenus = array();

		// old , new
		// Since 2.2
		$changedMenus[] = array('index.php?option=com_quick2cart&view=vendor&layout=default', 'index.php?option=com_quick2cart&view=stores&layout=my');
		$changedMenus[] = array('index.php?option=com_quick2cart&view=managecoupon', 'index.php?option=com_quick2cart&view=coupons&layout=my');
		$changedMenus[] = array('index.php?option=com_quick2cart&view=reports&layout=mypayouts', 'index.php?option=com_quick2cart&&view=payouts&layout=my');

		foreach ($changedMenus as $menu)
		{
			$childMenuId = $this->menuExists($menu[0]);

			if (!empty($childMenuId))
			{
				$childObj = new stdClass();
				$childObj->link = $menu[1];
				$childObj->id = $childMenuId;

				if (!$db->updateObject('#__menu', $childObj, 'id'))
				{
					echo $this->_db->stderr();
					return false;
				}
			}
		}
	}

	function migrateDbfix()
	{
		$db = JFactory::getDBO();
		$config=JFactory::getConfig();
		if (JVERSION>=3.0)
		{
			$dbname=$config->get('db');
			$dbprefix=$config->get('dbprefix');
		}
        else
        {
			$dbname=$config->getValue('config.db');
		    $dbprefix=$config->getvalue('config.dbprefix');
	  	}

		if ($this->componentStatus == "update")
		{
			// Check wheter previoulsy exist or not
			$query = "SHOW TABLES LIKE '" . $dbprefix . "kart_users_backup';";
			$db->setQuery($query);
			$backup_exists = $db->loadResult();
			$query="Select COUNT(*) From #__kart_users";
			$db->setQuery($query);
			$billing_data=$db->loadObjectlist();

			if (!$backup_exists && !empty($billing_data))
			{
				// Check whether tj field component installed or not
				$query="SHOW COLUMNS FROM #__tj_country WHERE `Field` = 'country_jtext'";
				$db->setQuery($query);
				$check=$db->loadResult();

				$latestversion = 2.2;
				if (!$check)
				{
					echo "<span id='NewVersion' style='padding-top: 5px; color: red; font-weight: bold; padding-left: 5px;'>". JText::_("COM_QUICK2CART_PLS_INSTLL_TJFIEDLS_COMP"). $latestversion ."</span>";
					return;
				}

				$document = JFactory::getDocument();
				$document->addScript(JUri::root(true) . '/media/techjoomla_strapper/js/akeebajq.js');
				?>
				<script src="<?php echo JUri::root(true) . '/media/techjoomla_strapper/js/akeebajq.js'; ?>" type="text/javascript"></script>
				<script type="text/javascript">

			function migrateOrders()
				{
					var root_url="<?php echo JUri::root(); ?>";
					techjoomla.jQuery.ajax({
						url: root_url + 'index.php?option=com_quick2cart&task=cart.migrateCountryRelatedFields&tmpl=component',
						type: 'GET',
						dataType: '',
						beforeSend: function()
						{	techjoomla.jQuery('#qtcLoader_image_div').show();
						},
						complete: function()
						{
							techjoomla.jQuery('#qtcLoader_image_div').hide();

						},
						success: function(data)
						{
							if (data==1)
							{
								techjoomla.jQuery('#qtcStatus').html("<?php echo JText::_("COM_QUICK2CART_MIGRATION_COMPLETED"); ?>");
								techjoomla.jQuery('#qtcStatus').show();
								techjoomla.jQuery('#qtc_migrate_btnDiv').hide();
							}

						}
					});
				}
			</script>
			<div class="q2c-wrapper techjoomla-bootstrap">
				<div class="row-fluid">
					<div class="span12">
					<div id='qtc_migrate_btnDiv'>
						<div class="alert alert-info">
							<input type="button" class="btn btn-primary" value="<?php echo JText::_("COM_QUICK2CART_CLICK_HERE"); ?>"
							onclick="migrateOrders();">
							<b><?php echo JText::_("COM_QUICK2CART_MIGRATE_OLD_ORDERS"); ?></b>
						</div>
					</div>


					<?php
						$image_path = JUri::root() . "components/com_quick2cart/assets/images/ajax.gif";
					?>
					<div class="" id="qtcLoader_image_div" style="display:none;margin-left:50%;">
						<img src='<?php echo $image_path; ?>' width="78" height="15" border="0"/>
					</div>
					<div class="alert alert-info" id="qtcStatus" style="display:none">

					</div>

				</div>
				</div>
			</div>
				<?php
			}
		}
	}

	function renameTable($table, $newTable, $appendDateTime = 1)
	{
		$db = JFactory::getDBO();
		$query = "RENAME TABLE " . $table . " TO " . $newTable;

		if ($appendDateTime)
		{
			$query = $query . '_' . date("Ymd_H_i_s");
		}

		$db->setQuery($query);
		if (!$db->execute())
		{
			echo $db->stderr();
			return false;
		}
		return true;
	}


	// Since 2.2
	function permissionsFix()
	{
		$db = JFactory::getDBO();
		$query = "SELECT id, rules FROM `#__assets` WHERE `name` = 'com_quick2cart' ";
		$db->setQuery($query);
		$result = $db->loadobject();

		if(strlen(trim($result->rules))<=3)
		{
			$obj = new Stdclass();
			$obj->id = $result->id;
			$obj->rules = '{"core.admin":[],"core.manage":[],"core.create":{"2":1},"core.delete":[],"core.edit":[],"core.edit.state":{"2":1},"core.edit.own":{"2":1}}';

			if (!$db->updateObject('#__assets', $obj, 'id'))
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage($db->stderr(), 'error');
			}
		}
	}

	/**
	 * This function is used to migrate the tax and shipping charge detail to new format.
	 * OLD: order tax = SUM(item Tax) similary for shipping charges
	 * NEW: Item level tax is not add in order tax fields. Order tax fields contain only order tax.
	 *
	 * @return  ''.
	 *
	 * @since   2.3.0
	 */
	function migrateTaxShipDetails()
	{
		$db     = JFactory::getDBO();
		$comquick2cartHelper = new Comquick2cartHelper;
		$path                = JPATH_SITE . '/components/com_quick2cart/models/cartcheckout.php';
		$Quick2cartModelcartcheckout = $comquick2cartHelper->loadqtcClass($path, "Quick2cartModelcartcheckout");

		// A.Check for column itemTaxShipIncluded. If present then only migration required else not.
		$query = "SHOW COLUMNS FROM `#__kart_orders`";
		$db->setQuery($query);
		$columns = $db->loadColumn();

		// B.If col is not present then add
		if (!in_array('itemTaxShipIncluded', $columns) )
		{
			// C. Else add column= 'itemTaxShipIncluded' to db
			$query = "ALTER TABLE  `#__kart_orders` ADD  `itemTaxShipIncluded` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Flag : whether order tax and shipping is summation of order item tax and ship or not. 1 =>  orderTax = sum(order item tax)'";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo JText::_('COM_QUICK2CART_UNABLE_TO_ALTER_COLUMN') . " - #__kart_orders.";
				echo $db->getErrorMsg();

				return 0;
			}

			// D. Set its value column value to 1

			$query = $db->getQuery(true);

			// Fields to update.
			$fields = array(
				$db->quoteName('itemTaxShipIncluded') . ' = 1'
			);

			// Conditions for which records should be updated.
			$conditions = array();

			$query->update($db->quoteName('#__kart_orders'))->set($fields);
			$db->setQuery($query);
			$result = $db->execute();
		}

		// E:
		$query = $db->getQuery(true);
		$query->select(' o.* ');
		$query->from('#__kart_orders as o');
		$query->where("(o.order_tax > 0 OR o.order_shipping > 0 )");
		$query->where("itemTaxShipIncluded=1");
		$query->order("o.id DESC");

		$db->setQuery($query);
		$orderList = $db->loadObjectList('id');

		foreach ($orderList as $order)
		{
			$query = $db->getQuery(true);
			$modifiedOrder = new stdClass;
			$modifiedOrder->id = $order->id;


			// Get order item details
			$query->select('order_id, sum(product_final_price) as totalItemprice,sum(item_tax) as totalItemTax,sum(item_shipcharges) as totalShipCharge');
			$query->from('#__kart_order_item as i');
			$query->where("order_id= " . $order->id);
			$db->setQuery($query);
			$itemDetail = $db->loadAssoc('id');

			// 1. Update original amount
			$modifiedOrder->original_amount = $itemDetail['totalItemprice'];


			// 2.update tax and ship
			$OrderLevelTax = $order->order_tax - $itemDetail['totalItemTax'];

			if ($OrderLevelTax >= 0)
			{
				$modifiedOrder->order_tax = $OrderLevelTax;
			}
			else
			{
				$modifiedOrder->order_tax = 0;
			}

			$OrderLevelShip = $order->order_shipping - $itemDetail['totalShipCharge'];

			if ($OrderLevelTax >= 0)
			{
				$modifiedOrder->order_shipping = $OrderLevelShip;
			}
			else
			{
				$modifiedOrder->order_shipping = 0;
			}


			// 3. Check for coupon
			$copDiscount = 0;

			if ($order->coupon_code)
			{
				$copDiscount = $Quick2cartModelcartcheckout->afterDiscountPrice($modifiedOrder->original_amount, $order->coupon_code ,"", "order", $modifiedOrder->id);

				$copDiscount = ($copDiscount >= 0) ? $copDiscount : 0;
			}

			// 4. Update amount column according to discount,tax,shipping details
			$modifiedOrder->amount = $modifiedOrder->original_amount + $modifiedOrder->order_tax + $modifiedOrder->order_shipping - $copDiscount;

			if (!$db->updateObject('#__kart_orders', $modifiedOrder, 'id'))
			{
				$this->setError($db->getErrorMsg());

				return 0;
			}
		}
	}

	/**
	 * Migrate coupon to 2.8 version
	 * 1. Changes in order item table : Add coupon from param->coupon code to coupon_code column.
	 * 2. we have to add old coupon price to discount_amount column
	 * 3. Update order table: original_amount= sum (all item final price), order's tax, ship column contain only order level taxation and shipping plugin detail
	 *
	 * @return  [type]  [description]
	 */
	public function migrateCouponChangesTO28version()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("order_item_id,order_id,	params,product_item_price,product_attributes_price, product_quantity, item_tax,item_shipcharges,product_final_price");
		$query->from('#__kart_order_item AS oi');
		$conditions = array(
			$db->quoteName('oi.params') . ' LIKE ' . $db->quote("%coupon_code%")
		);

		$query->where($conditions);
		$db->setQuery($query);
		$orderItems = $db->loadObjectList();


		if (!empty($orderItems))
		{
			// 1: Migrate coupon chnages in kart_order_items
			foreach ($orderItems as $oi)
			{

				$oitem                    = new stdClass;
				$oitem->order_item_id = $oi->order_item_id;

				// Final amount of order item (FP) = (base product price (OP) * qty) + Tax + ship - discount
				//  Discount = (base price product (OP)* qty) + Tax + ship - final amount of order item (FP)
				$discount = (($oi->product_item_price + $oi->product_attributes_price) * $oi->product_quantity) + $oi->item_tax + $oi->item_shipcharges - $oi->product_final_price;

				if ($discount > 0)
				{
					$oitem->discount = $discount;

					// Update params column
					$param = $oi->params;

					$paramArray = json_decode($param,true);

					// Add old coupon code in coupon_code column and discount in discount column
					if (isset($paramArray['coupon_code']))
					{
						$oitem->coupon_code = $paramArray['coupon_code'];

						unset($paramArray['coupon_code']);
						$oitem->params = json_encode($paramArray);
					}

					$status =  $db->updateObject('#__kart_order_item', $oitem, 'order_item_id');

					// If discount found
					if (!$db->updateObject('#__kart_order_item', $oitem, 'order_item_id'))
					{
						echo $db->stderr();

						return 0;
					}
				}
			}
		}

		// A.Check for column itemTaxShipIncluded. If present then only migration required else not.
		$query = "SHOW COLUMNS FROM `#__kart_orders`";
		$db->setQuery($query);
		$columns = $db->loadColumn();

		// B.If col is not present then add
		if (!in_array('migrateto28version', $columns) )
		{
			// C. Else add column= 'itemTaxShipIncluded' to db
			$query = "ALTER TABLE  `#__kart_orders` ADD  `migrateto28version` SMALLINT( 3 ) NOT NULL DEFAULT  '0' COMMENT 'Flag : to migrate to version 2.8.'";
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo JText::_('COM_QUICK2CART_UNABLE_TO_ALTER_COLUMN') . " - #__kart_orders {For migrateto28version column}.";
				echo $db->getErrorMsg();

				return 0;
			}

			// D. Set its value column value to 1

			$query = $db->getQuery(true);

			// Fields to update.
			$fields = array(
				$db->quoteName('migrateto28version') . ' = 1'
			);

			// Conditions for which records should be updated.
			$conditions = array();

			$query->update($db->quoteName('#__kart_orders'))->set($fields);
			$db->setQuery($query);
			$result = $db->execute();
		}

		// 2. Update original_amount, tax, shipping amount from order table
		$query = $db->getQuery(true);
		$query->select('  oi.`order_id` , SUM(  `product_item_price` ) AS product_item_price, SUM(  `product_attributes_price` ) AS product_attributes_price, SUM( `product_quantity` ) AS product_quantity, SUM(  `discount` ) AS discount, SUM(  `item_tax` ) AS item_tax, SUM(  `item_shipcharges` ) AS item_shipcharges, SUM(  `product_final_price` ) AS product_final_price,o.id, o.`original_amount` , o.`amount` , o.`order_tax` , o.`order_shipping` , o.`coupon_discount`  ');
		$query->from('#__kart_order_item AS oi');
		$query->join('INNER', '#__kart_orders AS o ON oi.`order_id` = o.`id`');
		$query->where("oi.`order_id` IS NOT NULL");
		$query->group("oi.`order_id`");
		$query->where("o.`migrateto28version` = 1");

		$db->setQuery($query);
		$orderList = $db->loadObjectList();

		if (!empty($orderList))
		{
			foreach ($orderList as $oiSumData)
			{
				$query = $db->getQuery(true);
				$modifiedOrder = new stdClass;
				$modifiedOrder->id = $oiSumData->order_id;

				// 1. Update original amount
				$modifiedOrder->original_amount = $oiSumData->product_final_price;

				// 2.update tax: order tax = Old order tax - sum(item tax)
				if (!empty($oiSumData->item_tax))
				{
					$newTax = $oiSumData->order_tax - $oiSumData->item_tax;
					$modifiedOrder->order_tax = ($newTax < 0) ? 0 : $newTax;
				}
				else
				{
					// Order level tax  is present for db order
					$modifiedOrder->order_tax = $oiSumData->order_tax;
				}

				// 3.update Ship: order ship = Old order shio - sum(item ship)
				if (!empty($oiSumData->item_shipcharges))
				{
					$newShip = $oiSumData->order_shipping - $oiSumData->item_shipcharges;
					$modifiedOrder->order_shipping = ($newShip < 0) ? 0 : $newShip;
				}
				else
				{
					// Order level shipping  is present for db order
					$modifiedOrder->order_shipping = $oiSumData->order_shipping;
				}

				// 4: Need to update discount ($modifiedOrder->amount i old amount)
				$orderDiscount = $modifiedOrder->original_amount + $modifiedOrder->order_tax + $modifiedOrder->order_shipping - $oiSumData->amount;

				$modifiedOrder->coupon_discount = ($orderDiscount > 0) ? $orderDiscount : 0;

				// 5: for amount
				$finalOrderAmt = $modifiedOrder->original_amount + $modifiedOrder->order_tax + $modifiedOrder->order_shipping - $modifiedOrder->coupon_discount;

				if ($oiSumData->amount == $finalOrderAmt)
				{
					$modifiedOrder->migrateto28version = 0;
				}

				//$modifiedOrder->amount = $finalOrderAmt;
				if (!$db->updateObject('#__kart_orders', $modifiedOrder, 'id'))
				{
					$this->setError($db->getErrorMsg());

					return 0;
				}
			}
		}
	}
	/**
	 * Function to add tables related to global attribute
	 *
	 * @return  null
	 *
	 * @since  2.5
	 * */
	function globalAttributeRelatedTables()
	{
		// Add global attribute set table
		$db = JFactory::getDBO();
		$field_array = array();
		$query="CREATE TABLE IF NOT EXISTS `#__kart_global_attribute_set` (
			`id` int(11) NOT NULL auto_increment,
			`global_attribute_set_name` varchar(255) NOT NULL,
			`global_attribute_ids` text default NULL,
			`state` tinyint(3) default '1',
			PRIMARY KEY  (`id`)
			)   DEFAULT CHARSET=utf8 COMMENT='Quick2Cart global attribute set info' AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		$db->execute();

		// Add global attribute option table
		$db = JFactory::getDBO();
		$field_array = array();
		$query="CREATE TABLE IF NOT EXISTS `#__kart_global_attribute_option` (
				  `id` int(11) NOT NULL auto_increment,
				  `option_name` varchar(255) NOT NULL,
				  `attribute_id` int(11) NOT NULL,
				  `ordering` int(11) NOT NULL,
				  PRIMARY KEY  (`id`)
				)   DEFAULT CHARSET=utf8 COMMENT='Quick2Cart global attribute options info' AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		$db->execute();

		// Add global attribute table
		$db = JFactory::getDBO();
		$field_array = array();
		$query="CREATE TABLE IF NOT EXISTS `#__kart_global_attribute` (
				  `id` int(11) NOT NULL auto_increment,
				  `attribute_name` varchar(255) NOT NULL,
				  `display_name` varchar(255) NOT NULL,
				  `state` tinyint(3) default '1',
				  PRIMARY KEY  (`id`)
				)   DEFAULT CHARSET=utf8 COMMENT='Quick2Cart global attributes info' AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		$db->execute();

		// Add category attribute set
		$db = JFactory::getDBO();
		$field_array = array();
		$query="CREATE TABLE IF NOT EXISTS `#__kart_category_attribute_set` (
				  `id` int(11) NOT NULL auto_increment,
				  `category_id` int(11) NOT NULL,
				  `attribute_set_id` int(11) NOT NULL,
				  PRIMARY KEY  (`id`)
				)   DEFAULT CHARSET=utf8 COMMENT='Quick2Cart category-attributeset mapping info' AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Function to add tables related to global attribute
	 *
	 * @return  null
	 *
	 * @since  2.5
	 * */
	function promotionRelatedTables()
	{
		// Add kart_promotions_rules table
		$db = JFactory::getDBO();
		$field_array = array();
		$query = "CREATE TABLE IF NOT EXISTS `#__kart_promotions_rules` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `promotion_id` int(11),
				  `condition_on` varchar(255),
				  `condition_on_attribute` varchar(255) COMMENT 'Used for where condition (Key,value). Eg key = category',
				  `condition_attribute_value` varchar(255) COMMENT 'Used for  where condition (Key,value).  Eg value = T-shirt category',
				  `quantity` int(11) DEFAULT NULL,
				  `is_compulsary` varchar(127),
				  `operation` varchar(255),
				  PRIMARY KEY (`id`)
				)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		$db->execute();

		// Add kart_promotion_discount table
		$db = JFactory::getDBO();
		$field_array = array();
		$query = "CREATE TABLE IF NOT EXISTS `#__kart_promotion_discount` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `promotion_id` int(11),
				  `discount` float(10,2) DEFAULT NULL,
				  `max_discount` float(10,2) DEFAULT NULL,
				  `currency` varchar(16) DEFAULT NULL,
				  `discount_type` varchar(60) COMMENT 'flat, percentage, quanity_discount',
				  PRIMARY KEY (`id`)
				)   DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Change the views folder according to bootstrap uses
	 *
 	 * @param   STRING  $appendDateTime  Add date time
	 *
	 * @return  ''.
	 *
	 * @since   2.3.2
	 */
	/*public function changeBSViews($changeTo = "bs3")
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$compPath = JPATH_SITE . '/components/com_quick2cart/';
		$moveStatus = false;

		if ($changeTo == "bs3")
		{
			// We have to rename view to bs3 format
			// Check for view_bs3 folder, if exists then we have to convert views -> views_bs2 and views_bs3 -> views
			// Other wise already converted to bs3
			$isBs3FolderExist = JPATH_SITE . '/components/com_quick2cart/views_bs3';

			if (JFolder::exists($isBs3FolderExist))
			{
				// Convert views -> views_bs2
				$status = JFolder::move($compPath . 'views', $compPath . 'views_bs2');

				// And convert views_bs3 -> views
				if ($status)
				{
					$moveStatus = JFolder::move($compPath . 'views_bs3', $compPath . 'views');
				}
			}
			else
			{
				$moveStatus = false;
			}

		}
		elseif ($changeTo == "bs2")
		{
			// We have to rename view to bs2 format

			// Check for view_bs3 folder, if exists then we have to convert views -> views_bs2 and views_bs3 -> views
			// Other wise already converted to bs3
			$folderExist = JPATH_SITE . '/components/com_quick2cart/views_bs2';

			if (JFolder::exists($folderExist))
			{
				// Convert views -> views_bs2
				$status = JFolder::move($compPath . 'views', $compPath . 'views_bs3');

				// And convert views_bs3 -> views
				if ($status)
				{
					$moveStatus = JFolder::move($compPath . 'views_bs2', $compPath . 'views');
				}
			}
			else
			{
				$moveStatus = false;
			}
		}

		return $moveStatus;
	}*/

	function migrateCustomerAddresses()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__kart_users'));
		$query->where($db->quoteName('address_type') . "= 'BT'");
		$query->group($db->quoteName('user_id'));

		$db->setQuery($query);

		$userAddresses = $db->loadObjectlist();

		foreach ($userAddresses as $userAddress)
		{
			$addressDetails = new stdclass;

			if (empty($userAddress->user_id))
			{
				continue;
			}

			$addressDetails->user_id = $userAddress->user_id;
			$addressDetails->firstname = $userAddress->firstname;
			$addressDetails->middlename = $userAddress->middlename;
			$addressDetails->lastname = $userAddress->lastname;
			$addressDetails->vat_number = $userAddress->vat_number;
			$addressDetails->phone = $userAddress->phone;
			$addressDetails->address_title = $userAddress->firstname;
			$addressDetails->user_email = $userAddress->user_email;
			$addressDetails->address = $userAddress->address;
			$addressDetails->land_mark = $userAddress->land_mark;
			$addressDetails->zipcode = $userAddress->zipcode;
			$addressDetails->country_code = $userAddress->country_code;
			$addressDetails->state_code = $userAddress->state_code;
			$addressDetails->city = $userAddress->city;

			$db = JFactory::getDbo();

			$db->insertObject('#__kart_customer_address', $addressDetails, 'id');
			$order_item_id = $db->insertid();
		}
	}

	/**
	 * Generate alias from product title
	 *
	 * @return  null
	 *
	 * @since   2.9.5
	 */
	public function migrateAlias()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__kart_items'));
		$db->setQuery($query);
		$items = $db->loadObjectlist();

		foreach ($items as $item)
		{
			$alias = $item->name;

			if (JFactory::getConfig()->get('unicodeslugs') == 1)
			{
				$alias = JFilterOutput::stringURLUnicodeSlug($alias);
			}
			else
			{
				$alias = JFilterOutput::stringURLSafe($alias);
			}

			// Check if course with same alias is present
			JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_category/tables');
			JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_quick2cart/tables');
			$table = JTable::getInstance('Product', 'Quick2cartTable', array('dbo', $db));

			if ($table->load(array('alias' => $alias)))
			{
				while ($table->load(array('alias' => $alias)))
				{
					$alias = JString::increment($alias, 'dash');
				}
			}

			// Check if category with same alias is present
			$category = JTable::getInstance('Category', 'JTable', array('dbo', $db));

			if ($category->load(array('alias' => $alias)))
			{
				while ($category->load(array('alias' => $alias)))
				{
					$alias = JString::increment($alias, 'dash');
				}
			}

			$quick2cartViews = array('adduserform', 'createorder', 'productpage', 'shipprofileform', 'vendor',
				'attributes', 'customer_addressform', 'promotion', 'shipprofiles', 'zoneform', 'cart',
				'downloads', 'promotions', 'stores', 'zones', 'cartcheckout', 'taxprofileform', 'category',
				'orders', 'taxprofiles', 'couponform', 'payouts', 'registration', 'taxrateform', 'coupons',
				'product', 'shipping', 'taxrates');

			if (in_array($alias, $quick2cartViews))
			{
				$alias = JString::increment($alias, 'dash');

				while ($table->load(array('alias' => $alias)))
				{
					$alias = JString::increment($alias, 'dash');
				}
			}

			if (trim(str_replace('-', '', $alias)) == '')
			{
				$alias = JFactory::getDate()->format("Y-m-d-H-i-s");
			}

			$item->alias = $alias;

			$db = JFactory::getDbo();
			$db->updateObject('#__kart_items', $item, 'item_id');
			$db->insertid();
		}
	}
}
