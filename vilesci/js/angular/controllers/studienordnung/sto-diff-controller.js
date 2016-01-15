angular.module('stgv2')
		.controller('StoDiffController', function ($scope, $http, $state, $stateParams, errorService, $filter, successService) {
			$scope.stoid = $stateParams.stoid;
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
					console.log($scope);
					if($scope.stgkz !== "")
					{
						ctrl.old.stgkz = $scope.stgkz;
						ctrl.old.stoid = $scope.stoid;
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
						console.log(response.data.info);
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
				console.log(ctrl.old);
				console.log(ctrl.new);
				$http({
					method: "GET",
					url: "./api/helper/diff.php?studienordnung_id_old=" + ctrl.old.stoid+"&studienordnung_id_new="+ctrl.new.stoid
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.diff = response.data.info;
						console.log(ctrl.diff);
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
	this.stoid = "";
	this.studienordnungList = [];
}