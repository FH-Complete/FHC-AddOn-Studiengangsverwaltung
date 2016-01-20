<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/akadgrad.class.php');
require_once('../../../../../../include/studiensemester.class.php');
require_once('../../../../include/Auslandssemester.class.php');
require_once('../../functions.php');

$studienplan_id = filter_input(INPUT_GET, "studienplan_id");

if(is_null($studienplan_id))
{
    returnAJAX(false, "Variable studienplan_id nicht gesetzt");    
}
elseif(($studienplan_id == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$auslandssemester = new auslandssemester();
$auslandssemester->getAll($studienplan_id);

$data = $auslandssemester->result;


returnAJAX(true, $data);
?>