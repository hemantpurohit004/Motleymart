<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

//jimport('joomla.form.formvalidator');
jimport('joomla.html.pane');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.tooltip');
JHtmlBehavior::framework();
jimport('joomla.html.parameter');
JToolBarHelper::back( JText::_('QTC_HOME') , 'index.php?option=com_quick2cart');
JToolBarHelper::save($task ='saveCoupon', $alt = JText::_('QTC_SAVE'));
JToolBarHelper::cancel( $task = 'cancelcoupon', $alt =JText::_('QTC_CLOSE')  );
//added by aniket
$entered_numerics= "'".JText::_('QTC_ENTER_NUMERICS')."'";
// added by sj
?>
<script src="<?php echo JUri::root().'administrator/components/com_quick2cart/assets/js/geo/jquery-1.7.2.js'?>"></script>
<script src="<?php echo JUri::root().'administrator/components/com_quick2cart/assets/js/geo/jquery.ui.core.js'?>"></script>
<script src="<?php echo JUri::root().'administrator/components/com_quick2cart/assets/js/geo/jquery.ui.widget.js'?>"></script>
<script src="<?php echo JUri::root().'administrator/components/com_quick2cart/assets/js/geo/jquery.ui.position.js'?>"></script>
<script src="<?php echo JUri::root().'administrator/components/com_quick2cart/assets/js/geo/jquery.ui.autocomplete.js'?>"></script>
<script src="<?php echo JUri::root().'components/com_quick2cart/assets/js/auto.js'?>"></script>
<link rel="stylesheet" href="<?php echo JUri::root().'administrator/components/com_quick2cart/assets/css/geo/geo.css' ?>">
<!-- geo target end here -->

<?php
	$jinput = JFactory::getApplication()->input;
	$document = JFactory::getDocument();
	$cid	= $jinput->get('cid','0');


	if(!$cid)
	{
		$this->coupons=array();
	}

	if($this->coupons)
		$published 	= $this->coupons[0]->published;
	else
		$published 	= 0;

	$this->lists['published'] = JHtml::_('select.booleanlist',  'published', 'class="inputbox"', $published );

$js_key="
	/*
	function checkforalpha(el)
	{
		var i =0 ;
		for(i=0;i<el.value.length;i++){
			if((el.value.charCodeAt(i) > 64 && el.value.charCodeAt(i) < 92) || (el.value.charCodeAt(i) > 96 && el.value.charCodeAt(i) < 123))
			{
				alert('Please Enter Numerics');
				el.value = el.value.substring(0,i); break;
				}
			}
	}
	*/
	function checkfornum(el)
	{
		var i =0 ;
		for(i=0;i<el.value.length;i++){
			if(el.value.charCodeAt(i) > 47 && el.value.charCodeAt(i) < 58) {
				alert('Numerics Not Allowed');
				el.value = el.value.substring(0,i); break;
			}
		}
	}
	";
	$document->addScriptDeclaration($js_key);


?>


<script type="text/javascript">


window.addEvent("domready", function(){
    document.formvalidator.setHandler('name', function (value) {
		if(value<=0){
			alert( "<?php echo JText::_('VAL_GRT')?>" );
			return false;
		}
		else if(value == ' '){
			alert("<?php echo JText::_('NO_BLANK')?>" );
			return false;
		}
		else{
			return true;
		}
	});
});



window.addEvent("domready", function(){
   document.formvalidator.setHandler('verifydate', function(value) {
      regex=/^\d{4}(-\d{2}){2}$/;
      return regex.test(value);
   })

})

var validcode1=0;
	function checkcode()
	{

		var selectedcode=document.getElementById('code').value;
		var cid=<?php if($cid) echo $cid[0];else echo "0"; ?>;

		if(parseInt(cid)==0)
			var url = "index.php?option=com_quick2cart&task=getcode&controller=managecoupon&selectedcode="+selectedcode;
		else
			var url = "index.php?option=com_quick2cart&task=getselectcode&controller=managecoupon&couponid="+cid+"&selectedcode="+selectedcode;

		techjoomla.jQuery.ajax({
		url:url,
		type: 'GET',
		success: function(response) {
				cid=<?php if($cid) echo $cid[0];else echo "0"; ?>;

				if(parseInt(cid)==0)
				{
					if(parseInt(response)!=0)
					{
						alert("<?php echo JText::_('COP_EXIST')?>");
						validcode1=0;
						return 0;
					}
					else
					{
						validcode1=1;
						return 1;
					}
				}
				else
				{
					if(parseInt(response)!=0)
					{
						alert("<?php echo JText::_('COP_EXIST')?>");
						validcode1=0;
						return 0;
					}
					else
					{
						validcode1=1;
						return 1;
					}
				}
			}
		});

	}	//end function check code


	<?php if(JVERSION >= '1.6.0') { ?>
		Joomla.submitbutton = function(action){
	<?php } else {?>
		function submitbutton( action ) {
	<?php } ?>

	var form = document.adminForm;
	if(action=='save')
	{
		var validateflag = document.formvalidator.isValid(document.adminForm);
		if(validateflag)
		{
			techjoomla.jQuery(document).ready(function() {
				//alert(selectedcode);
				//console.log(" NOT COMMING HERE");
				var cid=<?php if($cid) echo $cid[0];else echo "0"; ?>;
				if(parseInt(cid)==0)
				{
					var selectedcode=document.getElementById('code').value;
					//selectedcode=addslashes(selectedcode);
					var url = "index.php?option=com_quick2cart&task=getcode&controller=managecoupon&selectedcode="+selectedcode;
				}
				else
				{
					var selectedcode=document.getElementById('code').value;
					//selectedcode=addslashes(selectedcode);
					var url = "index.php?option=com_quick2cart&task=getselectcode&controller=managecoupon&couponid="+cid+"&selectedcode="+selectedcode;
				}
				<?php if(JVERSION >= '1.6.0') { ?>
						var a = new Request({url:url,
				<?php } else {?>
						new Ajax(url, {
				<?php } ?>
					method: 'get',
					onComplete: function(response) {
						var cid=<?php if($cid[0]) echo $cid[0];else echo "0"; ?>;

						if(parseInt(cid)==0)
						{
							if(parseInt(response)!=0)
							{
								alert("<?php echo JText::_('COP_EXIST')?>");
								validcode1=0;
								return false;
							}
							else
							{
								submitform( action );
								return true;
							}
						}
						else
						{
							if(parseInt(response)!=0)
							{
								alert("<?php echo JText::_('COP_EXIST')?>");
								validcode1=0;
								return false;
							}
							else
							{
								submitform( action );
								return true;
							}
						}
					}
			<?php if(JVERSION >= '1.6.0') { ?>
				}).send();
			<?php } else {?>
				}).request();
			<?php } ?>
			});

		}//if validate flag
		else
		return false;
	}//if action=save
	else
	submitform( action );
	}
	/* this function allow only numberic and specified char (at 0th position)
	// ascii (code 43 for +) (48 for 0 ) (57 for 9)  (45 for  - (negative sign))
		(code 46 for .)
		@param el :: html element
		@param allowed_ascii::ascii code that shold allow

	*/
function checkforalpha(el, allowed_ascii,entered_numericsMsg )
	{
		// by defau
		allowed_ascii= (typeof allowed_ascii === "undefined") ? "" : allowed_ascii;
		var i =0 ;
		for(i=0;i<el.value.length;i++){
		  if((el.value.charCodeAt(i) < 48 || el.value.charCodeAt(i) >= 58) || (el.value.charCodeAt(i) == 45 ))
		  {
			if(allowed_ascii ==el.value.charCodeAt(i))   // && i==0)  // + allowing for phone no at first char
			{
				var temp=1;
			}
			else
			{
					alert(entered_numericsMsg);
					el.value = el.value.substring(0,i);
					return false;
			}


		  }
		}
		return true;
	}
	//sanjivani
jQuery(document).ready(function() {
	jQuery("#store_ID").val(jQuery("#store_ID option:selected").val());
	jQuery("select").change(function()   {
		var no = jQuery("#store_ID option:selected").val();
		jQuery("#current_store_id").val(no);
	});
});

</script>
<div class="<?php echo Q2C_WRAPPER_CLASS;?> coupon-form">
<form action="index.php" name="adminForm" id="adminForm" class="form-validate form-horizontal" method="post" >
	<input type="hidden" name="check" value="post"/>

	<legend><?php echo JText::_( "COP_INFO"); ?></legend>
	<div>
<div class="control-group">
	<label for="coupon_name" class="control-label"><?php echo JHtml::tooltip(JText::_('COUPAN_NAME_TOOLTIP'), JText::_('COUPAN_NAME'), '', JText::_('COUPAN_NAME'));?></label>
	<div class="controls">
		<input type="text" name="coupon_name" id="coupon_name" class="inputbox required validate-name"   size="20" value="<?php if($this->coupons){  echo stripslashes($this->coupons[0]->name); } ?>" autocomplete="off" />
	</div>
</div>
<div class="control-group">
	<label for="code" class="control-label"><?php echo JHtml::tooltip(JText::_('COUPAN_CODE_TOOLTIP'), JText::_('COUPAN_CODE'), '', JText::_('COUPAN_CODE'));?></label>
	<div class="controls"><input type="text" name="code" id="code" class="inputbox required validate-name"    size="20" value="<?php if($this->coupons){ echo $this->escape( stripslashes( $this->coupons[0]->code ) ); } ?>" 	 autocomplete="off" />
	</div>
</div>
<div class="control-group">
	<label for="" class="control-label"><?php echo JHtml::tooltip(JText::_('COUPAN_ENABLED_TOOLTIP'), JText::_('COUPAN_ENABLED'), '', JText::_('COUPAN_ENABLED'));?></label>

<?php if(JVERSION < '3.0'){	?>
<div class="controls">
	<?php echo $this->lists['published']; ?>
</div>
<?php }else{
	echo $this->lists['published'];
 }
?>
</div>
<!-- SELECT STORE -->
<?php
//made by sanjivani
$comquick2cartHelper=new comquick2cartHelper;
$this->store_role_list=$store_role_list=$comquick2cartHelper->getAllStoreIds();
//JLoader::import('managecoupon', JPATH_SITE . DS . 'components' . DS . 'com_quick2cart' . DS . 'models');
if($this->coupons)
{
	$model = new quick2cartModelManagecoupon();
	$this->coupons = $model->Editlist($this->coupons[0]->id);
}
$params = JComponentHelper::getParams('com_quick2cart');
$multivendor_enable=$params->get('multivendor');
//sanjivani end
//				$options[] = JHtml::_('select.option', "", "Select Country");
if($multivendor_enable == '1')
{
	?>
	<div class="control-group">
		<label for="qtc_store" class="control-label"><?php echo JHtml::tooltip(JText::_('QTC_PROD_SELECT_STORE_DES'), JText::_('QTC_PROD_SELECT_STORE'), '', JText::_('QTC_PROD_SELECT_STORE'));?></label>
		<div class="controls">
			<?php
				$default=!empty($this->coupons[0]->store_id)?$this->coupons[0]->store_id:(!empty($store_role_list[0]['id'])?$store_role_list[0]['id']:'');
				$options = array();
				$options[] = JHtml::_('select.option', '0',JText::_('COUPON_STORE_SELECT'));//submitAction('deletecoupon');
				foreach($this->store_role_list as $key=>$value)
				{
					$options[] = JHtml::_('select.option', $value["id"],$value['title']);//submitAction('deletecoupon');
				}
				echo $this->dropdown = JHtml::_('select.genericlist',$options,'current_store','class=" qtc_putmargintop10px required" size="0"   ','value','text',$default,'store_ID');
			?>
		</div>
	</div>
<?php
}
// sj end
?>

<div class="control-group">
	<label for="value" class="control-label"><?php echo JHtml::tooltip(JText::_('COUPAN_VALUE_TOOLTIP'), JText::_('COUPAN_VALUE'), '', JText::_('COUPAN_VALUE'));?></label>
	<div class="controls">
		<input  class="inputbox required validate-name" type="text" name="value" id="value" Onkeyup= "checkforalpha(this,46,<?php echo $entered_numerics; ?>);" size="20" value="<?php if($this->coupons){ echo $this->coupons[0]->value; } ?>" autocomplete="off" />
	</div>
</div>
<div class="control-group">
	<label for="val_type" class="control-label"><?php echo JHtml::tooltip(JText::_('COUPAN_VALUE_TYPE_TOOLTIP'), JText::_('COUPAN_VALUE_TYPE'), '', JText::_('COUPAN_VALUE_TYPE'));?></label>

		<?php
		if($this->coupons)
		$val_type 	= $this->coupons[0]->val_type;
		else
		$val_type 	= 0;
		$val_type1[] = JHtml::_('select.option', '0', JText::_("COP_FLAT"));
		$val_type1[] = JHtml::_('select.option', '1', JText::_("COP_PER")); // first parameter is value, second is text
		$lists['val_type'] = JHtml::_('select.radiolist', $val_type1, 'val_type', 'class="inputbox" ', 'value', 'text', $val_type, 'val_type');

		 ?>
	<?php if(JVERSION < '3.0'){	?>
<div class="controls">
			<?php echo $lists['val_type'];  ?>
</div>
<?php }else{
	echo $lists['val_type'];
 }
if($multivendor_enable == '1')   // sj change
{				//// sj change
 ?>
</div>
<!-- -sj change -->
<div class="control-group">
	<label for="selections.item_id" class="control-label qtc_product_cop_txtbox_lable"><?php echo JHtml::tooltip(JText::_('COUPAN_ITEMID_TOOLTIP'), JText::_('COUPAN_ITEMID'), '', "* ".JText::_('COUPAN_ITEMID'));?></label>
	<div class="controls">
		<ul class='selections' id='selections.item_id'>
			<input type="text" id="item_id" class="auto_fields inputbox validate-item_id_hidden qtc_product_cop_txtbox" size="20" value="<?php echo ($this->coupons) ? $this->coupons[0]->item_id : JText::_('ITEMID_START_TYP_MSG'); ?>" autocomplete="off" />
			<input type="hidden" class="auto_fields_hidden" name="item_id" id="item_id_hidden" value="" autocomplete='off' />
		</ul>
		<input type="hidden" class="" id="item_id_hiddenname" value="<?php echo ($this->coupons) ? $this->coupons[0]->item_id_name:'' ;?>" autocomplete='off' />
	<input type="hidden" name="store_ID" id="store_ID" value="" />
	</div>
</div>
<?php }?>
</div>
<div class="control-group">
	<label for="max_use" class="control-label"><?php echo JHtml::tooltip(JText::_('COUPAN_MAXUSES_TOOLTIP'), JText::_('COUPAN_MAXUSES'), '', JText::_('COUPAN_MAXUSES'));?></label>
	<div class="controls">
		<input type="text" name="max_use" id="max_use" class="inputbox" Onkeyup= "checkforalpha(this,'',<?php echo $entered_numerics; ?>);" size="20" value="<?php if($this->coupons){ echo $this->coupons[0]->max_use; } ?>" autocomplete="off" />
	</div>
</div>
<div class="control-group">
	<label for="max_per_user" class="control-label"><?php echo JHtml::tooltip(JText::_('COUPAN_MAXUSES_PERUSER_TOOLTIP'), JText::_('COUPAN_MAXUSES_PERUSER'), '', JText::_('COUPAN_MAXUSES_PERUSER'));?></label>
	<div class="controls">
		<input type="text" name="max_per_user" id="max_per_user" class="inputbox" Onkeyup= "checkforalpha(this,'',<?php echo $entered_numerics; ?>);" size="20" value="<?php if($this->coupons){  echo $this->coupons[0]->max_per_user; } ?>" autocomplete="off" />
	</div>
</div>
<div class="control-group">
	<label for="from_date" class="control-label"><?php echo JHtml::tooltip(JText::_('VALID_FROM_TOOLTIP'), JText::_('VALID_FROM'), '', JText::_('VALID_FROM'));?></label>
	<div class="controls">
				<?php
				if($this->coupons)
				{
					if($this->coupons[0]->from_date != '0000-00-00 00:00:00')
						$date_from=date("Y-m-d",strtotime($this->coupons[0]->from_date));
					else
						$date_from='';
				}
				else
					$date_from='';

				 echo JHtml::_("calendar", "$date_from", "from_date", "from_date", "%Y-%m-%d"); ?>
		</div>
</div>
<div class="control-group">
	<label for="exp_date" class="control-label"><?php echo JHtml::tooltip(JText::_('EXPIRES_ON_TOOLTIP'), JText::_('EXPIRES_ON'), '', JText::_('EXPIRES_ON'));?></label>
	<div class="controls">
				<?php
				if($this->coupons)
				{
					if($this->coupons[0]->exp_date != '0000-00-00 00:00:00')
						$date_exp=trim(date("Y-m-d",strtotime($this->coupons[0]->exp_date)));
					else
						$date_exp='';
				}
				else
					$date_exp='';
				  echo JHtml::_("calendar",  "$date_exp", "exp_date", "exp_date", "%Y-%m-%d");

				?>
		</div>
</div>
<div class="control-group">
	<label for="description" class="control-label"><?php echo JHtml::tooltip(JText::_('DESCRIPTION_TOOLTIP'), JText::_('DESCRIPTION'), '', JText::_('DESCRIPTION'));?></label>
	<div class="controls">
		<textarea   size="28" rows="3" name="description" id="description" class="inputbox" ><?php if($this->coupons){  echo trim($this->coupons[0]->description); } ?></textarea>
	</div>
</div>
<div class="control-group">
	<label for="params" class="control-label"><?php echo JHtml::tooltip(JText::_('PARAMETERS_TOOLTIP'), JText::_('PARAMETERS'), '', JText::_('PARAMETERS'));?></label>
	<div class="controls">
		<textarea  size="28" rows="3" name="params" id="params" class="inputbox" ><?php if($this->coupons){  echo trim($this->coupons[0]->params); } ?></textarea>
	</div>
</div>

		</div>

<!--sj change -->
<input type="hidden" name="coupon_id" id="coupon_id" value="<?php if($this->coupons){ echo $this->coupons[0]->id; } ?>" />
<input type="hidden" name="id1" id="id1" value="<?php if($this->coupons){ echo $this->coupons[0]->id; } ?>" />
<label for="id1" ></label>
	<input type="hidden" name="option" value="com_quick2cart" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="managecoupon" />
	<input type="hidden" name="controller" value="managecoupon" />
		<?php echo JHtml::_('form.token'); ?>

</form>
</div>
