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
	if(val instanceof Date)
	{
		var date = val;
		var month = ("0"+(date.getMonth()+1)).slice(-2);
		var day = ("0"+date.getDate()).slice(-2);
		var dateString = date.getFullYear()+"-"+month+"-"+day;
		return dateString;
	}
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
	
	if((val !== null) && (val !== undefined))
	{
		var bits = val.split(/\D/);
		if(bits.length === 6)
			return new Date(bits[0], --bits[1], bits[2], bits[3], bits[4], bits[5]);
		else if(bits.length === 5)
			return new Date(bits[0], --bits[1], bits[2], bits[3], bits[4]);
		else if(bits.length === 3)
			return new Date(bits[0], --bits[1], bits[2]);
		else
			return null;
	}
	else
	{
		return null;
	}
	
//	var date = new Date(Date.parse(val));
//	return date;
}

function formatStringToTime(val, separator)
{
	var time = val.split(separator);
	if(time.length >= 2)
		return new Date(0,0,0,time[0],time[1]);
	return new Date();
}

function formatTimeToString(val)
{
	if(!(val instanceof Date))
		val = new Date(val);

	if(val instanceof Date)
	{
		var date = val;
		var hour = ("0"+date.getHours()).slice(-2);
		var min = ("0"+date.getMinutes()).slice(-2);
		var timeString = hour+":"+min;
		return timeString;
	}
}

function dateTimeStringToDateString(dateTimeString)
{
	if((dateTimeString !== null) && (dateTimeString !== undefined))
		return dateTimeString.split(" ")[0];
	else
		return null;
}