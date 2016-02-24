<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/studiengangsgruppe.class.php');

require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/deleteStudiengangsgruppen",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studiengangsgruppen zu löschen.", "detail"=>"stgv/deleteStudiengangsgruppen");
    returnAJAX(FALSE, $error);
}

$studiengang_kz = filter_input(INPUT_GET, "studiengang_kz");

if(is_null($studiengang_kz))
{
    returnAJAX(false, "Variable studiengang_kz nicht gesetzt");    
}
elseif($studiengang_kz == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$studiengangsgruppe = new studiengangsgruppe();

if($studiengangsgruppe->deleteZuordnung($studiengang_kz))
{
    returnAJAX(true, "Zuordnung erfolgreich gelöscht.");
}
else
{
    $error = array("message"=>"Fehler beim Löschen der Zuordnung.", "detail"=>$studiengangsgruppe->errormsg);
    returnAJAX(false, $error);
}


function mapDataToReihungstest($data)
{
    $rt = new reihungstest();
    $rt->reihungstest_id = $data->reihungstest_id;
    return $rt;
}

?>