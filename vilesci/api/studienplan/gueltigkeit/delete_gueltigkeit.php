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