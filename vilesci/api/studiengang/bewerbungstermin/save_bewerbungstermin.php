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
if(!$berechtigung->isBerechtigt("stgv/editBewerbungsfrist",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Bewerbungsfristen anzulegen oder zu editieren.", "detail"=>"stgv/editBewerbungsfrist");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$bewerbungstermin = mapDataToBewerbungstermin($data);
if($bewerbungstermin->save())
{
    returnAJAX(true, "Bewerbungstermin erfolgreich gepspeichert.");
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Bewerbungstermins.", "detail"=>$bewerbungstermin->errormsg);
    returnAJAX(false, $error);
}

function mapDataToBewerbungstermin($data)
{
    $bt = new bewerbungstermin();
    $bt->new = true;
    $bt->studiengang_kz = $data->studiengang_kz;
    $bt->studiensemester_kurzbz = $data->studiensemester_kurzbz;
    $bt->anmerkung = $data->anmerkung;
    $bt->beginn = $data->beginn;
    $bt->ende = $data->ende;
    $bt->insertvon = get_uid();
    $bt->nachfrist_ende = $data->nachfrist_ende;
    $bt->nachfrist = parseBoolean($data->nachfrist);
	$bt->studienplan_id = $data->studienplan_id;
    return $bt;
}

?>
