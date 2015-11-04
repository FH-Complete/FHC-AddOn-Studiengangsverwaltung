angular.module('stgv2')
		.controller('StoMetadatenIndexCtrl', function ($scope, $http, $state, $stateParams, errorService) {
			$scope.stoid = $stateParams.stoid;
			var ctrl = this;
			ctrl.data = "";
			ctrl.origin = "";
			ctrl.changed = false;
			ctrl.studiensemesterList = "";
			ctrl.akadGradList = "";
			//TODO list from db or config
			ctrl.aenderungsvarianteList = [
				{bezeichnung: "geringfügig"},
				{bezeichnung: "nicht geringfügig"},
				{bezeichnung: "akkreditierungspflichtig"}];
			
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

			$http({
				method: 'GET',
				url: './api/studienordnung/metadaten.php?stoId=' + $scope.stoid
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
				var saveData = ctrl.data;
				$http({
					method: 'POST',
					url: './api/studienordnung/save_metadaten.php',
					headers: {
						'Content-Type': 'application/json'
					},
					data: JSON.stringify(saveData)
				}).then(function success(response) {
					//TODO success
					$("#treeGrid").treegrid('reload');
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
		});