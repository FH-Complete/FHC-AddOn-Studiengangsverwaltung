<?php
/* Copyright (C) 2018 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 */
require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/dms.class.php');
require_once('../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/downloadDokumente",null,"suid"))
{
	$error = array("message"=>"Sie haben nicht die Berechtigung um Dokumente herunterzuladen.", "detail"=>"stgv/downloadDokumente");
	returnAJAX(FALSE, $error);
}

$dms_id = filter_input(INPUT_GET, "dms_id");

if (is_null($dms_id))
{
	returnAJAX(false, "Variable dms_id nicht gesetzt");
}
elseif (($dms_id == false))
{
	returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

// Pruefen ob es ein Dokument des Addons ist
// sonst zugriff verbieten
$db = new basis_db();
$qry = "
	SELECT dms_id FROM addon.tbl_stgv_studienordnung_dokument WHERE dms_id=".$db->db_add_param($dms_id)."
	UNION
	SELECT dms_id FROM addon.tbl_stgv_foerdervertrag_dokument WHERE dms_id=".$db->db_add_param($dms_id)."
	UNION
	SELECT dms_id FROM addon.tbl_stgv_doktorat_dokument WHERE dms_id=".$db->db_add_param($dms_id);

if($result = $db->db_query($qry))
{
	if($db->db_num_rows($result) == 0)
	{
		die('Permission denied');
	}
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
