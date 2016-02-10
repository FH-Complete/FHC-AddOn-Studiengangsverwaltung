<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once ('../../../include/StudienplanAddonStgv.class.php');
require_once ('../../../include/StudienordnungAddonStgv.class.php');
require_once('../functions.php');

$studienordnung_id = filter_input(INPUT_GET, "studienordnung_id");
$studienplan_id = filter_input(INPUT_GET, "studienplan_id");

$studienordnung = new StudienordnungAddonStgv();

if (!is_null($studienordnung_id))
{
    $studienordnung->loadStudienordnung($studienordnung_id);
} 
elseif (!is_null($studienplan_id))
{
    $studienordnung->getStudienordnungFromStudienplan($studienplan_id);
} 
else
{
    $studienordnung->getAll();
    $data = array();
    foreach ($studienordnung->resul as $sto)
    {
	$obj = new stdClass();
	$obj->studienordnung_id = $sto->studienordnung_id;
	$obj->studiengang_kz = $sto->studiengang_kz;
	$obj->version = $sto->version;
	$obj->bezeichnung = $sto->bezeichnung;
	$obj->ects = $sto->ects;
	$obj->gueltigvon = $sto->gueltigvon;
	$obj->gueltigbis = $sto->gueltigbis;
	$obj->studiengangbezeichnung = $sto->studiengangbezeichnung;
	$obj->studiengangbezeichnung_englisch = $sto->studiengangbezeichnung_englisch;
	$obj->studiengangkurzbzlang = $sto->studiengangkurzbzlang;
	$obj->akadgrad_id = $sto->akadgrad_id;
	$obj->aenderungsvariante_kurzbz = $sto->aenderungsvariante_kurzbz;
	$obj->status_kurzbz = $sto->status_kurzbz;
	$obj->begruendung = $sto->begruendung;
	$obj->studiengangsart = $sto->studiengangsart;
	$obj->orgform_kurzbz = $sto->orgform_kurzbz;
	$obj->standort_id = $sto->standort_id;
	$obj->updateamum = $sto->updateamum;
	$obj->updatevon = $sto->updatevon;
	$obj->insertamum = $sto->insertamum;
	$obj->insertvon = $sto->insertvon;
	array_push($data, $obj);
    }
    returnAJAX(true, $data);
}

$data = array(
    "studienordnung_id" => $studienordnung->studienordnung_id,
    "studiengang_kz" => $studienordnung->studiengang_kz,
    "version" => $studienordnung->version,
    "bezeichnung" => $studienordnung->bezeichnung,
    "ects" => $studienordnung->ects,
    "gueltigvon" => $studienordnung->gueltigvon,
    "gueltigbis" => $studienordnung->gueltigbis,
    "studiengangbezeichnung" => $studienordnung->studiengangbezeichnung,
    "studiengangbezeichnung_englisch" => $studienordnung->studiengangbezeichnung_englisch,
    "studiengangkurzbzlang" => $studienordnung->studiengangkurzbzlang,
    "akadgrad_id" => $studienordnung->akadgrad_id,
    "aenderungsvariante_kurzbz" => $studienordnung->aenderungsvariante_kurzbz,
    "status_kurzbz" => $studienordnung->status_kurzbz,
    "begruendung" => $studienordnung->begruendung,
    "studiengangsart" => $studienordnung->studiengangsart,
    "orgform_kurzbz" => $studienordnung->orgform_kurzbz,
    "standort_id" => $studienordnung->standort_id,
    "updateamum" => $studienordnung->updateamum,
    "updatevon" => $studienordnung->updatevon,
    "insertamum" => $studienordnung->insertamum,
    "insertvon" => $studienordnung->insertvon
);


returnAJAX(true, $data);
?>