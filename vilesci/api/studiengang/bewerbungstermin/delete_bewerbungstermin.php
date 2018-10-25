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
require_once('../../../../../../include/bewerbungstermin.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/deleteBewerbungsfrist",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Bewerbungsfristen zu löschen.", "detail"=>"stgv/deleteBewerbungsfrist");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$bewerbungstermin = mapDataToBewerbungstermin($data);
$bewerbungstermin_id = $bewerbungstermin->bewerbungstermin_id;

if($bewerbungstermin->delete($bewerbungstermin_id))
{
    returnAJAX(true, "Bewerbungstermin erfolgreich gelöscht.");
}
else
{
    $error = array("message"=>"Fehler beim Löschen des Bewerbungstermins.", "detail"=>$bewerbungstermin->errormsg);
    returnAJAX(false, $error);
}


function mapDataToBewerbungstermin($data)
{
    $bt = new bewerbungstermin();
    $bt->bewerbungstermin_id = $data->bewerbungstermin_id;
    return $bt;
}

?>
