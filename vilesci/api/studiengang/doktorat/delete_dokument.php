<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/dms.class.php');

require_once('../../../../include/doktorat.class.php');

require_once('../../functions.php');
//TODO Berechtigung

$dms_id = filter_input(INPUT_GET, "dms_id");
$doktorat_id = filter_input(INPUT_GET, "doktorat_id");

if (is_null($dms_id))
{
    returnAJAX(false, "Variable dms_id nicht gesetzt");
} 
elseif (is_null($doktorat_id))
{
    returnAJAX(false, "Variable doktorat_id nicht gesetzt");
} 
elseif (($dms_id == false) || ($doktorat_id == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

$doktorat = new doktorat();
if($doktorat->deleteDokument($doktorat_id, $dms_id))
{
    returnAJAX(true,"Dokument erfolgreich gelöscht.");
}
else
{
    $error = array("message"=>"Fehler beim Löschen des Dokuments.", "detail"=>$doktorat->errormsg);
    returnAJAX(false, $error);
}
