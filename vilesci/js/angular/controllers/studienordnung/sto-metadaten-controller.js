angular.module('stgv2')
		.controller('StoMetadatenCtrl', function ($rootScope, $scope, $http, $state, $stateParams, errorService, successService) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			var ctrl = this;
			ctrl.data = "";
			ctrl.changed = false;
			ctrl.studiensemesterList = "";
			ctrl.aenderungsvarianteList = "";
			ctrl.status= "";
			
			if($stateParams.studienordnung_id !== undefined && $rootScope.studienordnung === null)
			{
				$rootScope.setStudienordnung($stateParams.studienordnung_id);
			};
						
			//loading Studiensemester list
			ctrl.studiensemesterList = $rootScope.studiensemesterList;
			
			//loading Aenderungsvariante list
			ctrl.aenderungsvarianteList = $rootScope.aenderungsvarianteList;

			//TODO load data if not in $rootscope
			$http({
				method: 'GET',
				url: './api/studienordnung/metadaten/metadaten.php?studienordnung_id=' + $scope.studienordnung_id
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