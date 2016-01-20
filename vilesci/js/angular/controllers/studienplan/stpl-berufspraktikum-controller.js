angular.module('stgv2')
		.controller('stplBerufspraktikumCtrl', function ($scope, $http, $state, $stateParams, errorService, successService, $compile) {
			$scope.studienplan_id = $stateParams.studienplan_id;
			var ctrl = this;
			var scope = $scope;

			ctrl.studienplan = "";
			ctrl.berufspraktikum = new Berufspraktikum();
			ctrl.berufspraktikum.studienplan_id = $scope.studienplan_id;
			ctrl.old = {
				berufspraktikum: new Berufspraktikum()
			};

			//loading Studienplan (regelstudiendauer needed)
			$http({
				method: 'GET',
				url: './api/studienplan/eckdaten/eckdaten.php?studienplan_id=' + $scope.studienplan_id
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.studienplan = response.data.info;
					angular.forEach(ctrl.range(ctrl.studienplan.regelstudiendauer), function (value, index) {
						ctrl.berufspraktikum.data.push({semester: false, dauer: "", ects: "", stunden: ""});
						ctrl.old.berufspraktikum.data.push({semester: false, dauer: "", ects: "", stunden: ""});
					});
					ctrl.loadData();
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});

			ctrl.loadData = function ()
			{
				$http({
					method: 'GET',
					url: './api/studienplan/berufspraktikum/berufspraktikum.php?studienplan_id=' + $scope.studienplan_id
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						if (response.data.info.length > 0)
							ctrl.berufspraktikum = response.data.info[0];
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};

			ctrl.range = function (max)
			{
				var values = [];
				for (i = 1; i <= max; i++)
				{
					values.push(i);
				}
				return values;
			};

			ctrl.save = function ()
			{
				//manually validate form
				angular.forEach(ctrl.berufspraktikum.data, function (value, index)
				{
					if (value.semester)
					{
						if ((value.dauer == "") || (value.ects == "") || (value.stunden == ""))
						{
							$scope.form.$valid = false;
							$scope.form.$invalid = true;
							$("#auslandssemester tr td:nth-child(" + (index + 2) + ")").find("div").each(function (i, v) {
								$(v).addClass("has-error");
							});
						}
					}
				});
				
				if ($scope.form.$valid)
				{
					var saveData = {data: ""}
					saveData.data = angular.copy(ctrl.berufspraktikum);
					saveData.data.data = JSON.stringify(saveData.data.data);
					$http({
						method: 'POST',
						url: './api/studienplan/berufspraktikum/save_berufspraktikum.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if (response.data.erfolg)
						{
							successService.setMessage("Daten erfolgreich gespeichert.");
							ctrl.berufspraktikum.berufspraktikum_id = response.data.info[0];
							$scope.form.$setPristine();
						}
						else
						{
							errorService.setError(getErrorMsg(response));
						}
					}, function error(response) {
						errorService.setError(getErrorMsg(response));
					});
				}
			};

			ctrl.changed = function (index, val)
			{				
				if(ctrl.berufspraktikum.data[index].semester)
				{
					ctrl.berufspraktikum.data[index].dauer = angular.copy(ctrl.old.berufspraktikum.data[index].dauer);
					ctrl.berufspraktikum.data[index].ects = angular.copy(ctrl.old.berufspraktikum.data[index].ects);
					ctrl.berufspraktikum.data[index].stunden = angular.copy(ctrl.old.berufspraktikum.data[index].stunden);
				}
				else
				{
					ctrl.old.berufspraktikum.data[index].dauer = angular.copy(ctrl.berufspraktikum.data[index].dauer);
					ctrl.old.berufspraktikum.data[index].ects = angular.copy(ctrl.berufspraktikum.data[index].ects);
					ctrl.old.berufspraktikum.data[index].stunden = angular.copy(ctrl.berufspraktikum.data[index].stunden);
					ctrl.berufspraktikum.data[index] = {semester: false, dauer: "", ects: "", stunden: ""};
				}
			};
		});

function Berufspraktikum()
{
	this.berufspraktikum_id = null;
	this.studienplan_id = null;
	this.erlaeuterungen = null;
	this.data = [];
}