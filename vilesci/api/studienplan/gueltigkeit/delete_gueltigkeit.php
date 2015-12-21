<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');

require_once('../../../../include/StudienplanAddonStgv.class.php');
require_once('../../../../include/StudienordnungAddonStgv.class.php');
//TODO functions from core?
require_once('../../functions.php');

//TODO
$DEBUG = true;

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/changeStudienplan",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienpläne zu ändern.", "detail"=>"stgv/changeStudienplan");
    returnAJAX(FALSE, $error);
}

$studienplan_id = filter_input(INPUT_GET, "studienplan_id");
$studiensemester_kurzbz = filter_input(INPUT_GET, "studiensemester_kurzbz");

if(is_null($studienplan_id))
{
    returnAJAX(false, "Variable studienplan_id nicht gesetzt");    
}
elseif(is_null($studiensemester_kurzbz))
{
    returnAJAX(false, "Variable studiensemester_kurzbz nicht gesetzt");
}
elseif($studienplan_id == false || $studiensemester_kurzbz == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$studienplan = new StudienplanAddonStgv(); 
$studienplan->loadStudienplan($studienplan_id);
$studienordnung = new StudienordnungAddonStgv();
$studienordnung->loadStudienordnung($studienplan->studienordnung_id);

if($studienordnung->status_kurzbz !== "development")
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienpläne in diesem Status zu ändern.", "detail"=>"stgv/changeStudienplan");
    returnAJAX(FALSE, $error);
}

if($studienplan->deleteSemesterZuordnung($studienplan_id, $studiensemester_kurzbz))
{
    returnAJAX(true, "Zuordnung erfolgreich gelöscht.");
}
else
{
    $error = array("message"=>"Fehler beim Löschen der Zuordnung.", "detail"=>$studienplan->errormsg);
    returnAJAX(false, $error);
}

?>