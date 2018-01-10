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
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.modal');
?>
<script type="text/javascript">
	function qtcsubmitAction(action)
	{
		var form = document.taxrateForm;

		switch(action)
		{
			case 'save': form.task.value='taxprofileform.save';
			break

			case 'saveAndClose':
			form.task.value='taxprofileform.saveAndClose';
			break
		}
		// Submit form
		form.submit();
		return;
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
			url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&view=taxprofileform&task=taxprofileform.deleteProfileRule&tmpl=component",
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
			techjoomla.jQuery('#qtcErrorContentDiv').html("<?php echo JText::_('COM_QUICK2CART_S_INVALID_SELECTION'); ?>");
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
					url :"<?php echo JUri::base();?>index.php?option=com_quick2cart&view=taxprofileform&task=taxprofileform.addTaxRule&tmpl=component",
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
							var editbtn = '<input type="button" value="<?php echo JText::_('COM_QUICK2CART_TAXPROFILEERULE_EDIT'); ?>" class="btn btn-primary">';
							var editHref = 'index.php?option=com_quick2cart&view=taxprofileform&layout=setrule&id='+taxrule_id+'&tmpl=component';
							var editLink = '<a rel="{handler:\'iframe\',size:{x: window.innerWidth-450, y: window.innerHeight-250}}" href="'+editHref+'" class="modal qtc_modal">'+editbtn+'</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

							var delLink = '<input onclick="qtcDeleteProfileRule('+
										taxrule_id+',this);" class="btn btn-danger" type="button" value="<?php echo JText::_('COM_QUICK2CART_PROFILERULE_DELETE'); ?>">';
							//alert(links);
							var result='<tr><td id="qtc_taxrate_'+taxrule_id+'">'+taxrate+'</td><td id="qtc_address_'+taxrule_id+'">'+address+'</td><td>' + editLink + delLink + '</td></tr>';
							techjoomla.jQuery('#tableBody').append(result);

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
<div class=" <?php echo Q2C_WRAPPER_CLASS; ?>">
	<?php
	$helperobj=new comquick2cartHelper;
	$active = 'zones';
	$order_currency=$helperobj->getCurrencySession();
	$view=$helperobj->getViewpath('vendor','toolbar');
	ob_start();
		include $view;
		$html = ob_get_contents();
	ob_end_clean();
	echo $html;
	?>
<form id="taxprofileform" name="taxrateForm"   method="post" class="form-validate" enctype="multipart/form-data">
	<div class="row-fluid">
		<div class="form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_QUICK2CART_TAXPROFILE'); ?></legend>

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
						<div class="controls">
							<?php echo $this->form->getInput('store_id'); ?>
							<div class="text-warning">
								<div>&nbsp;</div>
								<p><?php echo JText::_('COM_QUICK2CART_TAXPROFILE_CAN_NOT_CAHGNE_STORE_MSG'); ?></p>
							</div>
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

					<div class="text-info">
						<p><?php echo JText::_('COM_QUICK2CART_TAXPROFILE_ADD_TAXRATE_MSG'); ?></p>
					</div>

					<!-- Map the tax rule aginst tax profile -->
					<br/>
					<div class="row-fluid">
						<div class="span4">
							<?php echo $this->taxrate; ?>
						</div>
						<div class="span4">
							<span title="<?php echo JText::_("COM_QUICK2CART_TAXPROFILE_ADD_TAXRATE_MSG");?>">
								<?php echo $this->address; ?>
							</span>
						</div>
						<div class="span4">
							<input type="button" id="CreateTaxRule"
								value="<?php echo JText::_('COM_QUICK2CART_TAXPROFILE_ADD_TAXRATE'); ?>"
								class="btn btn-success" onClick="qtc_addTaxRule()" />
						</div>
					</div>

					<!-- For Error Display-->
					<div class="row-fluid">
						<div class="error alert alert-error qtcError" style="display: none;">
							<?php echo JText::_('COM_QUICK2CART_ZONE_ERROR'); ?>
							<i class="icon-cancel pull-right" style="align: right;"
								onclick="techjoomla.jQuery(this).parent().fadeOut();"> </i> <br />
							<hr />
							<div id="qtcErrorContentDiv"></div>
						</div>
					</div>


					<!-- Show the taxprofile rules -->
					<table class="  table table-striped table-bordered">
						<thead>
							<tr>
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
								<td id="qtc_taxrate_<?php echo $trule->taxrule_id; ?>" ><?php echo $trule->name; ?>&nbsp;(<?php echo floatval($trule->percentage); ?>%)
								</td>
								<td id="qtc_address_<?php echo $trule->taxrule_id; ?>"><?php
									echo ucfirst($trule->address);
								?>
								</td>
								<td>
									<a rel="{handler:'iframe',size:{x: window.innerWidth-450, y: window.innerHeight-150}}"
									href="index.php?option=com_quick2cart&view=taxprofileform&layout=setrule&id=<?php echo $trule->taxrule_id;?>&tmpl=component" class="modal qtc_modal">
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

			<!-- Action part -->
			<div class="form-actions ">

					<button type="button" class="btn btn-success validate" title="<?php echo JText::_('COM_QUICK2CART_SAVE_ITEM'); ?>" onclick="qtcsubmitAction('save');">
						<?php echo JText::_('COM_QUICK2CART_SAVE_ITEM'); ?>
					</button>

					<?php if($this->item->get('id')):?>
						<button type="button" class="btn  validate" title="<?php echo JText::_('COM_QUICK2CART_COMMON_SAVE_AND_CLOSE'); ?>" onclick="qtcsubmitAction('saveAndClose');">
							<?php echo JText::_('COM_QUICK2CART_COMMON_SAVE_AND_CLOSE'); ?>
						</button>
					<?php endif;?>

					<a href="<?php echo JRoute::_('index.php?option=com_quick2cart&task=taxprofileform.cancel&id='.$this->item->id); ?>" class="btn " title="<?php echo JText::_('COM_QUICK2CART_CANCEL_ITEM'); ?>">
						<?php echo JText::_('COM_QUICK2CART_CANCEL_ITEM'); ?>
					</a>

				</div>

		</div>
	</div>

		<input type="hidden" name="option" value="com_quick2cart" />
		<input type="hidden" name="task" value="taxprofileform.save" />
		<?php echo JHtml::_('form.token'); ?>

		<input type="hidden" name="id" id="id" value="<?php echo $this->item->get('id')?>" />
		<input type="hidden" name="jform[id]" id="jform_taxprofile_id" value="<?php echo $this->item->id; ?>" />


</form>
</div> <!--com_quick2cart_wrapper -->
