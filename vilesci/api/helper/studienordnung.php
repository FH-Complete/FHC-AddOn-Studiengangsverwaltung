<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once ('../../../include/studienplanAddonStgv.class.php');
require_once ('../../../include/studienordnungAddonStgv.class.php');
require_once('../functions.php');

$sto_array = array();

$studiengang_kz = filter_input(INPUT_GET, "stgkz");
$status = filter_input(INPUT_GET, "state");

if(is_null($studiengang_kz))
{
    returnAJAX(false, "Variable stgkz nicht gesetzt");    
}
elseif(is_null($status))
{
    returnAJAX(false, "Variable state nicht gesetzt");
}
elseif(($studiengang_kz == false) || ($status == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$studienordnung = new StudienordnungAddonStgv();

switch($status)
{
    case "all":
	$studienordnung->loadStudienordnungSTG($studiengang_kz);
    default:
	$studienordnung->loadStudienordnungWithStatus($studiengang_kz, $status);
	break;
}

$data = array();
foreach($studienordnung->result as $key=>$sto)
{
   $data[$key]["studienordnung_id"] = $sto->studienordnung_id;
   $data[$key]["bezeichnung"] = $sto->bezeichnung;
   $data[$key]["orgform_kurzbz"] = $sto->orgform_kurzbz;
   $data[$key]["status_bezeichnung"] = $sto->status_bezeichnung;
}
returnAJAX(true, $data);
?>