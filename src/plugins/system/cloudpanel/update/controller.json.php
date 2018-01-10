<?php
/**
 * @package 	Cloud Panel Component for Joomla!
 * @author 		CloudAccess.net LCC
 * @copyright 	(C) 2010 - CloudAccess.net LCC
 * @license 	GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

//!no direct access
defined ('_JEXEC') or die ('Restricted access');

class updateController extends CAController  
{
	/**
	 * Return JCE current version
	 * 
	 * @since 	5.0
	 */
	public function vjce()
	{
		$jce = JComponentHelper::getComponent('com_jce');
		
		if ($jce->id == 0) {
			$response = array('stats' => 'failure', 'code' => 6, 'message' => 'JCE not found');
		}
		else {
			$xml = simplexml_load_file(JPATH_ADMINISTRATOR.'/components/com_jce/jce.xml');
			$jce_version = (string)$xml->version;
			$response = array('stats' => 'ok', 'version' => $jce_version);
		}

		stripeResponse($response);
	}

	/**
	 * Return CMS version
	 * 
	 * @since 	5.0
	 */
	public function vcms()
	{
		stripeResponse(array('stats' => 'ok', 'version' => JVERSION));
	}

	/**
	 * Return if user is authenticated
	 * 
	 * @since 	5.0
	 */
	public function login()
	{
		$response = array('stats' => 'ok', 'code' => 6, 'message' => 'Authenticated');
		stripeResponse($response);
	}

	/**
	 * Prepare the installation of the new Joomla! version 
	 * 
	 * @since 	5.0
	 */
	public function jce()
	{
		$jce = JComponentHelper::getComponent('com_jce');
		
		if ($jce->id == 0) {
			$response = array('stats' => 'failure', 'code' => 6, 'message' => 'JCE not found');
		}
		else {
			$xml = simplexml_load_file(JPATH_ADMINISTRATOR.'/components/com_jce/jce.xml');
			$jce_version = (string)$xml->version;

			if ($jce_version <= '2.3.1') {
				$response = array('stats' => 'ok', 'code' => 4, 'message' => 'This JCE version not supported for update. Allowed versions > 2.3.1');
			} else {
				$http = new JHttp;
				$params = array('task' => 'check','jversion' => '2.5', 'joomla[com_jce]'=> $jce_version);
				$request = $http->post('https://www.joomlacontenteditor.net/index.php?option=com_updates&format=raw', $params);
				$jsonData = json_decode($request->body);

				if ($request->code != 200) {
					$response = array('stats' => 'ok', 'code' => 4, 'message' => 'Cant fetch JCE update');
				} else {
					if (empty($jsonData)) {
						$response = array('stats' => 'ok', 'code' => 4, 'message' => 'JCE is up-to-date');
					} elseif(!empty($jsonData)) {
						$config = new JConfig;

						$file_path = $config->tmp_path.DIRECTORY_SEPARATOR.$jsonData[0]->name;

						if (!file_exists($file_path)) {
							
							$request_download = $http->post('https://www.joomlacontenteditor.net/index.php?option=com_updates&format=raw', array('task'=>'download', 'id' => $jsonData[0]->id));
							$jsonDataDownload = json_decode($request_download->body);
							require_once JPATH_ADMINISTRATOR.'/components/com_joomlaupdate/helpers/download.php';
							if (!AdmintoolsHelperDownload::download($jsonDataDownload->url, $file_path)) {
								$response = array('stats' => 'ok', 'code' => 8, 'message' => 'JCE update failed - error to download jce');
							}
						}

						$archive = $file_path;
		    			// Temporary folder to extract the archive into
				        $tmpdir = uniqid('install_');
				        // Clean the paths to use for archive extraction
				        $extractdir = JPath::clean(dirname($archive) . '/' . $tmpdir);
				        // Do the unpacking of the archive
				        try {
				            JArchive::extract($archive, $extractdir);
				        } catch (Exception $e) {
				            $response = array('stats' => 'ok', 'code' => 4, 'message' => 'error when try to extract JCE');
				        }
						
						$installer = JInstaller::getInstance();
						try {
							if (!$installer->install($extractdir)) {
								JFolder::delete($extractdir);
								JFile::delete($archive);
								$response = array('stats' => 'ok', 'code' => 4, 'message' => 'JCE update failed - error when try to install jce');
							} else {
								$response = array('stats' => 'ok', 'code' => 4, 'message' => 'JCE updated success');
							}
						} catch (Exception $e) {
							$response = array('stats' => 'ok', 'code' => 8, 'message' => 'JCE update failed - '.$e->getMessage());
						}
					}
				}
			}
		}

		stripeResponse($response);
	}

	/**
	 * Prepare the installation of the new Joomla! version 
	 * 
	 * @since 	5.0
	 */
	public function start()
	{
		$this->_applyCredentials();

		$eid = 700;
		
		$model = CAModel::getInstance('update','updatemodel');
		$modelJoomlaUpdate = $this->getModel('joomlaupdate');
		
		$db = JFactory::getDBO();
		// Note: TRUNCATE is a DDL operation
		// This may or may not mean depending on your database
		$db->setQuery('TRUNCATE TABLE #__updates');
		if ($db->Query()) {
			// Reset the last update check timestamp
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__update_sites'));
			$query->set($db->quoteName('last_check_timestamp').' = '.$db->q(0));
			$db->setQuery($query);
			$db->query();
		}
		$db->setQuery('UPDATE #__update_sites SET enabled = 1 WHERE enabled = 0');
		$db->query();
		
		if (method_exists($modelJoomlaUpdate,'purge'))
		{
			$modelJoomlaUpdate->purge();
		}
		if (method_exists($modelJoomlaUpdate,'getUpdateInformation'))
		{
			$modelJoomlaUpdate->getUpdateInformation();
		}
		
		$model->purge();
		$model->enableSites();
		
		try {
			$model->findUpdates($eid, 0);
			$result = $model->findUpdates($eid, 3600);
		} catch (Exception $e) {
		}
		
		$model->setState('filter.extension_id', $eid);
		$updates = $model->getItems();
		
		if (empty($updates))
		{
			$response = array('stats' => 'ok', 'code' => 4, 'message' => 'Joomla! is up-to-date');
			stripeResponse($response);
		}
		if ($updates[0]->version == JVERSION)
		{
			$response = array('stats' => 'ok', 'code' => 4, 'message' => 'Joomla! is up-to-date');
			stripeResponse($response);
		}
		
		$file = $modelJoomlaUpdate->download();
		JFactory::getApplication()->setUserState('com_joomlaupdate.file', $file);
		
		$url = 'caupdate=1&task=install&format=json';
		
		if ($file === false)
		{
			$response = array('stats' => 'failure', 'code' => 5, 'message' => 'tmp folder dont have sufficient permissions');
			stripeResponse($response);
		}
		
		$html = null;
		$response = array(
			'stats' => 'ok',
			'ajax' => array(
				'options' => array(
					'data' => $url
				)
			),
			'message' => 'installing update'
		);
		stripeResponse($response);
	}

	/**
	 * Start the installation of the new Joomla! version 
	 * 
	 * @since 	5.0
	 */
	public function install()
	{
		$this->_applyCredentials();

		$modelDefault = $this->getModel('joomlaupdate');
		
		$file = JFactory::getApplication()->getUserState('com_joomlaupdate.file', null);
		$restorationFile = $modelDefault->createRestorationFile($file);
		
		if ($restorationFile === false)
		{
			$response = array('stats' => 'failure', 'code' => 6, 'message' => 'cant write joomla restoration file');
			stripeResponse($response);
		}
		
		$password = JFactory::getApplication()->getUserState('com_joomlaupdate.password', null);
		$filesize = JFactory::getApplication()->getUserState('com_joomlaupdate.filesize', null);
		
		$ajaxUrl = JURI::base();
		$ajaxUrl .= 'plugins/system/cloudpanel/update/restore.php';
		$returnUrl = JURI::base();
		
		$response = array(
			'stats' => 'ok',
			'vars' => array(
				"joomlaupdate_password = '$password'",
				"joomlaupdate_totalsize = '$filesize'",
				"joomlaupdate_ajax_url = '$ajaxUrl'",
				"joomlaupdate_return_url = '$returnUrl'",
			),
			'cbfunc' => 'pingUpdate()',
			'message' => 'starting update'
		);
		stripeResponse($response);
	}

	/**
	 * Finalise the upgrade by running the necessary scripts
	 * 
	 * @since 	5.0
	 */
	public function finalise()
	{
		$this->_applyCredentials();

		$modelDefault = $this->getModel('joomlaupdate');
		$modelDefault->finaliseUpgrade();

		$url = 'caupdate=1&task=cleanup&format=json';
		$response = array(
			'stats' => 'ok', 
			'ajax' => array(
				'options' => array(
					'data' => $url
				)
			),
			'message' => 'cleaning install'
		);
		stripeResponse($response);
	}

	/**
	 * Clean up after ourselves
	 * 
	 * @since 	5.0
	 */
	public function cleanup()
	{
		$this->_applyCredentials();

		$modelDefault = $this->getModel('joomlaupdate');
		$modelDefault->cleanUp();

		//aways set LTS after finalize
		$joomlaupdate = JComponentHelper::getParams('com_joomlaupdate');
		$joomlaupdate->set('updatesource','lts');
		$joomlaupdate->set('customurl','');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__extensions AS e')->set('e.params = '.$db->quote($joomlaupdate->toString('json')))->where('e.element="com_joomlaupdate"');
		$db->setQuery($query);
		$db->query();

		$model = CAModel::getInstance('update','updatemodel');
		$modelDefault->applyUpdateSite();
		if (method_exists($modelDefault, 'purge')) {
			$modelDefault->purge();
		}
		if (method_exists($model, 'purge')) {
			$model->purge();
		}
		$model->enableSites();

		$response = array('stats' => 'ok', 'code' => 100, 'message' => 'update success');
		stripeResponse($response);
	}

	/**
	 * Applies FTP credentials to Joomla! itself, when required
	 * 
	 * @return void
	 *
	 * @since 	5.0
	 */
	protected function _applyCredentials()
	{
		if (!JClientHelper::hasCredentials('ftp'))
		{
			$user = JFactory::getApplication()->getUserStateFromRequest('com_joomlaupdate.ftp_user', 'ftp_user', null, 'raw');
			$pass = JFactory::getApplication()->getUserStateFromRequest('com_joomlaupdate.ftp_pass', 'ftp_pass', null, 'raw');

			if ($user != '' && $pass != '')
			{
				// Add credentials to the session
				if (JClientHelper::setCredentials('ftp', $user, $pass))
				{
					$return = false;
				}
				else
				{
					$return = JError::raiseWarning('SOME_ERROR_CODE', JText::_('JLIB_CLIENT_ERROR_HELPER_SETCREDENTIALSFROMREQUEST_FAILED'));
				}
			}
		}
	}
}
