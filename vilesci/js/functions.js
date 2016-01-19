function getErrorMsg(error)
{
	var message = "";
	message += "<p>" + error.status + " " + error.statusText + "</p>";
	message += "<p>" + error.config.url + "</p>";
	if (error.data.message != undefined)
	{
		message += "<p>" + error.data.message.message + "</p>";
		if (error.data.message.detail != undefined)
			message += "<p>" + error.data.message.detail + "</p>";
	}
	
	return message;
}

function formatDateToString(val,row){
	if(val != null)
	{
		var date = new Date(Date.parse(val.split(" ")[0]));
		var month = ("0"+(date.getMonth()+1)).slice(-2);
		var day = ("0"+date.getDate()).slice(-2);
		var dateString = date.getFullYear()+"-"+month+"-"+day;
		return dateString;
	}
}

function formatStringToDate(val){
	var date = new Date(Date.parse(val));
	return date;
}