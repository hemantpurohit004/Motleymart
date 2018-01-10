<?php
/**
 * @version     2.2
 * @package     com_quick2cart
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <contact@techjoomla.com> - http://techjoomla.com
 */
// no direct access
defined('_JEXEC') or die;
 JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.modal');


?>
<script type="text/javascript">


	Joomla.submitbutton = function(task)
	{
		if (task == 'taxprofile.cancel')
		{
			Joomla.submitform(task, document.getElementById('taxprofile-form'));
		}
		else
		{
			if (task != 'taxprofile.cancel' && document.formvalidator.isValid(document.id('taxprofile-form')))
			{
				Joomla.submitform(task, document.getElementById('taxprofile-form'));
			}
			else
			{
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}

	function qtcDeleteProfileRule(ruleId,delBtn)
	{
		var data = {
			jform : {
				taxrule_id : ruleId,
			}
		};

		techjoomla.jQuery.ajax({
			type : "POST",
			url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&view=taxprofile&task=taxprofile.deleteProfileRule",
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

    function qtc_addTaxRule()
    {
		var qtc_taxrate_id = document.id('jformtaxrate_id').value;
		var qtc_address = document.id('jformaddress').value;

		if(qtc_taxrate_id == '' || qtc_address == '')
		{
			techjoomla.jQuery('#qtcErrorContentDiv').html("<?php echo JText::_('COM_QUICK2CART_TAXPROFILE_INVALID_SELECTION'); ?>");
			techjoomla.jQuery('.error').fadeIn();
			return false;
		}

		var data = {
			jform : {
				taxprofile_id : document.id('jform_taxprofile_id').value,
				taxrate_id : qtc_taxrate_id,
				address : qtc_address,
			}
		};

		var taxrate = techjoomla.jQuery("#jformtaxrate_id").children("option").filter(":selected").text() ;
		var address = techjoomla.jQuery("#jformaddress").children("option").filter(":selected").text() ;
		techjoomla.jQuery.ajax({
					type : "POST",
					url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&view=taxprofile&task=taxprofile.addTaxRule",
					data : data,
					dataType: "json",
					success : function(response)
					{
						if (response.error != 1)
						{
							// Remove Error dive content
							techjoomla.jQuery('#qtcErrorContentDiv').html('');
							techjoomla.jQuery('.qtcError').fadeOut();


							var taxrule_id= response.taxrule_id;
							var q="'";

							var editLink = '<a rel="{handler:\'iframe\',size:{x: window.innerWidth-450, y: window.innerHeight-150}}"'+' href="index.php?option=com_quick2cart&view=taxprofile&layout=setrule&id='+taxrule_id+'&tmpl=component" class="modal qtc_modal"> <input type="button" value="<?php echo JText::_('COM_QUICK2CART_ZONERULE_EDIT'); ?>" class="btn btn-primary"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
							var delLink = '<input onclick="qtcDeleteProfileRule('+
										taxrule_id+',this);" class="btn btn-danger" type="button" value="<?php echo JText::_('COM_QUICK2CART_ZONERULE_DELETE'); ?>">';
							//alert(links);
							var result='<tr><td></td><td id="qtc_taxrate_'+taxrule_id+'">'+taxrate+'</td><td id="qtc_address_'+taxrule_id+'">'+address+'</td><td>' + editLink + delLink + '</td></tr>';
							techjoomla.jQuery('#tableBody').append(result);

							// intialize squeeze box again for edit button to work
							SqueezeBox.initialize({});
							SqueezeBox.assign($$('a.modal'),
							{
								parse: 'rel'
							});

						} else {
							techjoomla.jQuery('#qtcErrorContentDiv').html(response.errorMessage);
							techjoomla.jQuery('.error').fadeIn();
						}
					}
				});

		return false;
	}

</script>

<div class="<?php echo Q2C_WRAPPER_CLASS; ?>">
<form action="<?php echo JRoute::_('index.php?option=com_quick2cart&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="taxprofile-form" class="form-validate">

	<div class="form-horizontal">
        <div class="row-fluid">
            <div class="span10 form-horizontal">
                <fieldset class="adminform">
					<legend><?php echo JText::_('COM_QUICK2CART_TAXPROFILE'); ?></legend>
					<input type="hidden" name="jform[id]" id="jform_taxprofile_id" value="<?php echo $this->item->id; ?>" />
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
					</div>
					<?php
					if (empty($this->item->id))
					{
						?>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('store_id'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('store_id'); ?>
								<span class="help-block">
									<i class="icon-hand-right"></i>
									<?php echo JText::_('COM_QUICK2CART_TAXPROFILE_CAN_NOT_CAHGNE_STORE_MSG'); ?></span>
							</div>
						</div>
						<?php
					}
					else
					{
						?>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('store_id'); ?></div>
							<div class="controls">
								<?php echo ucfirst($this->storeDetails['title']); ?>
								<span style="display:none;">
								<?php echo $this->form->getInput('store_id'); ?>
								</span>
							</div>
						</div>
						<?php
					}?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>
					<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
					<div class="alert alert-info">
						<?php echo JText::_('COM_QUICK2CART_TAXPROFILES_HELP_TEXT');?>
					</div>
				</fieldset>

				<fieldset>
					<?php
					if (!empty($this->item->id))
					{
					?>
						<legend>
							<?php echo JText::_('COM_QUICK2CART_TAXPROFILE_ADD_TAXRATES'); ?>
							<small><?php echo JText::_('COM_QUICK2CART_TAXPROFILE_TAXRATE_MAP_HELP'); ?></small>
						</legend>

						<!-- Map the tax rule aginst tax profile -->
						<table>
							<tr>
								<td><?php echo $this->taxrate; ?></td>
								<td>
									<span title="<?php echo JText::_("COM_QUICK2CART_TAXPROFILE_ADD_TAXRATE_MSG");?>"><?php echo $this->address; ?>
									</span>
								</td>
								<td valign="top"><input type="button" id="CreateTaxRule"
									value="<?php echo JText::_('COM_QUICK2CART_TAXPROFILE_ADD_TAXRATE'); ?>"
									class="btn btn-success" onClick="qtc_addTaxRule()" />
								</td>
							</tr>
							<tr >
								<td colspan="3">

								<p class="text-info"><?php echo JText::_("COM_QUICK2CART_TAXPROFILE_ADD_TAXRATE_MSG");?></p>
								</td>
							</tr>

							<tfoot>
								<!-- For Error Display-->
								<tr>
									<td id="" colspan="3">
										<div class="error alert alert-danger qtcError" style="display: none;">
											<?php echo JText::_('COM_QUICK2CART_ZONE_ERROR'); ?>
											<i class="icon-cancel pull-right" style="align: right;"
												onclick="techjoomla.jQuery(this).parent().fadeOut();"> </i> <br />
											<hr />
											<div id="qtcErrorContentDiv"></div>
										</div>
									</td>
								</tr>

							</tfoot>

						</table>

						<!-- Show the taxprofile rules -->
						<table class="adminlist table table-striped table-bordered">
							<thead>
								<tr>
									<th><?php echo JText::_('COM_QUICK2CART_TAXPROFILE_NUM'); ?> </th>
									<th><?php echo JText::_('COM_QUICK2CART_TAXPROFILE_TAXRATE'); ?> </th>
									<th><?php echo JText::_('COM_QUICK2CART_TAXPROFILE_ADDRESS'); ?></th>
									<th><?php echo JText::_('COM_QUICK2CART_TAXPROFILE_ACTION'); ?></th>
								</tr>
							</thead>
							<tbody id="tableBody">
								<?php
							$i=1;

							foreach($this->taxrules as $trule)
							{ ?>
								<tr>
									<td><?php echo $i++; ?>
									</td>
									<td id="qtc_taxrate_<?php echo $trule->taxrule_id; ?>" ><?php echo $trule->name; ?>&nbsp;(<?php echo floatval($trule->percentage); ?>%)
									<!-- [ <?php// echo JText::_("COM_QUICK2CART_TAXPROFILES_STORE_NAME") . ' : ' . $trule->storeName ; ?>  : ] -->
									</td>
									<td id="qtc_address_<?php echo $trule->taxrule_id; ?>"><?php
										echo ucfirst($trule->address);
									?>
									</td>
									<td>
										<a rel="{handler:'iframe',size:{x: window.innerWidth-450, y: window.innerHeight-150}}"
										href="index.php?option=com_quick2cart&view=taxprofile&layout=setrule&id=<?php echo $trule->taxrule_id;?>&tmpl=component" class="modal qtc_modal">
											<input type="button" value="<?php echo JText::_('COM_QUICK2CART_TAXPROFILEERULE_EDIT'); ?>" 	class=" btn btn-primary">
										</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

										<input onclick="qtcDeleteProfileRule(<?php echo $trule->taxrule_id;?>,this);" class="btn btn-danger" type="button" value="<?php echo JText::_('COM_QUICK2CART_PROFILERULE_DELETE'); ?>">
									</td>
								</tr>

								<?php
							}//end for?>
							</tbody>

						</table>



					<?php
					}
					?>
			</fieldset>


            </div>
        </div>

        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>

    </div>
</form>
</div> <!--qyc_admin_taxprofile -->
