<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/akadgrad.class.php');
require_once('../../../../../../include/studiensemester.class.php');
require_once('../../../../include/taetigkeitsfeld.class.php');
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

$taetigkeitsfeld = new taetigkeitsfeld();
$taetigkeitsfeld->getAll($studienordnung_id);

//$studienordnung = new StudienordnungAddonStgv();
//$studienordnung->loadStudienordnung($studienordnung_id);
//
//$akadgrad = new akadgrad();
//$akadgrad->load($studienordnung->akadgrad_id);
//$akadgrad->getAll();
//$studiensemester = new studiensemester();
//$studiensemester->getAll();

$data = $taetigkeitsfeld->result;


returnAJAX(true, $data);
?>