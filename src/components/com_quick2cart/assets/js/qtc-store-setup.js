/* Add Js function related views: Add product, zone, tax rate, tax profile, shipping, Stores,Global Attributes,Global Attribute Set,Category Attribute Set Mapping
 * */
function qtc_ispositive(ele)
{
	var val=ele.value;
	if (val==0 || val < 0)
	{
		ele.value='';
		alert(Joomla.JText._('QTC_ENTER_POSITIVE_ORDER'));
		return false;
	}
}

/**
 * Add product view: This function is called when store owener click on attribute set
 *
 * @param   string   eleId            called element id
 * @param   integer  attrContainerNo  attribute container number like 1,2 etc
 * @param   object   qtcJsData  This containd all variable that should come from php
 *
 * @return  Object list.
 *
 * @since	2.5
	 */
function qtcLoadArribute(eleId, attrContainerNo, qtcJsData)
{
	var globalAttId = techjoomla.jQuery('#' + eleId).val();
	var globalAttName = techjoomla.jQuery("#" +eleId + " option:selected").text();

	/* closest() selects the first element that matches the selector, up from the DOM tree.*/
	var attributeContainerId = techjoomla.jQuery("#" + eleId).closest(".qtc_container").attr('id');

	var attrData = {
			globalAttId : globalAttId
	};

	if(globalAttId != 0)
	{
		techjoomla.jQuery.ajax({
			url : qtcJsData.qtcLoadArributefunction_URL,
			type : 'POST',
			dataType : 'json',
			data:attrData,
			beforeSend: function()
			{
				/* Show loading */
				techjoomla.jQuery('.attri_ajax_loading_' + attrContainerNo).show();
			},
			success : function(data)
			{
				/* Hide */
				techjoomla.jQuery('.attri_ajax_loading_' + attrContainerNo).hide();
				if (data.error == 0)
				{
					if (data.hasOwnProperty("goption"))
					{
						/* Change change attibute name Global attribtue id */
						techjoomla.jQuery("#" + attributeContainerId).find('.qtc_attrib').attr("value", globalAttName);
						techjoomla.jQuery("#" + attributeContainerId).find('.global_atrri_idClass').attr("value", globalAttId);


						/* For field type: keep select type and don't allow to change*/
						// Trigger onchange funtin onchange="qtc_fieldTypeChange(this,'qtc_container1')" which iss on  field type select box
						/* Remove all option except first */

						var currentTr = 0;
						techjoomla.jQuery("#" + attributeContainerId).find('tr.clonedInput').each(function()
						{
							if (currentTr != 0)
							{
								techjoomla.jQuery(this).remove();
							}

							currentTr++;
						});
//@TODO use language constant for title=\"Add Options.\"
						/* Replace last children delete button to plus */
						techjoomla.jQuery('#' + attributeContainerId + ' tr.clonedInput:last' ).children().last().replaceWith('<button type=\"button\" class=\"btnAdd btn btn-mini btn-primary\" id=\"' + attributeContainerId + '\"  onclick=\"addopt(this.id)\" ><i class=\" icon-plus-2 icon22-white22 \"></i></button> ');

						var gAttroptions = data['goption'];

						/* Add empty gAttroptions. i=1 because 1 option already added */
						for (i = 1; i < gAttroptions.length; i++)
						{
							addopt(attributeContainerId);
						}

						/*Fill data*/
						var optionIndex = 0;
						var option_order = 1;
						techjoomla.jQuery("#" + attributeContainerId).find('tr.clonedInput').each(function()
						{
							/* Add option's global id */
							techjoomla.jQuery(this).find(".qtcGlobalOptionId").val(gAttroptions[optionIndex]["id"]);

							/* Add option name */
							techjoomla.jQuery(this).find(".qtcOptionNameClass").val(gAttroptions[optionIndex]["option_name"]);

							/* PRefix */
							techjoomla.jQuery(this).find(".qtc-attribute-Option-order").val(option_order);


							/*Set All price values to 0*/
							techjoomla.jQuery(this).find('.qtc_currencey_textbox .currtext').val("0.00");

							optionIndex++;
							option_order++;
						});

						/* Hide remove / add option */
					//	techjoomla.jQuery("#" + attributeContainerId + " .qtcRemoveOption ").hide();
						techjoomla.jQuery("#" + attributeContainerId + " .btnAdd ").hide();
						techjoomla.jQuery('#'+attributeContainerId+'.btnAdd').replaceWith('<button type=\"button\" class=\"btn btn-mini btn-danger qtcRemoveOption\"   onclick=\"techjoomla.jQuery(this).closest(\'tr\').remove();\" ><i class=\"' + qtcJsData.Q2C_ICON_TRASH +'\"></i></button> ');
						/* Set type=select and don't allow to edit*/


						/* Show add more option list */
						techjoomla.jQuery("#" + attributeContainerId + " .qtcGlobalOptionsList ").show();

					}
					// Store global attribute id and option id in hidden form

					if (data.hasOwnProperty("goptionSelectHtml"))
					{
						techjoomla.jQuery("#" + attributeContainerId).find('.qtcGlobalOptionsList').html(data.goptionSelectHtml);
					}
				}
			},
			error : function (e)
			{
				console.log('Someting is wrong');
			}
		});
	}
	else
	{
		/* 1 Clean attibute name Global attribtue id (hidden) value */
		techjoomla.jQuery("#" + attributeContainerId).find('.qtc_attrib').attr("value", '');
		techjoomla.jQuery("#" + attributeContainerId).find('.global_atrri_idClass').attr("value", '');

		/* 2. Remove all option except first */
		var currentTr = 0;
		techjoomla.jQuery("#" + attributeContainerId).find('tr.clonedInput').each(function()
		{
			if (currentTr != 0)
			{
				techjoomla.jQuery(this).remove();
			}
			else
			{
				techjoomla.jQuery(this).find('input:text').each(function()
				{
					techjoomla.jQuery(this).val('');
				});
			}

			currentTr++;
		});

		/* Replace last children delete button to plus */
		techjoomla.jQuery('#' + attributeContainerId + ' tr.clonedInput:last' ).children().last().replaceWith('<button type=\"button\" class=\"btnAdd btn btn-mini btn-primary\" id=\"' + attributeContainerId + '\"  onclick=\"addopt(this.id)\" ><i class=\"icon-plus-2 icon-white\"></i></button> ');

		/* Hide remove / add option */
		techjoomla.jQuery("#" + attributeContainerId  + " .qtcRemoveOption ").show();
		techjoomla.jQuery("#" + attributeContainerId + " .btnAdd ").show();
		techjoomla.jQuery("#" + attributeContainerId + " .qtcGlobalOptionsList ").hide();
	}

}

function qtcLoadAttributeOption(obj)
{
	/* closest() selects the first element that matches the selector, up from the DOM tree.*/
	var attributeContainerId = techjoomla.jQuery(obj).closest(".qtc_container").attr('id');
	var selectedGoptionValue = techjoomla.jQuery("#" + attributeContainerId).find(".globalOptionSelect option:selected").val();

	if (selectedGoptionValue)
	{
		var isPresent = 0;
		techjoomla.jQuery('#' + attributeContainerId + ' .qtcGlobalOptionId:input').each(function()
		{
			if (techjoomla.jQuery(this).val() == selectedGoptionValue)
			{
				isPresent = 1;
			}
		});

		if (isPresent == 0)
		{
			/* Add one more TR */
			addopt(attributeContainerId);

			var selectedGoptionText = techjoomla.jQuery("#" + attributeContainerId).find(".globalOptionSelect option:selected").text();

			/* Add in last option */
			/* Add option's global id */
			techjoomla.jQuery("#" + attributeContainerId).find("tr .qtcGlobalOptionId").last().val(selectedGoptionValue);

			/* Add option name */
			techjoomla.jQuery("#" + attributeContainerId).find("tr .qtcOptionNameClass").last().val(selectedGoptionText);

			/*Set All price values to 0*/
			techjoomla.jQuery("#" + attributeContainerId).find("tr").last().find('.qtc_currencey_textbox .currtext').val(0);
		}
		else
		{
			alert(Joomla.JText._('COM_QUICK2CART_ADD_PROD_GATTRIBUTE_OPTION_ALREADY_PRESENT'));
		}
	}
	else
	{
		alert(Joomla.JText._('COM_QUICK2CART_ADD_PROD_SEL_ATTRIBUTE_OPTION'));
	}
}

/* Add attribute view: This function is called on change of is_stock_keeping checkbox */
function qtcOnChange_is_stock_keeping(ele)
{
	var attributeContainerId = techjoomla.jQuery(ele).closest(".qtc_container").attr('id');
	var proceed = 1;

	if (ele.checked)
	{
		// If count >1  then do this
		if (techjoomla.jQuery(".qtc_is_stock_keeping:checked").length > 1)
		{
			var r = confirm(Joomla.JText._('COM_QUICK2CART_CHANGE_STOCKABLE_ATTRIBUTE_ALERT'));
			if (r == true)
			{
				/* Check for other stock keeping attributes */
				techjoomla.jQuery(".qtc_is_stock_keeping:checked").each(function(){
					var currentAttributeContainerId = techjoomla.jQuery(this).closest(".qtc_container").attr('id');

					/*Clear data and uncheck the checkbox*/
					qtcClearStockFields(currentAttributeContainerId);
					techjoomla.jQuery(this).prop('checked', false);
				});

				/* Check the current Stockable attribute */
				techjoomla.jQuery("#" + attributeContainerId + " .qtc_is_stock_keeping").prop('checked', 'checked');
			}
			else
			{
				 proceed = 0;
				techjoomla.jQuery("#" + attributeContainerId + " .qtc_is_stock_keeping").prop('checked', false);
			}
		}

		if (proceed == 1)
		{
			/* Show stock and sku*/
			techjoomla.jQuery("#" + attributeContainerId + " .qtcStockSkufields").removeClass("qtc_hideEle");

			techjoomla.jQuery("#" + attributeContainerId + " .qtcfieldType").val("Select");
			techjoomla.jQuery("#" + attributeContainerId + " .qtcfieldType").hide(); //prop('readonly', 'readonly');
			techjoomla.jQuery("#" + attributeContainerId + " .qtcfieldTypeTitle").hide();

			/* Check and disable the is Compulsory field*/
			techjoomla.jQuery("#" + attributeContainerId + " .checkboxdiv").prop('checked', 'checked');
			techjoomla.jQuery("#" + attributeContainerId + " .checkboxdiv").hide(); //prop('readonly', 'readonly');
			techjoomla.jQuery("#" + attributeContainerId + " .checkboxdivTitle").hide();
		}
	}
	else
	{
		/* HIde and CLEAR stock and sku*/
		qtcClearStockFields(attributeContainerId)
	}
}

function qtcClearStockFields(attributeContainerId)
{
	/* HIde and CLEAR stock and sku*/
	techjoomla.jQuery("#" + attributeContainerId + " .qtcStockSkufields").addClass("qtc_hideEle");
	techjoomla.jQuery("#" + attributeContainerId + " .qtcStockSkufields input:text").val('');

	/*  Show selectbox and checkbox*/
	techjoomla.jQuery("#" + attributeContainerId + " .qtcfieldType").show(); //removeProp('readonly');
	techjoomla.jQuery("#" + attributeContainerId + " .qtcfieldTypeTitle").show(); //removeProp('readonly');
	techjoomla.jQuery("#" + attributeContainerId + " .checkboxdiv").show(); //removeProp('readonly');
	techjoomla.jQuery("#" + attributeContainerId + " .checkboxdivTitle").show(); //removeProp('readonly');
}
