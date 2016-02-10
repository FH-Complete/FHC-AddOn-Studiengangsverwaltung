angular.module('stgv2')
		.controller('StoNeuController', function ($scope, $http, $stateParams, errorService, $filter, successService, StudienordnungStatusService, StudiensemesterService, AenderungsvarianteService, StudiengangService) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			var ctrl = this;
			ctrl.studiensemesterList = "";
			ctrl.studiengangList = "";
			ctrl.studienordnungList = "";
			ctrl.aenderungsvarianteList = "";
			ctrl.initialStatus = "";
			ctrl.sto = {
				status_kurzbz: "development",
				stg_kz: "",
				version: "",
				gueltigvon: "",
				gueltigbis: "",
				begruendung: "",
				aenderungsvariante_kurzbz: "",
				vorlage_studienordnung_id: ''
			};
			
			$("#editor").wysiwyg(
			{
				'form':
				{
					'text-field': 'editorForm',
					'seperate-binary': false
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
			
			//loading Studiengang list
			StudiengangService.getStudiengangList()
				.then(function (result) {
					ctrl.studiengangList = result;
					var node = $('#west_tree').tree("getSelected");
					if(node && node.attributes)
					{
						ctrl.sto.stg_kz = node.attributes[0].urlParams[0].stgkz;
						ctrl.loadStudienordnungList();
						ctrl.updateVersion();
					}
				}, function (error) {
					errorService.setError(getErrorMsg(error));
				});
			
			ctrl.loadStudienordnungList = function ()
			{
				//loading Studienordnung list
				$http({
					method: "GET",
					url: "./api/helper/studienordnung.php?stgkz=" + ctrl.sto.stg_kz + "&state=all"
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.studienordnungList = response.data.info;
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			}
			
			//loading initialStatus
			StudienordnungStatusService.getStudienordnungStatusList()
				.then(function (result) {
					ctrl.initialStatus = result[0];
					ctrl.sto.status_kurzbz = ctrl.initialStatus.status_kurzbz;

				});
					
			ctrl.save = function () {
				if($scope.form.$valid)
				{
					ctrl.sto.begruendung = JSON.stringify($("#editor").html());
					var saveData = {data: ""}
					saveData.data = ctrl.sto;				
					$http({
						method: 'POST',
						url: './api/studienordnung/create_studienordnung.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if(response.data.erfolg)
						{
							$("#treeGrid").treegrid('reload');
							successService.setMessage(response.data.message);
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
			
			ctrl.updateVersion = function()
			{
				var stg = $filter('filter')(ctrl.studiengangList, {studiengang_kz: ctrl.sto.stg_kz})[0];
				ctrl.sto.version = ctrl.sto.stg_kz+"-"+stg.kurzbzlang+"-"+ctrl.sto.gueltigvon;
			}
		});