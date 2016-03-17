angular.module('stgv2')
		.controller('StoAufnahmeverfahrenCtrl', function ($rootScope, $scope, $http, $stateParams, errorService, successService) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			var ctrl = this;
			ctrl.aufnahmeverfahren = new Aufnahmeverfahren($scope.studienordnung_id);

			if ($stateParams.studienordnung_id !== undefined && $rootScope.studienordnung === null)
			{
				$rootScope.setStudienordnung($stateParams.studienordnung_id);
			}
			
			$("#editor").wysiwyg(
			{
				'form':
				{
					'text-field': 'editorForm',
					'seperate-binary': false
				}
			});
			$("#editor").on('paste', function(event){
				event.preventDefault();
				var clipboardData = event.originalEvent.clipboardData.getData("text");
				$("#editor").html(clipboardData);
			});
			
			$http({
				method: 'GET',
				url: './api/studienordnung/aufnahmeverfahren/aufnahmeverfahren.php?studienordnung_id=' + $scope.studienordnung_id
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					if(response.data.erfolg)
					{
						if(response.data.info.length > 0)
						{
							$('#editor').html(response.data.info[0].data);
							ctrl.aufnahmeverfahren.aufnahmeverfahren_id = response.data.info[0].aufnahmeverfahren_id;
							ctrl.preview();
						}
						else
						{
							ctrl.loadInitialData();
						}
					}
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
			
			ctrl.loadInitialData = function(){
				$http({
					method: 'GET',
					url: './api/studienordnung/aufnahmeverfahren/aufnahmeverfahren.html'
				}).then(function success(response) {
					$("#editor").html(response.data);
					$("#initialData").show();
					ctrl.preview();
				}, function error(error) {
					console.log(error);
				});
			};
			
			ctrl.preview = function()
			{
				$('#editorPreview').html($('#editor').html());
			};
			

			ctrl.save = function()
			{
				$('#formSubmission').val($('#editor').html());
				ctrl.aufnahmeverfahren.data = JSON.stringify($('#editor').html());
				var saveData = {data: ""}
				saveData.data = ctrl.aufnahmeverfahren;
				
				$http({
					method: 'POST',
					url: './api/studienordnung/aufnahmeverfahren/save_aufnahmeverfahren.php',
					data: $.param(saveData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.preview();
						$("#initialData").hide();
//						successService.setMessage(response.data.info);
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
		
		
function Aufnahmeverfahren(studienordnung_id)
{
	this.aufnahmeverfahren_id = null;
	this.studienordnung_id = studienordnung_id;
	this.data = "";
}