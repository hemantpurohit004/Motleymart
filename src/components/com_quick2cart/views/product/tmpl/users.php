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

jimport( 'activity.socialintegration.profiledata' );
require_once JPATH_SITE.DS.'components'.DS.'com_quick2cart'.DS.'helpers'.DS.'product.php';

$productHelper = new productHelper();

// Get Item Id by url parameter
$params = JComponentHelper::getParams('com_quick2cart');
$socialintegration = $params->get('integrate_with','none');
$who_bought_limit = $params->get('who_bought_limit', 2);
$peopleWhoBought = $productHelper->peopleWhoBought(JRequest::getInt('itemid'),$params->get('who_bought_limit',2));
?>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
		<div class="row">
					<div class="span12 well well-small">
						<div align="center"><h4><?php echo JText::_('COM_QUICK2CART_WHO_BOUGHT') ;?></h4></div>
						<ul class="center thumbnails qtc_ForLiStyle" >
							<?php
							$i = 0 ;
							foreach($peopleWhoBought as $data)
							{
								$i ++;
								$libclass = new activitysocialintegrationprofiledata();
							?>
								<li>
									<a href="<?php echo $libclass->getUserProfileUrl($socialintegration, $data->id);?>">
											<img title="<?php echo $data->name; ?>" alt="Image Not Found" src="<?php echo $libclass->getUserAvatar($socialintegration, $data);?>" class="user-bought img-rounded ">
									</a>
								</li>
							<?php
							}
							?>

						</ul>
					</div>
		</div>
</div>

