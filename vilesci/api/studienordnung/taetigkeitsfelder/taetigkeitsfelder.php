<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/akadgrad.class.php');
require_once('../../../../../../include/studiensemester.class.php');
require_once('../../../../include/Taetigkeitsfeld.class.php');
require_once('../../functions.php');

$sto_array = array();

$stoId = filter_input(INPUT_GET, "stoId");

if(is_null($stoId))
{
    returnAJAX(false, "Variable stoId nicht gesetzt");    
}
elseif(($stoId == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$taetigkeitsfeld = new taetigkeitsfeld();
$taetigkeitsfeld->getAll($stoId);

//$studienordnung = new StudienordnungAddonStgv();
//$studienordnung->loadStudienordnung($stoId);
//
//$akadgrad = new akadgrad();
//$akadgrad->load($studienordnung->akadgrad_id);
//$akadgrad->getAll();
//$studiensemester = new studiensemester();
//$studiensemester->getAll();

$data = $taetigkeitsfeld->result;


returnAJAX(true, $data);
?>