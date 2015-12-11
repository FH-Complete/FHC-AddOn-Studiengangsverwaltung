angular.module('stgv2')
		.controller('StoNeuController', function ($scope, $http, $state, $stateParams, errorService, $filter, successService) {
			$scope.stoid = $stateParams.stoid;
			var ctrl = this;
			ctrl.studiensemesterList = "";
			ctrl.studiengangList = "";
			//TODO list from db or config
			ctrl.aenderungsvarianteList = "";
			ctrl.initialStatus = "";
			ctrl.sto = {
				status_kurzbz: "development",
				stg_kz: "",
				version: "",
				gueltigvon: "",
				gueltigbis: "",
				begruendung: "",
				aenderungsvariante_kurzbz: ""
			};
			
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
			
			//loading Studiengang list
			$http({
				method: "GET",
				url: "./api/helper/studiengang.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.studiengangList = response.data.info;
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
			
			//loading initial Status
			$http({
				method: "GET",
				url: "./api/helper/studienordnungStatus.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.initialStatus = response.data.info[0];
					ctrl.sto.status_kurzbz = ctrl.initialStatus.status_kurzbz;
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
			};
			
			ctrl.updateVersion = function()
			{
				var stg = $filter('filter')(ctrl.studiengangList, {studiengang_kz: ctrl.sto.stg_kz})[0];
				ctrl.sto.version = ctrl.sto.stg_kz+"-"+stg.kurzbzlang+"-"+ctrl.sto.gueltigvon;
			}
		});