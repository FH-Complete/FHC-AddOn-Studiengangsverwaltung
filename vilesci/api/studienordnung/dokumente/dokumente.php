<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/dms.class.php');

require_once('../../../../include/studienordnungAddonStgv.class.php');

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

$studienordnung = new StudienordnungAddonStgv();
$studienordnung->getDokumente($studienordnung_id);

$data = array();

foreach($studienordnung->dokumente as $dms_id)
{
    $dms = new dms();
    $dms->load($dms_id);
    array_push($data, $dms);
}

returnAJAX(true, $data);
?>