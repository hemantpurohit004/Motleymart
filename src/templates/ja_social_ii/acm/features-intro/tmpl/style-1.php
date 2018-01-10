<?php
/**
 * ------------------------------------------------------------------------
 * JA Social II template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/
defined('_JEXEC') or die;
?>

<?php
  $align = $helper->get('align');
  
  if ($align == 1): 
  	$alignClass 	= "features-content-right";
  	$contentPull 	= "col-xs-12 col-md-6 pull-right";
  	$imgPull 			= "col-xs-12 col-md-6 pull-left";
  elseif ($align == 2):
  	$alignClass = "features-content-center";
  	$contentPull 	= "";
  	$imgPull 			= "";
  else:
  	$alignClass = "features-content-left";
  	$contentPull 	= "col-xs-12 col-md-6 pull-left";
  	$imgPull 			= "col-xs-12 col-md-6 pull-right";
  endif;
?>
<?php 
	$featuresImg 				= $helper->get('block-bg');
	$featuresBackground  = 'background-image: url("'.$featuresImg.'"); background-repeat: no-repeat; background-size: cover; background-position: center center;';
?>
<div class="section-inner <?php echo $helper->get('block-extra-class'); ?>" <?php if($featuresImg): echo 'style="'.$featuresBackground.'"'; endif; ?>>
	<?php if($module->showtitle || $helper->get('block-intro')): ?>
	<h3 class="section-title ">
		<?php if($module->showtitle): ?>
			<span><?php echo $module->title ?></span>
		<?php endif; ?>
		<?php if($helper->get('block-intro')): ?>
			<p class="container-sm section-intro hidden-xs"><?php echo $helper->get('block-intro'); ?></p>
		<?php endif; ?>	
	</h3>
	<?php endif; ?>		
	<div class="acm-features style-1 <?php echo $helper->get('features-style'); ?>">
		<div class="features-content <?php echo $alignClass; ?>"><div class="row">
			<?php if($helper->get('img-features')) : ?>
			<div class="features-image <?php echo $imgPull; ?>">
				<img src="<?php echo $helper->get('img-features'); ?>" />
			</div>
			<?php endif ; ?>
			
			<?php $count = $helper->getRows('data-s1.title'); ?>
			<?php for ($i=0; $i<$count; $i++) : ?>

				<div class="features-item <?php echo $contentPull; ?>">
					
					<?php if($helper->get('data-s1.title',$i)) : ?>
						<h3>
							<?php if ($helper->get('data-s1.title-link', $i)): ?>
								<a href="<?php echo $helper->get('data-s6.title-link',$i); ?>" title="<?php echo $helper->get('data-s1.title', $i) ?>">
							<?php endif; ?>
							
							<?php echo $helper->get('data-s1.title', $i) ?>
							
							<?php if ($helper->get('data-s1.title-link', $i)): ?>
								</a>
							<?php endif; ?>
						</h3>
					<?php endif ; ?>
					
					<?php if($helper->get('data-s1.description',$i)) : ?>
						<p><?php echo $helper->get('data-s1.description', $i) ?></p>
					<?php endif ; ?>
					
					<?php if($helper->get('data-s1.button',$i)) : ?>
						<a class="btn btn-primary btn-rounded btn-border" href="<?php echo $helper->get('data-s1.title-link',$i); ?>"><?php echo $helper->get('data-s1.button', $i) ?></a>
					<?php endif ; ?>
				</div>
			<?php endfor ?>
		</div></div>
	</div>
</div>