<?php
/*
 * Copyright (C) 2018 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA
 * .
 * Authors: Stefan Puraner <stefan.puraner@technikum-wien.at>,
 *			Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/dms.class.php');
require_once('../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/uploadDokumente",null,"suid"))
{
	$error = array("message"=>"Sie haben nicht die Berechtigung um Dokumente hochzuladen.", "detail"=>"stgv/uploadDokumente");
	returnAJAX(FALSE, $error);
}

$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$filename = uniqid();
$filename.="." . $ext;
$uploadfile = DMS_PATH . $filename;

if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile))
{
	$dms = new dms();
	if (!$dms->setPermission($uploadfile))
	{
		$error = array("message"=>"Fehler beim Speichern des Dokuments.", "detail"=>$dms->errormsg);
		returnAJAX(false, $error);
	}
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
	}
	else
	{
		$error = array("message"=>"Fehler beim Speichern des Dokuments.", "detail"=>$dms->errormsg);
		returnAJAX(false, $error);
	}
}
