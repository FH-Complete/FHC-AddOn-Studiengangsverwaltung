<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/reihungstest.class.php');
require_once('../../functions.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if (!$rechte->isBerechtigt('addon/studiengangsverwaltung'))
{
	$error = array("message"=>"Sie haben keine Berechtigung für diese Aktion.", "detail"=>$rechte->errormsg);
	returnAJAX(false, $error);
}

$studiengang_kz = filter_input(INPUT_GET, "stgkz");
$studiensemester_kurzbz = filter_input(INPUT_GET, "studiensemester_kurzbz");
$sort = filter_input(INPUT_GET, "sort");
$order = filter_input(INPUT_GET, "order");

$sort = explode(",",$sort);
$order = explode(",",$order);

$sortString = null;

foreach($sort as $key=>$s)
{
    $sortString .= $s." ".$order[$key].", ";
}

$sortString = substr($sortString,0,-2);

if($sortString == " ")
    $sortString = null;

if(is_null($studiengang_kz))
{
    returnAJAX(false, "Variable stgkz nicht gesetzt");
}
elseif($studiengang_kz == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

if($studiensemester_kurzbz == "null")
    $studiensemester_kurzbz = null;

$reihungstest = new reihungstest();
$reihungstest->getReihungstest($studiengang_kz, $sortString, $studiensemester_kurzbz);
$data = $reihungstest->result;

returnAJAX(true, $data);
