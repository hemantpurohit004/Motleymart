<?php
/**
 * @package 	Cloud Panel Component for Joomla!
 * @author 		CloudAccess.net LCC
 * @copyright 	(C) 2010 - CloudAccess.net LCC
 * @license 	GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

//!no direct access
defined ('_JEXEC') or die ('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_joomlaupdate/models/default.php';

class updateModelJoomlaupdate extends JoomlaupdateModelDefault
{
	/**
	 * Downloads a package file to a specific directory
	 *
	 * @param   string  $url     The URL to download from
	 * @param   string  $target  The directory to store the file
	 *
	 * @return boolean True on success
	 * @since 2.5.2
	 */
	protected function downloadPackage($url, $target)
	{
		JLoader::import('helpers.download', JPATH_ADMINISTRATOR.'/components/com_joomlaupdate');
		$result = AdmintoolsHelperDownload::download($url, $target);
		if(!$result)
		{
			return false;
		}
		else
		{
			return basename($target);
		}
	}
	
	public function createRestorationFile($basename = null)
	{
		// Get a password
		jimport('joomla.user.helper');
		$password = JUserHelper::genRandomPassword(32);
		JFactory::getApplication()->setUserState('com_joomlaupdate.password', $password);

		// Do we have to use FTP?
		$method = JRequest::getCmd('method', 'direct');

		// Get the absolute path to site's root
		$siteroot = JPATH_SITE;

		// If the package name is not specified, get it from the update info
		if (empty($basename))
		{
			$updateInfo = $this->getUpdateInformation();
			$packageURL = $updateInfo['object']->downloadurl->_data;
			$basename = basename($packageURL);
		}

		// Get the package name
		$tempdir = JFactory::getConfig()->get('config.tmp_path');
		if (is_null($tempdir))
		{
			$tempdir = JFactory::getConfig()->get('tmp_path');
		}
		$file  = $tempdir . '/' . JFile::getName($basename);
		
		$filesize = @filesize($file);
		
		JFactory::getApplication()->setUserState('com_joomlaupdate.file', $file);
		JFactory::getApplication()->setUserState('com_joomlaupdate.password', $password);
		JFactory::getApplication()->setUserState('com_joomlaupdate.filesize', $filesize);

		$data = "<?php\ndefined('_AKEEBA_RESTORATION') or die('Restricted access');\n";
		$data .= '$restoration_setup = array('."\n";
		$data .= <<<ENDDATA
	'kickstart.security.password' => '$password',
	'kickstart.tuning.max_exec_time' => '5',
	'kickstart.tuning.run_time_bias' => '75',
	'kickstart.tuning.min_exec_time' => '0',
	'kickstart.procengine' => '$method',
	'kickstart.setup.sourcefile' => '$file',
	'kickstart.setup.destdir' => '$siteroot',
	'kickstart.setup.restoreperms' => '0',
	'kickstart.setup.filetype' => 'zip',
	'kickstart.setup.dryrun' => '0'
ENDDATA;

		if ($method == 'ftp')
		{
			// Fetch the FTP parameters from the request. Note: The password should be
			// allowed as raw mode, otherwise something like !@<sdf34>43H% would be
			// sanitised to !@43H% which is just plain wrong.
			$ftp_host = JRequest::getVar('ftp_host','');
			$ftp_port = JRequest::getVar('ftp_port', '21');
			$ftp_user = JRequest::getVar('ftp_user', '');
			$ftp_pass = JRequest::getVar('ftp_pass', '', 'default', 'none', 2);
			$ftp_root = JRequest::getVar('ftp_root', '');

			// Is the tempdir really writable?
			$writable = @is_writeable($tempdir);
			if($writable) {
				// Let's be REALLY sure
				$fp = @fopen($tempdir.'/test.txt','w');
				if($fp === false) {
					$writable = false;
				} else {
					fclose($fp);
					unlink($tempdir.'/test.txt');
				}
			}

			// If the tempdir is not writable, create a new writable subdirectory
			if(!$writable) {
				jimport('joomla.client.ftp');
				jimport('joomla.client.helper');
				jimport('joomla.filesystem.folder');

				$FTPOptions = JClientHelper::getCredentials('ftp');
				$ftp = & JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);
				$dest = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $tempdir.'/admintools'), '/');
				if(!@mkdir($tempdir.'/admintools')) $ftp->mkdir($dest);
				if(!@chmod($tempdir.'/admintools', 511)) $ftp->chmod($dest, 511);

				$tempdir .= '/admintools';
			}

			// Just in case the temp-directory was off-root, try using the default tmp directory
			$writable = @is_writeable($tempdir);
			if(!$writable) {
				$tempdir = JPATH_ROOT.'/tmp';

				// Does the JPATH_ROOT/tmp directory exist?
				if(!is_dir($tempdir)) {
					jimport('joomla.filesystem.folder');
					jimport('joomla.filesystem.file');
					JFolder::create($tempdir, 511);
					JFile::write($tempdir.'/.htaccess',"order deny, allow\ndeny from all\nallow from none\n");
				}

				// If it exists and it is unwritable, try creating a writable admintools subdirectory
				if(!is_writable($tempdir)) {
					jimport('joomla.client.ftp');
					jimport('joomla.client.helper');
					jimport('joomla.filesystem.folder');

					$FTPOptions = JClientHelper::getCredentials('ftp');
					$ftp = & JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);
					$dest = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $tempdir.'/admintools'), '/');
					if(!@mkdir($tempdir.'/admintools')) $ftp->mkdir($dest);
					if(!@chmod($tempdir.'/admintools', 511)) $ftp->chmod($dest, 511);

					$tempdir .= '/admintools';
				}
			}

			// If we still have no writable directory, we'll try /tmp and the system's temp-directory
			$writable = @is_writeable($tempdir);
			if(!$writable) {
				if(@is_dir('/tmp') && @is_writable('/tmp')) {
					$tempdir = '/tmp';
				} else {
					// Try to find the system temp path
					$tmpfile = @tempnam("dummy","");
					$systemp = @dirname($tmpfile);
					@unlink($tmpfile);
					if(!empty($systemp)) {
						if(@is_dir($systemp) && @is_writable($systemp)) {
							$tempdir = $systemp;
						}
					}
				}
			}

			$data.=<<<ENDDATA
	,
	'kickstart.ftp.ssl' => '0',
	'kickstart.ftp.passive' => '1',
	'kickstart.ftp.host' => '$ftp_host',
	'kickstart.ftp.port' => '$ftp_port',
	'kickstart.ftp.user' => '$ftp_user',
	'kickstart.ftp.pass' => '$ftp_pass',
	'kickstart.ftp.dir' => '$ftp_root',
	'kickstart.ftp.tempdir' => '$tempdir'
ENDDATA;
		}

		$data .= ');';

		// Remove the old file, if it's there...
		jimport('joomla.filesystem.file');
		$configpath = JPATH_ADMINISTRATOR . '/components/com_joomlaupdate/restoration.php';
		if( JFile::exists($configpath) )
		{
			JFile::delete($configpath);
		}

		// Write new file. First try with JFile.
		$result = JFile::write( $configpath, $data );
		// In case JFile used FTP but direct access could help
		if(!$result) {
			if(function_exists('file_put_contents')) {
				$result = @file_put_contents($configpath, $data);
				if($result !== false) $result = true;
			} else {
				$fp = @fopen($configpath, 'wt');
				if($fp !== false) {
					$result = @fwrite($fp, $data);
					if($result !== false) $result = true;
					@fclose($fp);
				}
			}
		}
		return $result;
	}
	
	/**
	 * Removes the extracted package file
	 *
	 * @return void
	 */
	public function cleanUp()
	{
		jimport('joomla.filesystem.file');

		// Remove the update package
		$jreg = JFactory::getConfig();
		$tempdir = $jreg->get('config.tmp_path');
		$file = JFactory::getApplication()->getUserState('com_joomlaupdate.file', null);
		$target = $tempdir.'/'.$file;
		if (!@unlink($target))
		{
			jimport('joomla.filesystem.file');
			JFile::delete($target);
		}

		// Remove the restoration.php file
		$target = JPATH_ADMINISTRATOR . '/components/com_joomlaupdate/restoration.php';
		if (!@unlink($target))
		{
			JFile::delete($target);
		}

		// Remove joomla.xml from the site's root
		$target = JPATH_ROOT . '/joomla.xml';
		if (!@unlink($target))
		{
			JFile::delete($target);
		}

		// Unset the update filename from the session
		JFactory::getApplication()->setUserState('com_joomlaupdate.file', null);
	}
}
