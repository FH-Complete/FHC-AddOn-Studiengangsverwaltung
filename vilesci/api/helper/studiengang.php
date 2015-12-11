<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/studiengang.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$uid = get_uid();
$uid = "kofler";
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("addon/studiengangsverwaltung",null,"suid"))
{
    $error = array("message"=>"Sie haben keine Berechtigung für diese Anwendung.", "detail"=>"addon/studiengangsverwaltung.");
    returnAJAX(FALSE, $error);
}
$stg_kz_array = $berechtigung->getStgKz("addon/studiengangsverwaltung");

$studiengang = new studiengang();
$studiengang->loadArray($stg_kz_array, "kurzbzlang", true);

$data =  $studiengang->result;
returnAJAX(true, $data);
?>