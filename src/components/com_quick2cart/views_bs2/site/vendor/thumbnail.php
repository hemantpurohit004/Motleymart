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

$helperobj=new comquick2cartHelper;
$storeHelper=new storeHelper;
$store_link   = $storeHelper->getStoreLink($data['id']);
//$store_link= JRoute::_('index.php?option=com_quick2cart&view=vendor&layout=store&store_id='.$data['id'].'&Itemid='.$addProd_Itemid);
$com_params = JComponentHelper::getParams('com_quick2cart');
$img_width=$com_params->get('storeavatar_width');
?>

<li class="store_wrapper" <?php echo (empty($prodclass)?'style="width:'.($img_width+30).'px;"' :'class="'.$prodclass.'"' ); ?> >
	<div class="thumbnail">
		<!-- store name-->
		<div class="row-fluid">
			<div class="span12" align="center" >
				<a href="<?php echo $store_link; ?>">
					<strong><?php echo $data['title'];?></strong>
				</a>
			</div>
		</div>
		<!-- image div  -->
		<div class="row-fluid">
			<div class="span12" align="center" style="height: <?php echo $com_params->get( 'storeavatar_height' )+5; ?>px;" >
			<?php
			$image=$data['store_avatar'];
			$img='';
				if (!empty($data['store_avatar']))
				{
					$img=$helperobj->isValidImg($data['store_avatar']);
				}

				if (empty($img))
				{
					$img = $storeHelper->getDefaultStoreImage();
				}
			?>
				<a href="<?php echo $store_link; ?>">
					<img class=' img-rounded' src="<?php echo $img;?>" alt="<?php echo  JText::_('QTC_IMG_NOT_FOUND') ?>"/>
				</a>
			</div>
		</div>
	</div>
</li>

