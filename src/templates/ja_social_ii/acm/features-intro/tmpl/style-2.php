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
	$featuresImg 				= $helper->get('block-bg');
	$featuresBackground  = 'background-image: url("'.$featuresImg.'"); background-repeat: no-repeat; background-size: cover; background-position: center center;';
?>
<div class="section-inner <?php echo $helper->get('block-extra-class'); ?>" <?php if($featuresImg): echo 'style="'.$featuresBackground.'"'; endif; ?>>

	<div class="acm-features style-2 <?php echo $helper->get('features-style'); ?>">
		<?php $count = $helper->getRows('data.title'); ?>
		<?php $column = $helper->get('columns'); ?>
		<?php 
			for ($i=0; $i<$count; $i++) : 
			if ($i%$column==0) echo '<div class="row">'; 
		?>
		
			<div class="features-item col-sm-<?php echo 12/$column ?> center">
				
				<?php if($helper->get('data.font-icon', $i)) : ?>
					<div class="font-icon">
						<span><i class="<?php echo $helper->get('data.font-icon', $i) ; ?>"></i></span>
					</div>
				<?php endif ; ?>

				<?php if($helper->get('data.img-icon', $i)) : ?>
					<div class="img-icon">
						<img src="<?php echo $helper->get('data.img-icon', $i) ?>" alt="" />
					</div>
				<?php endif ; ?>
				
				<?php if($helper->get('data.title', $i)) : ?>
					<h3><?php echo $helper->get('data.title', $i) ?></h3>
				<?php endif ; ?>
				
				<?php if($helper->get('data.description', $i)) : ?>
					<p><?php echo $helper->get('data.description', $i) ?></p>
				<?php endif ; ?>
			</div>
			<?php if ( ($i%$column==($column-1)) || $i==($count-1) )  echo '</div>'; ?>
		<?php endfor ?>
	</div>
</div>