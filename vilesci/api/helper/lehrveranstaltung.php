<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/lehrveranstaltung.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$lehrveranstaltung = new lehrveranstaltung();
$lehrveranstaltung->load_lva(257);

$lv_array = array();

foreach($lehrveranstaltung->lehrveranstaltungen as $key=>$lv)
{
    $temp = new stdClass();
    $temp->id = $lv->lehrveranstaltung_id;
    $temp->text = $lv->bezeichnung;
    array_push($lv_array, $temp);
}
returnAJAX(true, $lv_array)


?>