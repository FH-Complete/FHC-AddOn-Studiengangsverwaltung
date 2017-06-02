angular.module('stgv2')
		.controller('StplNeuController', function ($scope, $rootScope, $http, $state, $stateParams, errorService, successService, $filter) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			var ctrl = this;
			ctrl.studienordnung = $("#treeGrid").treegrid('getSelected');
			ctrl.data = new Studienplan();
			ctrl.version_origin = "";
			ctrl.version_edited = false;
			if (ctrl.studienordnung != null)
			{
				ctrl.data.version = ctrl.studienordnung.version + "-";
				ctrl.version_origin = ctrl.studienordnung.version;
				ctrl.data.studienordnung_id = ctrl.studienordnung.studienordnung_id;
			}
			else
			{
				$http({
					method: 'GET',
					url: './api/studienordnung/metadaten/metadaten.php?studienordnung_id=' + $scope.studienordnung_id
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.studienordnung = response.data.info;
						ctrl.data.version = ctrl.studienordnung.version + "-";
						ctrl.version_origin = ctrl.studienordnung.version;
						ctrl.data.studienordnung_id = ctrl.studienordnung.studienordnung_id;
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
				ctrl.data.studienordnung_id = $scope.studienordnung_id;
			}
			ctrl.orgformList = "";

			ctrl.versionChanged = function ()
			{
				ctrl.version_origin = ctrl.data.version;
			};

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

			ctrl.updateVersion = function ()
			{
				ctrl.data.version = ctrl.version_origin + "-" + ctrl.data.orgform_kurzbz;
			};

			ctrl.save = function ()
			{
				if ($scope.form.$valid)
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
	this.onlinebewerbung_studienplan = true;
}