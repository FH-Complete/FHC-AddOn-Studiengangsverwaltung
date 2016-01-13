<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../include/lehrformAddonStgv.class.php');
//TODO functions from core?
require_once('../functions.php');

$lehrtyp_kurzbz = filter_input(INPUT_GET, "lehrtyp_kurzbz");

if(is_null($lehrtyp_kurzbz))
{
    returnAJAX(false, "Variable lehrtyp_kurzbz nicht gesetzt");    
}
elseif($lehrtyp_kurzbz == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$lehrform = new lehrformAddonStgv();
if($lehrform->getByLehrtyp($lehrtyp_kurzbz))
{
    $data =  $lehrform->lehrform;
}
else
{
    returnAJAX(false, $lehrform->errormsg);
}

returnAJAX(true, $data);
?>