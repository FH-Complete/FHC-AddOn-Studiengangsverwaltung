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
require_once('../../../../../include/studiengang.class.php');
require_once('../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("addon/studiengangsverwaltung",null,"suid"))
{
	$error = array("message"=>"Sie haben keine Berechtigung für diese Anwendung.", "detail"=>"addon/studiengangsverwaltung.");
	returnAJAX(FALSE, $error);
}
$stg_kz_array = $berechtigung->getStgKz("addon/studiengangsverwaltung");

$studiengang = new studiengang();
$studiengang->loadArray($stg_kz_array, "kurzbzlang", true);

$data =  $studiengang->result;
returnAJAX(true, $data);
?>
