angular.module('stgv2')
		.controller('StplLehrveranstaltungCtrl', function ($scope, $http, $state, $stateParams, errorService, $compile, StudienplanService, successService, StudiensemesterService, StudiengangService, StudienordnungService) {
			$scope.studienplan_id = $stateParams.studienplan_id;
			var ctrl = this;
			var scope = $scope;
			ctrl.data = "";
			ctrl.meta = {
				name: "",
				ects: ""
			};
			ctrl.studienplan = "";
			ctrl.oeList = [{bezeichnung: "Keine Auswahl", oe_kurzbz: "alle"}];
			ctrl.oe_kurzbz = "alle";
			ctrl.lehrtypList = "";
			ctrl.lehrtyp_kurzbz = "lv";
			ctrl.semester = "null";
			ctrl.semesterList = [{
					"key": "null",
					"value": "alle"
				}];
			var editingId = null;
			ctrl.lvRegelTypen = [];
			ctrl.LVREGELnewcounter=0; // Counter fuer neue Regeln
			ctrl.LVREGELStudienplanLehrveranstaltungID=''; // ID der ausgewaehlten Lehrveranstaltungszuordnung
			ctrl.LVREGELLehrveranstaltungAutocompleteArray = new Array(); // Enthaelt die IDs der Input Felder die zu Autocomplete Feldern werden sollen
			scope.lvs = [];
			scope.bezeichnung = "";
			ctrl.studienSemesterList = [];
			ctrl.studiengangList = [{bezeichnung: "Keine Auswahl", studiengang_kz: "alle"}];

			$(document).ready(function(){
				$('[data-toggle="tooltip"]').tooltip();
			});

			//loading Studiensemester list
			StudiensemesterService.getStudiensemesterList().then(function (result) {
				ctrl.studiensemesterList = result;
			}, function (error) {
				errorService.setError(getErrorMsg(error));
			});

			ctrl.initSemesterList = function()
			{
				for(var j=0; j<=ctrl.studienplan.regelstudiendauer; j++)
				{
					var item = {
						"key": j,
						"value": j
					}
					ctrl.semesterList.push(item);
				}
			};

			ctrl.initStplTree = function ()
			{

				// Ueberschreiben der autoSizeColumn Funktion da es sonst zu Performanceproblemen kommt
				var origTreegrid_autoSizeColumn = $.fn.datagrid.methods['autoSizeColumn'];
				$.extend($.fn.treegrid.methods, {
				    autoSizeColumn: function(jq, field) {
				        $.each(jq, function() {
				            var opts = $(this).treegrid('options');
				            if (!opts.skipAutoSizeColumns) {
				                var tg_jq = $(this);
				                if (field) origTreegrid_autoSizeColumn(tg_jq, field);
				                else origTreegrid_autoSizeColumn(tg_jq);
				            }
				        });
				    }
				});
				$("#stplTreeGrid").treegrid({
					url: "./api/studienplan/lehrveranstaltungen/lehrveranstaltungTree.php?studienplan_id=" + $scope.studienplan_id,
					idField: "id",
					treeField: "bezeichnung",
					rownumbers: false,
					skipAutoSizeColumns: true,
					autoRowHeight: false,
					fit: true,
					toolbar: [
						{
							id: 'saveChanges',
							disabled: true,
							iconCls: 'icon-save',
							text: 'Speichern',
							handler: function()
							{
								if(editingId !== null)
								{
									$("#stplTreeGrid").treegrid('endEdit', editingId);
									$("#saveChanges").linkbutton("disable");
									$("#editNode").linkbutton("disable");
								}
							}
						},
						{
							id: 'deleteNode',
							disabled: true,
							iconCls: 'glyphicon glyphicon-remove red',
							text: 'Aus Studienplan entfernen',
							handler: function()
							{
								$("#deleteNode").linkbutton("disable");
								ctrl.removeStudienplanLehrveranstaltung();
							}
						},
						{
							id: 'editNode',
							disabled: true,
							iconCls: 'icon-edit',
							text: 'Attribute editieren',
							handler: function()
							{
								$("#saveChanges").linkbutton("enable");
								if(editingId !== null)
								{
									$("#stplTreeGrid").treegrid('endEdit', editingId);
									editingId = null;
								}
								var row = $("#stplTreeGrid").treegrid('getSelected');
								editingId = row.id;
								$("#stplTreeGrid").treegrid('beginEdit', editingId);
							}
						},
						{
							id: 'ReloadTree',
							disabled: false,
							iconCls: 'icon-reload',
							text: 'Aktualisieren',
							handler: function()
							{
								$("#stplTreeGrid").treegrid('reload');
							}
						},
						{
							id: 'ExpandNodes',
							disabled: false,
							iconCls: 'icon-edit',
							text: 'Alle aufklappen',
							handler: function()
							{
								$("#stplTreeGrid").treegrid('expandAll');
							}
						}
					],
					columns: [[
						{field: 'bezeichnung', width:'300', title:'Lehrveranstaltung'},
						{field: 'ects',align: 'right', title:'ECTS'},
//						{field: 'semesterstunden',align: 'right', title:'LAS (Semesterstunden)'},
						{field: 'lvs', align: 'right', title:'LVS'},
						{field: 'alvs', align: 'right', title:'ALVS'},
						{field: 'sws', align: 'right', title:'SWS'},
						{field: 'lehrform_kurzbz',align: 'right', title:'Lehrform'},
						{field: 'export',align: 'center', /*editor: {type: 'checkbox'},*/ title:'StudPlan', formatter: booleanToIconFormatter},
						{field: 'stpllv_pflicht',align: 'center', title:'Pflicht', formatter: booleanToIconFormatter},
						{field: 'genehmigung',align: 'center', title:'Gen', formatter: booleanToIconFormatter},
						{field: 'lehre',align: 'center', /*editor: {type: 'checkbox'},*/ title:'Lehre/CIS', formatter: booleanToIconFormatter},
						{field: 'lvinfo',align: 'center', /*editor: {type: 'checkbox'},*/ title:'LV-Info', formatter: booleanToIconFormatter},
						{field: 'benotung',align: 'center', /*editor: {type: 'checkbox'},*/ title:'Benotung', formatter: booleanToIconFormatter},
						{field: 'zeugnis',align: 'center', /*editor: {type: 'checkbox'},*/ title:'Zeugnis', formatter: booleanToIconFormatter},
						{field: 'lehrauftrag',align: 'center', /*editor: {type: 'checkbox'},*/ title:'Lehrauftrag', formatter: booleanToIconFormatter},
						{field: 'lvnr',align: 'right', title:'LVNR'}
						//{field: 'curriculum',align: 'center', /*editor: {type: 'checkbox'},*/ title:'Studienplan', formatter: booleanToIconFormatter}
					]],
					onContextMenu: function(e ,row)
					{
						if (row && row.type != "sem" && ctrl.studienplan.status_kurzbz === "development")
						{
							e.preventDefault();
							$(this).treegrid('select', row.id);
							$('#stplTreeGridContextMenu').menu();
							$('#stplTreeGridContextMenu').menu('show', {
								left: e.pageX,
								top: e.pageY
							});
						}
					},
					loadFilter: function (data)
					{
						if (data.erfolg)
						{
                            return data.info;
							/*
							var tree = [];
							$(data.info).each(function (i, v)
							{
								if (v.stpllv_semester == 0)
								{
									//tree.push(generateChildren(v));
								}
							});
							return tree;
							*/
						}
						else
						{
							/*
							 * Dieser Zweig wird ausgeführt bei Drag and Drop
							 */
							if (data.erfolg == undefined)
							{
								return data;
							}
							//TODO Fehler ausgeben data.message
						}
					},
					onLoadSuccess: function (row)
					{
						$(this).treegrid("enableDnd", row ? row.id : null);
						//workaround to change tree icons
						changeTreeIcons("stplTree", "stplTreeGrid");
					},
					onClickRow: function (row)
					{
						if (row.type != "sem")
						{
							if(ctrl.studienplan.status_kurzbz === 'development')
							{
								$("#deleteNode").linkbutton("enable");
								$("#editNode").linkbutton("enable");
							}
							ctrl.loadLVRegeln(row.studienplan_lehrveranstaltung_id);
							ctrl.meta = angular.copy(row);
							ctrl.meta.oe = ctrl.getOeName(ctrl.meta.oe_kurzbz);
							$scope.$apply();
						}
						else
						{
							$("#deleteNode").linkbutton("disable");
							$("#editNode").linkbutton("disable");
						}
					},
					onBeforeEdit: function(row)
					{
						var benotung = $(this).treegrid('getColumnOption','benotung');
//						var curriculum = $(this).treegrid('getColumnOption','curriculum');
						var exportCol = $(this).treegrid('getColumnOption','export');
						var zeugnis = $(this).treegrid('getColumnOption','zeugnis');
						var lvinfo = $(this).treegrid('getColumnOption','lvinfo');
						var lehrauftrag = $(this).treegrid('getColumnOption','lehrauftrag');
						var lehre = $(this).treegrid('getColumnOption','lehre');
						var pflicht = $(this).treegrid('getColumnOption','stpllv_pflicht');
						var genehmigung = $(this).treegrid('getColumnOption','genehmigung');

						if(row.type==="modul")
						{
							//set editors for module editing
							exportCol.editor = {type: "checkbox"};
							lvinfo.editor = {type: "checkbox"};
							lehrauftrag.editor = {type: "checkbox"};
							lehre.editor = {type: "checkbox"};
							pflicht.editor = {type: "checkbox"};
							genehmigung.editor = {type: "checkbox"};
							benotung.editor = {type: "checkbox"};
						}
						else
						{
							var parent = $(this).treegrid('getParent',row.id);
							benotung.editor = {type: "checkbox"};
							//curriculum.editor = {type: "checkbox"};
							exportCol.editor = {type: "checkbox"};
							zeugnis.editor = {type: "checkbox"};
							lvinfo.editor = {type: "checkbox"};
							lehrauftrag.editor = {type: "checkbox"};
							lehre.editor = {type: "checkbox"};
							pflicht.editor = {type: "checkbox"};
							genehmigung.editor = {type: "checkbox"};
						}
					},
					onAfterEdit: function(row, changes)
					{
						var lv = $("#stplTreeGrid").treegrid('getSelected');
						var parent = $("#stplTreeGrid").treegrid('getParent', lv.id);

						var benotung = $(this).treegrid('getColumnOption','benotung');
//						var curriculum = $(this).treegrid('getColumnOption','curriculum');
						var exportCol = $(this).treegrid('getColumnOption','export');
						var zeugnis = $(this).treegrid('getColumnOption','zeugnis');
						var lvinfo = $(this).treegrid('getColumnOption','lvinfo');
						var lehrauftrag = $(this).treegrid('getColumnOption','lehrauftrag');
						var lehre = $(this).treegrid('getColumnOption','lehre');
						var pflicht = $(this).treegrid('getColumnOption','stpllv_pflicht');
						var genehmigung = $(this).treegrid('getColumnOption','genehmigung');

						benotung.editor = null;
//						curriculum.editor = null;
						exportCol.editor = null;
						zeugnis.editor = null;
						lvinfo.editor = null;
						lehrauftrag.editor = null;
						lehre.editor = null;
						pflicht.editor = null;
						genehmigung.editor = null;

						//update studienplan_lehrveranstaltung
						var data = {};
						data.semester = lv.stpllv_semester;
						if(parent.type != "sem")
						{
							data.studienplan_lehrveranstaltung_id_parent = parent.id;
						}
						else
						{
							data.studienplan_lehrveranstaltung_id_parent = "";
						}
						data.studienplan_lehrveranstaltung_id = lv.id;
						data.curriculum = lv.curriculum;
						data.export = lv.export;
						data.pflicht = lv.stpllv_pflicht;
						data.genehmigung = lv.genehmigung;

						if((changes.curriculum !== undefined) || (changes.export !== undefined) || (changes.stpllv_pflicht !== undefined) || (changes.genehmigung !== undefined))
						{
							var updateData = {data: ""};
							updateData.data = data;
							$http({
								method: 'POST',
								url: './api/studienplan/lehrveranstaltungen/update_studienplanLehrveranstaltung.php',
								data: $.param(updateData),
								headers: {
									'Content-Type': 'application/x-www-form-urlencoded'
								}
							}).then(
								function success(response)
								{
									if (response.data.erfolg)
									{
										successService.setMessage("Daten erfolgreich geändert.");
									}
									else
									{
										for(var prop in changes)
										{
											row[prop] = !changes[prop];
										}
										$("#stplTreeGrid").treegrid('refreshRow', row.id);
										errorService.setError(getErrorMsg(response));
									}
								},
								function error(response)
								{
									errorService.setError(getErrorMsg(response));
								}
							);
						}

						if((changes.benotung !== undefined)
								|| (changes.zeugnis !== undefined)
								|| (changes.lvinfo !== undefined)
								|| (changes.lehrauftrag !== undefined)
								|| (changes.lehre !== undefined)
								)
						{
							//update lehrveranstaltung
							var data = {};
							data.benotung = lv.benotung;
							data.zeugnis = lv.zeugnis;
							data.lvinfo = lv.lvinfo;
							data.lehrauftrag = lv.lehrauftrag;
							data.lehre = lv.lehre;
							data.lehrveranstaltung_id = lv.lehrveranstaltung_id;

							var updateData = {data: ""};
							updateData.data = data;

							$http({
								method: 'POST',
								url: './api/studienplan/lehrveranstaltungen/update_lehrveranstaltung.php',
								data: $.param(updateData),
								headers: {
									'Content-Type': 'application/x-www-form-urlencoded'
								}
							}).then(function success(response) {
								if (response.data.erfolg)
								{
									successService.setMessage("Daten erfolgreich geändert.");
								}
								else
								{
									for(var prop in changes)
									{
										row[prop] = !changes[prop];
									}
									$("#stplTreeGrid").treegrid('refreshRow', row.id);
									errorService.setError(getErrorMsg(response));
								}
							}, function error(response) {
								errorService.setError(getErrorMsg(response));
							});
						}
					},
					onBeforeDrag: function (row)
					{
						if (row.type === "sem")
						{
							return false;
						}
						else
						{
							row.moving = true;
						}
					},
					onDragEnter: function (target, source)
					{
						if(ctrl.studienplan.status_kurzbz !== "development")
						{
							return false;
						}
					},
					onBeforeDrop: function(target, source, point)
					{
						var node = $("#stplTreeGrid").treegrid("find",source._parentId);
						if(node != null)
						{
							node.ects -= parseFloat(source.ects);
							$("#stplTreeGrid").treegrid("refresh",node.id);
						}

						//set values depending on target node
						//e.g. children of modules
						if(source.typ !== "modul")
						{
							if(target.lehrform_kurzbz === "kMod")
							{
								source.benotung = true;
							}
							else if(target.lehrform_kurzbz === "iMod")
							{
								source.benotung = false;
							}

						}

						if(source.stpllv_pflicht === undefined)
						{
							source.stpllv_pflicht = true;
						}

						if(source.curriculum === undefined)
						{
							source.curriculum = true;
						}

						if(source.export === undefined)
						{
							if(ctrl.studienplan.status_kurzbz !== "development")
							{
								source.export = false;
							}
							else
							{
								source.export = true;
							}
						}
						if(source.genehmigung === undefined)
						{
							source.genehmigung = true;
						}
					},
					onDrop: function (target, source, point)
					{
						var data = {};
						data.semester = target.sem;
						source.stpllv_semester = target.sem;
						if(target.type != "sem")
						{
							data.studienplan_lehrveranstaltung_id_parent = target.id;
						}
						else
						{
							data.studienplan_lehrveranstaltung_id_parent = "";
						}
						data.pflicht = true;
						data.export = source.export;
						data.curriculum = source.curriculum;
						data.genehmigung = source.genehmigung;
						//TODO errorhandling

						//update moved entry
						if(source.moving)
						{
							data.studienplan_lehrveranstaltung_id = source.id;
							var updateData = {data: ""};
							updateData.data = data;
							$http({
								method: 'POST',
								url: './api/studienplan/lehrveranstaltungen/update_studienplanLehrveranstaltung.php',
								data: $.param(updateData),
								headers: {
									'Content-Type': 'application/x-www-form-urlencoded'
								}
							}).then(function success(response) {
								changeTreeIcons("stplTree", "stplTreeGrid", target);
								if (response.data.erfolg)
								{
									//$("#stplTreeGrid").treegrid("reload",{nodeId: target.id});
									//TODO recalculate ects sums
									var parentId = source._parentId;
									target.ects = parseFloat(target.ects) + parseFloat(source.ects);
									$("#stplTreeGrid").treegrid("refresh",target.id);

									// Reload Subtree to ensure all Entrys are visible
									$('#stplTreeGrid').treegrid('reload', parentId);
								}
								else
								{
									errorService.setError(getErrorMsg(response));
								}
							}, function error(response) {
								errorService.setError(getErrorMsg(response));
							});
						}
						else
						{
							//save new entry moved from other tree
							data.studienplan_id = $scope.studienplan_id;
							data.lehrveranstaltung_id = source.id;
							var saveData = {data: ""};
							saveData.data = data;
							$http({
								method: 'POST',
								url: './api/studienplan/lehrveranstaltungen/save_studienplanLehrveranstaltung.php',
								data: $.param(saveData),
								headers: {
									'Content-Type': 'application/x-www-form-urlencoded'
								}
							}).then(function success(response) {
								changeTreeIcons("stplTree", "stplTreeGrid", target);

								if (response.data.erfolg)
								{
									try
									{
										//node-id an neue DB-ID anpassen
										var root = $('#stplTreeGrid').treegrid('getRoot');
										var idView1 = $('tr[node-id='+root.id+']:eq(0)').attr("id").replace(root.id,'');
										//var idView2 = $('tr[node-id='+root.id+']:eq(1)').attr("id").replace(root.id,'');
										$($('#stplTreeGrid').treegrid('find', source.id)).attr('node-id', response.data.info[0]);
										$('#'+idView1 + source.id).attr('node-id', response.data.info[0]);
										$('#'+idView1 + source.id).attr('id',idView1 + response.data.info[0]);
										//$('#'+idView2 + source.id).attr('node-id', response.data.info[0]);
										//$('#'+idView2 + source.id).attr('id',idView2 + response.data.info[0]);
										var  row = $('#stplTreeGrid').treegrid('find', source.id);

										row.id = response.data.info[0];
										row.sem = saveData.data.semester;
										//needed to detect later if node is moved in tree or dropped from another tree
										row.moving = true;

										// Reload LV Tree - If we dont reload you cant drag the same lv a second time
										$('#lvTreeGrid').treegrid('reload');

										// Reload Subtree to ensure all Data is available.
										// otherwise editing afterwards will fail
										var parentNode = $('#stplTreeGrid').treegrid('getParent', response.data.info[0]);
										$('#stplTreeGrid').treegrid('reload', parentNode);
									}
									catch(e)
									{
										console.log("Error",e);
									}
								}
								else
								{
									errorService.setError(getErrorMsg(response));
								}
							}, function error(response) {
								errorService.setError(getErrorMsg(response));
							});
						}
					}
				});

				$.extend($.fn.datagrid.defaults.editors, {
					checkbox: {
						init: function(container, options){
							var input = $('<input type="checkbox">').appendTo(container);
							return input;
						},
						getValue: function(target){
							return $(target).prop('checked');
						},
						setValue: function(target, value){
							$(target).prop('checked',value);
						}
					}
				});
			};

			StudienplanService.getStudienplan($scope.studienplan_id).then(function(result){
				ctrl.studienplan = result;
				ctrl.initSemesterList();
				ctrl.initStplTree();
			}, function(error){

			});

			//TODO load from Service
			//load organisationseinheiten
			$http({
				method: 'GET',
				url: './api/helper/organisationseinheitByTyp.php?oetyp_kurzbz=Institut'
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					var institute = response.data.info;
					$http({
						method: 'GET',
						url: './api/helper/organisationseinheitByTyp.php?oetyp_kurzbz=Studiengang'
					}).then(function success(response) {
						if (response.data.erfolg)
						{
							angular.forEach(institute, function(value, index){
								ctrl.oeList.push(value);
							});
							angular.forEach(response.data.info, function(value, index){
								ctrl.oeList.push(value);
							});
						}
						else
						{
							errorService.setError(getErrorMsg(response));
						}
					}, function error(response) {
						errorService.setError(getErrorMsg(response));
					});
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});

			StudiengangService.getStudiengangList().then(function (result) {
				angular.forEach(result, function(value, index){
					ctrl.studiengangList.push(value);
				});
				StudienordnungService.getStudienordnungByStudienplan($scope.studienplan_id).then(function (result) {
					ctrl.studienordnung = result;
					ctrl.studiengang_kz = ctrl.studienordnung.studiengang_kz;
				}, function (error) {
					errorService.setError(getErrorMsg(error));
				});
			}, function (error) {
				errorService.setError(getErrorMsg(error));
			});

			//TODO load from Service
			//load lehrtypen
			$http({
				method: 'GET',
				url: './api/helper/lehrtyp.php'
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.lehrtypList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});

			$("#lvTreeGrid").treegrid({
				url: '',
				method: 'get',
				rownumbers: false,
				idField: 'id',
				treeField: 'bezeichnung',
				fit: true,
				multiSort: true,
				columns: [[
					{field: 'bezeichnung', align: 'left', width:'250', sortable: true,title:'Lehrveranstaltung'},
					{field: 'ects', align:'right', sortable: true, title:'ECTS'},
					{field: 'lehrform_kurzbz', align:'right', sortable: true, title:'Lehrform'},
				]]
			});

			ctrl.loadLehrveranstaltungen = function (selection)
			{
				var oe_kurzbz = $("#oe").val();
				if (oe_kurzbz === "? string: ?")
				{
					alert("Bitte wählen Sie eine Organisationseinheit aus.");
					return false;
				}
				if(ctrl.studiengang_kz === "alle" && ctrl.oe_kurzbz === "alle")
				{
					alert("Bitte wählen Sie eine Organisationseinheit oder einen Studiengang aus.");
					return false;
				}
				var lehrtyp_kurzbz = $("#lehrtyp").val();
				var semester = $("#semester").val();
				if (semester === "? string: ?")
				{
					semester = null;
				}

				$("#lvTreeGrid").treegrid({
					url: "./api/helper/lehrveranstaltungByOe.php?oe_kurzbz=" + oe_kurzbz + "&lehrtyp_kurzbz=" + lehrtyp_kurzbz + "&semester=" + semester + "&studiengang_kz=" + ctrl.studiengang_kz,
					method: 'GET',
					idField: 'id',
					treeField: 'bezeichnung',
					rownumbers: false,
					skipAutoSizeColumns: true,
					autoRowHeight: false,
					multiSort: true,
					columns: [[
						{field: 'bezeichnung', align: 'left', width:'250', sortable: true,title:'Lehrveranstaltung'},
						{field: 'ects', align:'right', sortable: true, title:'ECTS'},
						{field: 'lehrform_kurzbz', align:'right', sortable: true, title:'Lehrform'},
					]],
					loadFilter: function (data)
					{
						if (data.erfolg)
						{
							angular.forEach(data.info, function(value, index){
								switch(value.type)
								{
									case "lv":
										value.iconCls = "icon-lv";
										break;
									case "modul":
										value.iconCls = "icon-module";
										break;
									case "lf":
										value.iconCls = "icon-lv";
										break;
									default:
										value.iconCls = "icon-lv";
										break;
								}
							});
							return data.info;
						}
						else
						{
							/*
							 * Dieser Zweig wird ausgeführt bei Drag and Drop
							 */
							if (data.erfolg == undefined)
							{
								return data;
							}
							//TODO Fehler ausgeben data.message
						}

					},
					onClick: function (node)
					{
						return true;
					},
					onClickRow: function (row)
					{
						ctrl.meta = row;
						ctrl.meta.oe = ctrl.getOeName(ctrl.meta.oe_kurzbz);
						$scope.$apply();
					},
					onLoadSuccess: function (row)
					{
						$(this).treegrid("enableDnd", row ? row.id : null);
						if(selection !== undefined)
						{
							$(this).treegrid('select',selection);
						}
					},
					onDragEnter: function (target, source)
					{
						return false;
					}
				});
			};

			ctrl.copyAndReplaceLehrveranstaltung = function()
			{
				if(confirm("Wollen Sie dieses Element wirklich durch eine Kopie ersetzen?"))
				{
					var node = $('#stplTreeGrid').treegrid('getSelected');
					var saveData = {data: ""}
					saveData.data = angular.copy(node);
					saveData.data.bezeichnung=saveData.data.bezeichnung+' Kopie';

					$http({
						method: 'POST',
						url: './api/studienplan/lehrveranstaltungen/save_lehrveranstaltung.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response){
						if(response.data.erfolg)
						{
							$('#stplTreeGrid').treegrid('reload');
						}
						else
						{
							errorService.setError(getErrorMsg(response));
						}
					}, function error(response){
						errorService.setError(getErrorMsg(response));
					});
				}
			}

			ctrl.removeStudienplanLehrveranstaltung = function ()
			{
				var node = $('#stplTreeGrid').treegrid('getSelected');
				if (node){
					if(node.children === undefined || node.children.length === 0)
					{
						$http({
							method: 'GET',
							url: './api/studienplan/lehrveranstaltungen/delete_studienplanLehrveranstaltung.php?studienplan_lehrveranstaltung_id='+node.id
						}).then(function success(response) {
							if (response.data.erfolg)
							{
								$('#stplTreeGrid').treegrid('remove', node.id);
								changeTreeIcons("stplTree", "stplTreeGrid");
							}
							else
							{
								errorService.setError(getErrorMsg(response));
							}
						}, function error(response) {
							errorService.setError(getErrorMsg(response));
						});
					}
					else
					{
						alert("Knoten mit Kind-Elementen kann nicht gelöscht werden.");
					}
				}
				else
				{
					console.log("fail");
				}
			};

			ctrl.getOeName = function(oe_kurzbz)
			{
				var returnObject = "not found";
				$(ctrl.oeList).each(function(i,v)
				{
					if(oe_kurzbz == v.oe_kurzbz)
					{
						returnObject = v.bezeichnung;
						return true;
					}
				})
				return returnObject;
			};

			ctrl.dialog = function(lv)
			{
				$http({
					method: 'GET',
					url: './templates/pages/studienplan/lehrveranstaltungen/stplNewLehrveranstaltung.html',
				}).then(function success(response) {
					var html = $("#dialog").html(response.data);
					$compile(html)(scope);
					$("#dialog").dialog({
						title: 'Neue Lehrveranstaltung anlegen',
						width: '80%',
						height: '80%',
						closed: false,
						cache: false,
						modal: true,
						closable: true,
						collapsible: true,
						resizable: true,
						maximizable: true,
						onOpen: function()
						{
							if(lv !== undefined)
							{
								$scope.$broadcast("editLehrveranstaltung", lv);
							}
						},
						onClose: function()
						{
							$("#farbe").ColorPicker("destroy");
						}
					});
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};

			ctrl.setFilter = function(lv_id, oe_kurzbz, lehrtyp_kurzbz, semester)
			{
				$("#oe").val(oe_kurzbz);
				$("#lehrtyp").val(lehrtyp_kurzbz);
				$("#semester").val(semester);
				ctrl.oe_kurzbz = oe_kurzbz;
				ctrl.lehrtyp_kurzbz = lehrtyp_kurzbz;
				ctrl.semester = semester;
				ctrl.loadLehrveranstaltungen(lv_id);
				$("#dialog").dialog('close');
			};

			$scope.$on("setFilter", function(event, args){
				ctrl.setFilter(args.lv_id, args.oe_kurzbz, args.lehrtyp_kurzbz, args.semester);
			});

			$scope.tabs = [
				{label: 'Details', link: 'details'},
				{label: 'LV Regeln', link: 'lvRegeln'},
				{label: 'LV Info', link: 'lvInfo'},
			];

			$scope.selectedTab = $scope.tabs[0];
			$scope.setSelectedTab = function (tab)
			{
				$scope.selectedTab = tab;
			};

			$scope.getSelectedTabName = function()
			{
				return $scope.selectedTab.label;
			}

			$scope.getTabClass = function (tab)
			{
				if ($scope.selectedTab == tab)
				{
					return "active";
				}
				else
				{
					return "";
				}
			};

			ctrl.loadLVRegeln = function(studienplan_lehrveranstaltung_id)
			{
				if(ctrl.lvRegelTypen.length == 0)
				{
					$http({
						method: 'POST',
						url: "../../../soap/fhcomplete.php",
						data: $.param({
							"typ": "json",
							"class": "lvregel",
							"method":"loadLVRegelTypen"
						}),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						ctrl.lvRegelTypen = response.data.result;
						ctrl.loadRegeln(studienplan_lehrveranstaltung_id);
					}, function error(response) {
						errorService.setError(getErrorMsg(response));
					});
				}
				else
				{
					ctrl.loadRegeln(studienplan_lehrveranstaltung_id);
				}
			};

			ctrl.loadRegeln = function(studienplan_lehrveranstaltung_id)
			{
				ctrl.LVREGELStudienplanLehrveranstaltungID = studienplan_lehrveranstaltung_id;
				$http({
					method: 'POST',
					url: "../../../soap/fhcomplete.php",
					data: $.param({
						"typ": "json",
						"class": "lvregel",
						"method":	"getLVRegelTree",
						"parameter_0": studienplan_lehrveranstaltung_id
					}),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if(response.data.error=='true' && response.data.errormsg!=null)
						alert('Fehler:'+response.data.errormsg);
					else
						ctrl.drawLVRegeln(response.data.return);
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};

			ctrl.drawLVRegeln = function(data)
			{
				var html = $('#tab-regel').html(ctrl.getChilds(data));
				$compile(html)(scope);
				ctrl.LVRegelAddAutocomplete();
			};

			ctrl.getChilds = function(data, parent)
			{
				parent = (typeof parent === "undefined") ? "" : parent;
				var obj = '';
				obj = obj+'<ul id="lvregel_ul'+parent+'">';

				for(var i in data)
				{
					obj = obj+ctrl.drawRegel(data[i][0]);

					if(!jQuery.isEmptyObject(data[i]['childs']))
						obj=obj+getChilds(data[i]['childs'], data[i][0].lvregel_id);

				}

				obj = obj+'</ul>';

				if(parent=='')
				{
					// Hinzufuegen Button
					obj = obj+'<a href="#Hinzufuegen" title="Regel hinzufügen" ng-click="stplCtrl.addRegel(\'\')"><img src="../../../skin/images/plus.png" /> Regel hinzufügen</a>';
				}
				return obj;
			};

			ctrl.addRegel = function(lvregel_id_parent)
			{
				ctrl.LVREGELnewcounter++;

				var regel= new Object();
				regel.neu=true;
				regel.lvregel_id='NEU_'+ctrl.LVREGELnewcounter;
				regel.parameter='';
				regel.operator='u';
				regel.regeltyp_kurzbz='';
				regel.lehrveranstaltung_id='';
				regel.studienplan_lehrveranstaltung_id = ctrl.LVREGELStudienplanLehrveranstaltungID;
				regel.lvregel_id_parent=lvregel_id_parent;

				if($('#lvregel_ul'+lvregel_id_parent).length>0)
				{
					var html = $('#lvregel_ul'+lvregel_id_parent).append(ctrl.drawRegel(regel));
					$compile(html)(scope);
				}
				else
				{
					var html = $('#lvregel_li'+lvregel_id_parent).append('<ul id="lvregel_ul'+lvregel_id_parent+'">'+ctrl.drawRegel(regel)+'<ul>');
					$compile(html)(scope);
				}
				ctrl.LVRegelAddAutocomplete();
			};

			ctrl.drawRegel = function(regel)
			{
				var val='';

				if(regel.neu==true)
					var neustyle='class="newLVRegel"';
				else
					var neustyle='';

				val = val+'<li id="lvregel_li'+regel.lvregel_id+'" '+neustyle+'>';

				val = val+'<input size="2" type="hidden" value="'+regel.lvregel_id+'" />';
				val = val+'<input type="hidden" id="lvregel_lvregel_id_parent'+regel.lvregel_id+'" value="'+ClearNull(regel.lvregel_id_parent)+'" />';
				val = val+'<input type="hidden" id="lvregel_studienplan_lehrveranstaltung_id'+regel.lvregel_id+'" value="'+regel.studienplan_lehrveranstaltung_id+'" />';
				if(regel.neu==true)
				{
					val = val+'<input type="hidden" id="lvregel_neu_'+regel.lvregel_id+'" value="true" />';
				}
				else
					val = val+'<input type="hidden" id="lvregel_neu_'+regel.lvregel_id+'" value="false"/>';

				// Operator DropDown
				val = val+'<select id="lvregel_operator'+regel.lvregel_id+'">';
				val = val+'<option value="u" '+(regel.operator=='u'?'selected':'')+'>U</option>';
				val = val+'<option value="o" '+(regel.operator=='o'?'selected':'')+'>O</option>';
				val = val+'<option value="x" '+(regel.operator=='x'?'selected':'')+'>X</option>';
				val = val+'</select>';

				//LVRegelTypen
				val = val+'<select id="lvregel_lvregeltyp'+regel.lvregel_id+'" onchange="LVRegelTypChange(\''+regel.lvregel_id+'\')">';

				for(var i in ctrl.lvRegelTypen)
				{
					if(ctrl.lvRegelTypen[i].lvregeltyp_kurzbz==regel.lvregeltyp_kurzbz)
						var selected='selected';
					else
						var selected='';

					val = val+'<option value="'+ctrl.lvRegelTypen[i].lvregeltyp_kurzbz+'" '+selected+'>'+ctrl.lvRegelTypen[i].bezeichnung+'</option>';
				}
				val = val+'</select>';

				// Parameter
				// Input Feld verstecken wenn der Typ LVpositiv ist
				if(regel.lvregeltyp_kurzbz=='lvpositiv' || regel.lvregeltyp_kurzbz=='lvpositivabschluss')
					var style='style="display:none"';
				else
					var style='';

				val = val+'<input type="text" '+style+' size="1" id="lvregel_parameter'+regel.lvregel_id+'" value="'+ClearNull(regel.parameter)+'" />';

				if(regel.lvregeltyp_kurzbz=='lvpositiv' || regel.lvregeltyp_kurzbz=='lvpositivabschluss')
					var style='';
				else
					var style='style="display: none"';

				val = val+'<span '+style+' id="lvregel_lehrveranstaltung_data'+regel.lvregel_id+'">';
				// Lehrveranstaltung ID
				//val = val+'<input type="hidden" size="4" id="lvregel_lehrveranstaltung_id'+regel.lvregel_id+'" value="'+ClearNull(regel.lehrveranstaltung_id)+'" />';

				// Autocomplete Feld fuer Lehrveranstaltung
				var autocompletebezeichnung = ClearNull(regel.lehrveranstaltung_bezeichnung);
				if(regel.lehrveranstaltung_bezeichnung==undefined)
				{
					autocompletebezeichnung='Lehrveranstaltungsname eingeben';
				}
				//val = val+'<input type="text" size="12" id="lvregel_lehrveranstaltung_id_autocomplete'+regel.lvregel_id+'" value="'+autocompletebezeichnung+'" lvregel_id="'+regel.lvregel_id+'"/>';
//				val += '<input type="hidden" id="lvregel_lehrveranstaltung_id'+regel.lvregel_id+'" value="{{stplCtrl.lehrveranstaltung_id}}">';
				val += '<input id="lvregel_lehrveranstaltung_id_autocomplete_input'+regel.lvregel_id+'" type="text" ng-model="bezeichnung" ng-change="stplCtrl.loadLvs()"/>';
				val += '<select id="lvregel_lehrveranstaltung_id_autocomplete'+regel.lvregel_id+'" ng-model="stplCtrl.lehrveranstaltung_id" onChange="setLvBezeichnung(\''+regel.lvregel_id+'\');"><option ng-repeat="lv in lvs | filter:bezeichnung" value="{{lv.lehrveranstaltung_id}}">{{lv.bezeichnung}}</option></select>'
				if(regel.lehrveranstaltung_bezeichnung==null || regel.lehrveranstaltung_bezeichnung=='undefined' || regel.lehrveranstaltung_bezeichnung=='')
					var lvbezeichnung = 'klicken um LV auszuwählen';
				else
					var lvbezeichnung = regel.lehrveranstaltung_bezeichnung;

				val = val+' <a href="#" style="font-size: x-small" onclick="LVRegelShowAutocomplete(\''+regel.lvregel_id+'\',true);return false;" id="lvregel_lehrveranstaltung_span'+regel.lvregel_id+'">'+lvbezeichnung+'</a>';
				// Die Autocomplete Funktionalitaet wird erst hinzugefuegt, wenn das Input Feld tatsaechlich existiert und
				// bis dort hin zwischengespeichert
				ctrl.LVREGELLehrveranstaltungAutocompleteArray[ctrl.LVREGELLehrveranstaltungAutocompleteArray.length]=regel.lvregel_id;
				val = val+'</span>';

				// Speichern Button
				val = val+' <input type="button" ng-click="stplCtrl.saveRegel(\''+regel.lvregel_id+'\');" value="ok">';

				if(regel.neu==true)
				{
					// Loeschen Button
					val = val+' <a href="#Loeschen" title="Regel entfernen" onclick="$(\'#lvregel_li'+regel.lvregel_id+'\').remove(); return false;"><img src="../../../skin/images/delete_round.png" height="12px"/></a>';
				}
				else
				{
					// Hinzufuegen Button
					val = val+' <a href="#Hinzufuegen" title="Unterregel hinzufügen" ng-click="stplCtrl.addRegel('+regel.lvregel_id+');"><img src="../../../skin/images/plus.png" /></a>';

					// Loeschen Button
					val = val+' <a href="#Loeschen" title="Regel entfernen" ng-click="stplCtrl.deleteRegel('+regel.lvregel_id+');"><img src="../../../skin/images/delete_round.png" height="12px"/></a>';
				}
				val = val+'</li>';
				return val;
			};

			ctrl.loadLvs = function()
			{
				if(scope.bezeichnung.length >= 3)
				{
					$http({
						method: 'POST',
						url: "./api/helper/lehrveranstaltung.php",
						data: $.param({
							"filter": scope.bezeichnung
						}),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if(response.data.erfolg)
						{
							scope.lvs = response.data.info;
						}
					}, function error(response) {
						errorService.setError(getErrorMsg(response));
					});
				}
			};


			/**
			* Speichert eine Regel
			*/
		   ctrl.saveRegel = function(id)
		   {
			   var neu = $('#lvregel_neu_'+id).val();
			   var lvregeltyp_kurzbz = $('#lvregel_lvregeltyp'+id+' option:selected').val();
			   var parameter = $('#lvregel_parameter'+id).val();
			   var lehrveranstaltung_id = ctrl.lehrveranstaltung_id;
			   var operator = $('#lvregel_operator'+id+' option:selected').val();
			   var studienplan_lehrveranstaltung_id = $('#lvregel_studienplan_lehrveranstaltung_id'+id).val();
			   var lvregel_id_parent = $('#lvregel_lvregel_id_parent'+id).val();
			   var lehrveranstaltung_bezeichnung=$('#lvregel_lehrveranstaltung_span'+id).text();
			   lvregel_id_parent=ClearNull(lvregel_id_parent);

			   // Vorhandene Eintraege werden vor dem Speichern geladen
			   if(neu=='false')
			   {
				   loaddata = {
					   "method": "load",
					   "parameter_0": id
				   };
			   }
			   else
				   loaddata={};

			   savedata = {
				   "lvregeltyp_kurzbz":lvregeltyp_kurzbz,
				   "parameter":parameter,
				   "lehrveranstaltung_id":lehrveranstaltung_id,
				   "operator":operator,
				   "studienplan_lehrveranstaltung_id":studienplan_lehrveranstaltung_id,
				   "lvregel_id_parent":lvregel_id_parent
			   };

			   $http({
					method: 'POST',
					url: "../../../soap/fhcomplete.php",
					data: $.param({
						"typ": "json",
						"class": "lvregel",
						"method": "save",
						"loaddata": JSON.stringify(loaddata),
						"savedata": JSON.stringify(savedata)
					}),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if(response.data.error=='true')
						alert('Fehler:'+response.data.errormsg);
					else
					{
						// Gespeicherte Zeile neue Zeichnen
						//$('#lvregel_li'+id).parent().append(drawRegel(data.result[0]));
						response.data.result[0].lehrveranstaltung_bezeichnung=lehrveranstaltung_bezeichnung;
						var html = $(ctrl.drawRegel(response.data.result[0])).insertAfter('#lvregel_li'+id);
						$compile(html)(scope);
						// Neu Zeile entfernen
						$('#lvregel_li'+id).remove();
						ctrl.LVRegelAddAutocomplete();
						ctrl.loadRegeln(studienplan_lehrveranstaltung_id);
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
		   };

		   /**
			* Loescht eine Regel
			*/
		   ctrl.deleteRegel = function(id)
		   {
			   $http({
					method: 'POST',
					url: "../../../soap/fhcomplete.php",
					data: $.param({
						"typ": "json",
						"class": "lvregel",
						"method": "delete",
						"parameter_0":id
					}),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if(response.data.error=='true')
						alert('Fehler:'+response.data.errormsg);
					else
						$('#lvregel_li'+id).remove();
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};

			ctrl.LVRegelAddAutocomplete = function()
			{
				for(var i in ctrl.LVREGELLehrveranstaltungAutocompleteArray)
				{
					$('#lvregel_lehrveranstaltung_id_autocomplete'+ctrl.LVREGELLehrveranstaltungAutocompleteArray[i]).hide();
					$('#lvregel_lehrveranstaltung_id_autocomplete_input'+ctrl.LVREGELLehrveranstaltungAutocompleteArray[i]).hide();
				}
			};

			ctrl.editLehrveranstaltung = function()
			{
				var row = $("#stplTreeGrid").treegrid('getSelected');
				ctrl.dialog(row);
			}
		});

function generateChildren(item, sem)
{
	var children = [];
//	if (item.children.length != 0)
//	{
//		$(item.children).each(function (i, v)
//		{
//			children.push(generateChildren(v, sem));
//		});
//	}
	var node = {};
	node.id = item.studienplan_lehrveranstaltung_id;
	node.lehrveranstaltung_id = item.lehrveranstaltung_id;
	node.bezeichnung = item.bezeichnung;
	node.type = item.lehrtyp_kurzbz;
	switch(item.lehrtyp_kurzbz)
	{
		case "lv":
			node.iconCls = "icon-lv";
			break;
		case "modul":
			node.iconCls = "icon-module";
			break;
		case "lf":
			node.iconCls = "icon-lv";
			break;
		default:
			node.iconCls = "icon-lv";
			break;
	}
	node.sem = sem;
	node.ects = item.ects;
	node.semesterstunden = item.semesterstunden;
	node.lehrform_kurzbz = item.lehrform_kurzbz;
	node.lvnr = item.lvnr;
	node.kurzbz = item.kurzbz;
	node.semester = item.semester;
	node.sprache = item.sprache;
	node.bezeichnung_english = item.bezeichnung_english;
	node.sws = item.sws;
	node.orgform_kurzbz = item.orgform_kurzbz;
	node.incoming = item.incoming;
	node.oe_kurzbz = item.oe_kurzbz;
	node.semesterwochen = item.semesterwochen;
	node.lvs = item.lvs;
	node.alvs = item.alvs;
	node.lvps = item.semesterstunden;
	node.las = item.las;
	node.benotung = item.benotung;
	node.zeugnis = item.zeugnis;
	node.lvinfo = item.lvinfo;
	node.curriculum = item.curriculum;
	node.stpllv_pflicht = item.stpllv_pflicht;
	node.export = item.export;
	node.lehrauftrag = item.lehrauftrag;
	node.lehre = item.lehre;
	node.genehmigung = item.genehmigung;
	if (children.length != 0)
	{
		node.children = children;
		node.state = 'closed';
	}

	return node;
}

function changeTreeIcons(divId, treeId, target)
{
	//remove tree-file class to change empty sem node to folder
	$(".tree-folder.tree-file").each(function(index, value){
		$(value).removeClass("tree-file");
	});

	//remove tree-file class to change empty sem node to folder after move
	$(".tree-folder-open.tree-file").each(function(index, value){
		$(value).removeClass("tree-file tree-folder-open").addClass("tree-folder");
	});

	$("#"+divId).find('tr[node-id] td[field]:nth-child(1)').each(function(i,v)
	{
		var ele = $("#"+treeId).treegrid('find', $(v).parent().attr("node-id"));
		var node = $(v).find("span.tree-icon");

		//add tree hit if node gets children after drop
		if((!$(node).hasClass("tree-folder-open")) && (ele.children !== undefined) && (ele.children.length > 0) && (target === ele))
		{
			$(node).addClass("tree-folder-open");
			$(node).prev("span").addClass("tree-hit");
			$(node).prev("span").addClass("tree-expanded");
			$(node).prev("span").removeClass("tree-indent");
		}
	});
}

function booleanToIconFormatter(value)
{
	if(value === true)
		return '<span aria-hidden="true" class="glyphicon glyphicon-ok green"></span>';
	else if(value === false)
		return '<span aria-hidden="true" class="glyphicon glyphicon-remove red"></span>';
	else
		return "";
}

/**
 * Entfernt Null Werte
 */
function ClearNull(value)
{
	if(value===null)
		return '';
	else
		return value;
}

function LVRegelTypChange(id)
{
	var typ = $('#lvregel_lvregeltyp'+id+' option:selected').val();

	if(typ=='lvpositiv' || typ=='lvpositivabschluss')
	{
		$('#lvregel_lehrveranstaltung_data'+id).show();
		$('#lvregel_parameter'+id).hide();
	}
	else
	{
		$('#lvregel_lehrveranstaltung_data'+id).hide();
		$('#lvregel_parameter'+id).show();
	}
};

function LVRegelShowAutocomplete(lvregel_id,show)
{
	if(show)
	{
		$('#lvregel_lehrveranstaltung_id_autocomplete'+lvregel_id).show();
		$('#lvregel_lehrveranstaltung_id_autocomplete_input'+lvregel_id).show();
		$('#lvregel_lehrveranstaltung_id_autocomplete_input'+lvregel_id).focus();
		$('#lvregel_lehrveranstaltung_id_autocomplete_input'+lvregel_id).select();
		$('#lvregel_lehrveranstaltung_span'+lvregel_id).hide();
	}
	else
	{
		$('#lvregel_lehrveranstaltung_id_autocomplete'+lvregel_id).hide();
		$('#lvregel_lehrveranstaltung_id_autocomplete_input'+lvregel_id).hide();
		$('#lvregel_lehrveranstaltung_span'+lvregel_id).show();
	}
}

function setLvBezeichnung(lvregel_id)
{
	$('#lvregel_lehrveranstaltung_span'+lvregel_id).text($("#lvregel_lehrveranstaltung_id_autocomplete"+lvregel_id+" option:selected").text());
}
