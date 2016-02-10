<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/dms.class.php');

require_once('../../../../include/foerdervertrag.class.php');

require_once('../../functions.php');
//TODO Berechtigung

$dms_id = filter_input(INPUT_GET, "dms_id");
$foerdervertrag_id = filter_input(INPUT_GET, "foerdervertrag_id");

if (is_null($dms_id))
{
    returnAJAX(false, "Variable dms_id nicht gesetzt");
} 
elseif (is_null($foerdervertrag_id))
{
    returnAJAX(false, "Variable foerdervertrag_id nicht gesetzt");
} 
elseif (($dms_id == false) || ($foerdervertrag_id == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

$foerdervertrag = new foerdervertrag();
if($foerdervertrag->deleteDokument($foerdervertrag_id, $dms_id))
{
    returnAJAX(true,"Dokument erfolgreich gelöscht.");
}
else
{
    $error = array("message"=>"Fehler beim Löschen des Dokuments.", "detail"=>$foerdervertrag->errormsg);
    returnAJAX(false, $error);
}
