<?php
/**
* @version   $Id: error.php 15753 2013-11-18 18:49:43Z btowles $
* @author    RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*
* Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
*
*/
defined( '_JEXEC' ) or die( 'Restricted access' );
if (!isset($this->error)) {
	$this->error = JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
	$this->debug = false;
}

$doc = JFactory::getDocument();
if ($doc->_type === 'raw'){
	$output = "{$this->error->getCode()} {$this->error->getMessage()}";
	return;
}
$doc->setTitle($this->error->getCode() . ' - '.$this->title);

// load and inititialize gantry class
global $gantry;
require_once(dirname(__FILE__) . '/lib/gantry/gantry.php');
$gantry->init();

$gantry->addStyle('grid-responsive.css', 5);
$gantry->addLess('bootstrap.less', 'bootstrap.css', 6);

if ($gantry->browser->name == 'ie') {
        	if ($gantry->browser->shortversion == 9){
        		$gantry->addInlineScript("if (typeof RokMediaQueries !== 'undefined') window.addEvent('domready', function(){ RokMediaQueries._fireEvent(RokMediaQueries.getQuery()); });");
        	}
	if ($gantry->browser->shortversion == 8) {
		$gantry->addScript('html5shim.js');
	}
}
$gantry->addScript('rokmediaqueries.js');



ob_start();
?>
<body <?php echo $gantry->displayBodyTag(); ?>>
	<div id="rt-top-surround">
		<div id="rt-header">
			<div class="rt-container">
				<?php echo $gantry->displayModules('header','standard','standard'); ?>
				<div class="clear"></div>
			</div>
		</div>
	</div>
	<div class="rt-container">
		<div class="component-content">
			<div class="rt-grid-12">
				<div class="rt-block">
					<div class="rt-error-content">
						<div>
                            <h1 class="error-title title"><?php echo $this->error->getCode(); ?></h1>
                            <h2 class="error-message"><?php echo $this->error->getMessage(); ?></h2>
                            <div class="error-content">
                            <p>The page you requested cannot be found.</p>
                            <p><a href="<?php echo $gantry->baseUrl; ?>"><span>Return to the Homepage</span></a></p>
                        </div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
<?php

$body = ob_get_clean();
$gantry->finalize();

require_once(JPATH_LIBRARIES.'/joomla/document/html/renderer/head.php');
$header_renderer = new JDocumentRendererHead($doc);
$header_contents = $header_renderer->render(null);
ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<?php echo $header_contents; ?>
	<?php if ($gantry->get('layout-mode') == '960fixed') : ?>
	<meta name="viewport" content="width=960px">
	<?php elseif ($gantry->get('layout-mode') == '1200fixed') : ?>
	<meta name="viewport" content="width=1200px">
	<?php else : ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php endif; ?>
</head>
<?php
$header = ob_get_clean();
echo $header.$body;;
