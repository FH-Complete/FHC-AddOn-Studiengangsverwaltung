<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/lehrveranstaltung.class.php');

require_once('../functions.php');

$lehrveranstaltung = new lehrveranstaltung();
$lehrveranstaltung->load_lva(257);

$lv_array = array();

foreach($lehrveranstaltung->lehrveranstaltungen as $key=>$lv)
{
    $temp = new stdClass();
    $temp->id = $lv->lehrveranstaltung_id;
    $temp->name = $lv->bezeichnung;
    array_push($lv_array, $temp);
    if($key==10)
	break;
}
returnAJAX(true, $lv_array)


?>