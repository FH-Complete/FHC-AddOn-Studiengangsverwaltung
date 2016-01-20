angular.module('stgv2')
		.controller('StoEckdatenCtrl', function ($rootScope, $scope, $http, $state, $stateParams, errorService, successService) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			var ctrl = this;
			ctrl.data = "";
			ctrl.changed = false;
			ctrl.studiensemesterList = "";
			ctrl.akadGradList = "";
			ctrl.orgformList = "";
			ctrl.standortList = "";
			
			//loading Studiensemester list
			ctrl.studiensemesterList = $rootScope.studiensemesterList;
			
			//loading akadGrad list
			ctrl.akadGradList = $rootScope.akadGradList;
			
			//loading orgformList
			ctrl.orgformList = $rootScope.orgformList;
			
			//loading standort list
			ctrl.standortList = $rootScope.standortList;
			
			//TODO load data if not in $rootscope
			$http({
				method: 'GET',
				url: './api/studienordnung/eckdaten/eckdaten.php?studienordnung_id=' + $scope.studienordnung_id
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.data = response.data.info;
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
				saveData.data = ctrl.data;
				$http({
					method: 'POST',
					url: './api/studienordnung/eckdaten/save_eckdaten.php',
					headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
					},
					data: $.param(saveData)
				}).then(function success(response) {
					if(response.data.erfolg)
					{
						$("#treeGrid").treegrid("reload");
						successService.setMessage(response.data.info);
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
		});