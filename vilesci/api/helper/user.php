<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/benutzer.class.php');
require_once('../../../../../include/person.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$uid = get_uid();
$benutzer = new benutzer();
if($benutzer->load($uid))
{
    $person = new person();
    if($person->load($benutzer->person_id))
    {
	$data["vorname"] = $person->vorname;
	$data["nachname"] = $person->nachname;
	returnAJAX(true, $data);
    }
    else
    {
	$error = array("message"=>"Personendaten konnten nicht geladen werden.","detail"=>$person->errormsg);
	returnAJAX(false, $error);
    }
}
else
{
    $error = array("message"=>"Benutzer konnte nicht geladen werden.","detail"=>$benutzer->errormsg);
    returnAJAX(false, $error);
}
?>