<?php
require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/StudienplanAddonStgv.class.php');
require_once('../../../../include/StudienordnungAddonStgv.class.php');
require_once('../../functions.php');


$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/changeStudienplan",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienpläne zu ändern.", "detail"=>"stgv/changeStudienplan");
    returnAJAX(FALSE, $error);
}

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
$studienplan->loadStudienplanLehrveranstaltung($studienplan_lehrveranstaltung_id);

$stpl = new StudienplanAddonStgv();
$stpl->loadStudienplan($studienplan->studienplan_id);

$studienordnung = new StudienordnungAddonStgv();
$studienordnung->loadStudienordnung($stpl->studienordnung_id);

if($studienordnung->status_kurzbz != "development")
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienpläne in diesem Status zu ändern.", "detail"=>"stgv/changeStudienplan");
    returnAJAX(FALSE, $error);
}

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