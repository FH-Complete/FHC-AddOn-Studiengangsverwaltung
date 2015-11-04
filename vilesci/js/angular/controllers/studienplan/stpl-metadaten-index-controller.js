angular.module('stgv2')
	.controller('StplMetadatenIndexCtrl', function($scope, $http, $state, $stateParams, errorService){
		$scope.stplid = $stateParams.stplid;
		var ctrl = this;
		ctrl.data = "";
		ctrl.origin = "";
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
			url: './api/studienplan/metadaten.php?stplId='+$scope.stplid
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
			
		ctrl.save = function(){
			var saveData = ctrl.data;
			$http({
				method: 'POST',
				url: './api/studienplan/save_metadaten.php',
				headers: {
					'Content-Type': 'application/json'
				},
				data: JSON.stringify(saveData)
			}).then(function success(response){
				//TODO success
				$("#treeGrid").treegrid('reload');
			}, function error(response){
				errorService.setError(getErrorMsg(response));
			});
		};	
	});