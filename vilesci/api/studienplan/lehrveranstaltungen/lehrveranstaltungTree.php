<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/lehrveranstaltung.class.php');

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

$lehrveranstaltung = new lehrveranstaltung();
$data = $lehrveranstaltung->getLvTree($studienplan_id);

returnAJAX(true, $data);
?>