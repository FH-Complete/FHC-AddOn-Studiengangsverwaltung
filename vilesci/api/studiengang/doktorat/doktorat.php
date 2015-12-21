<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/doktorat.class.php');
//TODO functions from core?
require_once('../../functions.php');

//TODO
$DEBUG = true;

$studiengang_kz = filter_input(INPUT_GET, "stgkz");

if(is_null($studiengang_kz))
{
    returnAJAX(false, "Variable stgkz nicht gesetzt");    
}
elseif($studiengang_kz == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

if($studiengang_kz == "null")
    $studiengang_kz = null;

$doktorat = new doktorat();
$doktorat->getAll($studiengang_kz);
$data = $doktorat->result;

returnAJAX(true, $data);