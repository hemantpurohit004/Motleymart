<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// no direct access
defined('_JEXEC') or die;

$lang = JFactory::getLanguage();
$lang->load('com_quick2cart', JPATH_SITE);
$comquick2cartHelper = new comquick2cartHelper;
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task=='shipprofile.add')
		{
			Joomla.submitform(task);

			return true;
		}
		elseif (task=='shipprofile.edit')
		{
			if (document.adminForm.boxchecked.value===0)
			{
				alert('<?php echo JText::_("COM_QUICK2CART_NO_SELECTION_MSG");?>');

				return;
			}
			elseif (document.adminForm.boxchecked.value > 1)
			{
				alert('<?php echo JText::_("COM_QUICK2CART_MAKE_ONE_SEL");?>');

				return;
			}

			Joomla.submitform(task);
		}
		else
		{
			if (document.adminForm.boxchecked.value==0)
			{
				alert('<?php echo JText::_("COM_QUICK2CART_MESSAGE_SELECT_ITEMS");?>');
				return false;
			}
			switch(task)
			{
				case 'shipprofile.publish':
					Joomla.submitform(task);
				break

				case 'shipprofile.unpublish':
					Joomla.submitform(task);
				break

				case 'shipprofile.delete':
					if (confirm("<?php echo JText::_('COM_QUICK2CART_DELETE_CONFIRM_SHIPPROFILE'); ?>"))
					{
						Joomla.submitform(task);
					}
					else
					{
						return false;
					}
				break
			}
		}
	}
</script>
<script type="text/javascript">

	function deleteShipProfileMethod(methodId,delBtn)
	{
		var data = {
			jform : {
				shipMethodId : methodId,
			}
		};

		techjoomla.jQuery.ajax({
			type : "POST",
			url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&view=<?php echo $actionViewName; ?> &task=<?php echo $actionControllerName; ?>.deleteShipProfileMethod",
			data : data,
			success : function(response)
			{
				if (response.error!=1)
				{
					//techjoomla.jQuery(delete_btn).parent().parent().fadeOut();
					techjoomla.jQuery(delBtn).closest('tr').remove();
				}
				else
				{
					techjoomla.jQuery('#qtczoneruleError').html(response.errorMessage);
					techjoomla.jQuery('.error').fadeIn();
				}
			}
		});
	}

	function qtcLoadPlgMethods()
	{
		var qtcShipPluginId = document.id('qtcShipPlugin').value;

		if(qtcShipPlugin == '' )
		{
			techjoomla.jQuery('#qtcErrorContentDiv').html("<?php echo JText::_('COM_QUICK2CART_S_SHIPPLUGIN_INVALID_SELECTION'); ?>");
			techjoomla.jQuery('.qtcError').fadeIn();
			return false;
		}
		var data = {
			qtcShipPluginId : qtcShipPluginId,
			store_id : <?php echo $shipProfileStoreId; ?>,
		};

		techjoomla.jQuery.ajax({
				type : "POST",
				url :"<?php echo JUri::root();?>index.php?option=com_quick2cart&view=<?php echo $actionViewName; ?>&task=<?php echo $actionControllerName; ?>.qtcLoadPlgMethods",
				data : data,
				dataType: "json",
				beforeSend: function()
				{
					// REMOVE ALL STATE OPTIONS
					techjoomla.jQuery('#qtc_shipMethod').find('option').remove().end();
					techjoomla.jQuery('.com_quick2cart_ajax_loading').show();
				},
				complete: function()
				{
					techjoomla.jQuery('.com_quick2cart_ajax_loading').hide();
				},
				success : function(response)
				{
					if (response.error != 1)
					{
						techjoomla.jQuery('#qtcShipMethContainer').html(response.shipMethList);
					}
					else
					{
						techjoomla.jQuery('#qtcErrorContentDiv').html(response.errorMessage);
						techjoomla.jQuery('.error').fadeIn();
					}

				}
			});

	}
    function qtc_addShipMethod()
    {
		var qtcShipPluginId = document.id('qtcShipPlugin').value;
		var qtc_shipMethodId = document.id('qtc_shipMethod').value;

		if(qtcShipPluginId == '' || qtc_shipMethodId == '')
		{
			techjoomla.jQuery('#qtcErrorContentDiv').html("<?php echo JText::_('COM_QUICK2CART_S_SHIPPLUGIN_INVALID_SELECTION'); ?>");
			techjoomla.jQuery('.qtcError').fadeIn();
			return false;
		}

		var data = {
			jform : {
				shipprofile_id : document.id('jform_id').value,
				qtcShipPluginId : qtcShipPluginId,
				methodId : qtc_shipMethodId,
			}
		};

		var qtc_selectedShipPlugin = techjoomla.jQuery("#qtcShipPlugin").children("option").filter(":selected").text() ;
		var qtc_selectedShipMethod = techjoomla.jQuery("#qtc_shipMethod").children("option").filter(":selected").text() ;

		techjoomla.jQuery.ajax({
					type : "POST",
					url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&view=<?php echo $actionViewName; ?>&task=<?php echo $actionControllerName; ?>.addShipMethod",
					data : data,
					dataType: "json",
					beforeSend: function()
					{
						techjoomla.jQuery('.qtcError').fadeOut();
					},
					complete: function()
					{
					},
					success : function(response)
					{

						if (response.error == 0)
						{
							// Remove Error dive content
							techjoomla.jQuery('#qtcErrorContentDiv').html('');
							techjoomla.jQuery('.qtcError').fadeOut();


							var shipProfileMethodId= response.shipProfileMethodId;
							var q="'";
							var editbtn = '<input type="button" value="<?php echo JText::_('COM_QUICK2CART_SHIPPROFIL_METH_EDIT'); ?>" class="btn btn-primary">';
							var editHref = 'index.php?option=com_quick2cart&view=<?php echo $actionViewName; ?>&layout=setrule&id='+shipProfileMethodId+'&tmpl=component';
							var editLink = '<a rel="{handler:\'iframe\',size:{x: window.innerWidth-450, y: window.innerHeight-250}}" href="'+editHref+'" class="modal qtc_modal">'+editbtn+'</a> &nbsp;';

							var delLink = '<input onclick="deleteShipProfileMethod('+
										shipProfileMethodId+',this);" class="btn btn-danger" type="button" value="<?php echo JText::_('COM_QUICK2CART_PROFILERULE_DELETE'); ?>">';
							//alert(links);


							var result='<tr><td id="qtcPlugnameTd_'+shipProfileMethodId+'">'+qtc_selectedShipPlugin+'</td><td id="qtcShipMethTd_'+shipProfileMethodId+'">'+qtc_selectedShipMethod+'</td><td>' + editLink + delLink + '</td></tr>';
							techjoomla.jQuery('#qtcShipMethTableBody').append(result);

							// intialize squeeze box again for edit button to work
							SqueezeBox.initialize({});
							SqueezeBox.assign($$('a.modal'),
							{
								parse: 'rel'
							});
						}
						else
						{
							techjoomla.jQuery('#qtcErrorContentDiv').html(response.errorMessage);
							techjoomla.jQuery('.error').fadeIn();
						}

					}
				});

		return false;
	}


</script>


<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">

	<?php
	if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">

	<?php
	else : ?>
	<div id="j-main-container">
		<?php
	endif; ?>
		<?php
		// Check for view override

		$actionViewName = 'shipprofile';
		$actionControllerName = 'shipprofile';
		$formName = 'adminForm';
		$att_list_path = $comquick2cartHelper->getViewpath('shipprofiles', 'shipprofilesdata', "ADMINISTRATOR", "SITE");
		ob_start();
		include($att_list_path);
		$item_options = ob_get_contents();
		ob_end_clean();
		echo $item_options;
		?>


	</div>
</div>
