<?php
if (JFactory::getUser()->authorise('core.manage', 'com_smartslider3')) {

    if (!class_exists('plgSystemNextendSmartslider3')) {
        require_once(JPATH_PLUGINS . '/system/nextendsmartslider3/nextendsmartslider3.php');
        if (class_exists('JEventDispatcher', false)) {
            $dispatcher = JEventDispatcher::getInstance();
        } else {
            $dispatcher = JDispatcher::getInstance();
        }
        $plugin = JPluginHelper::getPlugin('system', 'nextendsmartslider3');
        new plgSystemNextendSmartslider3($dispatcher, (array)($plugin));
    }
    
    jimport("nextend2.nextend.joomla.library");
    $smartSliderBackend = N2Base::getApplication("smartslider")
                                ->getApplicationType('backend');

    if (N2Settings::get('n2_ss3_version') != N2SS3::$version) {
        $smartSliderBackend->render(array(
            "controller" => "install",
            "action"     => "index",
            "useRequest" => false
        ), array(true));
    }

    $smartSliderBackend->render(array(
        "controller" => "sliders",
        "action"     => "index"
    ));
    n2_exit();
} else {
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
