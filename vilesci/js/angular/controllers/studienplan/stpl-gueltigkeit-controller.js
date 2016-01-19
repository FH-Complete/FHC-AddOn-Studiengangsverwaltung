angular.module('stgv2')
		.controller('StplGueltigkeitCtrl', function ($scope, $http, $state, $stateParams, errorService) {
			$scope.stplid = $stateParams.stplid;
			var ctrl = this;
			ctrl.data = "";
			ctrl.origin = "";
			ctrl.studiensemesterList = "";
			ctrl.studienplan = "";
			ctrl.zuordnungList = "";

			//loading Studienplan (regelstudiendauer needed)
			$http({
				method: 'GET',
				url: './api/studienplan/eckdaten/eckdaten.php?stplId=' + $scope.stplid
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.studienplan = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});

			//loading zuordnungen
			ctrl.loadZuordnung = function()
				{
				$http({
					method: 'GET',
					url: './api/studienplan/gueltigkeit/gueltigkeit.php?studienplan_id=' + $scope.stplid
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.zuordnungList = response.data.info;
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			}
			ctrl.loadFutureStudiensemester = function ()
			{
				//loading Studiensemester list
				$http({
					method: "GET",
					url: "./api/helper/futureStudiensemester.php"
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.studiensemesterList = response.data.info;
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			}
			ctrl.loadZuordnung();
			ctrl.loadFutureStudiensemester();

			ctrl.range = function (max)
			{
				var values = [];
				for (i = 1; i <= max; i++)
				{
					values.push(i);
				}
				return values;
			}

			ctrl.assign = function (ele)
			{
				var data = [];
				$("#newAssignment").children().each(function (key, value) {
					if ($(value).hasClass("newAssignment"))
					{
						var checkbox = $(value).children()[0];
						if ($(checkbox).prop("checked"))
						{
							var obj = {};
							obj.studienplan_id = $scope.stplid;
							obj.studiensemester_kurzbz = $("#studiensemester").val();
							obj.ausbildungssemester = $(checkbox).attr("sem");
							data.push(obj);
						}
					}
				})

				if (data.length !== 0)
				{
					var saveData = {data: ""}
					saveData.data = data;
					$http({
						method: 'POST',
						url: './api/studienplan/gueltigkeit/save_gueltigkeit.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if(response.data.erfolg)
						{
							ctrl.loadZuordnung();
							ctrl.resetForm();
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

			ctrl.delete = function (studiensemester_kurzbz)
			{
				$http({
					method: 'GET',
					url: './api/studienplan/gueltigkeit/delete_gueltigkeit.php?studienplan_id='+$scope.stplid+"&studiensemester_kurzbz="+studiensemester_kurzbz,
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					ctrl.loadZuordnung();
					if(!response.data.erfolg)
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			}
			
			ctrl.resetForm = function()
			{
				$("input:checkbox").each(function(key, value)
				{
					$(value).prop("checked", false);
				})
			}
		});