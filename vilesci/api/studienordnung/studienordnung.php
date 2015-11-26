<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
//require_once('../../../../../include/studienordnung.class.php');
//require_once('../../../../../include/studienplan.class.php');
require_once ('../../../include/StudienplanAddonStgv.class.php');
require_once ('../../../include/StudienordnungAddonStgv.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$sto_array = array();

$studiengang_kz = filter_input(INPUT_GET, "stgkz");
$status = filter_input(INPUT_GET, "state");

if(is_null($studiengang_kz))
{
    returnAJAX(false, "Variable stgkz nicht gesetzt");    
}
elseif(is_null($status))
{
    returnAJAX(false, "Variable state nicht gesetzt");
}
elseif(($studiengang_kz == false) || ($status == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$studienordnung = new StudienordnungAddonStgv();

switch($status)
{
    case "all":
	$studienordnung->loadStudienordnungSTG($studiengang_kz);
    default:
	$studienordnung->loadStudienordnungWithStatus($studiengang_kz, $status);
	break;
}

foreach($studienordnung->result as $key=>$sto)
{
    $temp = new stdClass();
    $temp->id = $sto->studienordnung_id;
    $temp->version = $sto->version;
    $temp->text = $sto->bezeichnung;
    if($key == 0 && $DEBUG)
	$temp->state = "open";
    else
	$temp->state = "closed";
    $temp->status = $sto->status_kurzbz;
    $temp->stgkz = $sto->studiengang_kz;
    $temp->ects = $sto->ects;
    $temp->gueltigvon = $sto->gueltigvon;
    $temp->gueltigbis = $sto->gueltigbis;
    
    //Creating attributes for treeGrid
    $temp->attributes = array();
    $attr = new stdClass();
    $attr->name = "node_type";
    $attr->value = "studienordnung";
    $attr->urlParams = array();
    $urlParam = new stdClass();
    $urlParam->stoid = $sto->studienordnung_id;
    array_push($attr->urlParams, $urlParam);
    array_push($temp->attributes, $attr);
    
    $stpl_array = array();
    $studienplan = new StudienplanAddonStgv();
    $studienplan->loadStudienplanSTO($sto->studienordnung_id);
    $temp->children = array();
    
    foreach($studienplan->result as $stpl)
    {
	$temp_stpl = new stdClass();
	$temp_stpl->id = $stpl->studienplan_id;
	$temp_stpl->text = $stpl->bezeichnung;
	$temp_stpl->version = $stpl->version;
	$temp_stpl->orgform_kurzbz = $stpl->orgform_kurzbz;
	$temp_stpl->regelstudiendauer = $stpl->regelstudiendauer;
	$temp_stpl->sprache = $stpl->sprache;
	$temp_stpl->ects_stpl = $stpl->ects_stpl;
	
	$temp_stpl->attributes = array();
	$node_attr = new stdClass();
	$node_attr->name = "node_type";
	$node_attr->value = "studienplan";
	$node_attr->urlParams = array();
	$node_urlParam = new stdClass();
	$node_urlParam->stplid = $stpl->studienplan_id;
	array_push($node_attr->urlParams, $node_urlParam);
	array_push($temp_stpl->attributes, $node_attr);
	array_push($temp->children, $temp_stpl);
    }
    
    array_push($sto_array, $temp);
}
returnAJAX(true, $sto_array)
?>