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
  
  if ($align==1): 
  	$contentAlign = "content-right";
  	$featuresContentPull = "col-sm-12 col-xs-12 col-md-6 col-md-offset-6";
  	$featuresImgPull = " hidden-sm hidden-xs col-md-6 pull-left";
  elseif ($align==0): 
  	$contentAlign = "content-left";
  	$featuresContentPull = "col-sm-12 col-xs-12 col-md-6 pull-left";
  	$featuresImgPull = " hidden-sm hidden-xs col-md-6 pull-right";
	else: 
		$contentAlign = "container";
  	$featuresContentPull = "";
  	$featuresImgPull = ""; 
  endif;
?>
<?php 
	$featuresImg 				= $helper->get('block-bg');
	$featuresBackground  = 'background-image: url("'.$featuresImg.'"); background-repeat: no-repeat; background-size: cover; background-position: center center;';
?>
<div class="section-inner <?php echo $helper->get('block-extra-class'); ?>" <?php if($featuresImg): echo 'style="'.$featuresBackground.'"'; endif; ?>>
	<div class="acm-features style-3 row <?php echo $helper->get('features-style'); ?>">
		<div class="<?php echo $contentAlign; ?>">
			<?php if($helper->get('img-features')) : ?>
			<div class="features-image <?php echo $featuresImgPull; ?>" style="background: url("<?php echo $helper->get('img-features'); ?>") no-repeat right top; background-size: cover;">
				
			</div>
			<?php endif; ?>
			
			<div class="features-content <?php echo $featuresContentPull; ?>">
				<!--- Features Content -->
				<?php if($module->showtitle || $helper->get('block-intro')) : ?>
					<div class="row ft-top">
						<div class="col-sm-12">
							<?php if($module->showtitle): ?>
								<h3><?php echo $module->title ?></h3>
							<?php endif; ?>
							<div class="lead">
								<?php echo $helper->get('block-intro'); ?>
							</div>
						</div>
					</div>
				<?php endif ; ?>
				
				<?php if($helper->get('data-s3.img-icon') || $helper->get('data-s3.title') || $helper->get('data-s3.description')) : ?>
					<?php 
						$count = $helper->getRows('data-s3.title'); 
						if (!$helper->get('columns')): $numberColumn = 3; else: $numberColumn = $helper->get('columns'); endif;
					?>
						
						<?php for ($i=0; $i<$count; $i++) : ?>
						<?php  if ($i%$numberColumn==0) echo '<div class="row ft-bottom">'; ?>
							<div class="col-sm-<?php echo 12/$numberColumn; ?>">
							
								<?php if($helper->get('data-s3.img-icon', $i)): ?>
									<div class="icon">
										<span><i class="<?php echo $helper->get('data-s3.img-icon', $i); ?>"></i></span>
									</div>
								<?php endif; ?>
								
								<?php if($helper->get('data-s3.title', $i)): ?>
									<div class="title">
										<?php if ($helper->get('data-s3.title-link', $i)): ?>
											<h4><a href="<?php echo $helper->get('data-s3.title-link',$i); ?>"><?php echo $helper->get('data-s3.title', $i); ?></a></h4>
											<?php else : ?>
											<h4><?php echo $helper->get('data-s3.title', $i); ?></h4>
										<?php endif; ?>
									</div>
								<?php endif; ?>
								
								<?php if($helper->get('data-s3.description', $i)): ?>
									<div class="description">
										<?php echo $helper->get('data-s3.description', $i); ?>
									</div>
								<?php endif; ?>
							</div>
							<?php if ( ($i%$numberColumn==($numberColumn-1)) || $i==($count-1) )  echo '</div>'; ?>
						<?php endfor; ?>
				<?php endif; ?>
				<!--- //Features Content -->
			</div>
		</div>
	</div>
</div>