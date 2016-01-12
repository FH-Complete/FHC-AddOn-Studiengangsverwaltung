<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/organisationseinheit.class.php');

require_once('../functions.php');

$oetyp_kurzbz = filter_input(INPUT_GET, "oetyp_kurzbz");

if(is_null($oetyp_kurzbz))
{
    returnAJAX(false, "Variable oetyp_kurzbz nicht gesetzt");    
}
elseif($oetyp_kurzbz == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$oe = new organisationseinheit();
$oe->getByTyp($oetyp_kurzbz);

$data =  $oe->result;
returnAJAX(true, $data);
?>