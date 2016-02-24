<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once ('../../../include/studienplanAddonStgv.class.php');
require_once('../functions.php');

$studienordnung_id = filter_input(INPUT_GET, "studienordnung_id");

if(is_null($studienordnung_id))
{
    returnAJAX(false, "Variable studienordnung_id nicht gesetzt");    
}
elseif($studienordnung_id == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}


$studienplan = new StudienplanAddonStgv();
$studienplan->loadStudienplanSTO($studienordnung_id);

$data = array();
foreach($studienplan->result as $key=>$stpl)
{
   $data[$key]["studienplan_id"] = $stpl->studienplan_id;
   $data[$key]["bezeichnung"] = $stpl->bezeichnung;
}

returnAJAX(true, $data);
	    

?>