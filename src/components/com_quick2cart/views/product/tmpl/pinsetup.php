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

if (isset($random_container))
{
	JHtml::_('behavior.tooltip');
	JHtml::_('behavior.framework');
	JHtml::_('behavior.modal');

	//$document = JFactory::getDocument();
	//$document->addScript(JUri::root(true).'/components/com_quick2cart/assets/js/masonry.pkgd.min.js');

	$this->params = JComponentHelper::getParams('com_quick2cart');

	// Get pin width
	$pin_width = $this->params->get('pin_width');

	if (empty($pin_width))
	{
		$pin_width = 170;
	}

	// Get pin padding
	$pin_padding = $this->params->get('pin_padding');

	if (empty($pin_padding))
	{
		$pin_padding = 7;
	}

	// Calulate columnWidth (columnWidth = pin_width+pin_padding)
	$columnWidth = $pin_width + $pin_padding;
	?>

	<?php if (!isset($pin_width_defined)): ?>
		<style type="text/css">
			.q2c_pin_item_<?php echo $random_container;?> { width: <?php echo $pin_width . 'px'; ?> !important; margin-bottom: <?php echo $pin_padding . 'px'; ?> !important; }
		</style>
	<?php endif; ?>

	<script type="text/javascript">

		var container_<?php echo $random_container;?> = document.getElementById(pin_container_<?php echo $random_container; ?>);
		var msnry = new Masonry( container_<?php echo $random_container;?>, {
			columnWidth: <?php echo $columnWidth; ?>,
			itemSelector: '.q2c_pin_item_<?php echo $random_container;?>',
			gutter: <?php echo $pin_padding; ?>});


		/*function QttPinArrange()
		{

			var container_<?php echo $random_container;?> = document.getElementById(pin_container_<?php echo $random_container; ?>);
			var msnry = new Masonry( container_<?php echo $random_container;?>, {
				columnWidth: <?php echo $columnWidth; ?>,
				itemSelector: '.q2c_pin_item_<?php echo $random_container;?>',
				gutter: <?php echo $pin_padding; ?>});
		}*/

		techjoomla.jQuery(document).ready(function()
		{
			/*var container_<?php echo $random_container;?> = document.getElementById(pin_container_<?php echo $random_container; ?>);
			var msnry = new Masonry( container_<?php echo $random_container;?>, {
				columnWidth: <?php echo $columnWidth; ?>,
				itemSelector: '.q2c_pin_item_<?php echo $random_container;?>',
				gutter: <?php echo $pin_padding; ?>});
			*/
			var random_containerId = pin_container_<?php echo $random_container; ?>;
			var columnWidth = <?php echo $columnWidth; ?>;
			var random_container = '.q2c_pin_item_<?php echo $random_container;?>';
			var pin_padding = <?php echo $pin_padding; ?>;


			setTimeout(function() { QttPinArrange(random_containerId, columnWidth, random_container, pin_padding); }, 1000);
			setTimeout(function() { QttPinArrange(random_containerId, columnWidth, random_container, pin_padding); }, 2000);
			setTimeout(function() { QttPinArrange(random_containerId, columnWidth, random_container, pin_padding); }, 3000);
			setTimeout(function() { QttPinArrange(random_containerId, columnWidth, random_container, pin_padding); }, 4000);
			setTimeout(function() { QttPinArrange(random_containerId, columnWidth, random_container, pin_padding); }, 5000);
			setTimeout(function() { QttPinArrange(random_containerId, columnWidth, random_container, pin_padding); }, 6000);
			setTimeout(function() { QttPinArrange(random_containerId, columnWidth, random_container, pin_padding); }, 7000);
			setTimeout(function() { QttPinArrange(random_containerId, columnWidth, random_container, pin_padding); }, 8000);
			setTimeout(function() { QttPinArrange(random_containerId, columnWidth, random_container, pin_padding); }, 9000);
			setTimeout(function() { QttPinArrange(random_containerId, columnWidth, random_container, pin_padding); }, 10000);
			setTimeout(function() { QttPinArrange(random_containerId, columnWidth, random_container, pin_padding); }, 11000);
		});
	</script>
<?php
}
?>
