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
require_once('../../../../include/doktorat.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/deleteDoktorat",null,"suid"))
{
	$error = array("message"=>"Sie haben nicht die Berechtigung um Doktoratsstudienverordnungen zu löschen.", "detail"=>"stgv/deleteDoktorat");
	returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$doktorat = mapDataToDoktorat($data);
$doktorat_id = $doktorat->doktorat_id;

$doktorat->getDokumente($doktorat_id);
foreach($doktorat->dokumente as $dms_id)
{
	if(!$doktorat->deleteDokument($doktorat_id, $dms_id))
	{
		$error = array("message"=>"Fehler beim Löschen der Dokumente.", "detail"=>$doktorat->errormsg);
		returnAJAX(false, $error);
	}
}

if($doktorat->delete($doktorat_id))
{
	returnAJAX(true, "Doktorat erfolgreich gelöscht.");
}
else
{
	$error = array("message"=>"Fehler beim Löschen des Doktorats.", "detail"=>$doktorat->errormsg);
	returnAJAX(false, $error);
}


function mapDataToDoktorat($data)
{
	$d = new doktorat();
	$d->doktorat_id = $data->doktorat_id;
	return $d;
}

?>
