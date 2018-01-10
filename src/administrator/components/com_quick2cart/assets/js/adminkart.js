function selectstatus(appid, ele)
{
	/*document.forms['adminForm'].id.value = appid; */
	var selInd=ele.selectedIndex;
	var status =ele.options[selInd].value;
	var reply	= '';
	if (status==1)
	{
		ele.options[0].disabled=true;
	}

	if (status==2)
	{
		var reply = prompt("Type Reason to reject", "");
	}

	if (!(reply==null))
	{
		document.getElementById('reason').value = reply;
		document.getElementById('hidid').value = appid;
		document.getElementById('hidstat').value = status;
		submitbutton('save');

	return;
	}
	else
	{
		return false;
	}
}

function selectzone(appid, ele)
{
	/*document.forms['adminForm'].id.value = appid;*/
	var selInd=ele.selectedIndex;
	var zone =ele.options[selInd].value;
	document.getElementById('hidid').value = appid;
	document.getElementById('hidzone').value = zone;
	submitbutton('updatezone');

	return;
}

//~ function selectstatusorder(appid, ele)
//~ {
	//~ var status = document.getElementById("pstatus" + orderId).value;
	//~ document.getElementById('hidid').value = appid;
	//~ document.getElementById('hidstat').value = status;
	//~ submitbutton('save');
	//~ return;
//~ }


/* @vm incomplete code. have to add on order list view.*/
//~ function updateOrderStatus(orderId, ele)
//~ {
//~
	//~ var noteId = "order_note_+ orderId;
//~ alert(noteId);
	//~ /* Update note field name to "order note" so that it will be compatible to oder detail page note field */
	//~ document.getElementById(noteId).setAttribute("name","comment");
//~
	//~ var selInd=ele.selectedIndex;
	//~ var status =ele.options[selInd].value;
	//~ document.getElementById('hidid').value = orderId;
	//~ document.getElementById('hidstat').value = status;
	//~ submitbutton('save');
	//~ return;
//~ }
