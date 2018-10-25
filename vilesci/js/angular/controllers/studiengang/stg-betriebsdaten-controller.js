angular.module('stgv2')
	.controller('StgBetriebsdatenCtrl', function($scope, $rootScope, $state, $stateParams){
		$scope.stgkz = $stateParams.stgkz;
		//TODO tabs from config
		$scope.tabs = [
			{label: 'Bewerbungsfristen', link: '.bewerbung'},
			{label: 'Reihungstesttermine', link: '.reihungstest'},
			{label: 'Kosten', link: '.kosten'},
			{label: 'Förderungen', link: '.foerderungen'},
			{label: 'Doktorat', link: '.doktorat'},
		];

		$rootScope.$broadcast("loadTreeGrid",{"stgkz": $scope.stgkz, "state": "all"});

		$scope.selectedTab = $scope.tabs[0];
		$scope.setSelectedTab = function (tab)
		{
			$scope.selectedTab = tab;
		}

		$scope.getTabClass = function (tab)
		{
			if ($scope.selectedTab == tab)
			{
				return "active";
			}
			else
			{
				return "";
			}
		}
	});
