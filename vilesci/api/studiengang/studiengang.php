<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/studiengang.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

//TODO
//$uid = "kofler";
$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("addon/studiengangsverwaltung",null,"suid"))
{
    $error = array("message"=>"Sie haben keine Berechtigung für diese Anwendung.", "detail"=>"addon/studiengangsverwaltung.");
    returnAJAX(FALSE, $error);
}
$stg_kz_array = $berechtigung->getStgKz("addon/studiengangsverwaltung");

//TODO
//Studiengänge nach Berechtigung laden
$studiengang = new studiengang();
$studiengang->loadArray($stg_kz_array, "kurzbzlang", true);

$stg_array = array();

//TODO Stati aus Datenank holen
//prepared stati
$stati = array();
$status1 = new stdClass();
$status1->name = "in Bearbeitung";
$status1->code = "development";
array_push($stati, $status1);

$status2 = new stdClass();
$status2->name = "in Review";
$status2->code = "review";
array_push($stati, $status2);

$status3 = new stdClass();
$status3->name = "genehmigt";
$status3->code = "approved";
array_push($stati, $status3);

$status4 = new stdClass();
$status4->name = "nicht genehmigt";
$status4->code = "notApproved";
array_push($stati, $status4);

$status5 = new stdClass();
$status5->name = "ausgelaufen";
$status5->code = "expired";
array_push($stati, $status5);

/**
 * Baumstruktur für jeden Studiengang anlegen
 */
foreach($studiengang->result as $key=>$stg)
{
    $temp = new stdClass();
    $temp->id = $stg->studiengang_kz;
    $temp->stgkz = $stg->studiengang_kz;
    $temp->kurzbzlang = $stg->kurzbzlang;
    //TODO Stg Bezeichnung von jüngster aktiven STO holen
    $temp->bezeichnung = $stg->bezeichnung;
    $temp->text = $stg->kurzbzlang." - ".$stg->bezeichnung;
    if($key == 0 && $DEBUG)
	$temp->state = "open";
    else
	$temp->state = "closed";
    $attributes = array();
    $attr = new stdClass();
    $attr->name = "node_type";
    $attr->value = "studiengang";

    $urlParams = array();
    $urlParam = new stdClass();
    $urlParam->stgkz = $stg->studiengang_kz;
    
    array_push($urlParams, $urlParam);
    $attr->urlParams = $urlParams;

    array_push($attributes, $attr);
    $temp->attributes = $attributes;
    
    //Children of Studiengang
	//Child Stammdaten
	$children = array();
	$stammdaten = new stdClass();
	$stammdaten->id = $key;
	$stammdaten->text = "Stammdaten";

	$stammdaten_attributes = array();
	$stammdaten_attr = new stdClass();
	$stammdaten_attr->name = "node_type";
	$stammdaten_attr->value = "stammdaten";

	$stammdaten_urlParams = array();
	$stammdaten_urlParam = new stdClass();
	$stammdaten_urlParam->stgkz = $stg->studiengang_kz;
	array_push($stammdaten_urlParams, $stammdaten_urlParam);

	$stammdaten_attr->urlParams = $stammdaten_urlParams;
	array_push($stammdaten_attributes, $stammdaten_attr);
	$stammdaten->attributes = $stammdaten_attributes;
	array_push($children, $stammdaten);
	
	//Child Betriebsdaten
	$betriebsdaten = new stdClass();
	$betriebsdaten->id = $key;
	$betriebsdaten->text = "Betriebsdaten";

	$betriebsdaten_attributes = array();
	$betriebsdaten_attr = new stdClass();
	$betriebsdaten_attr->name = "node_type";
	$betriebsdaten_attr->value = "betriebsdaten";

	$betriebsdaten_urlParams = array();
	$betriebsdaten_urlParam = new stdClass();
	$betriebsdaten_urlParam->stgkz = $stg->studiengang_kz;
	array_push($betriebsdaten_urlParams, $betriebsdaten_urlParam);

	$betriebsdaten_attr->urlParams = $betriebsdaten_urlParams;

	array_push($betriebsdaten_attributes, $betriebsdaten_attr);

	$betriebsdaten->attributes = $betriebsdaten_attributes;

	array_push($children, $betriebsdaten);
	
	//Child Studienordnungen
	$studienordnungen = new stdClass();
	$studienordnungen->id = $key;
	$studienordnungen->text = "Studienordnungen";
	if($key == 0 && $DEBUG)
	    $studienordnungen->state = "open";
	else
	    $studienordnungen->state = "closed";
	$studienordnungen->children = array();
	
	//Children of Studienordnungen
	    foreach($stati as $status)
	    {
		$node = new stdClass();
		$node->id = $key;
		$node->text = $status->name;

		$node_attributes = array();
		$node_attr = new stdClass();
		$node_attr->name = "node_type";
		$node_attr->value = "state";

		$node_urlParams = array();
		$node_urlParam = new stdClass();
		$node_urlParam->stgkz = $stg->studiengang_kz;
		$node_urlParam->state = $status->code;
		array_push($node_urlParams, $node_urlParam);

		$node_attr->urlParams = $node_urlParams;
		array_push($node_attributes, $node_attr);
		$node->attributes = $node_attributes;
		array_push($studienordnungen->children, $node);
	    }
	array_push($children, $studienordnungen);
    
    $temp->children = $children;
    
    array_push($stg_array, $temp);
}

returnAJAX(true, $stg_array)
?>