<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');

require_once('../../functions.php');
require_once('../../../../include/Studiengangsgruppe.class.php');

//TODO Berechtigungen

$studiengangsgruppe = new studiengangsgruppe();
$studiengangsgruppe->getAll();
$data = $studiengangsgruppe->result;

//$studiengang_kz = filter_input(INPUT_GET, "stgkz");
//$studiensemester_kurzbz = filter_input(INPUT_GET, "studiensemester_kurzbz");
//
//if(is_null($studiengang_kz))
//{
//    returnAJAX(false, "Variable stgkz nicht gesetzt");    
//}
//elseif($studiengang_kz == false)
//{
//    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
//}
//
//if($studiensemester_kurzbz == "null")
//    $studiensemester_kurzbz = null;
//
//$reihungstest = new reihungstest();
//$reihungstest->getReihungstest($studiengang_kz, "datum desc", $studiensemester_kurzbz);
//$data = $reihungstest->result;

returnAJAX(true, $data);