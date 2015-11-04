angular.module('stgv2')
	.controller('StoDokumenteIndexCtrl', function($scope, $http, $state, $stateParams){
		$scope.stoid = $stateParams.stoid;
		
		/*$http({method: 'GET', url:'./json/tree.json'}).success(function(data){
			$scope.data = data;
			//test Data
			$scope.data = [
				{label: "Property1", value: "Value1"},
				{label: "Property2", value: "Value2"},
				{label: "Property3", value: "Value3"}
			]
		});*/
	});