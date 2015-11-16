angular.module('stgv2')
		.controller('StoNeuController', function ($scope, $http, $state, $stateParams, errorService, $filter) {
			$scope.stoid = $stateParams.stoid;
			var ctrl = this;
			ctrl.studiensemesterList = "";
			ctrl.studiengangList = "";
			//TODO list from db or config
			ctrl.aenderungsvarianteList = [
				{bezeichnung: "geringfügig"},
				{bezeichnung: "nicht geringfügig"},
				{bezeichnung: "akkreditierungspflichtig"}];
			ctrl.sto = {
				status: "in Bearbeitung",
				stg_kz: "",
				version: "",
				gueltigvon: "",
				gueltigbis: "",
				begruendung: ""
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
			
			ctrl.save = function () {
				//TODO set stgkz
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
					console.log(response);
					//TODO success
					$("#treeGrid").treegrid('reload');
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