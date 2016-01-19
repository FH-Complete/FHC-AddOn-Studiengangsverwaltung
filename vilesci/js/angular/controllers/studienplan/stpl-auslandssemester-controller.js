angular.module('stgv2')
		.controller('stplAuslandssemesterCtrl', function ($scope, $http, $state, $stateParams, errorService, successService, $compile) {
			$scope.stplid = $stateParams.stplid;
			var ctrl = this;
			var scope = $scope;

			ctrl.studienplan = "";
			ctrl.auslandssemester = new Auslandssemester();
			ctrl.auslandssemester.studienplan_id = $scope.stplid;

			//loading Studienplan (regelstudiendauer needed)
			$http({
				method: 'GET',
				url: './api/studienplan/eckdaten/eckdaten.php?stplId=' + $scope.stplid
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.studienplan = response.data.info;
					angular.forEach(ctrl.range(ctrl.studienplan.regelstudiendauer), function (value, index) {
						ctrl.auslandssemester.data.push({optional: false, verpflichtend: false});
					});

				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});

			$http({
				method: 'GET',
				url: './api/studienplan/auslandssemester/auslandssemester.php?stplid=' + $scope.stplid
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.auslandssemester = response.data.info[0];
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});

			ctrl.range = function (max)
			{
				var values = [];
				for (i = 1; i <= max; i++)
				{
					values.push(i);
				}
				return values;
			}

			ctrl.save = function ()
			{
				if ($scope.form.$valid)
				{
					var saveData = {data: ""}
					saveData.data = angular.copy(ctrl.auslandssemester);
					saveData.data.data = JSON.stringify(saveData.data.data);
					$http({
						method: 'POST',
						url: './api/studienplan/auslandssemester/save_auslandssemester.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if (response.data.erfolg)
						{
							successService.setMessage("Daten erfolgreich gespeichert.");
							ctrl.auslandssemester.auslandssemester_id = response.data.info[0];
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
			
			ctrl.changed = function(index, val)
			{
				if(val === 'optional')
				{
					if(ctrl.auslandssemester.data[index][val])
						ctrl.auslandssemester.data[index].verpflichtend = false;
				}
				
				if(val === 'verpflichtend')
				{
					if(ctrl.auslandssemester.data[index][val])
						ctrl.auslandssemester.data[index].optional = false;
				}
			};
		});

function Auslandssemester()
{
	this.auslandssemester_id = null;
	this.studienplan_id = null;
	this.erlaeuterungen = null;
	this.data = [];
}