angular.module('stgv2')
		.controller('StplEckdatenIndexCtrl', function ($scope, $http, $state, $stateParams) {
			$scope.stplid = $stateParams.stplid;
			var ctrl = this;
			ctrl.data = "";
			ctrl.origin = "";
			ctrl.changed = false;
			$http({
				method: 'GET',
				url: './api/studienplan/metadaten.php?stplId=' + $scope.stplid
			}).then(function success(response) {
				//TODO success
				$("#treeGrid").treegrid('reload');
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});



//TODO save
//		ctrl.save = function(){
//			var saveData = ctrl.data;
//			$http({
//				method: 'POST',
//				url: './api/studienplan/save_metadaten.php',
//				headers: {
//					'Content-Type': 'application/json'
//				},
//				data: JSON.stringify(saveData)
//			}).then(function success(response){
//				//TODO success
//				console.log(response);
//			}, function error(response){
//				errorService.setError(getErrorMsg(response));
//			});
//		};	
		});