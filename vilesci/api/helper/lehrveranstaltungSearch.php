<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/lehrveranstaltung.class.php');
require_once('../functions.php');

$lv_bezeichnung = filter_input(INPUT_GET, "lv");

if(is_null($lv_bezeichnung))
{
    returnAJAX(false, "Variable lv nicht gesetzt");    
}
elseif($lv_bezeichnung == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$lehrveranstaltung = new lehrveranstaltung();
if($lehrveranstaltung->search($lv_bezeichnung))
{
    $lv_array = array();

    foreach($lehrveranstaltung->lehrveranstaltungen as $key=>$lv)
    {
	$temp = new stdClass();
	$temp->lehrveranstaltung_id = $lv->lehrveranstaltung_id;
	$temp->bezeichnung = $lv->bezeichnung;
	$temp->ects = $lv->ects;
	$temp->lehrtyp_kurzbz = $lv->lehrtyp_kurzbz;
	$temp->oe_kurzbz = $lv->oe_kurzbz;
	$temp->semester = $lv->semester;
	$temp->aktiv = $lv->aktiv;
	$temp->lehre = $lv->lehre;
	array_push($lv_array, $temp);
    }
}
else
{
    returnAJAX(false, $lehrveranstaltung->errormsg);
}
returnAJAX(true, $lv_array)


?>