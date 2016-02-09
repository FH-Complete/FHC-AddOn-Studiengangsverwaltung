<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/Aufnahmeverfahren.class.php');
require_once('../../functions.php');

$sto_array = array();

$studienordnung_id = filter_input(INPUT_GET, "studienordnung_id");

if(is_null($studienordnung_id))
{
    returnAJAX(false, "Variable studienordnung_id nicht gesetzt");    
}
elseif(($studienordnung_id == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$aufnahmeverfahren = new aufnahmeverfahren();
$aufnahmeverfahren->getAll($studienordnung_id);

$data = $aufnahmeverfahren->result;


returnAJAX(true, $data);
?>