angular.module('stgv2')
		.controller('StgFoerderungenCtrl', function ($scope, $http, $state, $stateParams, errorService, FileUploader) {
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.data = "";
			ctrl.foerdervertrag = new Foerdervertrag();
			ctrl.selectedStudiensemester = null;
			ctrl.studiensemesterList = "";
			ctrl.fileExtensionWhiteList = ["PDF"];
			ctrl.dokumente = "";
			ctrl.lastSelectedIndex = null;

			//loading Studiensemester list
			$http({
				method: "GET",
				url: "./api/helper/studiensemester.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.studiensemesterList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});

			ctrl.loadDataGrid = function ()
			{
				$("#dataGridFoerdervertrag").datagrid({
					//TODO format Time in column
					url: "./api/studiengang/foerdervertrag/foerdervertrag.php?stgkz=" + $stateParams.stgkz,
					method: 'GET',
					onLoadSuccess: function (data)
					{
						if(ctrl.lastSelectedIndex !== null)
						{
							$("#dataGridFoerdervertrag").datagrid('selectRow', ctrl.lastSelectedIndex);
							var row = $("#dataGridFoerdervertrag").datagrid("getSelected");
							ctrl.foerdervertrag = row;
							$scope.$apply();
						}
						//Error Handling happens in loadFilter
					},
					onLoadError: function () {
						//TODO Error Handling
					},
					loadFilter: function (data) {
						var result = {};
						if (data.erfolg)
						{
							ctrl.data = data.info;
							result.rows = data.info;
							result.total = data.info.length;
							return result;
						}
						else
						{
							errorService.setError(getErrorMsg(data));
							return result;
						}
					},
					onClickRow: function(index, row)
					{
						ctrl.lastSelectedIndex = index;
						ctrl.loadFoerdervertragDetails(row);
						if ($("#save").is(":visible"))
							ctrl.changeButtons();
					}
				});
			};
			
			ctrl.loadDataGrid();
			
			ctrl.loadFoerdervertragDetails = function(row)
			{
				ctrl.foerdervertrag = row;
				$scope.$apply();
				$("#foerdervertragDetails").show();
			}
			
			ctrl.save = function()
			{
				var saveData = {data: ""}
				saveData.data = ctrl.foerdervertrag;
				$http({
					method: 'POST',
					url: './api/studiengang/foerdervertrag/save_foerdervertrag.php',
					data: $.param(saveData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					//TODO success 
					ctrl.foerdervertrag = new Foerdervertrag();
					ctrl.foerdervertrag.foerdervertrag_id = response.data.info;
//					$("#dataGridFoerdervertrag").datagrid('reload');
					$($scope.uploader.queue).each(function(k,v){
						v.upload();
					});
					//TODO select recently added Reihungstest in Datagrid
					
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
			
			ctrl.update = function()
			{
				var updateData = {data: ""}
				updateData.data = ctrl.foerdervertrag;
				$http({
					method: 'POST',
					url: './api/studiengang/foerdervertrag/update_foerdervertrag.php',
					data: $.param(updateData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					//TODO success 
					$("#dataGridFoerdervertrag").datagrid('reload');
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
			
			ctrl.newFoerdervertrag = function()
			{
				$("#dataGridFoerdervertrag").datagrid("unselectAll");
				ctrl.foerdervertrag = new Foerdervertrag();
				ctrl.foerdervertrag.studiengang_kz = $scope.stgkz;
				if(!$("#save").is(":visible"))
					ctrl.changeButtons();
				$("#foerdervertragDetails").show();
			};
			
			ctrl.delete = function()
			{
				if(confirm("Wollen Sie den Fördervertrag wirklich Löschen?"))
				{
					var deleteData = {data: ""}
					deleteData.data = ctrl.foerdervertrag;
					$http({
						method: 'POST',
						url: './api/studiengang/foerdervertrag/delete_foerdervertrag.php',
						data: $.param(deleteData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						//TODO success 
						$("#dataGridFoerdervertrag").datagrid('reload');
						ctrl.newFoerdervertrag();
					}, function error(response) {
						errorService.setError(getErrorMsg(response));
					});
				}
			}
			
			ctrl.changeButtons = function()
			{
				if($("#save").is(":visible"))
				{
					$("#save").hide();
					$("#update").show();
					$("#delete").show();
					$("#upload").show();
				}
				else
				{
					$("#save").show();
					$("#update").hide();
					$("#delete").hide();
					$("#upload").hide();
				}
			};

			$scope.uploader = new FileUploader({
				url: './api/helper/upload_dokument.php',
				formData: [{
					foerdervertrag_id: $scope.stoid
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
					var data = {
						foerdervertrag_id : ctrl.foerdervertrag.foerdervertrag_id,
						dms_id : response.info
					}
					if(response.erfolg)
					{
						$http({
							method: 'POST',
							url: './api/studiengang/foerdervertrag/add_document.php',
							data: $.param(data),
							headers: {
								'Content-Type': 'application/x-www-form-urlencoded'
							}
						}).then(function success(response) {
							if(response.data.erfolg)
							{
								$("#dataGridFoerdervertrag").datagrid('reload');
							}
						}, function error(response) {
							errorService.setError(getErrorMsg(response));
						});
					}
				}
			});
			
			ctrl.deleteDokument = function (dms_id)
			{
				var data = {
					foerdervertrag_id : ctrl.foerdervertrag.foerdervertrag_id,
					dms_id : dms_id
				}
				$http({
					method: "POST",
					url: "./api/studiengang/foerdervertrag/delete_dokument.php",
					data: $.param(data),
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						var dokumente = ctrl.foerdervertrag.dokumente.filter(function(obj){
							return obj.dms_id !== dms_id;
						});
						ctrl.foerdervertrag.dokumente = dokumente;
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			}
			
			ctrl.uploadDokument = function()
			{
				$($scope.uploader.queue).each(function(k,v){
					v.upload();
				});
			}
		});

function Foerdervertrag()
{
	this.foerdervertrag_id = "";
	this.studiengang_kz = "";
	this.foerdergeber = "";
	this.foerdersatz = "";
	this.foerdergruppe = "";
	this.gueltigvon = "";
	this.gueltigbis = "";
	this.erlaeuterungen = "";
	this.insertamum = "";
	this.insertvon = "";
	this.updateamum = "";
	this.updatevon = "";
	this.dokumente = "";
}