angular.module('stgv2')
		.controller('StplLehrveranstaltungCtrl', function ($scope, $http, $state, $stateParams, errorService, $compile, StudienplanService, successService) {
			$scope.studienplan_id = $stateParams.studienplan_id;
			var ctrl = this;
			var scope = $scope;
			ctrl.data = "";
			ctrl.meta = {
				name: "",
				ects: ""
			};
			ctrl.studienplan = "";
			ctrl.oeList = "";
			ctrl.oe_kurzbz = "";
			ctrl.lehrtypList = "";
			ctrl.lehrtyp_kurzbz = "lv";
			ctrl.semester = "null";
			ctrl.semesterList = [{
					"key": "null",
					"value": "alle"
				}];
			var editingId = null;
			
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
				$("#stplTreeGrid").treegrid({
					url: "./api/studienplan/lehrveranstaltungen/lehrveranstaltungTree.php?studienplan_id=" + $scope.studienplan_id,
					idField: "id",
					treeField: "bezeichnung",
					rownumbers: true,
					fit: true,
					toolbar: [{
						id: 'saveChanges',
						disabled: true,
						iconCls: 'icon-save',
						text: 'Speichern',
						handler: function(){
							if(editingId !== null)
							{
								$("#stplTreeGrid").treegrid('endEdit', editingId);
								$("#saveChanges").linkbutton("disable");
								$("#editNode").linkbutton("disable");
							}
						}
					},{
						id: 'deleteNode',
						disabled: true,
						iconCls: 'glyphicon glyphicon-remove red',
						text: 'LV löschen',
						handler: function(){
							$("#deleteNode").linkbutton("disable");
							ctrl.removeStudienplanLehrveranstaltung();
						}
					},{
						id: 'editNode',
						disabled: true,
						iconCls: 'icon-edit',
						text: 'LV editieren',
						handler: function(){
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
					}],
					columns: [[
						{field: 'bezeichnung', width:'300', title:'Lehrveranstaltung'},
						{field: 'ects',align: 'right', title:'ECTS'},
						{field: 'semesterstunden',align: 'right', title:'Semesterstunden'},
						{field: 'lehrform_kurzbz',align: 'right', title:'Lehrform'},
						{field: 'lvnr',align: 'right', title:'LVNR'},
						{field: 'curriculum',align: 'center', /*editor: {type: 'checkbox'},*/ title:'Studienplan', formatter: booleanToIconFormatter},
						{field: 'export',align: 'center', /*editor: {type: 'checkbox'},*/ title:'Export', formatter: booleanToIconFormatter},
						{field: 'benotung',align: 'center', /*editor: {type: 'checkbox'},*/ title:'Benotung', formatter: booleanToIconFormatter},
						{field: 'zeugnis',align: 'center', /*editor: {type: 'checkbox'},*/ title:'Zeugnis', formatter: booleanToIconFormatter},
						{field: 'lvinfo',align: 'center', /*editor: {type: 'checkbox'},*/ title:'LV-Info', formatter: booleanToIconFormatter},
						{field: 'lehrauftrag',align: 'center', /*editor: {type: 'checkbox'},*/ title:'Lehrauftrag', formatter: booleanToIconFormatter},
						{field: 'lehre',align: 'center', /*editor: {type: 'checkbox'},*/ title:'Lehre', formatter: booleanToIconFormatter}
					]],
					onContextMenu: function(e ,row)
					{
						if (row && row.type != "sem" && ctrl.studienplan.status_kurzbz === "development") {
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
							var tree = [];
							$(data.info).each(function (i, v)
							{
								if (v.stpllv_semester == 0)
								{
									tree.push(generateChildren(v));
								}
							});
							for (var i = 1; i <= ctrl.studienplan.regelstudiendauer; i++)
							{
								var children = [];
								$(data.info).each(function (j, v)
								{
									if (v.stpllv_semester == i)
									{
										children.push(generateChildren(v, i));
									}
								});
								
								//sort by name
								children.sort(function(a,b){
									return a.bezeichnung > b.bezeichnung;
								});
								
								//sort by type -> modules after lv
								children.sort(function(a,b){
									return a.type > b.type;
								});

								var node = {};
								node.id = i + '_sem';
								node.bezeichnung = i + '. Semester';
								node.type = "sem";
								node.sem = i;
								node.iconCls = "tree-folder";
								if (children.length != 0)
								{
									node.children = children;
									node.state = 'closed';
								}
								tree.push(node);
							}

							return tree;
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
						var curriculum = $(this).treegrid('getColumnOption','curriculum');
						var exportCol = $(this).treegrid('getColumnOption','export');
						var zeugnis = $(this).treegrid('getColumnOption','zeugnis');
						var lvinfo = $(this).treegrid('getColumnOption','lvinfo');
						var lehrauftrag = $(this).treegrid('getColumnOption','lehrauftrag');
						var lehre = $(this).treegrid('getColumnOption','lehre');
						
						if(row.type==="modul")
						{
							//set editors for module editing
							exportCol.editor = {type: "checkbox"};
							lvinfo.editor = {type: "checkbox"};
							lehrauftrag.editor = {type: "checkbox"};
							lehre.editor = {type: "checkbox"};
						}
						else
						{
							var parent = $(this).treegrid('getParent',row.id);
							if(parent.type !== "modul")
							{
								benotung.editor = {type: "checkbox"};
							}
							curriculum.editor = {type: "checkbox"};
							exportCol.editor = {type: "checkbox"};
							zeugnis.editor = {type: "checkbox"};
							lvinfo.editor = {type: "checkbox"};
							lehrauftrag.editor = {type: "checkbox"};
							lehre.editor = {type: "checkbox"};
						}
					},
					onAfterEdit: function(row, changes)
					{
						var lv = $("#stplTreeGrid").treegrid('getSelected');
						var parent = $("#stplTreeGrid").treegrid('getParent', lv.id);

						var benotung = $(this).treegrid('getColumnOption','benotung');
						var curriculum = $(this).treegrid('getColumnOption','curriculum');
						var exportCol = $(this).treegrid('getColumnOption','export');
						var zeugnis = $(this).treegrid('getColumnOption','zeugnis');
						var lvinfo = $(this).treegrid('getColumnOption','lvinfo');
						var lehrauftrag = $(this).treegrid('getColumnOption','lehrauftrag');
						var lehre = $(this).treegrid('getColumnOption','lehre');

						benotung.editor = null;
						curriculum.editor = null;
						exportCol.editor = null;
						zeugnis.editor = null;
						lvinfo.editor = null;
						lehrauftrag.editor = null;
						lehre.editor = null;
							
						//update studienplan_lehrveranstaltung
						var data = {};
						data.semester = lv.sem;
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
						
						if((changes.curriculum !== undefined) || (changes.export !== undefined))
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
					},
					onDrop: function (target, source, point)
					{
						
						var data = {};
						data.semester = target.sem;
						if(target.type != "sem")
						{
							data.studienplan_lehrveranstaltung_id_parent = target.id;
						}
						else
						{
							data.studienplan_lehrveranstaltung_id_parent = "";
						}
						data.pflicht = true;
						data.export = target.export;
						data.curriculum = target.curriculum;
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
									//node-id an neue DB-ID anpassen
									var root = $('#stplTreeGrid').treegrid('getRoot');
									var idView1 = $('tr[node-id='+root.id+']:eq(0)').attr("id").replace(root.id,'');
									var idView2 = $('tr[node-id='+root.id+']:eq(1)').attr("id").replace(root.id,'');
									$($('#stplTreeGrid').treegrid('find', source.id)).attr('node-id', response.data.info[0]);
									$('#'+idView1 + source.id).attr('node-id', response.data.info[0]);
									$('#'+idView1 + source.id).attr('id',idView1 + response.data.info[0]);
									$('#'+idView2 + source.id).attr('node-id', response.data.info[0]);
									$('#'+idView2 + source.id).attr('id',idView2 + response.data.info[0]);
									var  row = $('#stplTreeGrid').treegrid('find', source.id);
									
									row.id = response.data.info[0];
									row.sem = saveData.data.semester;
									//needed to detect later if node is moved in tree or dropped from another tree
									row.moving = true;
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
					ctrl.oeList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
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
				rownumbers: true,
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
				var lehrtyp_kurzbz = $("#lehrtyp").val();
				var semester = $("#semester").val();
				if (semester === "? string: ?")
				{
					semester = null;
				}

				$("#lvTreeGrid").treegrid({
					url: "./api/helper/lehrveranstaltungByOe.php?oe_kurzbz=" + oe_kurzbz + "&lehrtyp_kurzbz=" + lehrtyp_kurzbz + "&semester=" + semester,
					method: 'GET',
					idField: 'id',
					treeField: 'bezeichnung',
					rownumbers: true,
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
			
			ctrl.dialog = function()
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
		});

function generateChildren(item, sem)
{
	var children = [];
	if (item.children.length != 0)
	{
		$(item.children).each(function (i, v)
		{
			children.push(generateChildren(v, sem));
		});
	}
	var node = {};
	node.id = item.studienplan_lehrveranstaltung_id;
	node.lehrveranstaltung_id = item.lehrveranstaltung_id
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
	node.lvps = item.lvps;
	node.las = item.las;
	node.benotung = item.benotung;
	node.zeugnis = item.zeugnis;
	node.lvinfo = item.lvinfo;
	node.curriculum = item.curriculum;
	node.stpllv_pflicht = item.stpllv_pflicht;
	node.export = item.export;
	node.lehrauftrag = item.lehrauftrag;
	node.lehre = item.lehre;
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

