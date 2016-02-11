angular.module('stgv2')
		.controller('StoDokumenteCtrl', function ($scope, $http, $state, $stateParams, errorService, successService, FileUploader) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			$scope.sortReverse = true;
			$scope.tooltipVisibility = false;
			var ctrl = this;
			ctrl.fileExtensionWhiteList = ["PDF","JPG","JPEG","DOC","DOCX"];
			ctrl.dokumente = "";

			function loadDokumente()
			{
				$http({
					method: "GET",
					url: "./api/studienordnung/dokumente/dokumente.php?studienordnung_id=" + $scope.studienordnung_id
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
					url: "./api/studienordnung/dokumente/delete_dokument.php?dms_id=" + dms_id +"&studienordnung_id=" + $scope.studienordnung_id
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
					studienordnung_id: $scope.studienordnung_id
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
					if(response.erfolg === false)
					{
						$scope.uploader.cancelAll();
						item.isSuccess = false;
						item.isUploaded = false;
						item.progress = 0;
						item.isError = true;
					}
				},
				onErrorItem: function(fileItem, response, status, headers) {
					console.log('onErrorItem', fileItem, response, status, headers);
				},
				onCompleteAll: function()
				{
					//wird am ende des uploads der gesamten queue aufgerufen
					var elements = $scope.uploader.getNotUploadedItems();
					if(elements.length > 0)
					{
						var response = JSON.parse(elements[0]._xhr.response);
						errorService.setError(response.message.message+" -> "+response.message.detail);
					}
					loadDokumente();
				}
			
			});
		});