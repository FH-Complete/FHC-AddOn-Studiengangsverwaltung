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
