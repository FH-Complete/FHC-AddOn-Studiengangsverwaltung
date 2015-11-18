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