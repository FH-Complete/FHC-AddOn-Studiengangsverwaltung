angular.module('stgv2')
		.controller('StoZgvCtrl', function ($rootScope, $scope, $http, $stateParams, errorService, StudienordnungService) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			var ctrl = this;
			ctrl.zugangsvoraussetzung = new Zugangsvoraussetzung($scope.studienordnung_id);
			ctrl.studienordnung = null;
			ctrl.data = "";
			ctrl.status = "";

			if ($stateParams.studienordnung_id !== undefined && $rootScope.studienordnung === null)
			{
				$rootScope.setStudienordnung($stateParams.studienordnung_id);
			}
			
			StudienordnungService.getStudienordnung($scope.studienordnung_id).then(function(result){
				ctrl.studienordnung = result;
				$http({
					method: 'GET',
					url: './api/studienordnung/zgv/zgv.php?studienordnung_id=' + $scope.studienordnung_id
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						if(response.data.erfolg)
						{
							if(response.data.info.length > 0)
							{
								$('#editor').html(response.data.info[0].data);
								ctrl.zugangsvoraussetzung.zugangsvoraussetzung_id = response.data.info[0].zugangsvoraussetzung_id;
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
			},function(error){

			});
			
			$("#editor").wysiwyg(
			{
				'form':
				{
					'text-field': 'editorForm',
					'seperate-binary': false
				}
			});
			$("#editor").on('paste', function(event){
				setTimeout(function(){
					$("#editor").html($("#editor").html());
				},100);
			});
			
			ctrl.loadInitialData = function(){
				if(ctrl.studienordnung.studiengangsart != null)
				{
					$http({
						method: 'GET',
						url: './api/studienordnung/zgv/zgv_'+ctrl.studienordnung.studiengangsart.toLowerCase()+".html"
					}).then(function success(response) {
						$("#editor").html(response.data);
						$("#initialData").show();
						ctrl.preview();
					}, function error(error) {
						console.log(error);
					});
				}
				else
				{
					alert("Bitte definieren Sie die Studiengangsart unter 'Eckdaten'");
				}
			};
			
			ctrl.preview = function()
			{
				$('#editorPreview').html($('#editor').html());
			};
			

			ctrl.save = function()
			{
				$('#formSubmission').val($('#editor').html());
				ctrl.zugangsvoraussetzung.data = JSON.stringify($('#editor').html());
				var saveData = {data: ""}
				saveData.data = ctrl.zugangsvoraussetzung;
				
				$http({
					method: 'POST',
					url: './api/studienordnung/zgv/save_zgv.php',
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
		
		
function Zugangsvoraussetzung(studienordnung_id)
{
	this.zugangsvoraussetzung_id = null;
	this.studienordnung_id = studienordnung_id;
	this.data = "";
}