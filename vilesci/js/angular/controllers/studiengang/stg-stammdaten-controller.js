angular.module('stgv2')
		.controller('StgStammdatenCtrl', function ($scope, $http, $state, $stateParams) {
			$scope.stgkz = $stateParams.stgkz;
		});