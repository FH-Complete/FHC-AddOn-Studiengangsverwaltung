angular.module('stgv2')
		.controller('StoTaetigkeitsfelderCtrl', function ($scope, $http, $state, $stateParams, errorService, successService, $compile) {
			$scope.stoid = $stateParams.stoid;
			var ctrl = this;
			var scope = $scope;
			ctrl.data = new Taetigkeitsfeld();
			ctrl.data.studienordnung_id = $scope.stoid;
			ctrl.temp = {
				branchen: "",
				positionen: "",
				aufgaben: ""
			}

			$http({
				method: 'GET',
				url: './api/studienordnung/taetigkeitsfelder/taetigkeitsfelder.php?stoId=' + $scope.stoid
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					if (response.data.info.length > 0)
					{
						ctrl.data = response.data.info[0];
//						ctrl.data.data = JSON.parse(ctrl.data.data);
						$(ctrl.data.data.branchen.elements).each(function(key, value)
						{
							ctrl.drawListItem("list_branchen",value);
						});
						
						$(ctrl.data.data.positionen.elements).each(function(key, value)
						{
							ctrl.drawListItem("list_positionen",value);
						});
						
						$(ctrl.data.data.aufgaben.elements).each(function(key, value)
						{
							ctrl.drawListItem("list_aufgaben",value);
						});
						
					}
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});

			ctrl.save = function () {
				var saveData = {data: ""}
				saveData.data = angular.copy(ctrl.data);
				saveData.data.data = JSON.stringify(saveData.data.data);
				$http({
					method: 'POST',
					url: './api/studienordnung/taetigkeitsfelder/save_taetigkeitsfelder.php',
					data: $.param(saveData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						successService.setMessage("Daten erfolgreich gespeichert.");
						ctrl.data.taetigkeitsfeld_id = response.data.info[0];
						console.log(ctrl.data.data);
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};

			ctrl.drawListItem = function (list_id, text)
			{
				var listItem = '<li class="list-group-item">' + text + '<span class="badge" ng-click="ctrl.removeListItem($event)"><span class="glyphicon glyphicon-trash"></span></span></li>';
				var html = $("#" + list_id).append(listItem);
				$compile(html)(scope);
				ctrl.temp = {
					branchen: "",
					positionen: "",
					aufgaben: ""
				};
			};

			ctrl.addListItem = function (list_id, name)
			{
				var text = "";
				switch (name)
				{
					case "branchen":
						text = ctrl.temp.branchen;
						break;
					case "positionen":
						text = ctrl.temp.positionen;
						break;
					case "aufgaben":
						text = ctrl.temp.aufgaben;
						break;
					default:
						break;
				}

				if (text !== "")
				{
					ctrl.drawListItem(list_id, text);
					ctrl.parseJson();
//					ctrl.save();
				}
			};

			ctrl.removeListItem = function (event)
			{
				$(event.target).parent().parent().remove();
				ctrl.parseJson();
				ctrl.save();
			};

			ctrl.parseJson = function ()
			{
				var branchen = [];
				var positionen = [];
				var aufgaben = [];
				$("#list_branchen li").each(function (key, value)
				{
					branchen.push($(value).text());
				});
				ctrl.data.data.branchen.elements = branchen;

				$("#list_positionen li").each(function (key, value)
				{
					positionen.push($(value).text());
				});
				ctrl.data.data.positionen.elements = positionen;

				$("#list_aufgaben li").each(function (key, value)
				{
					aufgaben.push($(value).text());
				});
				ctrl.data.data.aufgaben.elements = aufgaben;
			};
		});

function Taetigkeitsfeld()
{
	this.taetigkeitsfeld_id = null;
	this.studienordnung_id = null;
	this.ueberblick = "";
	this.data = {
			"branchen": {
				"fixed": "AbsolventInnen des Studienganges können in folgenden Kernbranchen tätig sein:",
				"elements": []
			},
			"positionen": {
				"fixed": "Aufgrund der Qualifikationsziele des Studienganges können die AbsolventInnen beispielhaft die folgenden Positionen und Funktionen wahrnehmen:",
				"elements": []
			},
			"aufgaben": {
				"fixed": "Aufgrund der Qualifikationsziele des Studienganges können die AbsolventInnen beispielhaft die folgenden Positionen und Funktionen wahrnehmen:",
				"elements": []
			}
		}
}