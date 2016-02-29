<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/bewerbungstermin.class.php');
require_once('../../functions.php');

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

$bewerbungstermin = new bewerbungstermin();
$bewerbungstermin->getBewerbungstermine($studiengang_kz, $studiensemester_kurzbz, $sortString);
$data = $bewerbungstermin->result;

returnAJAX(true, $data);