<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/lehrveranstaltung.class.php');

require_once('../functions.php');

$filter = filter_input(INPUT_POST, "filter");

if(is_null($filter))
{
    returnAJAX(false, "Variable filter nicht gesetzt");    
}
elseif($filter == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$lehrveranstaltung = new lehrveranstaltung();
$lehrveranstaltung->search($filter);

$lv_array = array();

foreach($lehrveranstaltung->lehrveranstaltungen as $key=>$lv)
{
    $temp = new stdClass();
    $temp->lehrveranstaltung_id = $lv->lehrveranstaltung_id;
    $temp->bezeichnung = $lv->bezeichnung;
    array_push($lv_array, $temp);
}
returnAJAX(true, $lv_array)


?>