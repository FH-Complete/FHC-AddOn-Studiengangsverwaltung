<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');

require_once('../../../../include/StudienplanAddonStgv.class.php');
require_once('../../functions.php');

$studienplan_id = filter_input(INPUT_GET, "studienplan_id");

if (is_null($studienplan_id)) {
    returnAJAX(false, "Variable studienplan_id nicht gesetzt");
} elseif (($studienplan_id == false)) {
    returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

$studienplan = new StudienplanAddonStgv();
$result = array();
$studiensemester = $studienplan->loadStudiensemesterFromStudienplan($studienplan_id);
if ($studiensemester != FALSE) {
    foreach ($studiensemester as $sem) {
	$semester = $studienplan->loadAusbildungsemesterFromStudiensemester($studienplan_id, $sem);
	if($semester != FALSE)
	{
	    $result[$sem]["studiensemester_kurzbz"] = $sem;
	    $result[$sem]["ausbildungssemester"] = $semester;
	}
	else
	{
	    $error = array("message"=>"Fehler beim Laden der Zuordnung.", "detail"=>$studienplan->errormsg);
	    returnAJAX(false, $error);
	}
    }
     returnAJAX(true, $result);
}
elseif(empty($studiensemester))
{
    returnAJAX(true, $result);
}
else
{
    $error = array("message"=>"Fehler beim Laden der Zuordnung.", "detail"=>$studienplan->errormsg);
    returnAJAX(false, $error);
}