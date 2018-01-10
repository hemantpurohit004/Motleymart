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

JHtml::_('behavior.modal');
//jimport( 'activity.socialintegration.profiledata' );

$comquick2cartHelper = new comquick2cartHelper;
$libclass = $comquick2cartHelper->getQtcSocialLibObj();

$mainframe = JFactory::getApplication();
$input = $mainframe->input;
$layout = $input->get('layout');
$params = JComponentHelper::getParams('com_quick2cart');

// Get store Owner.
if (!class_exists('storeHelper'))
{
	$path = JPATH_SITE . '/components/com_quick2cart/helpers/storeHelper.php';
	JLoader::register('storeHelper', $path);
	JLoader::load('storeHelper');
}

$storeHelper = new storeHelper;
$storeOwner = $storeHelper->getStoreOwner($this->store_id);
$integrate_with = $params->get('integrate_with','none');

if ($integrate_with != 'none')
{
	$profile_url = $libclass->getProfileUrl(JFactory::getUser($storeOwner));
	$UserName = JFactory::getUser($storeOwner)->name;
	$profile_path = "<a alt='' href='".$profile_url."'>".$UserName."</a>";
}

if (!empty($this->storeDetailInfo))
{
	$sinfo = $this->storeDetailInfo;

	if ($layout == "storeinfo")
	{
		?>
		<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
		<?php
	}
	?>
			<div class="row-">
				<div class=" col-xs-12">
					<legend>
						<?php
						$storeHelper = new storeHelper();
						$storeLink   = $storeHelper->getStoreLink($this->storeDetailInfo['id']);
						?>

						<a href="<?php echo $storeLink; ?>" class="btn btn-mini">
							<i class="<?php echo Q2C_ICON_HOME;?>"></i>
						</a> &nbsp; <?php echo $sinfo['title']; ?>

						<?php
						if (empty($this->editstoreBtn))
						{
							$social_options= '';
							$dispatcher = JDispatcher::getInstance();
							JPluginHelper::importPlugin('system');
							$result = $dispatcher->trigger('onProductDisplaySocialOptions', array($this->storeDetailInfo['id'], 'com_quick2cart.vendor.storeinfo', $sinfo['title'], $storeLink));

							// Call the plugin and get the result
							if (!empty($result))
							{
								$social_options=$result[0];
							}

							if (!empty($social_options))
							{
								?>
									<span class="social_options">
										<?php echo $social_options; ?>
									</span>
								<?php
							}
						}

						if (!empty($this->editstoreBtn))
						{
							// JRoute::_('index.php?option=com_quick2cart&view=orders&layout=mycustomer'),'_self'
							if (!empty($this->store_id))
							{
								$storeid = $this->store_id;
								$createstore_Itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=vendor&layout=createstore');
								echo "<button type='button' title=".JText::_( 'SA_EDIT' )." class='btn  btn_margin pull-right btn-mini' onclick=\"window.open('".JRoute::_("index.php?option=com_quick2cart&view=vendor&layout=createstore&store_id=".$storeid."&Itemid=".$createstore_Itemid)."')\" >
									<i class='" . QTC_ICON_EDIT . "'></i></button>";
							}
						}

						if ($integrate_with != 'none')
						{
							?>
							<p style="font-size: 13px;"><?php echo JText::sprintf('COM_QUICK2CART_CREATED_BY',$profile_path); ?></p>
							<?php
						}
						?>
					</legend>


					<div class="row">
						<div class="col-sm-4 col-xs-12">
							<!-- ADDRESS-->
							<?php
							$addInfo = array();
							$addInfo["address"] = $sinfo['address'];
							$addInfo["land_mark"] = $sinfo['land_mark'];
							$addInfo["cityRegion"] = $sinfo['city'];

							if ($sinfo['region'])
							{
								$addInfo["cityRegion"] =  $addInfo["cityRegion"] . ", " . $comquick2cartHelper->getStateName($sinfo['region']);
							}

							$addInfo["countryPincode"] = $comquick2cartHelper->getCountryName($sinfo['country']);

							if ($sinfo['pincode'])
							{
								$addInfo["countryPincode"] =  $addInfo["countryPincode"] . ", " . $sinfo['pincode'];
							}

							$addInfo = array_filter($addInfo,"strlen");
							$addStr = implode("<br/>", $addInfo);
							if (!empty($sinfo['address'])){ ?>
								<address class="">
									<strong><?php echo JText::_('VENDER_ADDRESS'); ?></strong>
									<br/>
									<span class="qtcWordWrap"><?php echo $addStr; ?></span>
								</address>
							<?php
							} ?>
						</div>
						<div class="col-sm-4 col-xs-12">
							<address>
								<abbr title="Phone">
									<strong><?php echo JText::_('VENDER_CONTACT_INFO'); ?></strong>:
								</abbr>
								<?php echo $sinfo['phone']; ?>
								<br/>
								<span class="qtcWordWrap"><?php echo $sinfo['store_email']; ?></span>
							</address>
						</div>
						<div class="col-sm-4 col-xs-12">
								<?php
								$img='';

								if (!empty($sinfo['store_avatar']))
								{
									$img = $comquick2cartHelper->isValidImg($sinfo['store_avatar']);
								}

								if (empty($img))
								{
									$img = $storeHelper->getDefaultStoreImage();
								}

								?>
								<img align="" class='img-rounded img-polaroid qtcImgAlignCenter' src="<?php echo $img;?>" alt="<?php echo  JText::_('QTC_IMG_NOT_FOUND') ?>"/>
						</div>
					</div>
					<div>
						<div class="col-xs-12">
								<p>
									<?php
									if ($layout=="storeinfo")
									{
										echo $sinfo['description'] ;
									}
									else
									{
										// GETTING STORE INFO LINK
										$vendor_Itemid = $comquick2cartHelper->getitemid('index.php?option=com_quick2cart&view=vendor');
										$storeinfo_link=JRoute::_('index.php?option=com_quick2cart&view=vendor&layout=storeinfo&Itemid=0&store_id='.$sinfo['id'].'&tmpl=component');
										$description_length=strlen($sinfo['description'] );
										$params = JComponentHelper::getParams('com_quick2cart');
										$limit=$params->get("storeDescriptionLimit",100);
										$readmore = substr($sinfo['description'] , 0, $limit);

										if (!empty($readmore) && $limit < $description_length)
										{
											$readmore =$readmore." ...&nbsp;";
										}

										echo $readmore;

										// chk FOR CHAR LIMIT TO SHOW
										if ($limit < $description_length)
										{
											// SHOE READ MORE LINK
											?>
											<a title="<?php echo JText::_('QTC_READMORE')?>" class="modal qtc_modal" rel="{handler: 'iframe', size: {x: window.innerWidth-350, y: window.innerHeight-150}, onClose: function(){}}" href="<?php echo $storeinfo_link;?>" style="display:inline-block;">
												<?php echo JText::_( 'QTC_READMORE' );?>
											</a>
											<?php
										}
									}
									?>
								</p>

						</div>
					</div>

				</div>
			</div>
			<div class="clearfix"></div>
	<?php
	if ($layout=="storeinfo")
	{
		?>
		</div>
		<?php
	}
}
?>

