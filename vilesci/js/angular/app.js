var stgv2 = angular.module("stgv2", ['ui.router', 'ngSanitize', 'angularFileUpload', 'angular-storage'], function ($httpProvider) {
	$httpProvider.defaults.headers.post['Content-Type'] = 'studienplan_idication/x-www-form-urlencoded;charset=utf-8';

	//initialize get if not there
    if (!$httpProvider.defaults.headers.get) {
        $httpProvider.defaults.headers.get = {};
    }
	//disable IE ajax request caching
    $httpProvider.defaults.headers.get['If-Modified-Since'] = 'Mon, 26 Jul 1997 05:00:00 GMT';
    // extra
    $httpProvider.defaults.headers.get['Cache-Control'] = 'no-cache';
    $httpProvider.defaults.headers.get['Pragma'] = 'no-cache';
});

angular.module("stgv2")
		.controller("AppCtrl", function ($state, errorService, $http, StudiengangService, StoreService)
		{
			var storeList = ["studiengangList", "standortList", "orgformList", "aenderungsvarianteList","akadgradList","studiensemesterList","studienordnungStatusList","studienplan", "studienordnung"];

			angular.forEach(storeList, function(v, i){
				StoreService.remove(v);
			});

			$('#west_tree').tree({
				url: "./api/studiengang/studiengang.php",
				method: "get",
				animate: "true",
				dnd: "true",
				loadFilter: function (data)
				{
					if (data.erfolg)
					{
//						StudiengangService.setStudiengangList(data.info);
						return data.info;
					}
					else
					{
						//TODO Fehler ausgeben data.message
					}

				},
				onLoadSuccess: function (rootNode, data)
				{
					data.forEach(function (node, v) {
						writeAttributesFromJson(node);
					});
					$('.tree-title').bind('click', function (event) {
						var ele = $(event.target);
						if (ele.attr('node_type') !== undefined)
							angular.element($("#west_tree")).scope().load(ele);
					});

				},
				onClick: function (node)
				{
					return true;
				},
				onBeforeDrop: function (target, source, point)
				{
					toAppend = $.extend(true, {}, source);
					$(this).tree('append', {
						parent: target,
						data: toAppend,
					});
					return false;
				},
				onDragEnter: function (target, source)
				{
					return false;
				}
			});

			function writeAttributesFromJson(node) {
				if (node.attributes)
				{
					node.attributes.forEach(function (attr, value)
					{
						$("#" + node.domId + " span").last().attr(attr.name, attr.value);
					});
				}

				if (node.children)
				{
					node.children.forEach(function (node) {
						writeAttributesFromJson(node);
					});
				}
			};

			function detectIE() {
				var ua = window.navigator.userAgent;

				var msie = ua.indexOf('MSIE ');
				if (msie > 0) {
					// IE 10 or older => return version number
					return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
				}

				var trident = ua.indexOf('Trident/');
				if (trident > 0) {
					// IE 11 => return version number
					var rv = ua.indexOf('rv:');
					return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
				}

				var edge = ua.indexOf('Edge/');
				if (edge > 0) {
					// Edge (IE 12+) => return version number
					return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
				}

				// other browser
				return false;
			}

			if (detectIE() !== false)
			{
				//TODO EXCLUDE INTERNET EXPLORER
//				alert("Internet Explorer is not Supported now. Please try Firefox or Google Chrome.");
			}

			var ctrl = this;
			ctrl.user = {
				name: "",
				lastname: ""
			};

			$http({
				method: "GET",
				url: "./api/helper/user.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.user.name = response.data.info.vorname;
					ctrl.user.lastname = response.data.info.nachname;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
		})
		.controller("MenuCtrl", function ($scope, $state, $compile, $stateParams, errorService, $http, successService, StudienordnungStatusService) {
			var ctrl = this;
			ctrl.studienordnung_id = "";
			ctrl.statusList = "";

			//loading SpracheList
			StudienordnungStatusService.getStudienordnungStatusList().then(function(result){
				ctrl.statusList = result;
				$compile($('#mm1').contents())($scope);
				$compile($('#mm2').contents())($scope);

				var item = $('#mm2').menu('findItem', 'Löschen');
				$('#mm2').menu('appendItem',
				{
					parent: item.target,
					text: "Studienordnung",
					onclick: function () {
						ctrl.delete("studienordnung");
					}
				});

				$('#mm2').menu('appendItem',
				{
					parent: item.target,
					text: "Studienplan",
					onclick: function () {
						ctrl.delete("studienplan");
					}
				});

				var item = $('#mm3').menu('findItem', 'Status ändern zu');

				$(ctrl.statusList).each(function (i, v) {
					$('#mm3').menu('appendItem',
					{
						parent: item.target,
						text: v.bezeichnung,
						onclick: function () {
							ctrl.changeStatus(v.status_kurzbz);
						}
					});
				});

				$compile($('#mm4').contents())($scope);
				$compile($('#mm5').contents())($scope);
			},function(error){
				errorService.setError(getErrorMsg(error));
			});

//			$http({
//				method: "GET",
//				url: "./api/helper/studienordnungStatus.php"
//			}).then(function success(response) {
//				if (response.data.erfolg)
//				{
//					ctrl.statusList = response.data.info;
//					$compile($('#mm1').contents())($scope);
//					$compile($('#mm2').contents())($scope);
//
//					var item = $('#mm2').menu('findItem', 'Löschen');
//					$('#mm2').menu('appendItem',
//					{
//						parent: item.target,
//						text: "Studienordnung",
//						onclick: function () {
//							ctrl.delete("studienordnung");
//						}
//					});
//
//					$('#mm2').menu('appendItem',
//					{
//						parent: item.target,
//						text: "Studienplan",
//						onclick: function () {
//							ctrl.delete("studienplan");
//						}
//					});
//
//					var item = $('#mm3').menu('findItem', 'Status ändern zu');
//
//					$(ctrl.statusList).each(function (i, v) {
//						$('#mm3').menu('appendItem',
//						{
//							parent: item.target,
//							text: v.bezeichnung,
//							onclick: function () {
//								ctrl.changeStatus(v.status_kurzbz);
//							}
//						});
//					});
//
//					$compile($('#mm4').contents())($scope);
//
//				}
//				else
//				{
//					errorService.setError(getErrorMsg(response));
//				}
//			}, function error(response) {
//				errorService.setError(getErrorMsg(response));
//			});


			ctrl.createStudienordnung = function ()
			{
				$state.go('studienordnungNeu');
			};
			ctrl.createStudienplan = function ()
			{
				var sto = $("#treeGrid").treegrid('getSelected');
				if ((sto != null) && (sto.attributes[0].value == "studienordnung"))
				{
					ctrl.studienordnung_id = sto.studienordnung_id;
					$state.go('studienplanNeu', {"studienordnung_id": ctrl.studienordnung_id});
				}
				else if ($stateParams.studienordnung_id != null)
				{
					ctrl.studienordnung_id = $stateParams.studienordnung_id;
					$state.go('studienplanNeu', {"studienordnung_id": ctrl.studienordnung_id});
				}
				else
				{
					errorService.setError("Bitte zuerst eine Studienordnung auswählen.", "info");
				}
			};

			ctrl.changeStatus = function (status)
			{
				var sto = $("#treeGrid").treegrid('getSelected');
				if ((sto != null) && (sto.attributes[0].value == "studienordnung"))
				{
					ctrl.studienordnung_id = sto.studienordnung_id;
					$http({
						method: "GET",
						url: "./api/studienordnung/changeStatus.php?studienordnung_id=" + ctrl.studienordnung_id + "&state=" + status
					}).then(function success(response) {
						if (response.data.erfolg)
						{
							$("#treeGrid").treegrid('reload');
							successService.setMessage(response.data.info);
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
					alert("Bitte zuerst eine Studienordnung auswählen.");
				}
			};

			ctrl.delete = function (type)
			{
				var node = $('#treeGrid').treegrid('getSelected');
				if(node != null)
				{
					switch (type)
					{
						case "studienplan":
							if(node.studienplan_id !== undefined)
							{
								if(confirm("Wollen Sie den Studienplan wirklich löschen?"))
								{
									$http({
										method: "GET",
										url: "./api/studienplan/delete_studienplan.php?studienplan_id=" + node.studienplan_id
									}).then(function success(response) {
										if (response.data.erfolg)
										{
											$("#treeGrid").treegrid('reload');
											successService.setMessage(response.data.info);
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
							else
							{
								alert("Bitte einen Studienplan auswählen.");
							}
							break;

						case "studienordnung":
							if(node.studienordnung_id !== undefined)
							{
								if(confirm("Wollen Sie die Studienordnung wirklich löschen?"))
								{
									$http({
										method: "GET",
										url: "./api/studienordnung/delete_studienordnung.php?studienordnung_id=" + node.studienordnung_id
									}).then(function success(response) {
										if (response.data.erfolg)
										{
											$("#treeGrid").treegrid('reload');
											successService.setMessage(response.data.info);
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
							else
							{
								alert("Bitte eine Studienordnung auswählen.");
							}
							break;
						default:
							alert("Bitte eine Studienordnung oder einen Studienplan auswählen.");
							break;
					}
				}
				else
				{
					alert("Bitte eine Studienordnung oder einen Studienplan auswählen.");
				}
			};

			ctrl.diff = function ()
			{
				var sto = $("#treeGrid").treegrid('getSelected');
				if ((sto != null) && (sto.attributes[0].value == "studienordnung"))
				{
					ctrl.studienordnung_id = sto.studienordnung_id;
					$state.go('studienordnungDiff', {"studienordnung_id": ctrl.studienordnung_id, "stgkz": sto.studiengang_kz});
				}
				else
				{
					$state.go('studienordnungDiff', {"stgkz": $stateParams.stgkz});
				}
			};

			ctrl.plausicheck = function () {
				var node = $('#treeGrid').treegrid('getSelected');
				if (node != null && node.studienplan_id !== undefined) {
					window.open("./checkstudienplan.php?studienplan_id=" + node.studienplan_id, '_blank');
				}
				else
				{
					window.open("./checkstudienplan.php", '_blank');
				}
			};


			ctrl.export = function (format, lvinfo)
			{
				var sto = $("#treeGrid").treegrid('getSelected');
				if ((sto != null) && (sto.attributes[0].value == "studienordnung"))
				{
					if(lvinfo)
						lvinfo='&lvinfo=true';
					else
						lvinfo='';
					window.location.href="export.php?studienordnung_id="+sto.studienordnung_id+"&output="+format+lvinfo;
				}
				else
				{
					alert('Bitte wählen Sie zuerst eine Studienordnung');
				}
			};
		})
		.controller("studienordnungTabCtrl", function ($scope, $state, $filter) {
			//TODO tabs from config
			$scope.tabs = [
				{label: 'Metadaten', link: '.metadaten'},
				{label: 'Dokumente', link: '.dokumente'},
				{label: 'Eckdaten', link: '.eckdaten'},
				{label: 'Tätigkeitsfelder', link: '.taetigkeitsfelder'},
				{label: 'Qualifikationsziele', link: '.qualifikationsziele'},
				{label: 'Zugangsvoraussetzungen', link: '.zgv'},
				{label: 'Aufnahmeverfahren', link: '.aufnahmeverfahren'}
			];

			$scope.selectedTab = $scope.tabs[0];
			$scope.setSelectedTab = function (tab)
			{
				$scope.selectedTab = tab;
			};

			//set substate from url
			var substate = $state.current.name.split(".");
			if(substate.length === 2)
			{
				substate = "."+substate[1];
				var tab = $filter('filter')($scope.tabs,{link:substate},true);
				if(tab.length === 1)
					$scope.setSelectedTab(tab[0]);
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
			}
		})
		.controller("studienplanTabCtrl", function ($scope, $state, $filter) {
			//TODO tabs from config

			$scope.tabs = [
				{label: 'Metadaten', link: '.metadaten'},
				{label: 'Eckdaten', link: '.eckdaten'},
				{label: 'Gültigkeit', link: '.gueltigkeit'},
//				{label: 'Module', link: '.module'},
				{label: 'Studienplanmatrix', link: '.lehrveranstaltungen'},
				{label: 'Auslandssemester', link: '.auslandssemester'},
				{label: 'Berufspraktikum', link: '.berufspraktikum'},
				{label: 'Studienjahr', link: '.studienjahr'},
				{label: 'Gemeinsames Studienprogramm', link: '.studienprogramm'}
			];

			$scope.selectedTab = $scope.tabs[0];
			$scope.setSelectedTab = function (tab)
			{
				//expand left tree and north treegrid when leaving LV tab
				if ($scope.selectedTab.link === ".lehrveranstaltungen")
				{
					$('#layoutWrapper').layout('expand', 'west');
					$('#centerLayout').layout('expand', 'north');
				}
				$scope.selectedTab = tab;
				//collapse left tree and north treegrid when entering LV tab
				if (tab.link === ".lehrveranstaltungen")
				{
					$('#layoutWrapper').layout('collapse', 'west');
					$('#centerLayout').layout('collapse', 'north');
				}
			};

			//set substate from url
			var substate = $state.current.name.split(".");
			if(substate.length === 2)
			{
				substate = "."+substate[1];
				var tab = $filter('filter')($scope.tabs,{link:substate},true);
				if(tab.length === 1)
					$scope.setSelectedTab(tab[0]);
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
			}
		})
		.controller("TreeCtrl", function ($scope, $state) {
			$scope.load = function (ele)
			{
				var target = $(ele).attr("node_type");
				var parent = $(ele).parent();
				var node = $('#west_tree').tree("getNode", parent);
				var params = node.attributes[0].urlParams;
				$state.go(target, params[0]);
			};
		})
		.controller("TreeGridCtrl", function ($scope, $state) {
			$scope.load = function (row)
			{
				var target = row.attributes[0].value;
				var params = row.attributes[0].urlParams;
				$state.go(target, params[0]);
			};
		});