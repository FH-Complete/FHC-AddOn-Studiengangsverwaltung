<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');

require_once('../../functions.php');
require_once('../../../../include/studiengangsgruppe.class.php');

$studiengangsgruppe_id = filter_input(INPUT_GET, "studiengangsgruppe_id");

if(is_null($studiengangsgruppe_id))
{
    returnAJAX(false, "Variable studiengangsgruppe_id nicht gesetzt");    
}
elseif($studiengangsgruppe_id == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$studiengangsgruppe = new studiengangsgruppe();
$studiengangsgruppe->getAll();
$data = $studiengangsgruppe->getStudiengangsgruppenTreeChildren($studiengangsgruppe_id);

returnAJAX(true, $data);