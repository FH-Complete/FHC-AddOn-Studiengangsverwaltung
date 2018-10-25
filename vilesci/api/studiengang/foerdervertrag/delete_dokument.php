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

require_once('../../../../include/foerdervertrag.class.php');

require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/deleteDokument",null,"suid"))
{
	$error = array("message"=>"Sie haben nicht die Berechtigung um Dokumente zu löschen.", "detail"=>"stgv/deleteDokument");
	returnAJAX(FALSE, $error);
}

$dms_id = filter_input(INPUT_GET, "dms_id");
$foerdervertrag_id = filter_input(INPUT_GET, "foerdervertrag_id");

if (is_null($dms_id))
{
	returnAJAX(false, "Variable dms_id nicht gesetzt");
}
elseif (is_null($foerdervertrag_id))
{
	returnAJAX(false, "Variable foerdervertrag_id nicht gesetzt");
}
elseif (($dms_id == false) || ($foerdervertrag_id == false))
{
	returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

$foerdervertrag = new foerdervertrag();
if(!$foerdervertrag->DokumentExists($foerdervertrag_id, $dms_id))
{
	returnAJAX(false, "Dieser Vorgang ist nicht erlaubt");
}

if($foerdervertrag->deleteDokument($foerdervertrag_id, $dms_id))
{
	returnAJAX(true,"Dokument erfolgreich gelöscht.");
}
else
{
	$error = array("message"=>"Fehler beim Löschen des Dokuments.", "detail"=>$foerdervertrag->errormsg);
	returnAJAX(false, $error);
}
