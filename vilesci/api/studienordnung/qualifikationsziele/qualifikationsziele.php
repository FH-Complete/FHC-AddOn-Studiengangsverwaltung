<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/akadgrad.class.php');
require_once('../../../../../../include/studiensemester.class.php');
require_once('../../../../include/qualifikationsziel.class.php');
require_once('../../functions.php');

$studienordnung_id = filter_input(INPUT_GET, "studienordnung_id");

if(is_null($studienordnung_id))
{
    returnAJAX(false, "Variable studienordnung_id nicht gesetzt");    
}
elseif(($studienordnung_id == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$qualifikationsziel = new Qualifikationsziel();
$qualifikationsziel->getAll($studienordnung_id);

$data = $qualifikationsziel->result;


returnAJAX(true, $data);
?>