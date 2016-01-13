<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/dms.class.php');

require_once('../functions.php');
//TODO Berechtigung

$dms_id = filter_input(INPUT_GET, "dms_id");

if (is_null($dms_id))
{
    returnAJAX(false, "Variable dms_id nicht gesetzt");
} elseif (($dms_id == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

$dms = new dms();
if (!$dms->load($dms_id))
    die('Kein Dokument vorhanden');

$filename = DMS_PATH . $dms->filename;

if (file_exists($filename))
{
    if ($handle = fopen($filename, "r"))
    {
	if ($dms->mimetype == '')
	    $dms->mimetype = 'application/octetstream';

	header('Content-type: ' . $dms->mimetype);
	header('Content-Disposition: inline; filename="' . $dms->name . '"');
	header('Content-Length: ' . filesize($filename));

	while (!feof($handle))
	{
	    echo fread($handle, 8192);
	}
	fclose($handle);
    } 
    else
    {
	echo 'Fehler: Datei konnte nicht geoeffnet werden';
    }
} 
else
{
    echo 'Die Datei existiert nicht';
}
