<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2012-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
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

class pkg_quick2cartInstallerScript
{
	private $componentStatus="install";

	private $installation_queue = array(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules'=>array(
			'admin'=>array(),
			'site'=>array(
					'quick2cart' => array('position-7', 1),
					'qtcproductdisplay' =>array('position-7', 0),
					'qtcstoredisplay' =>array('position-7', 0),
					'qtc_categorylist' =>array('position-7', 0),
					'q2cfilters' =>array('position-7', 0),
					'q2c_search' =>array('banner', 0)
				)
		),

		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins'=>array(
			'system'=>array(
				'qtc_sys'=>1,
				'qtc_sample_development'=>1,
				'qtc_sms'=>0,
				'qtcamazon_easycheckout'=>0,
				'qtc_zoo'=>1,
				'tjassetsloader'=>1,
			),
			'content'=>array(
				'content_quick2cart'=>1
			),
			'k2'=>array(
				'qtc_k2'=>1
			),
			'flexicontent_fields'=>array(
				'quick2cart'=>1
			),
			'community'=>array(
				'quick2cartibought'=>1,
				'quick2cartproduct'=>1,
				/* 'quick2cartstore'=>1*/

			),
			'payment'=>array(
				'2checkout'=>0,
				'alphauserpoints'=>0,
				'authorizenet'=>0,
				'bycheck'=>1,
				'byorder'=>1,
				'ccavenue'=>0,
				'easysocialpoints'=>0,
				'jomsocialpoints'=>0,
				'linkpoint'=>0,
				'paypal'=>0,
				'paypal_adaptive_payment'=>0,
				'paypalpro'=>0,
				'payu'=>0,
			),
			'search'=>array(
				'quick2cart'=>1
			),
			'tjtaxation'=>array(
				'qtc_default_zonetaxation'=>1,
			),
			'tjshipping'=>array(
				'qtc_default_zoneshipping'=>1,
			),
			'qtcshipping'=>array(
				'qtc_shipping_default'=>0,
			),
			'qtctax'=>array(
				'qtc_tax_default'=>0,
			),
			'sms'=>array(
				'smshorizon'=>0,
				'clickatell'=>0,
			),
			'discounts'=>array(
				'share_for_discount'=>0,
			),
		),

		'applications'=>array(
			'easysocial'=>array(
					//'quick2cartproducts'=>0,
					//'quick2cartstores'=>0
					'q2c_boughtproducts'=>0,
					'q2cMyProducts'=>0
				)
		),

		'libraries'=>array(
			'techjoomla'=>1
		)
	);

	private $uninstall_queue = array(
		// modules => { (folder) => { (module) => { (position), (published) } }* }*
		'modules'=>array(
			'admin'=>array(),
			'site'=>array(
					'quick2cart' => array('position-7', 1),
					'qtcProductDisplay' =>array('position-7', 0),
					'qtcStoreDisplay' =>array('position-7', 0),
					'qtc_categorylist' =>array('position-7', 0),
					'q2cfilters' =>array('position-7', 0),
					'q2c_search' =>array('banner', 0)
			)
		),

		// plugins => { (folder) => { (element) => (published) }* }*
		'plugins'=>array(
			'system'=>array(
				'qtc_sys'=>1,
				'qtc_sample_development'=>0,
				'qtc_zoo'=>1,
				'qtc_sms'=>1,
				"qtcamazon_easycheckout"=>0
			),
			'content'=>array(
				'content_quick2cart'=>1
			),
			'k2'=>array(
				'qtc_k2'=>1
			),
			'flexicontent_fields'=>array(
				'quick2cart'=>1
			),
			'community'=>array(
				'quick2cartproduct'=>1,
				'quick2cartibought'=>1,
				'quick2cartstore'=>1

			),
			'qtcshipping'=>array(
				'qtc_shipping_default'=>1
			),
			'qtctax'=>array(
				'qtc_tax_default'=>1
			),
			'search'=>array(
				'quick2cart'=>1
			)
		)
	);

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
		if (!defined('DS'))
		{
			define('DS', DIRECTORY_SEPARATOR);
		}

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
	function postflight($type, $parent)
	{
		// Install subextensions
		$status = $this->_installSubextensions($parent);

		// Install Techjoomla Straper
		$straperStatus = $this->_installStraper($parent);

		// File, folder related activiy goes here eg move, delete, etc
		$straperStatus = $this->_moveFiles($parent);

		// Show the post-installation page
		$this->_renderPostInstallation($status, $straperStatus, $parent);
	}
	/**
	 * Renders the post-installation message
	 */
	private function _renderPostInstallation($status, $parent, $msgBox=array())
	{
		if (version_compare(JVERSION, '3.0', 'lt')) {
			 $document = JFactory::getDocument();
		  //http://172.132.45.200/~vidyasagar/testdemo25/components/com_quick2cart/css/quick2cart_style.css
			$document->addStyleSheet(JUri::root().'/media/techjoomla_strapper/css/bootstrap.min.css' );
		}

		$zooEleStatus=$this->addZooElement();
		$flexipath = JPATH_ROOT . '/components/com_flexicontent';

		if ( JFolder::exists($flexipath) )
		{
			//disable content plugin if flexi content present
			$db =  JFactory::getDBO();
			$query = "UPDATE #__extensions SET enabled=0 WHERE element='content_quick2cart'";
			$db->setQuery($query);
			$db->execute();
		}


		$enable="<span class=\"label label-success\">Enabled</span>";
		$disable= "<span class=\"label label-important\">Disabled</span>";
		$updatemsg="Updated Successfully";

		$bsSetupLink = JURI::base() . "index.php?option=com_quick2cart&view=dashboard&layout=setup";
		// Show link for payment plugins.
		$bsSetupLinkHtml = '<a
			href="' . $bsSetupLink . '" target="_blank"
			class="btn btn-small btn-primary ">'
				. JText::_('COM_QUICK2CART_CLICK_BS_SETUP_INSTRUCTION') .
			'</a>';

		?>
		<?php $rows = 1;?>
		<div class="q2c-wrapper techjoomla-bootstrap" >
			<div class="alert alert-success">
				<?php echo JText::sprintf('COM_QUICK2CART_INSTALL_BS_INSTRUCTION_MSG', $bsSetupLinkHtml);?>
			</div>
		<table class="table-condensed table">
			<thead>
				<tr class="row1">
					<th class="title" colspan="2">Extension</th>
					<th width="30%">Status</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3"></td>
				</tr>
			</tfoot>
			<tbody>
				<tr class="row2">
					<td class="key" colspan="2"><strong>Quick2Cart component</strong></td>
					<td><strong style="color: green">Installed</strong></td>
				</tr>
				<tr class="row2">
					<td class="key" colspan="2"><strong><?php echo JText::_("Custom Zoo Element");?></strong></td>
					<td><strong style="color: <?php echo ($zooEleStatus)? "green" : "red"?>"><?php echo ($zooEleStatus)?'Installed':'Not installed'; ?></strong>
				</tr>
				<?php
					if (!empty($msgBox))
					{
						// strore releated msg and menu releated msg
							foreach ($msgBox as $key=>$msgTopic)
							{

								if (!empty($msgTopic))
								{
									foreach ($msgTopic as $indexMsg=>$statusMsg)
									{
										if (!empty($statusMsg))
										{
									?>
									<tr class="row2">
										<td class="key" colspan="2"><strong><?php echo $indexMsg;?></strong></td>
										<td><strong style="color: <?php echo ($statusMsg)? "green" : "red"?>"><?php echo ($statusMsg)? $statusMsg:''; ?></strong>
									</tr>
									<?php
										}
									}

								}
							}
					}
				?>

				<?php if (count($status->modules)) : ?>
				<tr class="row1">
					<th>Module</th>
					<th>Client</th>
					<th></th>
					</tr>
				<?php foreach ($status->modules as $module) : ?>
				<tr class="row2 <?php //echo ($rows++ % 2); ?>">
					<td class="key"><?php echo ucfirst($module['name']); ?></td>
					<td class="key"><?php echo ucfirst($module['client']); ?></td>
					<td><strong style="color: <?php echo ($module['result'])? "green" : "red"?>"><?php echo ($this->componentStatus=="install") ?(($module['result'])?'Installed':'Not installed'):$updatemsg; ?></strong>

					<?php
					if ($this->componentStatus=="install")
					{
						if (!empty($module['result'])) // if installed then only show msg
						{
							echo $mstat=($module['status']? $enable :$disable);
						}
					}
					?>
					</td>
				</tr>
				<?php endforeach;?>
				<?php endif;?>
				<!-- pLUGIN DETAILS -->
				<?php if (count($status->plugins)) : ?>
				<tr class="row1">
					<th colspan="2">Plugin</th>
			<!--		<th>Group</th> -->
					<th></th>
				</tr>
				<?php
					$oldplugingroup="";
				foreach ($status->plugins as $plugin) :
					if ($oldplugingroup!=$plugin['group'])
					{
						$oldplugingroup=$plugin['group'];
				?>
					<tr class="row0">
						<th colspan="2"><strong><?php echo ucfirst($oldplugingroup)." Plugins";?></strong></th>
						<th></th>
				<!--		<td></td> -->
					</tr>
				<?php
					}

				 ?>
				<tr class="row2 <?php //echo ($rows++ % 2); ?>">
					<td colspan="2" class="key"><?php echo ucfirst($plugin['name']); ?></td>
		<!--			<td class="key"><?php //echo ucfirst($plugin['group']); ?></td> -->
					<td><strong style="color: <?php echo ($plugin['result'])? "green" : "red"?>"><?php echo ($this->componentStatus=="install") ?(($plugin['result'])?'Installed':'Not installed'):$updatemsg; ?></strong>
					<?php
					if ($this->componentStatus=="install")
					{
						if (!empty($plugin['result']))
						{
						echo $pstat=($plugin['status']? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>");

						}
					}
					?>
					</td>
				</tr>
				<?php endforeach; ?>
				<?php endif; ?>

				<!-- LIB INSTALL-->
				<?php if (count($status->libraries)) : ?>
				<tr class="row1">
					<th>Library</th>
					<th></th>
					<th></th>
					</tr>
				<?php foreach ($status->libraries as $libraries) : ?>
				<tr class="row2 <?php //echo ($rows++ % 2); ?>">
					<td class="key"><?php echo ucfirst($libraries['name']); ?></td>
					<td class="key"></td>
					<td><strong style="color: <?php echo ($libraries['result'])? "green" : "red"?>"><?php echo ($libraries['result'])?'Installed':'Not installed'; ?></strong>
					<?php
						if (!empty($libraries['result'])) // if installed then only show msg
						{
					//	echo $mstat=($libraries['status']? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>");

						}
					?>

					</td>
				</tr>
				<?php endforeach;?>
				<?php endif;?>

				<!-- Applications INSTALL -->
				<?php
				if (!empty($status->applications) && count($status->applications)) : ?>
					<tr class="row1">
						<th colspan="2">Applications</th>
						<th></th>
					</tr>
				<?php
					$oldappgroup="";
				foreach ($status->applications as $app) :
					if ($oldappgroup!=$app['group']){
						$oldappgroup=$app['group'];
						?>
						<tr class="row0">
							<th colspan="2"><strong><?php echo ucfirst($oldappgroup)." Application";?></strong></th>
							<th></th>
						</tr>
						<?php
					}

				 ?>
				<tr class="row2 <?php //echo ($rows++ % 2); ?>">
					<td colspan="2" class="key"><?php echo ucfirst($app['name']); ?></td>
					<td><strong style="color: <?php echo ($app['result'])? "green" : "red"?>"><?php echo ($this->componentStatus=="install") ?(($app['result'])?'Installed':'Not installed'):$updatemsg; ?></strong>
					<?php
					if ($this->componentStatus=="install") {
						if (!empty($app['result']))
						{
							echo $pstat=($app['status']? "<span class=\"label label-success\">Enabled</span>" : "<span class=\"label label-important\">Disabled</span>");

						}
					}
					?>
					</td>
				</tr>
				<?php endforeach; ?>
				<?php endif; ?>

				<!-- EASY SCOCIL INSTALL MSG -->
				<!--
				<tr class="row0">
						<th colspan="2"><strong>Easy Social Integration</strong></th>
						<th></th>
				</tr>
				<tr class="row2">
						<td colspan="2">Quick2Cart Products EasySocial Application</td>
						<td><strong style="color:red">Not installed</strong></td>
				</tr>
				<tr class="row2">
						<td colspan="2">Quick2Cart Store EasySocial Application</td>
						<td><strong style="color:red">Not installed</strong></td>
				</tr>
				<tr class="row2">
					<td colspan="3">
						<div class="alert alert-success">
								<h4 class="alert-heading">Message</h4>
								<p>Quick2Cart Products EasySocial Application - allow you to display your products on profile page. <br>
								Quick2Cart Store EasySocial Application - allow you to display your stores on profile page.</p>
								<div class="row-fluid">
									<div class="span12">
										<a href="https://techjoomla.com/documentation-for-quick2cart/integration-with-easysocial.html" target="_blank"><i class="icon-file"></i> <?php //echo JText::_('COM_QUICK2CART_INTEGRATION_WITH_EASY_SOCIAL');?></a>
									</div>
								</div>
						</div>


					<td>
				</tr>  -->
			</tbody>
		</table>
		</div> <!-- end akeeba bootstrap -->

		<?php

	}

	function addZooElement()
	{
		$install_source = dirname(__FILE__);
		$zoopath = JPATH_ROOT . '/media/zoo';

		if ( JFolder::exists($zoopath) )
		{
		//echo JText::_('<br/><span style="font-weight:bold;">Installing Custom Zoo Element:</span>');
		if ( ! JFolder::copy ($install_source . '/zoo_element' , $zoopath . '/elements',null,1 ) )
			{
			return 0;
			}
			else
			{
				return 1;
			}
		}
	}

	function _moveFiles()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$amazonPlug = JPATH_ROOT . '/plugins/system/qtcamazon_easycheckout';
		// If plugin files installed
		if (JFolder::exists($amazonPlug) )
		{
			$moveFileArray[0]['src'] = JPATH_ROOT . '/plugins/system/qtcamazon_easycheckout/lib/qtcamazonIOPN.php';
			$moveFileArray[0]['dest'] = JPATH_ROOT . '/qtcamazonIOPN.php';

			$moveFileArray[1]['src'] = JPATH_ROOT . '/plugins/system/qtcamazon_easycheckout/lib/qtcamazonSuccess.php';
			$moveFileArray[1]['dest'] = JPATH_ROOT . '/qtcamazonSuccess.php';

			$moveFileArray[2]['src'] = JPATH_ROOT . '/plugins/system/qtcamazon_easycheckout/lib/qtcamazonCancel.php';
			$moveFileArray[2]['dest'] = JPATH_ROOT . '/qtcamazonCancel.php';

			foreach ($moveFileArray as $file)
			{
				if (JFile::exists($file['src']))
				{
					JFile::copy($file['src'], $file['dest']);
				}
			}

		}
	}

	/**
	 * Installs subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param JInstaller $parent
	 * @return JObject The subextension installation status
	 */
	private function _installSubextensions($parent)
	{
		$src = $parent->getParent()->getPath('source');

		$db = JFactory::getDbo();

		$status = new JObject();
		$status->modules = array();
		$status->plugins = array();

		// Modules installation

		if (count($this->installation_queue['modules'])) {
			foreach ($this->installation_queue['modules'] as $folder => $modules) {
				if (count($modules))
					foreach ($modules as $module => $modulePreferences)
					{
						// Install the module
						if (empty($folder))
							$folder = 'site';
						$path = "$src/modules/$folder/$module";
						if (!is_dir($path))// if not dir
						{
							$path = "$src/modules/$folder/mod_$module";
						}
						if (!is_dir($path)) {
							$path = "$src/modules/$module";
						}

						if (!is_dir($path)) {
							$path = "$src/modules/mod_$module";
						}
						if (!is_dir($path))
						{

							$fortest='';
							//continue;
						}

						// Was the module already installed?
						$sql = $db->getQuery(true)
							->select('COUNT(*)')
							->from('#__modules')
							->where($db->qn('module').' = '.$db->q('mod_'.$module));
						$db->setQuery($sql);

						$count = $db->loadResult();
						$installer = new JInstaller;
						$result = $installer->install($path);
						$status->modules[] = array(
							'name'=>$module,
							'client'=>$folder,
							'result'=>$result,
							'status'=>$modulePreferences[1]
						);
						// Modify where it's published and its published state
						if (!$count) {
							// A. Position and state
							list($modulePosition, $modulePublished) = $modulePreferences;
							if ($modulePosition == 'cpanel') {
								$modulePosition = 'icon';
							}
							$sql = $db->getQuery(true)
								->update($db->qn('#__modules'))
								->set($db->qn('position').' = '.$db->q($modulePosition))
								->where($db->qn('module').' = '.$db->q('mod_'.$module));
							if ($modulePublished) {
								$sql->set($db->qn('published').' = '.$db->q('1'));
							}
							$db->setQuery($sql);
							$db->execute();

							// B. Change the ordering of back-end modules to 1 + max ordering
							if ($folder == 'admin') {
								$query = $db->getQuery(true);
								$query->select('MAX('.$db->qn('ordering').')')
									->from($db->qn('#__modules'))
									->where($db->qn('position').'='.$db->q($modulePosition));
								$db->setQuery($query);
								$position = $db->loadResult();
								$position++;

								$query = $db->getQuery(true);
								$query->update($db->qn('#__modules'))
									->set($db->qn('ordering').' = '.$db->q($position))
									->where($db->qn('module').' = '.$db->q('mod_'.$module));
								$db->setQuery($query);
								$db->execute();
							}

							// C. Link to all pages
							$query = $db->getQuery(true);
							$query->select('id')->from($db->qn('#__modules'))
								->where($db->qn('module').' = '.$db->q('mod_'.$module));
							$db->setQuery($query);
							$moduleid = $db->loadResult();

							$query = $db->getQuery(true);
							$query->select('*')->from($db->qn('#__modules_menu'))
								->where($db->qn('moduleid').' = '.$db->q($moduleid));
							$db->setQuery($query);
							$assignments = $db->loadObjectList();
							$isAssigned = !empty($assignments);
							if (!$isAssigned) {
								$o = (object)array(
									'moduleid'	=> $moduleid,
									'menuid'	=> 0
								);
								$db->insertObject('#__modules_menu', $o);
							}
						}
					}
			}
		}

		// Plugins installation
		if (count($this->installation_queue['plugins'])) {
			foreach ($this->installation_queue['plugins'] as $folder => $plugins) {
				if (count($plugins))
				foreach ($plugins as $plugin => $published) {
					$path = "$src/plugins/$folder/$plugin";
					if (!is_dir($path)) {
						$path = "$src/plugins/$folder/plg_$plugin";
					}
					if (!is_dir($path)) {
						$path = "$src/plugins/$plugin";
					}
					if (!is_dir($path)) {
						$path = "$src/plugins/plg_$plugin";
					}
					if (!is_dir($path)) continue;

					// Was the plugin already installed?
					$query = $db->getQuery(true)
						->select('COUNT(*)')
						->from($db->qn('#__extensions'))
						->where('( '.($db->qn('name').' = '.$db->q($plugin)) .' OR '. ($db->qn('element').' = '.$db->q($plugin)) .' )')
						->where($db->qn('folder').' = '.$db->q($folder));
					$db->setQuery($query);
					$count = $db->loadResult();

					$installer = new JInstaller;
					$result = $installer->install($path);

					$status->plugins[] = array('name'=>$plugin,'group'=>$folder, 'result'=>$result,'status'=>$published);


					if ($published && !$count) {
						$query = $db->getQuery(true)
							->update($db->qn('#__extensions'))
							->set($db->qn('enabled').' = '.$db->q('1'))
							->where('( '.($db->qn('name').' = '.$db->q($plugin)) .' OR '. ($db->qn('element').' = '.$db->q($plugin)) .' )')
							->where($db->qn('folder').' = '.$db->q($folder));
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}

		// library installation
		if (count($this->installation_queue['libraries'])) {
			foreach ($this->installation_queue['libraries']  as $folder=>$status1) {

					$path = "$src/libraries/$folder";

					$query = $db->getQuery(true)
						->select('COUNT(*)')
						->from($db->qn('#__extensions'))
						->where('( '.($db->qn('name').' = '.$db->q($folder)) .' OR '. ($db->qn('element').' = '.$db->q($folder)) .' )')
						->where($db->qn('folder').' = '.$db->q($folder));
					$db->setQuery($query);
					$count = $db->loadResult();

					$installer = new JInstaller;
					$result = $installer->install($path);

					$status->libraries[] = array('name'=>$folder,'group'=>$folder, 'result'=>$result,'status'=>$status1);
					//print"<pre>"; print_r($status->plugins); die;

					if ($published && !$count) {
						$query = $db->getQuery(true)
							->update($db->qn('#__extensions'))
							->set($db->qn('enabled').' = '.$db->q('1'))
							->where('( '.($db->qn('name').' = '.$db->q($folder)) .' OR '. ($db->qn('element').' = '.$db->q($folder)) .' )')
							->where($db->qn('folder').' = '.$db->q($folder));
						$db->setQuery($query);
						$db->execute();
					}
			}
		}
		/*
		 * 'applications'=>array(
			'easysocial'array(
					'quick2cartproducts'=>0,
					'quick2cartstores'=>0

			),
		 * */
		//Application Installations
		if (count($this->installation_queue['applications'])) {
			foreach ($this->installation_queue['applications'] as $folder => $applications) {
				if (count($applications)) {
					foreach ($applications as $app => $published) {
						$path = "$src/applications/$folder/$app";
						if (!is_dir($path)) {
							$path = "$src/applications/$folder/plg_$app";
						}
						if (!is_dir($path)) {
							$path = "$src/applications/$app";
						}
						if (!is_dir($path)) {
							$path = "$src/applications/plg_$app";
						}

						if (!is_dir($path)) continue;


						if (file_exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php')) {
							require_once( JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php' );

							// Was the app already installed?
							/*$query = $db->getQuery(true)
								->select('COUNT(*)')
								->from($db->qn('#__extensions'))
								->where('( '.($db->qn('name').' = '.$db->q($app)) .' OR '. ($db->qn('element').' = '.$db->q($app)) .' )')
								->where($db->qn('folder').' = '.$db->q($folder));
							$db->setQuery($query);
							$count = $db->loadResult();*/


							$installer     = Foundry::get( 'Installer' );
							// The $path here refers to your application path
							$installer->load( $path );
							$plg_install=$installer->install();
							//$status->app_install[] = array('name'=>'easysocial_camp_plg','group'=>'easysocial_camp_plg', 'result'=>$plg_install,'status'=>'1');
							$status->applications[] = array('name'=>$app,'group'=>$folder, 'result'=>$result,'status'=>$published);
						}
					}
				}
			}
		}

		return $status;
	}
	private function _installStraper($parent)
	{
		$src = $parent->getParent()->getPath('source');

		// Install the FOF framework
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.date');
		$source = $src . '/tj_strapper';
		$target = JPATH_ROOT . '/media/techjoomla_strapper';

		$haveToInstallStraper = false;
		if (!file_exists($target))
		{
			$haveToInstallStraper = true;
		}
		else
		{
			$straperVersion = array();
			if (JFile::exists($target . '/version.txt'))
			{
				$rawData                     = file_get_contents($target . '/version.txt');
				$info                        = explode("\n", $rawData);
				$straperVersion['installed'] = array(
					'version' => trim($info[0]),
					'date' => new JDate(trim($info[1]))
				);
			}
			else
			{
				$straperVersion['installed'] = array(
					'version' => '0.0',
					'date' => new JDate('2011-01-01')
				);
			}
			$rawData                   = file_get_contents($source . '/version.txt');
			$info                      = explode("\n", $rawData);
			$straperVersion['package'] = array(
				'version' => trim($info[0]),
				'date' => new JDate(trim($info[1]))
			);

			$haveToInstallStraper = $straperVersion['package']['date']->toUNIX() > $straperVersion['installed']['date']->toUNIX();
		}

		$installedStraper = false;
		if ($haveToInstallStraper)
		{
			$versionSource    = 'package';
			$installer        = new JInstaller;
			$installedStraper = $installer->install($source);
		}
		else
		{
			$versionSource = 'installed';
		}

		if (!isset($straperVersion))
		{
			$straperVersion = array();
			if (JFile::exists($target . '/version.txt'))
			{
				$rawData                     = file_get_contents($target . '/version.txt');
				$info                        = explode("\n", $rawData);
				$straperVersion['installed'] = array(
					'version' => trim($info[0]),
					'date' => new JDate(trim($info[1]))
				);
			}
			else
			{
				$straperVersion['installed'] = array(
					'version' => '0.0',
					'date' => new JDate('2011-01-01')
				);
			}
			$rawData                   = file_get_contents($source . '/version.txt');
			$info                      = explode("\n", $rawData);
			$straperVersion['package'] = array(
				'version' => trim($info[0]),
				'date' => new JDate(trim($info[1]))
			);
			$versionSource             = 'installed';
		}

		if (!($straperVersion[$versionSource]['date'] instanceof JDate))
		{
			$straperVersion[$versionSource]['date'] = new JDate();
		}

		return array(
			'required' => $haveToInstallStraper,
			'installed' => $installedStraper,
			'version' => $straperVersion[$versionSource]['version'],
			'date' => $straperVersion[$versionSource]['date']->format('Y-m-d')
		);
	}

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent)
	{
		// $parent is the class calling this method
	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent)
	{
		// Uninstall subextensions
		$status = $this->_uninstallSubextensions($parent);

		$this->_renderPostUninstallation($status, $parent);

		// $parent is the class calling this method
	}

	private function _renderPostUninstallation($status, $parent)
	{
?>
<?php $rows = 0;?>
<h2><?php echo JText::_('Quick2Cart Uninstallation Status'); ?></h2>
<table class="adminlist">
	<thead>
		<tr>
			<th class="title" colspan="2"><?php echo JText::_('Extension'); ?></th>
			<th width="30%"><?php echo JText::_('Status'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="row0">
			<td class="key" colspan="2"><?php echo 'Quick2Cart '.JText::_('Component'); ?></td>
			<td><strong style="color: green"><?php echo JText::_('Removed'); ?></strong></td>
		</tr>
		<?php if (count($status->modules)) : ?>
		<tr>
			<th><?php echo JText::_('Module'); ?></th>
			<th><?php echo JText::_('Client'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->modules as $module) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo $module['name']; ?></td>
			<td class="key"><?php echo ucfirst($module['client']); ?></td>
			<td><strong style="color: <?php echo ($module['result'])? "green" : "red"?>"><?php echo ($module['result'])?JText::_('Removed'):JText::_('Not removed'); ?></strong></td>
		</tr>
		<?php endforeach;?>
		<?php endif;?>
		<?php if (count($status->plugins)) : ?>
		<tr>
			<th><?php echo JText::_('Plugin'); ?></th>
			<th><?php echo JText::_('Group'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->plugins as $plugin) : ?>
		<tr class="row<?php echo (++ $rows % 2); ?>">
			<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
			<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			<td><strong style="color: <?php echo ($plugin['result'])? "green" : "red"?>"><?php echo ($plugin['result'])?JText::_('Removed'):JText::_('Not removed'); ?></strong></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
<?php
	}


	/**
	 * Uninstalls subextensions (modules, plugins) bundled with the main extension
	 *
	 * @param JInstaller $parent
	 * @return JObject The subextension uninstallation status
	 */
	private function _uninstallSubextensions($parent)
	{
		jimport('joomla.installer.installer');

		$db =  JFactory::getDBO();

		$status = new JObject();
		$status->modules = array();
		$status->plugins = array();

		$src = $parent->getParent()->getPath('source');

		// Modules uninstallation
		if (count($this->uninstall_queue['modules'])) {
			foreach ($this->uninstall_queue['modules'] as $folder => $modules) {
				if (count($modules)) foreach ($modules as $module => $modulePreferences) {
					// Find the module ID
					$sql = $db->getQuery(true)
						->select($db->qn('extension_id'))
						->from($db->qn('#__extensions'))
						->where($db->qn('element').' = '.$db->q('mod_'.$module))
						->where($db->qn('type').' = '.$db->q('module'));
					$db->setQuery($sql);
					$id = $db->loadResult();
					// Uninstall the module
					if ($id) {
						$installer = new JInstaller;
						$result = $installer->uninstall('module',$id,1);
						$status->modules[] = array(
							'name'=>'mod_'.$module,
							'client'=>$folder,
							'result'=>$result
						);
					}
				}
			}
		}

		// Plugins uninstallation
		if (count($this->uninstall_queue['plugins'])) {
			foreach ($this->uninstall_queue['plugins'] as $folder => $plugins) {
				if (count($plugins)) foreach ($plugins as $plugin => $published) {
					$sql = $db->getQuery(true)
						->select($db->qn('extension_id'))
						->from($db->qn('#__extensions'))
						->where($db->qn('type').' = '.$db->q('plugin'))
						->where($db->qn('element').' = '.$db->q($plugin))
						->where($db->qn('folder').' = '.$db->q($folder));
					$db->setQuery($sql);

					$id = $db->loadResult();
					if ($id)
					{
						$installer = new JInstaller;
						$result = $installer->uninstall('plugin',$id);
						$status->plugins[] = array(
							'name'=>'plg_'.$plugin,
							'group'=>$folder,
							'result'=>$result
						);
					}
				}
			}
		}

		return $status;
	}

	/**
	 * Method to update the component
	 *
	 * @param   String  $parent  String
	 *
	 * @return void
	 */
	public function update($parent)
	{
		$this->componentStatus="update";
		$db     = JFactory::getDBO();
		$config = JFactory::getConfig();

		$this->componentStatus="update";


		if (JVERSION >= 3.0)
		{
			$configdb = $config->get('db');
		}
		else
		{
			$configdb = $config->getValue('config.db');
		}

		// Get dbprefix
		if (JVERSION >= 3.0)
		{
			$dbprefix = $config->get('dbprefix');
		}
		else
		{
			$dbprefix = $config->getValue('config.dbprefix');
		}
	}
}
