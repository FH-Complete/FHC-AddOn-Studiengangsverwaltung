<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/akadgrad.class.php');
require_once('../../../../../../include/studiensemester.class.php');

require_once('../../../../include/studienordnungAddonStgv.class.php');
require_once('../../../../include/beschluss.class.php');
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

$beschluss = new beschluss();
$beschluss->getAll($studienordnung_id);

$data = array(
    'studienordnung_id'=> $studienordnung->studienordnung_id,
    'version'=> $studienordnung->version, 				
    'bezeichnung' => $studienordnung->bezeichnung,				
    'ects' => $studienordnung->ects,
    'gueltigvon' => $studienordnung->gueltigvon,
    'gueltigbis' => $studienordnung->gueltigbis,
    'studiengangbezeichnung' => $studienordnung->studiengangbezeichnung,
    'studiengangbezeichnung_englisch' => $studienordnung->studiengangbezeichnung_englisch,
    'studiengangkurzbzlang' => $studienordnung->studiengangkurzbzlang,
    'akadgrad_id' => $studienordnung->akadgrad_id,
    'aenderungsvariante_kurzbz' => $studienordnung->aenderungsvariante_kurzbz,
    'status_kurzbz' => $studienordnung->status_kurzbz,
    'begruendung' => $studienordnung->begruendung,
    'updateamum' => $studienordnung->updateamum,
    'updatevon' => $studienordnung->updatevon,
    'insertamum' => $studienordnung->insertamum,
    'insertvon' => $studienordnung->insertvon,
    'beschluesse' => $beschluss->result
);


returnAJAX(true, $data);
?>