angular.module('stgv2')
		.controller('StoDiffController', function ($scope, $http, $state, $stateParams, errorService, $filter, successService) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.studiengangList = [];
			ctrl.old = new Studienordnung();
			ctrl.new = new Studienordnung();
			ctrl.diff = null;

			//loading Studiengang list
			$http({
				method: "GET",
				url: "./api/helper/studiengang.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.studiengangList = response.data.info;
					if($scope.stgkz !== "")
					{
						ctrl.old.stgkz = $scope.stgkz;
						ctrl.old.studienordnung_id = $scope.studienordnung_id;
						ctrl.loadStudienordnungList(ctrl.old);
					}
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});


			ctrl.loadStudienordnungList = function (selection)
			{
				//loading Studienordnung list
				$http({
					method: "GET",
					url: "./api/helper/studienordnung.php?stgkz=" + selection.stgkz + "&state=all"
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						selection.studienordnungList = response.data.info;
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			}

			ctrl.loadDiff = function () {
				//loading diff
				$http({
					method: "GET",
					url: "./api/helper/diff.php?studienordnung_id_old=" + ctrl.old.studienordnung_id+"&studienordnung_id_new="+ctrl.new.studienordnung_id
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.diff = response.data.info;
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			}

		});

function Studienordnung()
{
	this.stgkz = "";
	this.studienordnung_id = "";
	this.studienordnungList = [];
}