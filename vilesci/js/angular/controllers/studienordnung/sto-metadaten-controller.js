angular.module('stgv2')
		.controller('StoMetadatenCtrl', function ($rootScope, $scope, $http, $filter, $stateParams, errorService, successService, StudiensemesterService, AenderungsvarianteService, StudienordnungStatusService, StoreService) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			var ctrl = this;
			ctrl.data = "";
			ctrl.beschluesse = [];
			ctrl.changed = false;
			ctrl.status = "";
			ctrl.studiensemesterList = [];
			ctrl.aenderungsvarianteList = [];
			ctrl.studienordnungStatusList = [];
			ctrl.beschlussList = ["Studiengang","Kollegium","AQ Austria"];

			//enable tooltips
			$(document).ready(function(){
				$('[data-toggle="tooltip"]').tooltip();
			});

			if ($stateParams.studienordnung_id !== undefined && $rootScope.studienordnung === null)
			{
				$rootScope.setStudienordnung($stateParams.studienordnung_id);
			}

			$("#editor").wysiwyg(
			{
				'form':
				{
					'text-field': 'editorForm',
					'seperate-binary': false
				}
			});
			$("#editor").on('paste', function(event){
				setTimeout(function(){
					$("#editor").html($("#editor").html());
				},100);
			});

			ctrl.deleteSelection = function()
			{
				window.getSelection().deleteFromDocument();
			};

			/*$scope.$watch("ctrl.data.aenderungsvariante_kurzbz", function (newValue, oldValue) {
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
					ctrl.beschluesse.push({datum: "", typ: ctrl.beschlussList[length-1]});
				}

				if (length < 0)
				{
					for (; length < 0; length++)
					{
						ctrl.beschluesse.pop();
					}
				}

			});*/

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
							var filtered = $filter('filter')(ctrl.studienordnungStatusList, {status_kurzbz: newValue},true);
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
						$("#editor").html(response.data.info.begruendung);
						ctrl.beschluesse = [null, null, null];
						//fill beschluesse array depending on typ (studiengang, Kollegium AQ Austria)
						angular.forEach(ctrl.data.beschluesse, function(value, index)
						{
							var position = $.inArray(value.typ, ctrl.beschlussList);
							var valuecopy = value;
							if (value.datum != null)
								valuecopy.datum = formatDateAsString(formatStringToDate(value.datum));
							ctrl.beschluesse.splice(position, 1, valuecopy);
						});

						//ctrl.beschluesse = ctrl.data.beschluesse;
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
				angular.forEach(ctrl.beschluesse, function(value, index){
					if(ctrl.beschluesse[index] != null)
						ctrl.beschluesse[index].typ = ctrl.beschlussList[index];
				});
				ctrl.data.beschluesse = angular.copy(ctrl.beschluesse);
				ctrl.data.begruendung = JSON.stringify($("#editor").html());
				if ($scope.form.$valid)
				{
					var saveData = {data: ""};
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
							StoreService.remove("studienordnung");
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
				else
				{
					$scope.form.$setPristine();
				}
			};

			//check if there is already a beschlussdatum - if yes, Änderungsvariante is disabled.
			ctrl.checkIfBeschlossen = function ()
			{
				var beschlossen = false;
				angular.forEach(ctrl.data.beschluesse, function(value){
					if(value !== null && value.datum !== null /*&& value.datum !== ""*/){
						beschlossen = true;
					}
				});
				return beschlossen;
			};

			ctrl.showBeschlussdatum = function (beschldatum)
			{
				switch (beschldatum)
				{
					case 0:
						return ctrl.data.aenderungsvariante_kurzbz === 'gering' || ctrl.data.aenderungsvariante_kurzbz === 'nichtGering' || ctrl.data.aenderungsvariante_kurzbz === 'akkreditierungspflichtig';
						break;
					case 1:
						return ctrl.data.aenderungsvariante_kurzbz === 'nichtGering' || ctrl.data.aenderungsvariante_kurzbz === 'akkreditierungspflichtig';
						break;
					case 2:
						return ctrl.data.aenderungsvariante_kurzbz === 'akkreditierungspflichtig';
						break;
					default:
						return false;
				}
			};

			ctrl.loadData();
		});