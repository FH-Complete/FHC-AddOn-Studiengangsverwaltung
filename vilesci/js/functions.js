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

function formatDateToString(val,row)
{
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

/**
 * Adds a leading 0 if the number is only 1 digit
 * zB: 4 -> 04
 */
function pad(number)
{
 	if (number < 10)
	{
		return '0' + number;
	}
	return number;
}

/**
 * Formats Javscript Date Object as ISO Date -> 2019-12-31
 */
function formatDateAsString(val)
{
	return val.getFullYear() +
	'-' + pad(val.getMonth() + 1) +
	'-' + pad(val.getDate());
}

/**
 * Formats Javascript Date Object as German Date String: 31.12.2019
 */
function formatDateAsGermanString(dateobj)
{
	return pad(dateobj.getDate()) +
	'.' + pad(dateobj.getMonth() + 1) +
	'.' + dateobj.getFullYear();
}

function formatStringToDate(val)
{

	if((val !== null) && (val !== undefined))
	{
		var bits = val.split(/\D/);
		if(bits.length === 6)
		{
			return new Date(bits[0], --bits[1], bits[2], bits[3], bits[4], bits[5]);
		}
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
	if((val !== null) && (val !== undefined))
	{
		var time = val.split(separator);
		if(time.length >= 2)
		{
			return new Date(0,0,0,time[0],time[1]);
		}
		return new Date();
	}
	else
	{
			return null;
	}
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

/**
 * Removes Time Information from Datetimestring
 * 2019-01-01 15:00:00 -> 2019-01-01
 * 01.01.2019 15:00 -> 01.01.2019
 */
function dateTimeStringToDateString(dateTimeString)
{
	if((dateTimeString !== null) && (dateTimeString !== undefined))
		return dateTimeString.split(" ")[0];
	else
		return null;
}

/**
 * Converts ISO Date or Date and Time to German Date and Time
 * 2019-01-01 15:00:00 -> 01.01.2019 15:00
 */
function dateTimeStringToGermanDateString(dateTimeString)
{
	if((dateTimeString !== null) && (dateTimeString !== undefined))
	{
		var datum = dateTimeString.split(" ")[0];

		var zeit = dateTimeString.split(" ")[1];

		if ((zeit !== null ) && (zeit !== undefined))
		{
			return datum.split("-")[2] + '.' + datum.split("-")[1] + '.' + datum.split("-")[0] + ' ' + zeit.split(":")[0] + ':' + zeit.split(":")[1];
		}
		else

			return datum.split("-")[2] + '.' + datum.split("-")[1] + '.' + datum.split("-")[0];
	}
	else

		return null;
}

/**
 * Converts German Date to ISO Date
 * 31.12.2019 -> 2019-12-31
 */
function GermanDateToISODate(datum)
{
	return datum.split(".")[2]+'-'+datum.split(".")[1]+'-'+datum.split(".")[0];
}

/**
 * Converts ISO Date or Date and Time to German Date Format
 * 2019-01-01 15:00:00 -> 01.01.2019
 */
function dateTimeStringToGermanDate(dateTimeString)
{
	if((dateTimeString !== null) && (dateTimeString !== undefined))
	{
		var datum = dateTimeString.split(" ")[0];
		var zeit = dateTimeString.split(" ")[1];

		return datum.split("-")[2]+'.'+datum.split("-")[1]+'.'+datum.split("-")[0];
	}
	else
		return null;
}

function dateTimeStringToTimeString(dateTimeString)
{
	if((dateTimeString !== null) && (dateTimeString !== undefined))
	{
		var timestring = dateTimeString.split(" ")[1];
		return timestring.split(':')[0] + ':' + timestring.split(':')[1];
	}
	else
		return null;
}
