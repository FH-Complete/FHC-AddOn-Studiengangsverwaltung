<?php
/* Copyright (C) 2015 fhcomplete.org
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
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if (!$rechte->isBerechtigt('basis/addon')) {
    die('Sie haben keine Berechtigung fuer diese Seite');
}
?>
<!DOCTYPE html>
<html ng-app="stgv2">
    <head>
	<meta charset="UTF-8">
	<title>Studiengangsverwaltung 2</title>
	<!--<link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css">-->
	<!--<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">-->
	
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="./css/bootstrap-3.3.5/dist/css/bootstrap.css">
	<link rel="stylesheet" href="./css/bootstrap-3.3.5/dist/css/bootstrap-theme.css">

	<!-- Easy UI CSS -->
	<link rel="stylesheet" type="text/css" href="./js/jquery-easyui-1.4.4/themes/default/easyui.css">
	<link rel="stylesheet" type="text/css" href="./js/jquery-easyui-1.4.4/themes/icon.css">
	<link rel="stylesheet" type="text/css" href="./js/jquery-easyui-1.4.4/themes/color.css">
	
	<!-- JQuery UI CSS -->
	<link rel="stylesheet" type="text/css" href="./js/jquery-ui-1.11.4.custom/jquery-ui.css">

	<!-- App CSS -->
	<link rel="stylesheet" type="text/css" href="./css/style.css">

	<!-- jQuery -->
	<script type="text/javascript" src="./js/jquery-1.11.3.js"></script>

	<!-- Easy UI JS -->
	<script type="text/javascript" src="./js/jquery-easyui-1.4.4/jquery.min.js"></script>
	<script type="text/javascript" src="./js/jquery-easyui-1.4.4/jquery.easyui.min.js"></script>
	
	<!-- JQuery UI -->
	<script type="text/javascript" src="./js/jquery-ui-1.11.4.custom/jquery-ui.js"></script>

	<!-- Bootstrap JS -->
	<script src="./css/bootstrap-3.3.5/dist/js/bootstrap.js"></script>
	
	<!-- JavaScript Helper-->
	<script type="text/javascript" src="./js/functions.js"></script>

	<!-- Angular JS -->
	<script type="text/javascript" src="./js/angular/1.4.7/angular.js"></script>
	<script type="text/javascript" src="./js/angular/1.4.7/angular-route.js"></script>
	<script type="text/javascript" src="./js/angular/1.4.7/angular-animate.js"></script>
	<script type="text/javascript" src="./js/angular/1.4.7/angular-sanitize.js"></script>
	<script type="text/javascript" src="./js/angular/ui-router/release/angular-ui-router.js"></script>
	<script type="text/javascript" src="./js/angular/ui.bootstrap/ui-bootstrap-0.14.2.min.js"></script>
	<script type="text/javascript" src="./js/angular/app.js"></script>
	<script type="text/javascript" src="./js/angular/states.js"></script>
	
	<!-- Error Controller -->
	<script type="text/javascript" src="./js/angular/controllers/error-controller.js"></script>
	
	<!-- Angular Studiengang Controllers -->
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-main-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-stammdaten-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-betriebsdaten-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-reihungstest-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-bewerbung-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-kosten-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-foerderungen-controller.js"></script>

	<!-- Angular Studienordnung Controllers -->
	<script type="text/javascript" src="./js/angular/controllers/studienordnung/sto-neu-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienordnung/sto-metadaten-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienordnung/sto-dokumente-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienordnung/sto-eckdaten-controller.js"></script>

	<!-- Angular Studienplan Controllers -->
	<script type="text/javascript" src="./js/angular/controllers/studienplan/stpl-neu-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienplan/stpl-metadaten-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienplan/stpl-eckdaten-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienplan/stpl-gueltigkeit-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienplan/stpl-lehrveranstaltung-controller.js"></script>

	<!-- Angular State Controllers -->
	<script type="text/javascript" src="./js/angular/controllers/state/state-main-controller.js"></script>
	
	<!-- Services -->
	<script type="text/javascript" src="./js/angular/services/errorService.js"></script>
	
	<!-- Directives -->
	<script type="text/javascript" src="./js/angular/directives/error-directive.js"></script>
    </head>
    <body>
	<div id='layoutWrapper' class="easyui-layout">
	    <div id="north" data-options="region:'north'" ng-controller="TabsCtrl as tabCtrl">
		<div class="easyui-panel" style="padding:5px;">
		    <!--<a ng-repeat="button in buttons" href="#" class="easyui-linkbutton" data-options="plain:true">{{button.label}}</a>-->
		    <a href="#" class="easyui-menubutton" data-options="menu:'#mm1'">Neu</a>
		    <a href="#" class="easyui-menubutton" data-options="menu:'#mm2'">Bearbeiten</a>
		    <a href="#" class="easyui-menubutton" data-options="menu:'#mm3'">Status</a>
		    <a href="#" class="easyui-menubutton" data-options="menu:'#mm4',iconCls:'icon-help'">About</a>
		</div>
		<div id="mm1" style="width:150px;">
		    <div ng-click='tabCtrl.createStudienordnung()'>Studienordnung</div>
		    <div ng-click='tabCtrl.createStudienplan()'>Studienplan</div>
		</div>
		<div id="mm2" style="width:100px;">
		    <div data-options="iconCls:'icon-remove'">Löschen</div>
		</div>
		<div id="mm3" style="width:150px;">
		    <div>
			<span>Status ändern zu</span>
			<div>
			    <div>in Bearbeitung</div>
			    <div>in Review</div>
			    <div>genehmigt</div>
			    <div>nicht genehmigt</div>
			    <div>ausgelaufen</div>
			</div>
		    </div>
		</div>
		<div id="mm4" class="menu-content" style="background:#f0f0f0;padding:10px;text-align:left">
		    <img src="http://fhcomplete.org/img/FHC-LogoOhneText.svg" style="width:38px;height:38px">
		    <p style="font-size:14px;color:#444;"><a href="http://www.fhcomplete.org" target="_blank">fhcomplete.org</a></p>
		</div>
		<!--<ul class="nav nav-pills" ng-controller="TabsCtrl">
		    <li ng-repeat="tab in tabs" tab="tab" ng-class="getTabClass(tab)"><button type="button" class="btn btn-default" ng-click="setSelectedTab(tab)">{{tab.label}}</button></li>
		</ul>-->
	    </div>
	    <div id="west"data-options="region:'west', split: true, maxWidth: 400" ng-controller="TreeCtrl">
		<ul id="west_tree"class="easyui-tree"></ul>
	    </div>
	    <div id="footer" data-options="region:'south'" style="height: 5%;">
		<!--TODO zusätzliche Daten anzeigen; z.B.: Username, DB, etc-->
		<div id="user">User: Stefan Puraner</div>
	    </div>
	    <div id="center" data-options="region:'center'">
		<div id="centerLayout" class="easyui-layout" fit="true">
		    <div id="centerNorth" data-options="region:'north', split:true, height: 200" border="false" ng-controller="TreeGridCtrl as gridCtrl" >
			<!--<div id="treeGridWrapper" >-->
			<table id="treeGrid" class="easyui-treegrid" data-options="url: '', method: 'get', rownumbers: true, idField: 'id', treeField: 'text', fit: true">
			    <thead frozen="true">
				<tr>
				    <th data-options="field: 'text'" width="250">Version</th>
				</tr>
			    </thead>
			    <thead>
				<tr>
				    <th data-options="field: 'status',align: 'right'">Status</th>
				    <th data-options="field: 'stgkz',align:'right'">STG KZ</th>
				    <!--<th data-options="field: 'version',align:'right'">Version</th>-->
				    <th data-options="field: 'orgform_kurzbz',align:'right'">Orgform</th>
				    <th data-options="field: 'ects_stpl',align:'right'">ECTS</th>
				    <th data-options="field: 'gueltigvon',align:'right'">Gültig von</th>
				    <th data-options="field: 'gueltigbis',align:'right'">gültig bis</th>
				    <th data-options="field: 'regelstudiendauer',align:'right'">Dauer</th>
				    <th data-options="field: 'sprache',align:'right'">Sprache</th>
				    <th data-options="field: 'aktiv',align:'right'">aktiv</th>
				</tr>
			    </thead>
			</table>
			<!--</div>-->
		    </div>
		    <div id="centerCenter" data-options="region:'center', split:true" border="true" style="border:1px solid #ccc;">
			<div id="mainView" ui-view>

			</div>
		    </div>
		</div>
	    </div>
	</div>
	<div id="modalDiv" style="display: none;"></div>
	<div id="error" ng-controller="ErrorCtrl" style="display: none;">
	    <error></error>
	</div>
    </body>
</html>
