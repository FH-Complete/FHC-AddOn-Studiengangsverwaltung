<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');

require_once('../../../../include/StudienplanAddonStgv.class.php');
//TODO functions from core?
require_once('../../functions.php');

//TODO
$DEBUG = true;
$studienplan_lehrveranstaltung_id = filter_input(INPUT_GET, "studienplan_lehrveranstaltung_id");

if(is_null($studienplan_lehrveranstaltung_id))
{
    returnAJAX(false, "Variable studienplan_lehrveranstaltung_id nicht gesetzt");    
}
elseif($studienplan_lehrveranstaltung_id == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$studienplan = new StudienplanAddonStgv(); 

if($studienplan->deleteStudienplanLehrveranstaltung($studienplan_lehrveranstaltung_id))
{
    returnAJAX(true, "Lehrveranstaltung erfolgreich gelöscht.");
}
else
{
    $error = array("message"=>"Fehler beim Löschen der Lehrveranstaltung aus dem Studienplan.", "detail"=>$studienplan->errormsg);
    returnAJAX(false, $error);
}

?>