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

//LOAD LANG FILE
$lang = JFactory::getLanguage();
$lang->load('mod_qtcstoredisplay', JPATH_ROOT);

//LOAD CSS FILES AND JS FILE
$comparams = JComponentHelper::getParams( 'com_quick2cart' );
$document = JFactory::getDocument();
//$document->addScript(JURI::root().'components/com_quick2cart/assets/js/order.js');
$document->addStyleSheet(JURI::base().'components/com_quick2cart/assets/css/quick2cart.css' );

$comquick2cartHelper = new comquick2cartHelper();
//$Itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=cart');
?>
<div class="<?php echo Q2C_WRAPPER_CLASS . ' ' . $params->get('moduleclass_sfx');?>" >
	<div  class=''>
		<?php
		if($qtc_modViewType=="qtc_listView")
		{
			$comquick2cartHelper = new comquick2cartHelper();
			$view = $comquick2cartHelper->getViewpath('vendor','storelist');
			$options = $target_data;
			ob_start();
			include($view);
			$html = ob_get_contents();
			ob_end_clean();
			echo $html;
		}
		else
		{
				$max_scroll_ht=!empty($qtc_mod_scroll_height)?trim($qtc_mod_scroll_height):250;
				$scroll_style="overflow-y:auto;max-height:".$max_scroll_ht."px;overflow-x:hidden;"
			?>
			<ul class="thumbnails" style="<?php echo $scroll_style;?>" >
			<?php
			foreach($target_data as $data)
			{
				$data = (array)$data;
				$path = $comquick2cartHelper->getViewpath('vendor', 'thumbnail', "SITE", "SITE");
				//$path = JPATH_SITE.DS.'components/com_quick2cart/views/vendor/tmpl/thumbnail.php';
					//@TODO  condition vise mod o/p
				ob_start();
				include($path);
				$html = ob_get_contents();
				ob_end_clean();
				echo $html;
				//break;
			}
				?>
			</ul>
			<?php
		} ?>
	</div>
</div>
