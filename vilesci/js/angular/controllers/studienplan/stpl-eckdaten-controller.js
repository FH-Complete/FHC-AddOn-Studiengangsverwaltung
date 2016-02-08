angular.module('stgv2')
		.controller('StplEckdatenCtrl', function ($scope, $http, $stateParams, errorService, successService, StudienplanService, StudienordnungService, SpracheService) {
			$scope.studienplan_id = $stateParams.studienplan_id;
			var ctrl = this;
			ctrl.data = "";
			ctrl.spracheList = [];
			
			//loading SpracheList
			SpracheService.getSpracheList().then(function(result){
				ctrl.spracheList = result;
				console.log(result);
			},function(error){
				errorService.setError(getErrorMsg(error));
			});

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

			ctrl.save = function () {
				var saveData = {data: ""}
				saveData.data = ctrl.data;
				$http({
					method: 'POST',
					url: './api/studienplan/eckdaten/save_eckdaten.php',
					data: $.param(saveData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
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
			};
		});