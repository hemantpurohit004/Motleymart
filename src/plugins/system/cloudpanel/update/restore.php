<?php
$component_path = dirname(dirname(dirname(dirname(__DIR__)))).'/administrator/components/com_joomlaupdate';
set_include_path($component_path);
if (!file_exists($component_path.'/'.basename(__FILE__))) {
	echo json_encode(array(
		'stats' => 'failure',
		'code' => '2',
		'message' => 'Your Joomla dont support update method'
	));
} else {
	require_once basename(__FILE__);
}