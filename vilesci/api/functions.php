<?php

function returnAJAX($success, $obj) {
    //if there is an error
    if (error_get_last())
	$ret = array(
	    "erfolg" => false,
	);
    else if (!$success) {
	$ret = array(
	    "erfolg" => false,
	    "message" => $obj,
	);
    }
    //if we dont have a valid user
    else if (!$getuid = get_uid()) {
	$ret = array(
	    "erfolg" => false,
	);
    }
    //if everything worked fine
    else {
	$ret = array(
	    "erfolg" => true,
	    "user" => $getuid,
	    "info" => $obj,
	);
    }
    echo json_encode($ret);
    if ($ret["erfolg"] === false)
	die("");
}
/*
 * @return TRUE for '1','true','on','yes' and FALSE for '0','false','off','no' NULL for non-boolean
 */
function parseBoolean($var)
{
    $bool = filter_var($var, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if(!is_null($bool))
	return $bool;
    else
	return false;
}

function fhc_formatNumber($number)
{
    $number = str_replace(".","",$number);
    $number = str_replace(",",".",$number);
    return $number;
}

function fhc_reformatNumber($number, $dec)
{
    $number = number_format($number,$dec,",", ".");
    return $number;
}
