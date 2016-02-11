<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/dms.class.php');

require_once('../../../../include/doktorat.class.php');
require_once('../../functions.php');

//TODO Berechtigungen

$uid = get_uid();
$doktorat_id = filter_input(INPUT_POST, "doktorat_id");
$dms_id = filter_input(INPUT_POST, "dms_id");

if(is_null($doktorat_id))
{
    returnAJAX(false, "Variable doktorat_id nicht gesetzt");    
}
elseif(is_null($dms_id))
{
    returnAJAX(false, "Variable dms_id nicht gesetzt");    
}
elseif(($doktorat_id == false) || ($dms_id == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$doktorat = new doktorat();
$doktorat->load($doktorat_id);
if (!$doktorat->saveDokument($dms_id))
{
    $error = array("message"=>"Fehler beim Verknüpfen des Dokuments mit der Doktoratstudienverordnung.", "detail"=>$doktorat->errormsg);
    returnAJAX(false, $error);
} 
else
{
    returnAJAX(true, "Dokument erfolgreich gespeichert.");
}
?>
