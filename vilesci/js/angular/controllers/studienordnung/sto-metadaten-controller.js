angular.module('stgv2')
		.controller('StoMetadatenCtrl', function ($rootScope, $scope, $http, $filter, $stateParams, errorService, successService, StudiensemesterService, AenderungsvarianteService, StudienordnungStatusService) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			var ctrl = this;
			ctrl.data = "";
			ctrl.changed = false;
			ctrl.status = "";
			ctrl.studiensemesterList = [];
			ctrl.aenderungsvarianteList = [];
			ctrl.studienordnungStatusList = [];

			if ($stateParams.studienordnung_id !== undefined && $rootScope.studienordnung === null)
			{
				$rootScope.setStudienordnung($stateParams.studienordnung_id);
			}

			//loading Studiensemester list
			StudiensemesterService.getStudiensemesterList()
					.then(function (result) {
						ctrl.studiensemesterList = result;

					}, function (error) {
						errorService.setError(getErrorMsg(error));
					});

			//loading AenderungsvarianteList list
			AenderungsvarianteService.getAenderungsvarianteList()
					.then(function (result) {
						ctrl.aenderungsvarianteList = result;
					}, function (error) {
						errorService.setError(getErrorMsg(error));
					});

			$scope.$watch("ctrl.data.status_kurzbz", function (newValue, oldValue) {
				//loading StudienordnungStatus list
				StudienordnungStatusService.getStudienordnungStatusList()
						.then(function (result) {
							ctrl.studienordnungStatusList = result;
							var filtered = $filter('filter')(ctrl.studienordnungStatusList, {status_kurzbz: newValue});
							if (filtered.length === 1)
							{
								ctrl.status = filtered[0];
							}
							else
							{
								ctrl.status = {bezeichnung: 'Error: not found with filter'};
							}

						});
			}, true);

			$http({
				method: 'GET',
				url: './api/studienordnung/metadaten/metadaten.php?studienordnung_id=' + $scope.studienordnung_id
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

			ctrl.save = function () {
				if ($scope.form.$valid)
				{
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
						if (response.data.erfolg)
						{
							$("#treeGrid").treegrid('reload');
							successService.setMessage(response.data.info);
							$scope.form.$setPristine();
						}
						else
						{
							errorService.setError(getErrorMsg(response));
						}
					}, function error(response) {
						errorService.setError(getErrorMsg(response));
					});
				}
			};
		});