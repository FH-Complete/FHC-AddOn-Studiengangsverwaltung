angular.module('stgv2')
		.controller('StoEckdatenCtrl',
				function ($rootScope, $scope, $http, $stateParams, errorService, successService, StandortService, StudiensemesterService, AkadgradService, OrgformService, StoreService) {
					$scope.studienordnung_id = $stateParams.studienordnung_id;
					var ctrl = this;
					ctrl.data = "";
					ctrl.changed = false;
					ctrl.studiensemesterList = [];
					ctrl.akadGradList = [];
					ctrl.orgformList = [];
					ctrl.standortList = [];
					ctrl.studiengangartList = [];
					
					//enable tooltips
					$(document).ready(function(){
						$('[data-toggle="tooltip"]').tooltip();
					});

					//loading Studiensemester list
					StudiensemesterService.getStudiensemesterList().then(function (result) {
						ctrl.studiensemesterList = result;
					}, function (error) {
						errorService.setError(getErrorMsg(error));
					});

					//loading akadGrad list
					AkadgradService.getAkadgradList().then(function (result) {
						ctrl.akadGradList = result;
					}, function (error) {
						errorService.setError(getErrorMsg(error));
					});

					//loading orgformList
					OrgformService.getOrgformList().then(function (result) {
						ctrl.orgformList = result;
					}, function (error) {
						errorService.setError(getErrorMsg(error));
					});

					//loading standort list
					StandortService.getStandortList().then(function (result) {
						ctrl.standortList = result;
					}, function (error) {
						errorService.setError(getErrorMsg(error));
					});
					
					$http({
						method: 'GET',
						url: './api/helper/studiengangtyp.php'
					}).then(function success(response) {
						if (response.data.erfolg)
						{
							ctrl.studiengangartList = response.data.info;
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
						url: './api/studienordnung/eckdaten/eckdaten.php?studienordnung_id=' + $scope.studienordnung_id
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
						var saveData = {data: ""}
						saveData.data = ctrl.data;
						$http({
							method: 'POST',
							url: './api/studienordnung/eckdaten/save_eckdaten.php',
							headers: {
								'Content-Type': 'application/x-www-form-urlencoded'
							},
							data: $.param(saveData)
						}).then(function success(response) {
							if (response.data.erfolg)
							{
								$("#treeGrid").treegrid("reload");
								successService.setMessage(response.data.info);
								StoreService.remove("studienordnung");
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