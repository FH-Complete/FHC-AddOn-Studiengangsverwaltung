angular.module('stgv2')
		.controller('StoDokumenteCtrl', function ($scope, $http, $state, $stateParams, errorService, successService, FileUploader) {
			$scope.stoid = $stateParams.stoid;
			var ctrl = this;
			ctrl.fileExtensionWhiteList = ["PDF"];
			ctrl.dokumente = "";

			function loadDokumente()
			{
				$http({
					method: "GET",
					url: "./api/studienordnung/dokumente/dokumente.php?studienordnung_id=" + $scope.stoid
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.dokumente = response.data.info;
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			}
			
			ctrl.delete = function (dms_id)
			{
				$http({
					method: "GET",
					url: "./api/studienordnung/dokumente/delete_dokument.php?dms_id=" + dms_id +"&studienordnung_id=" + $scope.stoid
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						loadDokumente();
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			}
			
			loadDokumente();

			$scope.uploader = new FileUploader({
				url: './api/studienordnung/dokumente/upload_dokument.php',
				formData: [{
					studienordnung_id: $scope.stoid
				}],
				filters: [{
					name: 'extensionFilter',
					fn: function (item, options) {
						var extension = item.name.split(".").pop();
						if ($.inArray(extension.toUpperCase(), ctrl.fileExtensionWhiteList) === 0)
						{
							return true;
						}
						return false;
					}
				}],
				onSuccessItem: function(item, response, status, headers)
				{
					//wird nach jedem item aufgerufen
				},
				onCompleteAll: function()
				{
					//wird am ende des uploads der gesamten queue aufgerufen
					loadDokumente();
				}
			
			});
		});