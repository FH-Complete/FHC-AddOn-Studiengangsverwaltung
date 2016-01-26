angular.module('stgv2')
		.controller('StplLehrveranstaltungCtrl', function ($scope, $http, $state, $stateParams, errorService, $compile, StudienplanService) {
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
					treeField: "name",
					rownumbers: true,
					fit: true,
					columns: [[
						{field: 'name', editor:'text', width:'300', title:'Lehrveranstaltung'},
						{field: 'ects',align: 'right', editor:'numberbox', title:'ECTS'},
						{field: 'semesterstunden',align: 'right', editor:'numberbox', title:'Semesterstunden'},
						{field: 'lehrform_kurzbz',align: 'right', editor:'text', title:'Lehrform'},
						{field: 'lvnr',align: 'right', editor:'numberbox', title:'LVNR'},
						{field: 'benotung',align: 'right', editor:'checkbox', title:'Benotung'},
						{field: 'zeugnis',align: 'right', editor:'checkbox', title:'Zeugnis'},
						{field: 'lvinfo',align: 'right', editor:'checkbox', title:'LV-Info'},
						{field: 'curriculum',align: 'right', editor:'checkbox', title:'Curriculum'},
						{field: 'stpllv_pflicht',align: 'right', editor:'checkbox', title:'Pflicht'}
					]],
					onContextMenu: function(e ,row)
					{
						if (row){
							console.log(row);
							console.log($(this).treegrid('select', row.id));
							e.preventDefault();
							$(this).treegrid('select', row.id);
							$('#stplTreeGridContextMenu').menu('show');
							$('#stplTreeGridContextMenu').menu('show',{
								left: e.pageX,
								top: e.pageY
							});                
						}
					},
					rowStyler: function(row)
					{
						
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
									return a.name > b.name;
								});
								
								//sort by type -> modules after lv
								children.sort(function(a,b){
									return a.type > b.type;
								});

								var node = {};
								node.id = i + '_sem';
								node.name = i + '. Semester';
								node.type = "sem";
								node.sem = i;
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
						console.log(row);
						if (row.type != "sem")
						{
							ctrl.meta = row;
							ctrl.meta.oe = ctrl.getOeName(ctrl.meta.oe_kurzbz);
							$scope.$apply();
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
									//TODO workaround to change icon
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
					},
					onContextMenu: function (e, row)
					{
						console.log(row);
						if (row && row.type != "sem" && ctrl.studienplan.status_kurzbz === "development") {
							e.preventDefault();
							$(this).treegrid('select', row.id);
							$('#stplTreeGridContextMenu').menu();
							$('#stplTreeGridContextMenu').menu('show', {
								left: e.pageX,
								top: e.pageY
							});
						}
					}
				});
                        };

			StudienplanService.getStudienplan($scope.studienplan_id).then(function(result){
				console.log(result);
				ctrl.studienplan = result;
				ctrl.initSemesterList();
				ctrl.initStplTree();
			}, function(error){
				
			});
//			//get Selected Studienplan if selected in TreeGrid
//			var node = $("#treeGrid").treegrid('getSelected');
//			if(node)
//			{
//				ctrl.studienplan = node;
//				ctrl.initSemesterList();
//				ctrl.initStplTree();
//			}
//			else
//			{
//				//if not selected get data from DB
//				$http({
//					method: 'GET',
//					url: './api/studienplan/eckdaten/eckdaten.php?studienplan_id=' + $scope.studienplan_id
//				}).then(function success(response) {
//					if (response.data.erfolg)
//					{
//						ctrl.studienplan = response.data.info;
//						ctrl.initSemesterList();
//						ctrl.initStplTree();
//					}
//					else
//					{
//						errorService.setError(getErrorMsg(response));
//					}
//				}, function error(response) {
//					errorService.setError(getErrorMsg(response));
//				});
//			}

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
			
			$("#lvTreeGrid").treegrid();

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
					treeField: 'name',
					rownumbers: true,
					loadFilter: function (data)
					{
						if (data.erfolg)
						{
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
						console.log(row);
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
						changeTreeIcons("lvTree", "lvTreeGrid");
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
						modal: true
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
	node.name = item.bezeichnung;
	node.type = item.lehrtyp_kurzbz;
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
	if (children.length != 0)
	{
		node.children = children;
		node.state = 'closed';
	}

	return node;
}

function changeTreeIcons(divId, treeId, target)
{
	//workaround to change icon
	$("#"+divId).find('tr[node-id] td[field]:nth-child(1)').each(function(i,v)
	{
		var ele = $("#"+treeId).treegrid('find', $(v).parent().attr("node-id"));
		if(ele.type !== "sem")
		{
			var node = $(v).find("span.tree-icon");
			if(ele.type === "modul")
			{
				$(node).addClass("icon-module");
			}
			else if(ele.type === "lv")
			{
				$(node).addClass("icon-lv");
			}
			else
			{
				$(node).addClass("tree-file");
			}
		}
		else
		{
			var node = $(v).find("span.tree-icon");
			//change file icon to empty folder
			if($(node).hasClass("tree-file"))
			{
				$(node).removeClass("tree-file");
				$(node).addClass("tree-folder");
			}
			
			//change open folder icon to empty folder icon if node has no children
			if($(node).hasClass("tree-folder-open") && ele.children.length === 0)
			{
				$(node).removeClass("tree-folder-open");
			}
			
			//add tree hit if node gets children after drop
			if((!$(node).hasClass("tree-folder-open")) && (ele.children !== undefined) && (ele.children.length > 0) && (target === ele))
			{
				$(node).addClass("tree-folder-open");
				$(node).prev("span").addClass("tree-hit");
				$(node).prev("span").addClass("tree-expanded");
				$(node).prev("span").removeClass("tree-indent");
			}
		}
	});
}

