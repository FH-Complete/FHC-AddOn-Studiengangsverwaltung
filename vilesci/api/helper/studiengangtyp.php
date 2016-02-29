<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/studiengang.class.php');

require_once('../functions.php');


$studiengang = new studiengang();

$studiengang->getAllTypes();
$data = array();

foreach($studiengang->studiengang_typ_arr as $key=>$value)
{
    if($value != null)
    {
	$obj = new stdClass();
	$obj->typ = $key;
	$obj->bezeichnung = $value;
	array_push($data, $obj);
    }
}

returnAJAX(true, $data);
?>