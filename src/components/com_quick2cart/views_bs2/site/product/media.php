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

// This is require for media pop up in backend
$this->productHelper = new productHelper;

//$m=0;
$prodMedia='prodMedia';
?>
<div class='qtcMediaWrapper'>
<div class=' row-fluid'>
	<div class="">
		<div class="span6">
			<div class=' row-fluid'>
				<!-- product name-->
				<div class="control-group">
					<label class="control-label" for="qtcmedianame"><?php echo JText::_( "COM_QUICK2CART_PROD_PAGE_MEDIA_NAME")?></label>
					<div class="controls">

						<input type="text" name="prodMedia[<?php echo $m ?>][name]>" value="<?php echo !empty($mediaDetail[$m]['file_display_name']) ? $mediaDetail[$m]['file_display_name'] : ''; ?>" class='input-medium qtcMediaFileName'  id="qtcmedianame<?php echo $m ?>"  placeholder="<?php echo JText::_( "COM_QUICK2CART_PROD_PAGE_MEDIA_NAME_PLACEHOLDER")?>">

						<input type="hidden" name="prodMedia[<?php echo $m ?>][file_id]>" class='input-medium' id="qtcmediaFileId<?php echo $m ?>" value="<?php echo !empty($mediaDetail[$m]['file_id']) ? $mediaDetail[$m]['file_id'] : ''; ?>">
					</div>
				</div>

				<!-- enable-->
				<div class="control-group">
					<label class="control-label" for="qtcmediaStatus"><?php echo JText::_( "COM_QUICK2CART_PROD_PAGE_MEDIA_STATUS")?></label>
					<div class="controls">
							<label class="checkbox">
							<?php
							$mediastatus = "checked";

							if (isset($mediaDetail[$m]['state']))
							{
								$mediastatus = ($mediaDetail[$m]['state']) ? "checked" : "";
							}
							?>
							<input type="checkbox" class="qtcMediaStatus" name="prodMedia[<?php echo $m ?>][status]>" autocomplete="off" <?php echo $mediastatus;?> ><?php echo JText::_('COM_QUICK2CART_PROD_PAGE_MEDIA_PUBLISHED')?>
						</label>
					</div>
					<div class="qtcClearBoth"></div>
				</div>

				<!-- upload mode-->
				<?php

				if ($fileUploadMode == 3)
				{
					$isPublished = " checked ";
					$uploadModeDisplay = !empty($mediaDetail[$m]['file_id']) ? "display:none;" : '' ?>
					<div class="control-group qtcMedUploadModeWrapper" style="<?php echo $uploadModeDisplay; ?>" >
						<label class="control-label" for="qtcmediaStatus"><?php echo JText::_( "COM_QUICK2CART_PROD_PAGE_MEDIA_UPLOADMODE")?></label>
						<div class="controls">
							<label class="radio inline">
								<input type="radio" class="qtcMeduaUploadMode_upload" id="qtcMeduaUploadMode_upload<?php echo $m?>" name="prodMedia[<?php echo $m ?>][uploadMode]>"  value="upload" onchange="changeUploadMethod('upload',<?php echo $m ?>)" checked  >
								<?php echo JText::_('COM_QUICK2CART_PROD_PAGE_MEDIA_UPLOAD_FILE')?>
							</label>
							<label class="radio inline">
								<input type="radio" class="qtcMeduaUploadMode_filepath" id="qtcMeduaUploadMode_filepath<?php echo $m?>" name="prodMedia[<?php echo $m ?>][uploadMode]>" onchange="changeUploadMethod('useFilePath',<?php echo $m ?>)"  value="useFilePath" >
								<?php echo JText::_('COM_QUICK2CART_PROD_PAGE_MEDIA_USE_FILE_PATH')?>
							</label>
						</div>
					</div>
				<?php
				}
				?>

				<!-- file upload-->
				<div class="control-group qtcMedUploadWrapper" style="<?php echo !empty($mediaDetail[$m]['file_id'])?'display:none;':'display:block;'; ?>">

						<?php
						$qtcFieldType = 'display:none;';
						if ( $fileUploadMode == 1 || $fileUploadMode == 3 )
						{
							?>
						<div class=""  style="margin-left:20px;">
							<input class="qtcMediaFileUploadEle" id="qtcMediaFile<?php echo $m ?>" type="file" name="qtcMediaFile<?php echo $m ?>" >
							<div class="progress progress-success progress-striped active qtc_progress-barWrapper" id="qtc_progress-barWrapper<?php echo $m ?>">
								<div class="bar qtcMediaProgressBar" id="qtc_progress-bar<?php echo $m ?>" style="width:0%"></div>
							</div>

						</div>
						<?php
						}
						$qtcFieldType = 'display:none;';
						if ( $fileUploadMode == 2 || $fileUploadMode == 3 )
						{
							if ($fileUploadMode == 2)
							{
								$qtcFieldType = 'display:block;';
							}

						}
						?>
						<label class="control-label" for="qtcmediaStatus" style="<?php echo $qtcFieldType; ?>"><?php echo JText::_( "FILEPATH")?></label>
						<div class="controls">
							<input type="text" class="qtcMediaUpload input-medium" name="prodMedia[<?php echo $m ?>][mediaFilePath]" id="ajax_upload_hidden<?php echo $m ?>" value="<?php echo !empty($mediaDetail[$m]['filePath']) ? $mediaDetail[$m]['filePath'] :'' ?>" style="<?php echo $qtcFieldType; ?>" placeholder="<?php echo JText::_( "QTC_FILEPATH")?>">
						</div>
				</div>
				<!-- file upload END -->
				<?php

				if (!empty($mediaDetail[$m]['file_id']) )
				{
					?>
					<div class="qtcMediaProdLink ">
						<div class="control-label"><strong><?php echo JText::_( "COM_QUICK2CART_PROD_PG_DOWNLOAD"); ?></strong></div>
						<br>
						<div class="qtcProdPgDownLink">
						<?php
							// require when call from backend
							$linkData = array();
							$linkData['linkName'] = $mediaDetail[$m]['file_display_name'];
							$linkData['href'] = $this->productHelper->getMediaDownloadLinkHref($mediaDetail[$m]['file_id'], "strorecall=1"); // authoized to store releated persons
							$linkData['event'] = '';
							$linkData['functionName'] = '';
							$linkData['fnParam'] = '';
							echo $this->productHelper->showMediaDownloadLink($linkData);
							?>
						</div>
					</div>
					<?php

				}
				?>
			</div>
			</div> <!-- FIRST span6 end-->

		<div class="span6">
				<div class=' row-fluid'>
				<!-- purchase require name-->
				<div class="control-group">
					<label class="control-label" for="qtcpurchaseRequire"><?php echo JText::_( "COM_QUICK2CART_PROD_PURCHASE_REQ")  ?></label>
					<div class="controls">
						<label class="checkbox">

							<?php
							$hideExpirationFields = "";
							$qtc_ck_att = "checked";

							if (isset($mediaDetail[$m]['purchase_required']))
							{
								$qtc_ck_att = ($mediaDetail[$m]['purchase_required']) ? "checked" : "";
								$hideExpirationFields = ($mediaDetail[$m]['purchase_required']) ? "" : "display:none;";
							}
							?>
							<input type="checkbox" class="qtc_MedPurchaseReq" name="prodMedia[<?php echo $m ?>][purchaseReq]" autocomplete="off" <?php echo $qtc_ck_att;?> onChange="qtc_expirationChange(<?php echo $m ?>)">
							<?php echo JText::_('COM_QUICK2CART_PROD_PAGE_MEDIA_PUBLISHE_YES')?>
						</label>
					</div>
				</div>
				<?php
				?>

				<!-- download count-->
				<?php

				if ($eProdUExpiryMode == 'epMaxDownload' || $eProdUExpiryMode == 'epboth')
				{
					$downcount = - 1;

					if (!empty($mediaDetail[$m]['download_limit']))
					{
						$downcount = $mediaDetail[$m]['download_limit'];
					}
				?>
				<div class="control-group" style="<?php echo $hideExpirationFields; ?>">
					<label class="control-label" for="qtcDownCount">
						<?php echo JHtml::tooltip(JText::_('COM_QUICK2CART_PROD_DOWN_COUNT_DES'), JText::_('COM_QUICK2CART_PROD_DOWN_COUNT'), '', JText::_('COM_QUICK2CART_PROD_DOWN_COUNT'));?>
					</label>
					<div class="controls">
							<input type="text" name="prodMedia[<?php echo $m ?>][downCount]" value="<?php echo $downcount;?>" class='input-mini qtcMediaDownCount' id="" placeholder="">
					</div>
				</div>
				<?php
				}
				?>

				<!-- expirary-->

				<?php
				if ($eProdUExpiryMode == 'epDateExpiry' || $eProdUExpiryMode == 'epboth')
				{
					/*if (empty($mediaDetail[$m]['expiry_mode']) || $mediaDetail[$m]['expiry_mode'] == -1 || $mediaDetail[$m]['expiry_mode'] == 1)
					{
						// -1 : it edit produt and changed setting form limit to date
						$expirary = 'epMonthExp';
					}
					else
					{
						$expirary = 'epDateExp';
					}*/

					// days or months
					$expFormat=JText::_( "COM_QUICK2CART_PROD_EXPIRARY_DAYS");
					$eProdExpFormat = $params->get('eProdExpFormat','epMonthExp');
					if ($eProdExpFormat == 'epMonthExp')
					{
						$expFormat = JText::_( "COM_QUICK2CART_PROD_EXPIRARY_MONTHS");
					}

					// DB EXPIRARY VALUE
					$expValue = 2;
					if (isset($mediaDetail[$m]['expiry_in']))
					{
						// -1 : it edit produt and changed setting form limit to date
						$expValue = $mediaDetail[$m]['expiry_in'];
					}
				?>
				<div class="control-group" style="<?php echo $hideExpirationFields; ?>">
					<label class="control-label" for=""><?php echo JText::_( "COM_QUICK2CART_PROD_EXPIRARY")?></label>
					<div class="controls">
						<div class="input-append">
							<input id=""  name="prodMedia[<?php echo $m ?>][expirary]" value="<?php echo $expValue; ?>" class="input-small qtcMediaExp" placeholder="" type="text">
							<span class="add-on"><?php echo $expFormat;?></span>
						</div>
					</div>
				</div>
				<?php
				}	?>

				<!-- order-->
	<!--
				<div class="control-group">
					<label class="control-label" for=""><?php//echo JText::_( "COM_QUICK2CART_PROD_ORDER")?></label>
					<div class="controls">
						<input id="" name="prodMedia[<?php //echo $m ?>][order]>" value="1" class="input-mini" placeholder="" type="text">
					</div>
				</div>
	-->

			</div><!-- FIRST span4 end-->
			</div>
	</div>
</div> <!--END of form horizontal -->
</div> <!-- end of qtcMediaWrapper -->

