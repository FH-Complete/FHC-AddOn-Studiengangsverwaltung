angular.module('stgv2')
	.controller('StgBetriebsdatenCtrl', function($scope, $http, $state, $stateParams){
		$scope.stgkz = $stateParams.stgkz;
		
		//TODO get tabs from config
		$scope.tabs = [
			{label: 'Studiengangsgruppen', link: '.studiengangsgruppen'},
			{label: 'Bewerbungstermine', link: '.bewerbung'},
			{label: 'Reihungstesttermine', link: '.reihungstest'},
			{label: 'Kosten', link: '.kosten'},
			{label: 'Förderungen', link: '.foerderungen'},
			{label: 'Doktorat', link: '.doktorat'}
		];
			
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