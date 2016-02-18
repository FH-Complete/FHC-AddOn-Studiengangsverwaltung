angular.module('stgv2')
		.controller('StoDiffController', function ($scope, $http, $state, $stateParams, errorService, StudiengangService, $sce) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.studiengangList = [];
			ctrl.old = new Studienordnung();
			ctrl.new = new Studienordnung();
			ctrl.diff = null;
			ctrl.studiengangList = [];
			ctrl.collapsed = true;
			ctrl.stateUrl = $state.current.url;

			//loading Studiengang list
			StudiengangService.getStudiengangList().then(function (result) {
				ctrl.studiengangList = result;
				if ($scope.stgkz !== "")
				{
					ctrl.old.stgkz = $scope.stgkz;
					ctrl.new.stgkz = $scope.stgkz;
					ctrl.old.studienordnung_id = $scope.studienordnung_id;
					ctrl.loadStudienordnungList(ctrl.old);
					ctrl.loadStudienordnungList(ctrl.new);
				}
			}, function (error) {
				errorService.setError(getErrorMsg(error));
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
			};
			
			ctrl.loadStudienplanList = function (selection)
			{
				//loading Studienordnung list
				$http({
					method: "GET",
					url: "./api/helper/studienplaeneBySto.php?studienordnung_id=" + selection.studienordnung_id
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						console.log(response.data.info);
						selection.studienplanList = response.data.info;
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};

			ctrl.loadDiff = function () {
				//loading diff
				$http({
					method: "GET",
					url: "./api/helper/diff.php?studienordnung_id_old=" + ctrl.old.studienordnung_id 
							+ "&studienordnung_id_new=" + ctrl.new.studienordnung_id 
							+ "&studienplan_id_old=" + ctrl.old.studienplan_id 
							+ "&studienplan_id_new=" + ctrl.new.studienplan_id
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.diff = response.data.info;
						angular.forEach(ctrl.diff, function (value, index) {
							ctrl.collapsed[index] = false;
						});
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
			
			ctrl.insertHTML = function(value){
				value = angular.element('<textarea />').html(value).text();
				return $sce.trustAsHtml(value);
			};
			
		});

function Studienordnung()
{
	this.stgkz = "";
	this.studienordnung_id = "";
	this.studienordnungList = [];
}