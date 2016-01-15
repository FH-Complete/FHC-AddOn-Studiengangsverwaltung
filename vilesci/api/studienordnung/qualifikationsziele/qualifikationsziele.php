<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/akadgrad.class.php');
require_once('../../../../../../include/studiensemester.class.php');

require_once('../../../../include/Qualifikationsziel.class.php');
//TODO functions from core?
require_once('../../functions.php');

//TODO
$DEBUG = true;

$stoId = filter_input(INPUT_GET, "stoId");

if(is_null($stoId))
{
    returnAJAX(false, "Variable stoId nicht gesetzt");    
}
elseif(($stoId == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$qualifikationsziel = new Qualifikationsziel();
$qualifikationsziel->getAll($stoId);

$data = $qualifikationsziel->result;


returnAJAX(true, $data);
?>