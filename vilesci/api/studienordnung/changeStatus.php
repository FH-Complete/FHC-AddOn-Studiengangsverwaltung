<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once ('../../../include/studienordnungAddonStgv.class.php');
require_once('../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if((!$berechtigung->isBerechtigt("stgv/changeStoStateSTG",null,"suid")) && (!$berechtigung->isBerechtigt("stgv/changeStoStateAdmin",null,"suid")))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um den Status einer Studienordnung zu ändern.", "detail"=>"stgv/changeStoStateSTG OR stgv/changeStoStateAdmin");
    returnAJAX(FALSE, $error);
}

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

if($berechtigung->isBerechtigt("stgv/changeStoStateSTG",null,"suid") && ($status != "review") && (!$berechtigung->isBerechtigt("stgv/changeStoStateAdmin",null,"suid")))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um eine Studienordnung in diesen Status zu verschieben.", "detail"=>"stgv/changeStoStateSTG");
    returnAJAX(FALSE, $error);
}

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