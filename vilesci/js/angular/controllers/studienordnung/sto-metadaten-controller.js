angular.module('stgv2')
		.controller('StoMetadatenCtrl', function ($rootScope, $scope, $http, $filter, $stateParams, errorService, successService, StudiensemesterService, AenderungsvarianteService, StudienordnungStatusService) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			var ctrl = this;
			ctrl.data = "";
			ctrl.beschluesse = [];
			console.log(ctrl.beschluesse);
			ctrl.changed = false;
			ctrl.status = "";
			ctrl.studiensemesterList = [];
			ctrl.aenderungsvarianteList = [];
			ctrl.studienordnungStatusList = [];

			if ($stateParams.studienordnung_id !== undefined && $rootScope.studienordnung === null)
			{
				$rootScope.setStudienordnung($stateParams.studienordnung_id);
			}

			$scope.$watch("ctrl.data.aenderungsvariante_kurzbz", function (newValue, oldValue) {
				console.log(newValue);
				var length = 0;
				switch (newValue)
				{
					case "gering":
						length = 1;
						break;
					case "nichtGering":
						length = 2;
						break;
					case "akkreditierungspflichtig":
						length = 3;
						break;
					default:
						break;
				}

				length = length - ctrl.beschluesse.length;

				for (var i = 0; i < length; i++)
				{
					ctrl.beschluesse.push({datum: "", typ: ""});
				}

				if (length < 0)
				{
					for (; length < 0; length++)
					{
						ctrl.beschluesse.pop();
					}
				}

			});

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

			$(".datepicker").datepicker({
				dateFormat: "yy-mm-dd",
				firstDay: 1
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

			ctrl.loadData = function()
			{
				$http({
					method: 'GET',
					url: './api/studienordnung/metadaten/metadaten.php?studienordnung_id=' + $scope.studienordnung_id
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.data = response.data.info;
						angular.forEach(ctrl.data.beschluesse, function(value, index){
							ctrl.data.beschluesse[index].datum = formatStringToDate(value.datum.split(" ")[0]);
						});
						ctrl.beschluesse = ctrl.data.beschluesse;
						console.log(ctrl.data.beschluesse);
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};

			ctrl.save = function () {
				console.log(ctrl.beschluesse);
				ctrl.data.beschluesse = angular.copy(ctrl.beschluesse);
				console.log(ctrl.beschluesse.length);
				if ($scope.form.$valid)
				{
					var saveData = {data: ""}
					saveData.data = ctrl.data;
					console.log(saveData);
					$http({
						method: 'POST',
						url: './api/studienordnung/metadaten/save_metadaten.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						console.log(ctrl.beschluesse);
						if (response.data.erfolg)
						{
							$("#treeGrid").treegrid('reload');
							successService.setMessage(response.data.info);
							$scope.form.$setPristine();
							ctrl.loadData();
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
			
			ctrl.loadData();
		});