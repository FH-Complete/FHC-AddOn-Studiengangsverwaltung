angular.module('stgv2')
		.controller('StoMetadatenCtrl', function ($scope, $http, $state, $stateParams, errorService) {
			$scope.stoid = $stateParams.stoid;
			var ctrl = this;
			ctrl.data = "";
			ctrl.origin = "";
			ctrl.changed = false;
			ctrl.studiensemesterList = "";
			ctrl.akadGradList = "";
			//TODO list from db or config
			ctrl.aenderungsvarianteList = "";
			
			//loading Studiensemester list
			$http({
				method: "GET",
				url: "./api/helper/studiensemester.php"
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
			
			//loading akadGrad list
			$http({
				method: "GET",
				url: "./api/helper/akadGrad.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.akadGradList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
			
			//loading Aenderungsvariante list
			$http({
				method: "GET",
				url: "./api/helper/aenderungsvariante.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.aenderungsvarianteList = response.data.info;
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
				url: './api/studienordnung/metadaten/metadaten.php?stoId=' + $scope.stoid
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					//TODO Preparation for watcher
					ctrl.origin = response.data.info;
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
					url: './api/studienordnung/metadaten/save_metadaten.php',
					data: $.param(saveData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					//TODO success
					$("#treeGrid").treegrid('reload');
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
		});