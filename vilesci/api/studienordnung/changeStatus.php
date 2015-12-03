<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once ('../../../include/StudienordnungAddonStgv.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$sto_array = array();

$studienordnung_id = filter_input(INPUT_GET, "studienordnung_id");
$status = filter_input(INPUT_GET, "state");

if(is_null($studienordnung_id))
{
    returnAJAX(false, "Variable studienordnung_id nicht gesetzt");    
}
elseif(is_null($status))
{
    returnAJAX(false, "Variable state nicht gesetzt");
}
elseif(($studienordnung_id == false) || ($status == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

//TODO check berechtigung

$studienordnung = new StudienordnungAddonStgv();
if($studienordnung->changeState($studienordnung_id, $status))
{
    returnAJAX(true, "Status erfolgreich geändert.");
}
else
{
    $error = array("message"=> "Status konnte nicht geändert werden.", "detail"=>$studienordnung->errormsg);
    returnAJAX(true, $error);
}



?>