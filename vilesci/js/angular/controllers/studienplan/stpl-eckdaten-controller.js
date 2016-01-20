angular.module('stgv2')
		.controller('StplEckdatenCtrl', function ($scope, $http, $state, $stateParams, errorService, successService) {
			$scope.studienplan_id = $stateParams.studienplan_id;
			var ctrl = this;
			ctrl.data = "";
			$http({
				method: 'GET',
				url: './api/studienplan/eckdaten/eckdaten.php?studienplan_id=' + $scope.studienplan_id
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
			
			ctrl.save = function(){
				var saveData = {data: ""}
				saveData.data = ctrl.data;
				$http({
					method: 'POST',
					url: './api/studienplan/eckdaten/save_eckdaten.php',
					data: $.param(saveData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response){
					if(response.data.erfolg)
					{
						$("#treeGrid").treegrid('reload');
						successService.setMessage(response.data.info);
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response){
					errorService.setError(getErrorMsg(response));
				});
			};
		});