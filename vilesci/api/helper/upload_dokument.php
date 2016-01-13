<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/dms.class.php');

require_once('../functions.php');

//TODO Berechtigungen

$uid = get_uid();

$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$filename = uniqid();
$filename.="." . $ext;
$uploadfile = DMS_PATH . $filename;

if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile))
{
    //TODO chgrp fails
//    if (!chgrp($uploadfile, 'dms'))
//	echo 'CHGRP failed';
    if (!chmod($uploadfile, 0774))
	echo 'CHMOD failed';
    exec('sudo chown wwwrun ' . $uploadfile);

    $dms = new dms();
    $dms->version = '0';
    $dms->kategorie_kurzbz = "studiengangsverwaltung";
    $dms->insertamum = date('Y-m-d H:i:s');
    $dms->insertvon = $uid;
    $dms->mimetype = $_FILES['file']['type'];
    $dms->filename = $filename;
    $dms->name = $_FILES['file']['name'];

    if ($dms->save(true))
    {
	$dms_id = $dms->dms_id;
	
	returnAJAX(true, $dms_id);

//	$sto = new StudienordnungAddonStgv();
//	$sto->loadStudienordnung($studienordnung_id);
//	if (!$sto->saveDokument($dms_id))
//	{
//	    $error = array("message"=>"Fehler beim Verknüpfen des Dokuments mit der Studienordnung.", "detail"=>$sto->errormsg);
//	    returnAJAX(false, $error);
//	} 
//	else
//	{
//	    returnAJAX(true, "Dokument erfolgreich gespeichert.");
//	}
    }
    else
    {
	$error = array("message"=>"Fehler beim Speichern des Dokuments.", "detail"=>$dms->errormsg);
	returnAJAX(false, $error);
    }
}
?>
