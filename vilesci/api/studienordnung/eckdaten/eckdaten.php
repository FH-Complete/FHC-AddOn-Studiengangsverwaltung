<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/akadgrad.class.php');
require_once('../../../../../../include/studiensemester.class.php');
require_once('../../../../../../include/studiengang.class.php');
require_once('../../../../include/studienordnungAddonStgv.class.php');
require_once('../../../../include/studienplanAddonStgv.class.php');
require_once('../../functions.php');

$sto_array = array();

$studienordnung_id = filter_input(INPUT_GET, "studienordnung_id");

if(is_null($studienordnung_id))
{
    returnAJAX(false, "Variable studienordnung_id nicht gesetzt");    
}
elseif(($studienordnung_id == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}


$studienordnung = new StudienordnungAddonStgv();
$studienordnung->loadStudienordnung($studienordnung_id);

$akadgrad = new akadgrad();
$akadgrad->load($studienordnung->akadgrad_id);
$akadgrad->getAll();
$studiensemester = new studiensemester();
$studiensemester->getAll();

$studiengang = new studiengang($studienordnung->studiengang_kz);
$studienplan = new StudienplanAddonStgv();
$studienplan->loadStudienplanSTO($studienordnung->studienordnung_id);


$orgform = "";
foreach($studienplan->result as $stpl)
{
    if($orgform === "")
    {
	$orgform .= $stpl->orgform_kurzbz." ";
    }
    else
    {
	$orgform .= "+ ".$stpl->orgform_kurzbz." ";
    }
}

$data = array(
    'studienordnung_id'=> $studienordnung->studienordnung_id,
    'stgkz'=> $studienordnung->studiengang_kz,
    'version'=> $studienordnung->version, 				
    'bezeichnung' => $studienordnung->bezeichnung,				
    'ects' => $studienordnung->ects,
    'gueltigvon' => $studienordnung->gueltigvon,
    'gueltigbis' => $studienordnung->gueltigbis,
    'studiengangbezeichnung' => $studienordnung->studiengangbezeichnung,
    'studiengangbezeichnung_englisch' => $studienordnung->studiengangbezeichnung_englisch,
    'studiengangkurzbzlang' => $studienordnung->studiengangkurzbzlang,
    'akadgrad_id' => $studienordnung->akadgrad_id,
    'studiengangsart' => $studiengang->typ,
    'standort_id' => $studienordnung->standort_id,
    'orgform' => $orgform,
    'status_kurzbz' => $studienordnung->status_kurzbz,
    'updateamum' => $studienordnung->updateamum,
    'updatevon' => $studienordnung->updatevon,
    'insertamum' => $studienordnung->insertamum,
    'insertvon' => $studienordnung->insertvon
);


returnAJAX(true, $data);
?>