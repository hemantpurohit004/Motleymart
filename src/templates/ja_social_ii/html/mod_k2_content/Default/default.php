<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;
?>

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2ItemsBlock<?php if($params->get('moduleclass_sfx')) echo ' '.$params->get('moduleclass_sfx'); ?>">

	<?php if($params->get('itemPreText')): ?>
	<p class="modulePretext"><?php echo $params->get('itemPreText'); ?></p>
	<?php endif; ?>

	<?php if(count($items)): ?>
  <ul class="equal-height equal-height-child">
    <?php foreach ($items as $key=>$item):	?>
    <li class="col <?php echo ($key%2) ? "odd" : "even"; if(count($items)==$key+1) echo ' lastItem'; ?>"><div class="item-inner">

      <!-- Plugins: BeforeDisplay -->
      <?php echo $item->event->BeforeDisplay; ?>

      <!-- K2 Plugins: K2BeforeDisplay -->
      <?php echo $item->event->K2BeforeDisplay; ?>

      <!-- Plugins: AfterDisplayTitle -->
      <?php echo $item->event->AfterDisplayTitle; ?>

      <!-- K2 Plugins: K2AfterDisplayTitle -->
      <?php echo $item->event->K2AfterDisplayTitle; ?>

      <!-- Plugins: BeforeDisplayContent -->
      <?php echo $item->event->BeforeDisplayContent; ?>

      <!-- K2 Plugins: K2BeforeDisplayContent -->
      <?php echo $item->event->K2BeforeDisplayContent; ?>

      <?php if($params->get('itemImage') || $params->get('itemIntroText')): ?>
      <div class="moduleItemIntrotext">
	      <?php if($params->get('itemImage') && isset($item->image)): ?>
	      <a class="moduleItemImage" href="<?php echo $item->link; ?>" title="<?php echo JText::_('K2_CONTINUE_READING'); ?> &quot;<?php echo K2HelperUtilities::cleanHtml($item->title); ?>&quot;">
	      	<img src="<?php echo $item->image; ?>" alt="<?php echo K2HelperUtilities::cleanHtml($item->title); ?>"/>
	      </a>
	      <?php endif; ?>

        <?php if($params->get('itemTitle')): ?>
        <a class="moduleItemTitle" href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
        <?php endif; ?>

        <?php if(($params->get('itemAuthorAvatar')) || ($params->get('itemAuthor')) || ($params->get('itemDateCreated')) || ($params->get('itemCategory')) || ($params->get('itemHits'))) : ?>
        <div class="item-meta">

          <?php if($params->get('itemAuthorAvatar')): ?>
          <a class="k2Avatar moduleItemAuthorAvatar" rel="author" href="<?php echo $item->authorLink; ?>">
            <img src="<?php echo $item->authorAvatar; ?>" alt="<?php echo K2HelperUtilities::cleanHtml($item->author); ?>" style="width:<?php echo $avatarWidth; ?>px;height:auto;" />
          </a>
          <?php endif; ?>

          <?php if($params->get('itemAuthor')): ?>
          <div class="moduleItemAuthor">
            <i class="fa fa-user"></i>      
            <?php if(isset($item->authorLink)): ?>
            <a rel="author" title="<?php echo K2HelperUtilities::cleanHtml($item->author); ?>" href="<?php echo $item->authorLink; ?>"><?php echo $item->author; ?></a>
            <?php else: ?>
            <?php echo $item->author; ?>
            <?php endif; ?>
            
          </div>
          <?php endif; ?>

          <?php if($params->get('itemDateCreated')): ?>
          <span class="moduleItemDateCreated"><i class="fa fa-clock-o"></i><?php echo JHTML::_('date', $item->created, JText::_('K2_DATE_FORMAT_LC3')); ?></span>
          <?php endif; ?>

          <?php if($params->get('itemCategory')): ?>
          <span class="moduleItemCategory"><i class="fa fa-folder"></i><a href="<?php echo $item->categoryLink; ?>"><?php echo $item->categoryname; ?></a></span>
          <?php endif; ?>

          <?php if($params->get('itemHits')): ?>
          <span class="moduleItemHits">
            <i class="fa fa-eye"></i><?php echo $item->hits; ?> <?php echo JText::_('K2_TIMES'); ?>
          </span>
          <?php endif; ?>

        </div>
        <?php endif ?>

      	<?php if($params->get('itemIntroText')): ?>
      	<?php echo $item->introtext; ?>
      	<?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Plugins: AfterDisplayContent -->
      <?php echo $item->event->AfterDisplayContent; ?>

      <!-- K2 Plugins: K2AfterDisplayContent -->
      <?php echo $item->event->K2AfterDisplayContent; ?>

			<?php if($params->get('itemReadMore') && $item->fulltext): ?>
			<div class="moduleItemReadMore">
        <a class="btn btn-primary" href="<?php echo $item->link; ?>">
  				<?php echo JText::_('K2_READ_MORE'); ?>
  			</a>
      </div>
			<?php endif; ?>

      <!-- Plugins: AfterDisplay -->
      <?php echo $item->event->AfterDisplay; ?>

      <!-- K2 Plugins: K2AfterDisplay -->
      <?php echo $item->event->K2AfterDisplay; ?>

      <div class="clr"></div>
    </div></li>
    <?php endforeach; ?>
    <li class="clearList"></li>
  </ul>
  <?php endif; ?>

	<?php if($params->get('itemCustomLink')): ?>
	<a class="moduleCustomLink" href="<?php echo $params->get('itemCustomLinkURL'); ?>" title="<?php echo K2HelperUtilities::cleanHtml($itemCustomLinkTitle); ?>"><?php echo $itemCustomLinkTitle; ?></a>
	<?php endif; ?>


</div>
