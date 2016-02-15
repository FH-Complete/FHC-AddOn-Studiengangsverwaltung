<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/lehrveranstaltung.class.php');
require_once('../functions.php');

$oe_kurzbz = filter_input(INPUT_GET, "oe_kurzbz");
$lehrtyp_kurzbz = filter_input(INPUT_GET, "lehrtyp_kurzbz");
$semester = filter_input(INPUT_GET, "semester");
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

if(is_null($oe_kurzbz))
{
    returnAJAX(false, "Variable oe_kurzbz nicht gesetzt");    
}
elseif(is_null($lehrtyp_kurzbz))
{
    returnAJAX(false, "Variable lehrtyp_kurzbz nicht gesetzt");   
}
elseif(is_null($semester))
{
    returnAJAX(false, "Variable semester nicht gesetzt");   
}
elseif($oe_kurzbz === false || $lehrtyp_kurzbz === false || $semester === false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

if($semester == "null")
{
    $semester = null;
}

$lehrveranstaltung = new lehrveranstaltung();
if($lehrveranstaltung->load_lva_oe($oe_kurzbz, true, $lehrtyp_kurzbz, $sortString, $semester))
{
    $lv_array = array();

    foreach($lehrveranstaltung->lehrveranstaltungen as $key=>$lv)
    {
	$temp = new stdClass();
	$temp->id = $lv->lehrveranstaltung_id;
	$temp->bezeichnung = $lv->bezeichnung;
	$temp->ects = $lv->ects;
	$temp->type = $lv->lehrtyp_kurzbz;
	$temp->kurzbz = $lv->kurzbz;
	$temp->semester = $lv->semester;
	$temp->sprache = $lv->sprache;
	$temp->semesterstunden = $lv->semesterstunden;
	$temp->lehrform_kurzbz = $lv->lehrform_kurzbz;
	$temp->bezeichnung_english = $lv->bezeichnung_english;
	$temp->orgform_kurzbz = $lv->orgform_kurzbz;
	$temp->incoming = $lv->incoming;
	$temp->oe_kurzbz = $lv->oe_kurzbz;
	$temp->semesterwochen = $lv->semesterwochen;
	$temp->lvnr = $lv->lvnr;
	$temp->sws = $lv->sws;
	$temp->lvs = $lv->lvs;
	$temp->alvs = $lv->alvs;
	$temp->lvps = $lv->lvps;
	$temp->las = $lv->las;
	$temp->benotung = $lv->benotung;
	$temp->lvinfo = $lv->lvinfo;
	$temp->zeugnis = $lv->zeugnis;
	array_push($lv_array, $temp);
    }
}
else
{
    returnAJAX(false, $lehrveranstaltung->errormsg);
}
returnAJAX(true, $lv_array)


?>