$(document).ready(function () {
	$('#west_tree').tree({
		url: "./api/studiengang/studiengang.php",
		method: "get",
		animate: "true",
		dnd: "true",
		loadFilter: function (data)
		{
			if (data.erfolg)
			{
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
	}
});

var stgv2 = angular.module("stgv2", ['ui.router', 'ngSanitize', 'angularFileUpload'], function ($httpProvider) {
	$httpProvider.defaults.headers.post['Content-Type'] = 'studienplan_idication/x-www-form-urlencoded;charset=utf-8';
});

angular.module("stgv2")
		.controller("AppCtrl", function ($scope, $state, $compile, $stateParams, errorService, $http)
		{
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
			
			if(detectIE() !== false)
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
		.controller("TabsCtrl", function ($scope, $state, $compile, $stateParams, errorService, $http, successService) {
			var ctrl = this;
			ctrl.studienordnung_id = "";
			ctrl.statusList = "";

			$http({
				method: "GET",
				url: "./api/helper/studienordnungStatus.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.statusList = response.data.info;
					$compile($('#mm1').contents())($scope);
					$compile($('#mm2').contents())($scope);

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

				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});


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

			ctrl.delete = function ()
			{
				var node = $('#treeGrid').treegrid('getSelected');
				console.log(node);
				switch (node.attributes[0].value)
				{
					case "studienplan":
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
						break;

					case "studienordnung":
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
						break;
					default:
						alert("Bitte eine Studienordnung oder einen Studienplan auswählen.");
						break;
				}
			};

			ctrl.diff = function ()
			{
				var sto = $("#treeGrid").treegrid('getSelected');
				if ((sto != null) && (sto.attributes[0].value == "studienordnung"))
				{
					console.log(sto);
					ctrl.studienordnung_id = sto.studienordnung_id;
					$state.go('studienordnungDiff', {"studienordnung_id": ctrl.studienordnung_id, "stgkz": sto.stgkz});
				}
				else
				{
					$state.go('studienordnungDiff');
				}
			};
		})
		.controller("studienordnungTabCtrl", function ($scope, $stateParams, $state) {
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
		.controller("studienplanTabCtrl", function ($scope) {
			//TODO tabs from config
			$scope.tabs = [
				{label: 'Metadaten', link: '.metadaten'},
				{label: 'Eckdaten', link: '.eckdaten'},
				{label: 'Gültigkeit', link: '.gueltigkeit'},
				{label: 'Module', link: '.module'},
				{label: 'Lehrveranstaltungen', link: '.lehrveranstaltungen'},
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
		}).controller("TreeGridCtrl", function ($scope, $state) {
	$scope.load = function (row)
	{
		var target = row.attributes[0].value
		var params = row.attributes[0].urlParams;
		$state.go(target, params[0]);
	};
});