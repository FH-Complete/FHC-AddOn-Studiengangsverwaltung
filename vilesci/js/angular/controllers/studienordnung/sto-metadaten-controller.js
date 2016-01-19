angular.module('stgv2')
		.controller('StoMetadatenCtrl', function ($scope, $http, $state, $stateParams, errorService, successService) {
			$scope.stoid = $stateParams.stoid;
			var ctrl = this;
			ctrl.data = "";
			ctrl.changed = false;
			ctrl.studiensemesterList = "";
			ctrl.akadGradList = "";
			ctrl.aenderungsvarianteList = "";
			ctrl.status= "";
			
			//loading Studiensemester list
			$http({
				method: "GET",
				url: "./api/helper/studiensemester.php"
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
			
			//loading akadGrad list
			$http({
				method: "GET",
				url: "./api/helper/akadGrad.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.akadGradList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
			
			//loading Aenderungsvariante list
			$http({
				method: "GET",
				url: "./api/helper/aenderungsvariante.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.aenderungsvarianteList = response.data.info;
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
				url: './api/studienordnung/metadaten/metadaten.php?stoId=' + $scope.stoid
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.data = response.data.info;
					$http({
						method: "GET",
						url: "./api/helper/studienordnungStatus.php"
					}).then(function success(response) {
						if (response.data.erfolg)
						{
							$(response.data.info).each(function(i,v){
								if(v.status_kurzbz === ctrl.data.status_kurzbz)
								{
									ctrl.status = v;
								}
							});

						}
						else
						{
							errorService.setError(getErrorMsg(response));
						}
					}, function error(response) {
						errorService.setError(getErrorMsg(response));
					});
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
			
			ctrl.save = function () {
				var saveData = {data: ""}
				saveData.data = ctrl.data;
				$http({
					method: 'POST',
					url: './api/studienordnung/metadaten/save_metadaten.php',
					data: $.param(saveData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if(response.data.erfolg)
					{
						$("#treeGrid").treegrid('reload');
						successService.setMessage(response.data.info);
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
		});