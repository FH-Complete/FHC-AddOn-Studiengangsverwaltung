angular.module('stgv2')
		.controller('StplMetadatenCtrl', function ($scope, $http, $rootScope, $stateParams, errorService, successService, OrgformService, StudienplanService, StudienordnungService, StudienordnungService) {
			$scope.studienplan_id = $stateParams.studienplan_id;
			var ctrl = this;
			ctrl.data = "";
			ctrl.changed = false;
			ctrl.orgformList = [];

			//loading data
			StudienplanService.getStudienplan($scope.studienplan_id).then(function (result) {
				ctrl.data = result;
				StudienordnungService.getStudienordnungByStudienplan($scope.studienplan_id).then(function (result) {
					ctrl.data.status_kurzbz = result.status_kurzbz;
				}, function (error) {
					errorService.setError(getErrorMsg(error));
				});
			}, function (error) {
				errorService.setError(getErrorMsg(error));
			});

			//loading orgformList
			OrgformService.getOrgformList().then(function (result) {
				ctrl.orgformList = result;
			}, function (error) {
				errorService.setError(getErrorMsg(error));
			});

			ctrl.save = function () {
				var saveData = {data: ""}
				saveData.data = ctrl.data;
				$http({
					method: 'POST',
					url: './api/studienplan/metadaten/save_metadaten.php',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					},
					data: $.param(saveData)
				}).then(function success(response) {
					$("#treeGrid").treegrid('reload');
					if (response.data.erfolg)
					{
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