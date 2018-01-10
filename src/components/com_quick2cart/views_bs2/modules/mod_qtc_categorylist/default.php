<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

$path = JPATH_SITE.DS.'components'.DS.'com_quick2cart'.DS.'helper.php';
//if(!class_exists('comquick2cartHelper'))
{
  //require_once $path;
   JLoader::register('comquick2cartHelper', $path );
   JLoader::load('comquick2cartHelper');
}
$comquick2cartHelper=new comquick2cartHelper();
$view=$comquick2cartHelper->getViewpath('category','categorylist');
$qtc_mod_scroll_height=$params->get('scroll_height');
?>
<div class="<?php echo Q2C_WRAPPER_CLASS . ' ' . $params->get('moduleclass_sfx'); ?>" >

	<div class="">
		<?php
		ob_start();
		include($view);
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
		?>

	</div>
</div>
