angular.module('stgv2')
		.controller('stplBerufspraktikumCtrl', function ($scope, $http, $state, $stateParams, errorService, successService, $compile) {
			$scope.stplid = $stateParams.stplid;
			var ctrl = this;
			var scope = $scope;

			ctrl.studienplan = "";
			ctrl.berufspraktikum = new Berufspraktikum();
			ctrl.berufspraktikum.studienplan_id = $scope.stplid;

			//loading Studienplan (regelstudiendauer needed)
			$http({
				method: 'GET',
				url: './api/studienplan/eckdaten/eckdaten.php?stplId=' + $scope.stplid
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.studienplan = response.data.info;
					console.log(ctrl);
					angular.forEach(ctrl.range(ctrl.studienplan.regelstudiendauer), function (value, index) {
						ctrl.berufspraktikum.data.push({semester: false, dauer: "", ects: "", stunden: ""});
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

			ctrl.loadData = function()
			{
				$http({
					method: 'GET',
					url: './api/studienplan/berufspraktikum/berufspraktikum.php?stplid=' + $scope.stplid
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						if(response.data.info.length > 0)
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
				angular.forEach(ctrl.berufspraktikum.data, function(value, index)
				{
					if(value.semester)
					{
						if((value.dauer == "") || (value.ects == "") || (value.stunden == ""))
						{
							$scope.form.$valid = false;
							$scope.form.$invalid = true;
//							console.log($("#auslandssemester tr td:nth-child("+(index+2)+")").find("div"));
							$("#auslandssemester tr td:nth-child("+(index+2)+")").find("div").each(function(i,v){
								console.log($(v));
								$(v).addClass("has-error");
							});
						}
					}
				});
				console.log(ctrl.berufspraktikum);
				console.log($scope.form);
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
				if (val === 'optional')
				{
					if (ctrl.berufspraktikum.data[index][val])
						ctrl.berufspraktikum.data[index].verpflichtend = false;
				}

				if (val === 'verpflichtend')
				{
					if (ctrl.berufspraktikum.data[index][val])
						ctrl.berufspraktikum.data[index].optional = false;
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