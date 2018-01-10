<?php
//!no direct access
defined ('_JEXEC') or die ('Restricted access');

require_once JPATH_ADMINISTRATOR.'/components/com_installer/models/update.php';

class UpdateModelUpdate extends InstallerModelUpdate
{
	/**
	 * Update function.
	 *
	 * Sets the "result" state with the result of the operation.
	 *
	 * @param	Array[int] List of updates to apply
	 * @since	1.6
	 */
	public function update($uids)
	{
		$result = true;
		foreach($uids as $uid) {
			$update = new JUpdate();
			$instance = JTable::getInstance('update');
			$instance->load($uid);
			$update->loadFromXML($instance->detailsurl);
			// install sets state and enqueues messages
			$res = $this->install($update);

			if ($res) {
				$instance->delete($uid);
			}

			$result = $res & $result;
		}

		// Set the final state
		$this->setState('result', $result);
	}
	
	/**
	 * Handles the actual update installation.
	 *
	 * @param	JUpdate	An update definition
	 * @return	boolean	Result of install
	 * @since	1.6
	 */
	private function install($update)
	{
		$app = JFactory::getApplication();
		$config = JFactory::getConfig();
		if (isset($update->get('downloadurl')->_data)) {
			$url = trim($update->downloadurl->_data);
		} else {
			JError::raiseWarning('', JText::_('COM_INSTALLER_INVALID_EXTENSION_UPDATE'));
			return false;
		}

		// Set the target path if not given
		$target = $config->get('tmp_path') . '/' . JInstallerHelper::getFilenameFromURL($url);
		
		if (!is_file($target)) {
			$p_file = JInstallerHelper::downloadPackage($url);
		} else {
			$p_file = JInstallerHelper::getFilenameFromURL($url);
		}

		// Was the package downloaded?
		if (!$p_file) {
			JError::raiseWarning('', JText::sprintf('COM_INSTALLER_PACKAGE_DOWNLOAD_FAILED', $url));
			return false;
		}

		$config		= JFactory::getConfig();
		$tmp_dest	= $config->get('tmp_path');

		// Unpack the downloaded package file
		$package	= JInstallerHelper::unpack($tmp_dest . '/' . $p_file);

		// Get an installer instance
		$installer	= JInstaller::getInstance();
		$update->set('type', $package['type']);

		// Install the package
		if (!$installer->update($package['dir'])) {
			// There was an error updating the package
			$msg = JText::sprintf('COM_INSTALLER_MSG_UPDATE_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
			$result = false;
		} else {
			// Package updated successfully
			$msg = JText::sprintf('COM_INSTALLER_MSG_UPDATE_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
			$result = true;
		}

		// Quick change
		$this->type = $package['type'];

		// Set some model state values
		$app->enqueueMessage($msg);

		// TODO: Reconfigure this code when you have more battery life left
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$app->setUserState('com_installer.message', $installer->message);
		$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));

		// Cleanup the install files
		if (!is_file($package['packagefile'])) {
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		return $result;
	}
}