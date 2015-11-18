angular.module('stgv2')
		.controller('StplNeuController', function ($scope, $http, $state, $stateParams, errorService, $filter) {
			$scope.stoid = $stateParams.stoid;
			var ctrl = this;
			ctrl.studienordnung = $("#treeGrid").treegrid('getSelected');
			ctrl.data = new Studienplan();
			ctrl.data.version = ctrl.studienordnung.version+"-";
			ctrl.data.studienordnung_id = ctrl.studienordnung.id;
			ctrl.orgformList = "";
			
			//loading orgform list
			$http({
				method: "GET",
				url: "./api/helper/orgform.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.orgformList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
			
			ctrl.updateVersion = function()
			{
				ctrl.data.version = ctrl.studienordnung.version+"-"+ctrl.data.orgform_kurzbz;
			}
			
			ctrl.save = function()
			{
				var saveData = {data: ""}
				saveData.data = ctrl.data;
				$http({
					method: 'POST',
					url: './api/studienplan/create_studienplan.php',
					data: $.param(saveData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if(!response.data.erfolg)
					{
						errorService.setError(getErrorMsg(response));
					}
					$("#treeGrid").treegrid('reload');
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			}
		});
		
function Studienplan()
{
	this.studienplan_id = "";
	this.studienordnung_id = "";
	this.orgform_kurzbz = "";
	this.version = "";
	this.regelstudiendauer = "";
	this.sprache = "";
	this.aktiv = true;
	this.semesterwochen = "";
	this.pflicht_sws = "";
	this.pflicht_lvs = "";
	this.erlaeuterungen = "";
	this.testtool_sprachwahl = true;
}