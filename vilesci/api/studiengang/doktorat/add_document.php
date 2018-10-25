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
require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/dms.class.php');

require_once('../../../../include/doktorat.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/uploadDokument",null,"suid"))
{
	$error = array("message"=>"Sie haben nicht die Berechtigung um Dokumente hochzuladen.", "detail"=>"stgv/uploadDokument");
	returnAJAX(FALSE, $error);
}

$doktorat_id = filter_input(INPUT_POST, "doktorat_id");
$dms_id = filter_input(INPUT_POST, "dms_id");

if(is_null($doktorat_id))
{
	returnAJAX(false, "Variable doktorat_id nicht gesetzt");
}
elseif(is_null($dms_id))
{
	returnAJAX(false, "Variable dms_id nicht gesetzt");
}
elseif(($doktorat_id == false) || ($dms_id == false))
{
	returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

$dms = new dms();
if(!$dms->load($dms_id))
	returnAJAX(false, "Fehler beim Laden der Daten");

if($dms->kategorie_kurzbz != 'studiengangsverwaltung')
	returnAJAX(false, "Dieser Vorgang ist nicht erlaubt");

$doktorat = new doktorat();
$doktorat->load($doktorat_id);
if (!$doktorat->saveDokument($dms_id))
{
	$error = array("message"=>"Fehler beim Verknüpfen des Dokuments mit der Doktoratstudienverordnung.", "detail"=>$doktorat->errormsg);
	returnAJAX(false, $error);
}
else
{
	returnAJAX(true, "Dokument erfolgreich gespeichert.");
}
?>
