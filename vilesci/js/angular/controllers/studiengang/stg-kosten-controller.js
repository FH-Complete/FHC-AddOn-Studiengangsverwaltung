angular.module('stgv2')
		.controller('StgKostenCtrl', function ($scope, $http, $state, $stateParams,errorService) {
			$scope.stgkz = $stateParams.stgkz;
//			$http({
//				method: "GET",
//				url: "./api/studiengang/reihungstest.php?stgkz=" + $stateParams.stgkz
//			}).then(function success(response) {
//				if (response.data.erfolg)
//				{
//					$scope.data = response.data.info;
//				}
//				else
//				{
//					errorService.setError(getErrorMsg(response));
//				}
//			}, function error(response) {
//				errorService.setError(getErrorMsg(response));
//			});

		});