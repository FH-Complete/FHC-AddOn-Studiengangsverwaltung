angular.module('stgv2')
		.controller('StgDoktoratCtrl', function ($scope, $http, $state, $stateParams,errorService,successService,FileUploader) {
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.data = "";
			ctrl.dokotrat = new Doktorat();
			ctrl.selectedStudiensemester = null;
			ctrl.studiensemesterList = "";
			ctrl.fileExtensionWhiteList = ["PDF","DOC","DOCX","JPG","JPEG"];
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
				$("#dataGridDoktorat").datagrid({
					url: "./api/studiengang/doktorat/doktorat.php?stgkz=" + $stateParams.stgkz,
					method: 'GET',
					singleSelect:true,
					onLoadSuccess: function (data)
					{
						if(ctrl.lastSelectedIndex !== null)
						{
							$("#dataGridDoktorat").datagrid('selectRow', ctrl.lastSelectedIndex);
							var row = $("#dataGridDoktorat").datagrid("getSelected");
							ctrl.loadDoktoratDetails(row);
//							$scope.$apply();
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
//						var row = $("#dataGridDoktorat").datagrid("getSelected");
						ctrl.lastSelectedIndex = index;
						ctrl.loadDoktoratDetails(row);
						if ($("#save").is(":visible"))
							ctrl.changeButtons();
					},
					columns: [[
						{field: 'doktorat_id', align: 'right', title:'ID'},
						{field: 'studiengang_kz', align:'left', title:'STG KZ'},
						{field: 'bezeichnung', align:'left', title:'Bezeichnung'},
						{field: 'datum_erlass', align:'left', formatter: formatDateToString, title:'Erlassdatum'},
						{field: 'gueltigvon', align:'left', title:'gültig von'},
						{field: 'gueltigbis', align:'left', title:'gültig bis'}
					]]
				});
			};
			
			ctrl.loadDataGrid();
			
			$("input[name=datum_erlass]").datepicker({
				dateFormat: "yy-mm-dd",
				firstDay: 1
			});
			
			ctrl.loadDoktoratDetails = function(row)
			{
				ctrl.doktorat = angular.copy(row);
				ctrl.doktorat.datum_erlass = formatStringToDate(row.datum_erlass);
				
				$scope.$apply();
				$("#doktoratDetails").show();
			}
			
			ctrl.save = function()
			{
				if($scope.form.$valid)
				{
					var saveData = {data: ""}
					saveData.data = ctrl.doktorat;
					$http({
						method: 'POST',
						url: './api/studiengang/doktorat/save_doktorat.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if(response.data.erfolg)
						{
							ctrl.doktorat = new Doktorat();
							ctrl.doktorat.studiengang_kz = $scope.stgkz;
							ctrl.doktorat.doktorat_id = response.data.info;
							$($scope.uploader.queue).each(function(k,v){
								v.upload();
							});
							$("#dataGridDoktorat").datagrid('reload');
							//TODO select recently added Doktorat in Datagrid
							$scope.form.$setPristine();
							successService.setMessage(response.data.info);
						}
						else
						{
							errorService.setError(getErrorMsg(response));
						}
					}, function error(response) {
						errorService.setError(getErrorMsg(response));
					});
				}
			};
			
			ctrl.update = function()
			{
				if($scope.form.$valid)
				{
					var updateData = {data: ""}
					updateData.data = ctrl.doktorat;
					console.log(updateData);
					$http({
						method: 'POST',
						url: './api/studiengang/doktorat/update_doktorat.php',
						data: $.param(updateData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if(response.data.erfolg)
						{
							$("#dataGridDoktorat").datagrid('reload');
							$scope.form.$setPristine();
							successService.setMessage(response.data.info);
						}
						else
						{
							errorService.setError(getErrorMsg(response));
						}
					}, function error(response) {
						errorService.setError(getErrorMsg(response));
					});
				}
			};
			
			ctrl.newDoktorat = function()
			{
				$("#dataGridDoktorat").datagrid("unselectAll");
				ctrl.doktorat = new Doktorat();
				$scope.form.$setPristine();
				ctrl.doktorat.studiengang_kz = $scope.stgkz;
				if(!$("#save").is(":visible"))
					ctrl.changeButtons();
				$("#doktoratDetails").show();
			};
			
			ctrl.delete = function()
			{
				if(confirm("Wollen Sie die Doktoratsstudienverordnung wirklich Löschen?"))
				{
					var deleteData = {data: ""}
					deleteData.data = ctrl.doktorat;
					$http({
						method: 'POST',
						url: './api/studiengang/doktorat/delete_doktorat.php',
						data: $.param(deleteData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if(response.data.erfolg)
						{
							$("#dataGridDoktorat").datagrid('reload');
							ctrl.newDoktorat();
							successService.setMessage(response.data.info);
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
			
			ctrl.changeButtons = function()
			{
				if($("#save").is(":visible"))
				{
					$("#save").hide();
					$("#update").show();
					$("#delete").show();
				}
				else
				{
					$("#save").show();
					$("#update").hide();
					$("#delete").hide();
				}
			};
			$scope.uploader = new FileUploader({
				url: './api/helper/upload_dokument.php',
				formData: [{
					doktorat_id: $scope.studienordnung_id
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
						doktorat_id : ctrl.doktorat.doktorat_id,
						dms_id : response.info
					};
					if(response.erfolg)
					{
						$http({
							method: 'POST',
							url: './api/studiengang/doktorat/add_document.php',
							data: $.param(data),
							headers: {
								'Content-Type': 'application/x-www-form-urlencoded'
							}
						}).then(function success(response) {
							if(response.data.erfolg)
							{
								$("#dataGridDoktorat").datagrid('reload');
							}
						}, function error(response) {
							errorService.setError(getErrorMsg(response));
						});
					}
					else
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
				}
			});
			
			ctrl.uploadDokument = function()
			{
				$($scope.uploader.queue).each(function(k,v){
					v.upload();
				});
			};
			
			ctrl.deleteDokument = function (dms_id)
			{
				$http({
					method: "GET",
					url: "./api/studiengang/doktorat/delete_dokument.php?dms_id="+dms_id+"&doktorat_id="+ctrl.doktorat.doktorat_id,
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						var dokumente = ctrl.doktorat.dokumente.filter(function(obj){
							return obj.dms_id !== dms_id;
						});
						ctrl.doktorat.dokumente = dokumente;
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

function Doktorat()
{
	this.doktorat_id = "";
	this.bezeichnung = "";
	this.datum_erlass = "";
	this.gueltigvon = "";
	this.gueltigbis = "";
	this.dokumente = [];
	this.insertamum = "";
	this.insertvon = "";
	this.updateamum = "";
	this.updatevon = "";
}