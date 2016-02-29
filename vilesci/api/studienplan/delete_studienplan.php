<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../include/studienplanAddonStgv.class.php');
require_once('../../../include/studienordnungAddonStgv.class.php');
require_once('../../../include/auslandssemester.class.php');
require_once('../../../include/berufspraktikum.class.php');
require_once('../../../include/studienjahr.class.php');
require_once('../functions.php');

$uid = get_uid();

$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if($berechtigung->isBerechtigt("stgv/deleteStudienplan", null, "suid"))
{
    $studienplan_id = filter_input(INPUT_GET, "studienplan_id");

    if(is_null($studienplan_id))
    {
	returnAJAX(false, "Variable studienplan_id nicht gesetzt");    
    }
    elseif($studienplan_id == false)
    {
	returnAJAX(false, "Fehler beim lesen der GET Variablen");    
    }

    $studienordnung = new StudienordnungAddonStgv();
    $studienordnung->getStudienordnungFromStudienplan($studienplan_id);
    
    $auslandssemester = new auslandssemester();
    $auslandssemester->getAll($studienplan_id);
    
    $berufspraktikum = new berufspraktikum();
    $berufspraktikum->getAll($studienplan_id);
    
    $studienjahr = new studienjahr();
    $studienjahr->getAll($studienplan_id);

    if($studienordnung->status_kurzbz == "development")
    {
	$studienplan = new StudienplanAddonStgv();
	
	if(!$studienplan->hasSemesterZugeordnet($studienplan_id))
	{
	    $studienplan->loadStudienplanLV($studienplan_id);
	    if(count($studienplan->result) > 0)
	    {
		$error = array("message"=>"Studienplan kann nicht gelöscht werden. Es sind noch Lehrveranstaltungen verknüpft.", "detail"=>$studienplan->errormsg);
		returnAJAX(false, $error);
	    }
	    
	    foreach($auslandssemester->result as $t)
	    {
		$auslandssemester->delete($t->auslandssemester_id);
	    }
	    
	    foreach($berufspraktikum->result as $t)
	    {
		$berufspraktikum->delete($t->berufspraktikum_id);
	    }
	    
	    foreach($studienjahr->result as $t)
	    {
		$studienjahr->delete($t->studienjahr_id);
	    }
	    

	    if($studienplan->delete($studienplan_id))
	    {
		returnAJAX(true, "Studienplan erfolgreich gelöscht");
	    }
	    else
	    {
		$error = array("message"=>"Fehler beim Löschen des Studienplans.", "detail"=>$studienplan->errormsg);
		returnAJAX(false, $error);
	    }
	}
	else
	{
	    $error = array("message"=>"Studienplan kann nicht gelöscht werden. Es sind Studiensemester mit dem Studienplan verknüpft.", "detail"=>$studienplan->errormsg);
	    returnAJAX(false, $error);
	}
    }
    else
    {
	$error = array("message"=>"Studienplan kann in diesem Status nicht gelöscht werden." , "detail"=>"");
	returnAJAX(false, $error);
    }
}
 else
{
    $error = array("message"=>"Sie haben keine Berechtigung.", "detail"=>"stgv/deleteStudienplan");
    returnAJAX(false, $error);
}

?>