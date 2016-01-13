<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/dms.class.php');

require_once('../../../../include/foerdervertrag.class.php');
require_once('../../functions.php');

//TODO Berechtigungen

$uid = get_uid();
$foerdervertrag_id = filter_input(INPUT_POST, "foerdervertrag_id");
$dms_id = filter_input(INPUT_POST, "dms_id");

if(is_null($foerdervertrag_id))
{
    returnAJAX(false, "Variable foerdervertrag_id nicht gesetzt");    
}
elseif(is_null($dms_id))
{
    returnAJAX(false, "Variable dms_id nicht gesetzt");    
}
elseif(($foerdervertrag_id == false) || ($dms_id == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$foerdervertrag = new foerdervertrag();
$foerdervertrag->load($foerdervertrag_id);
if (!$foerdervertrag->saveDokument($dms_id))
{
    $error = array("message"=>"Fehler beim Verknüpfen des Dokuments mit dem Fördervertrag.", "detail"=>$foerdervertrag->errormsg);
    returnAJAX(false, $error);
} 
else
{
    returnAJAX(true, "Dokument erfolgreich gespeichert.");
}
?>
