<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');

require_once('../../../../include/Studiengangsgruppe.class.php');
//TODO functions from core?
require_once('../../functions.php');

//TODO
$DEBUG = true;

$sto_array = array();

$stgkz = filter_input(INPUT_GET, "studiengang_kz");

if(is_null($stgkz))
{
    returnAJAX(false, "Variable studiengang_kz nicht gesetzt");    
}
elseif(($stgkz == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$studiengangsgruppe = new studiengangsgruppe();
$studiengangsgruppe->loadZuordnung($stgkz);

$data = array(
    'studiengangsgruppe_studiengang_id'=> $studiengangsgruppe->studiengangsgruppe_studiengang_id,
    'stgkz'=> $studiengangsgruppe->studiengang_kz,
    'data'=> $studiengangsgruppe->data,
    'updateamum' => $studiengangsgruppe->updateamum,
    'updatevon' => $studiengangsgruppe->updatevon,
    'insertamum' => $studiengangsgruppe->insertamum,
    'insertvon' => $studiengangsgruppe->insertvon
);

returnAJAX(true, $data);
?>