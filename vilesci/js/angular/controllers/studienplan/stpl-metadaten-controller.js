angular.module('stgv2')
	.controller('StplMetadatenCtrl', function($scope, $http, $state, $stateParams, errorService, successService){
		$scope.studienplan_id = $stateParams.studienplan_id;
		var ctrl = this;
		ctrl.data = "";
		ctrl.changed = false;
		ctrl.orgformList = "";
		
		//loading orgform list
		$http({
			method: "GET",
			url: "./api/helper/orgform.php"
		}).then(function success(response) {
			if (response.data.erfolg)
			{
				ctrl.orgformList = response.data.info;
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
			url: './api/studienplan/metadaten/metadaten.php?studienplan_id='+$scope.studienplan_id
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
				url: './api/studienplan/metadaten/save_metadaten.php',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				data: $.param(saveData)
			}).then(function success(response){
				$("#treeGrid").treegrid('reload');
				if (response.data.erfolg)
				{
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