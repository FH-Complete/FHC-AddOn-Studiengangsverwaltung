angular.module('stgv2')
	.controller('StplGueltigkeitCtrl', function($scope, $http, $state, $stateParams, errorService){
		$scope.stplid = $stateParams.stplid;
		var ctrl = this;
		ctrl.data = "";
		ctrl.origin = "";
		ctrl.studiensemesterList = "";
		ctrl.studienplan = "";
		ctrl.studienordnung = "";
		
		//loading Studienplan (regelstudiendauer needed)
		$http({
			method: 'GET',
			url: './api/studienplan/eckdaten/eckdaten.php?stplId=' + $scope.stplid
		}).then(function success(response) {
			console.log(response);
			if (response.data.erfolg)
			{
				ctrl.studienplan = response.data.info;
			}
			else
			{
				errorService.setError(getErrorMsg(response));
			}
		}, function error(response) {
			errorService.setError(getErrorMsg(response));
		});
		
		//loading Studienordnung (gueltig von needed)
		$http({
			method: 'GET',
			url: './api/studienordnung/metadaten.php?stoId=' + $scope.stoid
		}).then(function success(response) {
			if (response.data.erfolg)
			{
				ctrl.studienordnung = response.data.info;
			}
			else
			{
				errorService.setError(getErrorMsg(response));
			}
		}, function error(response) {
			errorService.setError(getErrorMsg(response));
		});
		
		ctrl.loadFutureStudiensemester()
		{
			//loading Studiensemester list
			//TODO semester ab gueltig von laden
			$http({
				method: "GET",
				url: "./api/helper/studiensemester.php?method="
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.studiensemesterList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
		}
		
	
		ctrl.range = function(max)
		{
			var values = [];
			for(i=1; i<=max; i++)
			{
				values.push(i);
			}
			return values;
		}
		
		/*
			Start-Studiensemester von STO holen
		*/
		
		
	});