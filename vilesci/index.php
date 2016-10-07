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

if (!$rechte->isBerechtigt('addon/studiengangsverwaltung')) {
    die('Sie haben keine Berechtigung fuer diese Seite');
}
?>
<!DOCTYPE html>
<html ng-app="stgv2">
    <head>
	<meta charset="UTF-8">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta http-equiv="Expires" content="Sat, 01 Dec 2001 00:00:00 GMT">
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
	<link href="./js/jquery-ui-1.11.4.custom/jquery-ui.css" rel="stylesheet" type="text/css">
	<link href="js/jquery-ui-1.11.4.custom/jquery-ui.theme.css" rel="stylesheet" type="text/css"/>
	<link href="js/jquery-ui-1.11.4.custom/jquery-ui.structure.css" rel="stylesheet" type="text/css"/>

	<!-- App CSS -->
	<link rel="stylesheet" type="text/css" href="./css/style.css">

	<!-- jQuery -->
	<script type="text/javascript" src="./js/jquery-1.11.3.js"></script>

	<!-- JQuery UI -->
	<script type="text/javascript" src="./js/jquery-ui-1.11.4.custom/jquery-ui.js"></script>

	<!-- Easy UI JS -->
	<!--<script type="text/javascript" src="./js/jquery-easyui-1.4.4/jquery.min.js"></script>-->
	<script type="text/javascript" src="./js/jquery-easyui-1.4.4/jquery.easyui.min.js"></script>
	<!-- TreeGrid DnD Extension -->
	<script type="text/javascript" src="./js/treegrid-dnd/treegrid-dnd.js"></script>

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

	<!-- Services -->
	<script src="js/angular/services/AenderungsvarianteService.js" type="text/javascript"></script>
	<script src="js/angular/services/AkadgradService.js" type="text/javascript"></script>
	<script src="js/angular/services/OrgformService.js" type="text/javascript"></script>
	<script src="js/angular/services/OrtService.js" type="text/javascript"></script>
    <script src="js/angular/services/LehrveranstaltungService.js" type="text/javascript"></script>
	<script src="js/angular/services/SpracheService.js" type="text/javascript"></script>
	<script src="js/angular/services/StandortService.js" type="text/javascript"></script>
	<script src="js/angular/services/StudiengangService.js" type="text/javascript"></script>
	<script src="js/angular/services/StudienordnungService.js" type="text/javascript"></script>
	<script src="js/angular/services/StudienordnungStatusService.js" type="text/javascript"></script>
	<script src="js/angular/services/StudienplanService.js" type="text/javascript"></script>
	<script src="js/angular/services/StudiensemesterService.js" type="text/javascript"></script>
	<script type="text/javascript" src="./js/angular/services/errorService.js"></script>
	<script src="js/angular/services/storeService.js" type="text/javascript"></script>
	<script type="text/javascript" src="./js/angular/services/successService.js"></script>

	<!-- Angular Storage -->
	<script src="js/angular/angular-storage.js" type="text/javascript"></script>

	<!-- Other Controller -->
	<script type="text/javascript" src="./js/angular/controllers/error-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/success-controller.js"></script>

	<!-- Angular Studiengang Controllers -->
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-main-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-stammdaten-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-betriebsdaten-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-reihungstest-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-bewerbung-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-kosten-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-foerderungen-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studiengang/stg-doktorat-controller.js"></script>

	<!-- Angular Studienordnung Controllers -->
	<script type="text/javascript" src="./js/angular/controllers/studienordnung/sto-neu-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienordnung/sto-metadaten-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienordnung/sto-dokumente-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienordnung/sto-eckdaten-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienordnung/sto-taetigkeitsfelder-controller.js"></script>
	<script src="js/angular/controllers/studienordnung/sto-qualifikationsziele-controller.js" type="text/javascript"></script>
	<script src="js/angular/controllers/studienordnung/sto-diff-controller.js" type="text/javascript"></script>
	<script src="js/angular/controllers/studienordnung/sto-zgv-controller.js" type="text/javascript"></script>
	<script src="js/angular/controllers/studienordnung/sto-aufnahmeverfahren-controller.js" type="text/javascript"></script>

	<!-- Angular Studienplan Controllers -->
	<script type="text/javascript" src="./js/angular/controllers/studienplan/stpl-neu-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienplan/stpl-metadaten-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienplan/stpl-eckdaten-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienplan/stpl-gueltigkeit-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienplan/stpl-lehrveranstaltung-controller.js"></script>
	<script type="text/javascript" src="./js/angular/controllers/studienplan/stpl-newLehrveranstaltung-controller.js"></script>
	<script src="js/angular/controllers/studienplan/stpl-auslandssemester-controller.js" type="text/javascript"></script>
	<script src="js/angular/controllers/studienplan/stpl-berufspraktikum-controller.js" type="text/javascript"></script>
	<script src="js/angular/controllers/studienplan/stpl-studienjahr-controller.js" type="text/javascript"></script>

	<!-- Angular State Controllers -->
	<script type="text/javascript" src="./js/angular/controllers/state/state-main-controller.js"></script>

	<!-- Directives -->
	<script type="text/javascript" src="./js/angular/directives/error-directive.js"></script>
	<script type="text/javascript" src="./js/angular/directives/numericOnly-directive.js"></script>
	<script type="text/javascript" src="./js/angular/directives/currency-directive.js"></script>
	<script src="js/angular/directives/stripHtml-directive.js" type="text/javascript"></script>
	<script src="js/angular/directives/timeFormat-directive.js" type="text/javascript"></script>
	<script src="js/angular/directives/integerOnly-directive.js" type="text/javascript"></script>
	<script src="js/angular/directives/charactersOnly-directive.js" type="text/javascript"></script>
	<script src="js/angular/directives/onNgRepeatFinished-directive.js" type="text/javascript"></script>

	<!-- Colorpicker -->
	<link rel="stylesheet" href="../../../skin/colorpicker.css" type="text/css"/>
	<script type="text/javascript" src="../../../include/js/colorpicker.js"></script>

	<!-- Timepicker -->
	<link rel="stylesheet" href="../../../skin/jquery.ui.timepicker.css" type="text/css"/>
	<script type="text/javascript" src="../../../include/js/jquery.ui.timepicker.js"></script>

	<!-- Angular File Upload -->
	<script src="js/angular/angular-file-upload/dist/angular-file-upload.js" type="text/javascript"></script>

	<script src="js/jquery.hotkeys.js" type="text/javascript"></script>
	<script src="js/bootstrap-wysiwyg-2.0/src/bootstrap-wysiwyg.js" type="text/javascript"></script>
	<link href="js/bootstrap-wysiwyg-2.0/css/style.css" rel="stylesheet" type="text/css"/>
	<link href="../vendor/fortawesome/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css"/>

    </head>
    <body ng-controller="AppCtrl as appCtrl">
	<div id='layoutWrapper' class="easyui-layout">
	    <div id="north" data-options="region:'north'" ng-controller="MenuCtrl as menuCtrl">
		<div class="easyui-panel" style="padding:5px;">
		    <!--<a ng-repeat="button in buttons" href="#" class="easyui-linkbutton" data-options="plain:true">{{button.label}}</a>-->
		    <a href="#" class="easyui-menubutton" data-options="menu:'#mm1'">Neu</a>
		    <a href="#" class="easyui-menubutton" data-options="menu:'#mm2'">Bearbeiten</a>
		    <a href="#" class="easyui-menubutton" data-options="menu:'#mm3'">Status</a>
		    <a href="#" class="easyui-menubutton" data-options="menu:'#mm4'">Diff</a>
			<a href="#" class="easyui-menubutton" data-options="menu:'#mm5'">Export</a>
		    <a href="#" class="easyui-menubutton" data-options="menu:'#mm6',iconCls:'icon-help'">About</a>
		</div>
		<div id="mm1" style="width:150px;">
		    <div ng-click='menuCtrl.createStudienordnung()'>Studienordnung</div>
		    <div ng-click='menuCtrl.createStudienplan()'>Studienplan</div>
		</div>
		<div id="mm2" style="width:100px;">
		    <div data-options="iconCls:'icon-remove'">
			<span>Löschen</span>
			<!--<div>
			    <div ng-click='menuCtrl.delete("studienordnung")' data-options="iconCls:'icon-remove'">Studienordnung</div>
			    <div ng-click='menuCtrl.delete("studienplan")' data-options="iconCls:'icon-remove'">Studienplan</div>
			</div>-->
		    </div>
		</div>
		<div id="mm3" style="width:150px;">
		    <div>
			<span>Status ändern zu</span>
			<div>
			    <div ng-repeat="status in menuCtrl.statusList" ng-click="menuCtrl.changeStatus()">{{status.bezeichnung}}</div>
			</div>
		    </div>
		</div>
		<div id="mm4" style="width:100px;">
		    <div ng-click='menuCtrl.diff()'>Diff</div>
		</div>
		<div id="mm5" style="width:100px;">
			<div ng-click="menuCtrl.export('pdf',false)">PDF</div>
			<div ng-click="menuCtrl.export('odt',false)">ODT</div>
			<div ng-click="menuCtrl.export('doc',false)">DOC</div>
			<div ng-click="menuCtrl.export('pdf',true)">PDF mit LVInfo</div>
			<div ng-click="menuCtrl.export('odt',true)">ODT mit LVInfo</div>
			<div ng-click="menuCtrl.export('doc',true)">DOC mit LVInfo</div>
		</div>
		<div id="mm6"  >
            <div>
                <a href="http://fhcomplete.technikum-wien.at/dokuwiki/doku.php?id=stgvt:allgemeines" target="_blank">Hilfe</a>
            </div>
            <div>
                <a href="https://signavio.technikum-wien.at/p/portal#/model/eceaf658462b4c4ca733d3987ebc8468" target="_blank">Signavio</a>
            </div>
            <div>
		        <a href="mailto:fhcomplete@technikum-wien.at" target="_blank">Support: fhcomplete@technikum-wien.at</a>
            </div>
		</div>
	    </div>
	    <div id="west" data-options="region:'west', split: true, maxWidth: 400" ng-controller="TreeCtrl">
		<ul id="west_tree" class="easyui-tree"></ul>
	    </div>
	    <div id="footer" data-options="region:'south'" style="height: 5%;">
		<!--TODO zusätzliche Daten anzeigen; z.B.: Username, DB, etc-->
		<div id="user">User: {{appCtrl.user.name}} {{appCtrl.user.lastname}}</div>
		<div id="success_data" ng-controller="SuccessCtrl">
		    {{SuccessCtrl.message}}
		</div>
	    </div>
	    <div id="center" data-options="region:'center'">
		<div id="centerLayout" class="easyui-layout" fit="true">
		    <div id="centerNorth" data-options="region:'north', split:true, height: 200" border="false" ng-controller="TreeGridCtrl as gridCtrl" >
			<!--<div id="treeGridWrapper" >-->
			<table id="treeGrid" class="easyui-treegrid">

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
