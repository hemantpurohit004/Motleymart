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

$params = JComponentHelper::getParams('com_quick2cart');
$currencies = $params->get('addcurrency');
$curr = explode(',',$currencies);
$mediaCount = !empty($this->getMediaDetail)?count($this->getMediaDetail) -1 :0;
$mediaDetail = !empty($this->getMediaDetail) ? $this->getMediaDetail:array();

$fileUploadMode = $params->get('eProdUploadMode',1);
$eProdUExpiryMode = $params->get('eProdUExpiryMode','epboth');
$allowedFileExtensions = $params->get('allowedFileExtensions');
$eProdMaxFileLimit = $params->get('eProdMaxFileLimit',5);
$allowedFileExtensions = $params->get('allowedFileExtensions',"zip,doc,docx,pdf,xls,txt,gz,gzip,rar,jpg,gif,tar.gz,xlsx,pps,csv,bmp,epg,ico,odg,odp,ods,odt,png,ppt,swf,xcf,wmv,avi,mkv,mp3,ogg,flac,wma,fla,flv,mp4,wav,aac,mov,epub,png,jpg");
$extArr=explode(',', $params->get('allowedFileExtensions'));
$extArrString = "'". implode("','", $extArr) ."'" ;
?>
<script src="<?php echo JUri::root().'components'.DS.'com_quick2cart'.DS.'assets'.DS.'js'.DS.'resumable.js'; ?>"></script>
<script type="text/javascript">
	/* globle params */
	var media_current_id = <?php echo $mediaCount  ?>;
	var maxAllowedMedia = <?php echo $eProdMaxFileLimit-1  ?>;  /* as media start from 0 */
	var r = new Array();
	var fileUpload_maxFileSize=<?php echo $params->get('eProdMaxSize')*1024*1024;?>;
	var fileUpload_acceptFileTypes = [<?php
			$arr=explode(',', $params->get('allowedFileExtensions'));
			echo "'". implode("','", $arr) ."'" ; /* Make format as 'avi','mp4','mpeg'. */
				?>];
	var qtcUploadTarget = '<?php echo Juri::root();?>index.php?option=com_quick2cart&task=product.mediaUpload&tmpl=component';
	/** This function is used to add attribute(eg. color)
	*/
	/*add clone script*/
	function addCloneMedia(rId,rClass)
	{
		var curr="<?php echo $currencies ?>";
		var temp = new Array();
		var temp= curr.split(',');


		// CURRENT ATRIBURE ID -- global declaration
		media_current_id++;

		// CHECK FOR MEDIA UPLAD LIMIT
		var mdiaCount = techjoomla.jQuery(".qtc_mediaContainer").length ;
		if (mdiaCount <= (maxAllowedMedia + 1))
		{
			var num=media_current_id + 1;//techjoomla.jQuery('.'+rClass).length;

			// CREATE REMOVE BUTTON
			var removeButton="<div class='col-md-1'>";
			removeButton+="<button class='btn btn-mini btn-default' type='button' id='remove"+num+"'";
			removeButton+="onclick=\"removeClone('"+rId+num+"','"+rClass+"');\" title=\"<?php echo JText::_('COM_QUICK2CRT_REMOVE_TOOLTIP');?>\" >";
			removeButton+="<i class=\"<?php echo QTC_ICON_MINUS;?> \"></i></button>";
			removeButton+="</div>";

			var oldnum=num -1;
			var newElem=techjoomla.jQuery('#'+rId+'0').clone().attr('id',rId+num);
			newElem.removeClass('qtc_media_hide');
			techjoomla.jQuery('.'+rClass+':last').after(newElem);
			//techjoomla.jQuery('div.'+rClass +' :last').append(removeButton);
			techjoomla.jQuery('#'+rId+num).children().last().replaceWith(removeButton);

			var newelementid=	rId+num;
			var option=0;

			/*1. CHANGE MEDIA FILE NAME */

			var newname = 'prodMedia['+num+'][name]';
			var ck=newElem.find('.qtcMediaFileName').attr({'name': newname,'id':newname,'value':''});

			/*2. CHANGE STATUS CHECKBOX NAME */
			var newname = 'prodMedia['+num+'][status]';
			var ck=newElem.find('.qtcMediaStatus').attr({'name': newname,'id':newname});

			/*3. CHANGE UPLOAD STATUS MODE */
			newname = 'prodMedia['+num+'][uploadMode]';
			var newFnName = "changeUploadMethod('upload',"+num+")";
			newElem.find('.qtcMeduaUploadMode_upload').attr({'name': newname,'id':'qtcMeduaUploadMode_upload'+num,'onchange': newFnName});  // for upload via browse

			newFnName = "changeUploadMethod('useFilePath',"+num+")";
			newElem.find('.qtcfieldType').attr({'onchange': newFnName});
			newElem.find('.qtcMeduaUploadMode_filepath').attr({'name': newname,'id':'qtcMeduaUploadMode_filepath'+num,'onchange': newFnName});  // for upload path

			//show file upload field
			newElem.find('.qtcMedUploadModeWrapper').show();
			newElem.find('.qtcMedUploadWrapper').show();

			//HIDE product link
			newElem.find('.qtcMediaProdLink').hide();


			/* 4. CHANGE FILE UPLOAD */
			newname = 'qtcMediaFile'+num;
			var fnname = "qtcProdMedUpload("+num+")";

			// change for file element
			newElem.find('.qtcMediaFileUploadEle').attr({'name': newname,'id':newname,'onchange': fnname});

			// change funtion qtcProdMedUpload
			var newname = 'prodMedia['+num+'][mediaFilePath]';
			newElem.find('.qtcMediaUpload').attr({'name': newname,'id':'ajax_upload_hidden'+num,'value':''});
			newElem.find('.qtc_progress-barWrapper').attr({'id':'qtc_progress-barWrapper'+num});
			// progress bar
			newElem.find('.qtcMediaProgressBar').attr({'id':'qtc_progress-bar'+num,'style':'width:0%'});


			/* 5. PURCHASE REQUIRE  */
			var newFnName = "qtc_expirationChange("+num+")";
			var newname = 'prodMedia['+num+'][purchaseReq]';
			var ck=newElem.find('.qtc_MedPurchaseReq').attr({'name': newname,'id':newname,'onchange': newFnName});

			/* 6. DOWNLOAD COUNT  */
			var newname = 'prodMedia['+num+'][downCount]';
			var ck=newElem.find('.qtcMediaDownCount').attr({'name': newname,'id':newname});

			/* 7. Expirary COUNT  */
			var newname = 'prodMedia['+num+'][expirary]';
			var ck=newElem.find('.qtcMediaExp').attr({'name': newname,'id':newname});

			var str =	qtcProdMedUpload(num);

			// execute the script
			eval(str);
		}
		else
		{
			alert("<?php echo JText::sprintf('COM_QUICK2CART_MEDIA_REACHED_MAX_ADD_MEDIALIMIT',$eProdMaxFileLimit); ?>");
		}
	}

	/** This function hide/show file field*/
	function changeUploadMethod(val,medNum)
	{
		if (val == 'upload')
		{
			// show file field
				techjoomla.jQuery("#qtcMediaFile"+medNum).show();
				techjoomla.jQuery("#qtc_progress-barWrapper"+medNum).show();
			// hide filePath textbox
			techjoomla.jQuery("#ajax_upload_hidden"+medNum).hide();
		}
		else
		{
			// hide file field
				techjoomla.jQuery("#qtcMediaFile"+medNum).hide();
				techjoomla.jQuery("#qtc_progress-barWrapper"+medNum).hide();
			// show filePath textbox
				techjoomla.jQuery("#ajax_upload_hidden"+medNum).show();

		}
	}

	function qtcProdMedUpload(index)
	{
		var obj = 'r'+index;

		var str= 	"var "+obj+" = new Resumable({target:'"+qtcUploadTarget+"', fileParameterName:'qtcMediaFile"+index+"', chunkSize : 2*1024*1024, query:{}, maxFiles:1, fileType:[<?php echo $extArrString ;?>], maxFileSize: "+fileUpload_maxFileSize+" });    ";

		str = str +  obj+".assignBrowse(document.getElementById('qtcMediaFile"+index+"'));   ";
		str = str +  obj+".assignDrop(document.getElementById('qtcMediaFile"+index+"'));   ";

		str = str +  obj+".on('fileSuccess', function(file){ " +
			"var len = file.chunks.length ; " +

			"if (jQuery.parseJSON(file.chunks[len-1].xhr.response).validate.error === 1)  "+
			" { "+
				 ' alert("<?php echo JText::_('COM_QUICK2CART_FILE_UPLOAD_ERROR_MSG', true); ?>"); ' +
			"}" +
			"else "+
			"{  "+
			"	jQuery('#ajax_upload_hidden"+index+ "').val( jQuery.parseJSON(file.chunks[len-1].xhr.response).fileUpload.filePath ); "+
				'alert("<?php echo JText::_('COM_QUICK2CART_UPLOAD_SUCCESS_MSG', true); ?>"); ' +
			"} "+
		"}); " +
 ";  ";

		str = str + obj+".on('fileAdded', function(file, event){ " +
			"jQuery('#qtc_progress-bar"+index+"').css('width',0 +'%'); " +
			obj+".upload(); "+
		"});  "+
		obj +".on('fileError', function(file, message){"+
			"alert(message); "+
		"});		";

		str = str + obj+".on('progress', function(){ "+
			"jQuery('#qtc_progress-bar"+index+"').css('width',(100 * "+obj+".progress(1)) + '%');  "+
		"});		" ;

		str = str + obj+".on('error', function(message, file){ " +
			 " alert(message); " +
		" }); ";

		return str;
	}


</script>


	<div class="">
		<!-- Allowed file types -->
		<?php
		$allowedFileExtensionsMSG = str_replace(",", ", ", $allowedFileExtensions);
		?>
		<div class="row-fluid">
			<div class="alert alert-info">
				<span>
					<?php echo JText::sprintf("COM_QUICK2CRT_MEDIA_SUPPORTED_FILE_TYPES",$allowedFileExtensionsMSG);?>
					<b><?php echo JText::sprintf("COM_QUICK2CRT_MEDIA_LIMIT_MSG",$eProdMaxFileLimit);?>  </b>
				</span>
			</div>
		</div>
		<div class="">
			<!--This is a repating block of html-->
			<?php
				$m = 0;
				$attribute_container_id = "qtc_mediaContainer".$m;

				// Tack backup
				$mediaDetailBackUp = $mediaDetail;

				// Make empty  As EDIT DONT FILL IN 0'TH INDEX
				$mediaDetail = array();

				 ?>
				<div id=<?php echo $attribute_container_id ?> class="qtc_mediaContainer qtc_media_hide " >
					<div class="com_qtc_media_repeat_block well well-small col-md-11 com_qtc_img_border">

						<?php
						// CHECK for view override
							$comquick2cartHelper = new comquick2cartHelper;
							$att_list_path=$comquick2cartHelper->getViewpath('product','media',"SITE","SITE");
							ob_start();
								include($att_list_path);
								$html_attri = ob_get_contents();
							ob_end_clean();

						echo $html_attri;
						?>

					</div>
				<!-- dont delete required empty div-->
					<div></div>
				</div>
				<?php
				// Restore backup
				$mediaDetail = $mediaDetailBackUp;
				if (!empty($mediaDetail))
				{
					$mediaDetail[] = $mediaDetail[0];
				}
				for($m=1; $m<=$mediaCount+1  && $m < $eProdMaxFileLimit+1; $m++) // for each attribute
				{
					$attribute_container_id = "qtc_mediaContainer".$m;
				 ?>
				<div id=<?php echo $attribute_container_id ?> class="qtc_mediaContainer ">
					<div class="com_qtc_media_repeat_block well well-small col-md-11 com_qtc_img_border">

						<?php
						// CHECK for view override
							$comquick2cartHelper = new comquick2cartHelper;
							$att_list_path=$comquick2cartHelper->getViewpath('product','media',"SITE","SITE");
							ob_start();
								include($att_list_path);
								$html_attri = ob_get_contents();
							ob_end_clean();

						echo $html_attri;
						?>

					</div>
					<?php //if ($m != 0)
					{?>
					<div class='col-md-1'>
						<button class='btn btn-mini btn-default' type='button' id='remove<?php echo $m;?>'
							onclick="removeClone('qtc_mediaContainer<?php echo $m;?>','qtc_mediaContainer');" title="<?php echo JText::_('COM_QUICK2CRT_REMOVE_TOOLTIP');?>" >
							<i class="<?php echo QTC_ICON_MINUS;?> "></i>
						</button>
					</div>
					<?php
					}
					?>
				<!-- dont delete required empty div-->
					<div></div>
				</div>
				<?php
				} // end of attribute for loop
				 ?>
			<div class=" col-md-1 ">
				<button class="btn btn-mini btn-default" type="button" id='add'
				onclick="addCloneMedia('qtc_mediaContainer','qtc_mediaContainer');"
				title="<?php echo JText::_('COM_QUICK2CRT_ADDMORE_TOOLTIP');?>">
					<i class="<?php echo QTC_ICON_PLUS; ?> "></i>
				</button>
			</div>

		</div>
	</div><!---->

<script type="text/javascript">

		var Mstr =	qtcProdMedUpload(<?php echo $m-1 ?>);
		// execute the script
		eval(Mstr);
/*
		var r = new Resumable({
			target:'<?php echo Juri::root();?>index.php?option=com_quick2cart&task=product.mediaUpload',
			fileParameterName:"qtcMediaFile<?php echo $m; ?>",
			chunkSize : 2*1024*1024,
			query:{},
			maxFiles:1,
			fileType: fileUpload_acceptFileTypes,
			maxFileSize: fileUpload_maxFileSize
		});

		r.assignBrowse(document.getElementById("qtcMediaFile<?php echo $m; ?>"));
		r.assignDrop(document.getElementById("qtcMediaFile<?php echo $m; ?>"));

		r.on('fileSuccess', function(file){
			var len = file.chunks.length ;
			if (jQuery.parseJSON(file.chunks[len-1].xhr.response).validate.error === 1)
			{
				alert('<?php echo JText::_('COM_QUICK2CART_FILE_UPLOAD_ERROR_MSG'); ?>');
			}
			else
			{

				jQuery("#ajax_upload_hidden<?php echo $m; ?>").val( jQuery.parseJSON(file.chunks[len-1].xhr.response).fileUpload.filePath );
				alert('<?php echo JText::_('COM_QUICK2CART_UPLOAD_SUCCESS_MSG'); ?>');

			}
		});

		r.on('fileAdded', function(file, event){
			jQuery("#qtc_progress-bar<?php echo $m; ?>").css('width',0 +'%');
			r.upload();
		});

		r.on('fileError', function(file, message){
			alert(message);
		});

		r.on('progress', function(){
			jQuery("#qtc_progress-bar<?php echo $m; ?>").css('width',(100 * r.progress(1)) + '%');
		});

		r.on('error', function(message, file){
			alert(message);
		});*/
</script>

