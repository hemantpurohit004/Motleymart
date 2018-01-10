<?php
defined('_JEXEC') or die('Restricted access');

class plgSystemCloudPanelInstallerScript
{
	public function update($parent)  {
		$this->install($parent);
	}

	public function install($parent)
	{
		$db = JFactory::getDbo();
		$db->setQuery("UPDATE #__extensions SET enabled = 1, ordering = 999999 WHERE element = 'cloudpanel' AND type = 'plugin'");
		$db->query();
	}
}