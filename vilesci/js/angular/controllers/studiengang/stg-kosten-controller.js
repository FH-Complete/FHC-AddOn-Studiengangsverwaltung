angular.module('stgv2')
		.controller('StgKostenCtrl', function ($scope, $http, $state, $stateParams,errorService) {
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.data = "";
			$http({
				method: "GET",
				url: "./api/studiengang/kosten.php"
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

		});