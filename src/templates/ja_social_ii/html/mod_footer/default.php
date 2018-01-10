<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_footer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$tplparams = JFactory::getApplication()->getTemplate(true)->params;
?>
<div class="module">
  <?php if ($tplparams->get('t3-rmvlogo', 1)): ?>
    <div class="poweredby text-hide">
      <a class="t3-logo t3-logo-small t3-logo-color" href="http://t3-framework.org" title="<?php echo JText::_('T3_POWER_BY_TEXT') ?>"
         target="_blank" <?php echo method_exists('T3', 'isHome') && T3::isHome() ? '' : 'rel="nofollow"' ?>><?php echo JText::_('T3_POWER_BY_HTML') ?></a>
    </div>
  <?php endif; ?>
  <small><?php echo $lineone; ?> Designed by <a href="http://www.joomlart.com/" title="Visit Joomlart.com!" <?php echo method_exists('T3', 'isHome') && T3::isHome() ? '' : 'rel="nofollow"' ?>>JoomlArt.com</a>.</small>
  <small><?php echo JText::_( 'MOD_FOOTER_LINE2' ); ?></small>
</div>