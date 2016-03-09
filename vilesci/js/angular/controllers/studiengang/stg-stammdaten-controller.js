angular.module('stgv2')
		.controller('StgStammdatenCtrl', function ($scope, $http, $state, $stateParams, errorService, successService) {
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.data = "";
			ctrl.model = new Studiengangsgruppe();
			ctrl.model.studiengang_kz = $scope.stgkz;
			ctrl.zuordnung = false;
			
			//enable tooltips
			$scope.$on("ngRepeatFinished",function(){
				console.log($('[data-toggle="tooltip"]').tooltip());
			});

			ctrl.save = function()
			{
				if(ctrl.model.data.length == 2)
				{
					if(Object.keys(ctrl.model.data[0]).length != 2 || Object.keys(ctrl.model.data[1]).length != 3)
					{
						alert("Bitte Daten bis zur untersten Ebene vervollständigen.");
					}
					else
					{
						var saveData = {data: ""}
						saveData.data = ctrl.model;

						saveData.data.data = JSON.stringify(saveData.data.data);

						$http({
							method: 'POST',
							url: './api/studiengang/studiengangsgruppen/save_studiengangsgruppenZuordnung.php',
							data: $.param(saveData),
							headers: {
								'Content-Type': 'application/x-www-form-urlencoded'
							}
						}).then(function success(response) {
							if (response.data.erfolg)
							{
								successService.setMessage("Daten erfolgreich gespeichert.");
								ctrl.loadZuordnung();
							}
							else
							{
								errorService.setError(getErrorMsg(response));
							}
						}, function error(response) {
							errorService.setError(getErrorMsg(response));
						});
					}
				}
				else
				{
					alert("Bitte Daten vervollständigen.");
				}
			}
			
			ctrl.deleteZuordnung = function()
			{
				$http({
					method: 'GET',
					url: './api/studiengang/studiengangsgruppen/delete_studiengangsgruppenZuordnung.php?studiengang_kz='+$scope.stgkz,
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						successService.setMessage("Daten erfolgreich gelöscht.");
						ctrl.zuordnung = false;
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			}
			
			ctrl.loadZuordnung = function()
			{
				$http({
					method: 'GET',
					url: './api/studiengang/studiengangsgruppen/load_studiengangsgruppenZuordnung.php?studiengang_kz='+$scope.stgkz,
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.zuordnung = response.data.info;
						if(ctrl.zuordnung.data === null)
							ctrl.loadStudiengangGruppen();
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
			
			this.loadZuordnung();
			
			ctrl.loadStudiengangGruppen = function()
			{
				$http({
					method: 'GET',
					url: './api/studiengang/studiengangsgruppen/studiengangsgruppen.php'
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
			}
		});
		
function Studiengangsgruppe()
{
	this.studiengang_kz = null;
	this.data = [];
}