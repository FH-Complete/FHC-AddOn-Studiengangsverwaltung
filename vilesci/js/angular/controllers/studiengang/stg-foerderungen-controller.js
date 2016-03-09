angular.module('stgv2')
		.controller('StgFoerderungenCtrl', function ($scope, $http, $state, $stateParams, errorService, successService, FileUploader, $filter) {
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.data = "";
			$scope.foerdergeber = "";
			$scope.foerdergruppe = "";
			$scope.foerdersatz = "";
			ctrl.foerdervertrag = new Foerdervertrag();
			ctrl.selectedStudiensemester = null;
			ctrl.studiensemesterList = "";
			ctrl.fileExtensionWhiteList = ["PDF"];
			ctrl.dokumente = "";
			ctrl.lastSelectedIndex = null;
			ctrl.foerdergeberList = ["BMWFW","FMMI","Sonstige"];
			ctrl.foerdergruppeList = [
				{
					foerdergruppe: "Studienplätze in Studiengängen mit einem Technikanteil von mindestens 50 %",
					foerdersatz: [{
						"label": "7.940",
						"value": "7940.00"
					},
					{
						"label": "8.850",
						"value": "8850.00"
					}]
				},
				{
					foerdergruppe: "Studienplätze in Studiengängen mit einem Technikanteil von mindestens 25 %",
					foerdersatz: [{
						"label": "6.990",
						"value": "6990.00"
					},
					{
						"label": "7.550",
						"value": "7550.00"
					}]
				},
				{
					foerdergruppe: "Studienplätze in Studiengängen mit dem Schwerpunkt Tourismus",
					foerdersatz: [{
						"label": "6.580",
						"value": "6580.00"
					},
					{
						"label": "7.550",
						"value": "7550.00"
					}]
				},
				{
					foerdergruppe: "Studienplätze in allen anderen Studiengängen",
					foerdersatz: [{
						"label": "6.510",
						"value": "6510.00"
					},
					{
						"label": "6.970",
						"value": "6970.00"
					}]
				}
			];
			ctrl.foerdersatzList = [];
			
			$scope.$watch('foerdergeber', function(data, old){
				if(data !== "Sonstige")
				{
					ctrl.foerdervertrag.foerdergeber = data;
				}
				else
				{
					if(ctrl.foerdergeberList.indexOf(ctrl.foerdervertrag.foerdergeber) != -1)
					{
						ctrl.foerdervertrag.foerdergeber = "";
					}
				}
			});
			
			$scope.$watch('foerdergruppe', function(data){
				var foerdergruppe = $filter('filter')(ctrl.foerdergruppeList, {foerdergruppe : data});
				if(foerdergruppe.length == 1)
				{
					ctrl.foerdervertrag.foerdergruppe = data;
					ctrl.foerdersatzList = foerdergruppe[0].foerdersatz;
				}
			});
			
			$scope.$watch('foerdersatz', function(data){
				ctrl.foerdervertrag.foerdersatz = data;
			});

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
					url: "./api/studiengang/foerdervertrag/foerdervertrag.php?stgkz=" + $stateParams.stgkz,
					method: 'GET',
					singleSelect: true,
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
						console.log(row);
						
						ctrl.lastSelectedIndex = index;
						ctrl.loadFoerdervertragDetails(row);
						if ($("#save").is(":visible"))
							ctrl.changeButtons();
					},
					columns: [[
						{field: 'foerdervertrag_id', align: 'right', title:'ID'},
						{field: 'studiengang_kz', align:'left', title:'STG KZ'},
						{field: 'foerdergeber', align:'left', title:'Fördergeber'},
						{field: 'foerdergruppe', align:'left', title:'Fördergruppe'},
						{field: 'foerdersatz', align:'left', title:'Fördersatz'},
						{field: 'gueltigvon', align:'left', title:'gültig von'},
						{field: 'gueltigbis', align:'left', title:'gültig bis'},
						{field: 'erlaeuterungen', align:'left', title:'Erläuterungen'}
					]]
				});
			};
			
			ctrl.loadDataGrid();
			
			ctrl.loadFoerdervertragDetails = function(row)
			{
				//needed to show data in dropdowns
				if(ctrl.foerdergeberList.indexOf(row.foerdergeber) != -1)
				{
					$scope.foerdergeber = row.foerdergeber;
				}
				else
				{
					$scope.foerdergeber = "Sonstige";
				}
				$scope.foerdergruppe = row.foerdergruppe;
				if($scope.foerdergeber == "BMWFW")
				{
//					var f = row.foerdersatz.substring(0,row.foerdersatz.indexOf("."));
					$scope.foerdersatz = row.foerdersatz;
				}
				else
				{
					$scope.foerdersatz = row.foerdersatz;
				}
				ctrl.foerdervertrag = row;
				$scope.$apply();
				$("#foerdervertragDetails").show();
			}
			
			ctrl.save = function()
			{
				var saveData = {data: ""}
				saveData.data = ctrl.foerdervertrag;
				if($scope.form_foerdervertrag.$valid)
				{
					$http({
						method: 'POST',
						url: './api/studiengang/foerdervertrag/save_foerdervertrag.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if(response.data.erfolg)
						{
							ctrl.foerdervertrag = new Foerdervertrag();
							ctrl.foerdervertrag.studiengang_kz = $scope.stgkz;
							ctrl.foerdervertrag.foerdervertrag_id = response.data.info;
							$($scope.uploader.queue).each(function(k,v){
								v.upload();
							});
							$scope.form_foerdervertrag.$setPristine();
							$("#dataGridFoerdervertrag").datagrid('reload');
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
				else
				{
					$scope.form_foerdervertrag.$setPristine();
				}
			};
			
			ctrl.update = function()
			{
				var updateData = {data: ""}
				updateData.data = ctrl.foerdervertrag;
				if($scope.form_foerdervertrag.$valid)
				{
					$http({
						method: 'POST',
						url: './api/studiengang/foerdervertrag/update_foerdervertrag.php',
						data: $.param(updateData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if(response.data.erfolg)
						{
							$("#dataGridFoerdervertrag").datagrid('reload');
							$scope.form_foerdervertrag.$setPristine();
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
						if(response.data.erfolg)
						{
							ctrl.lastSelectedIndex = null;
							$("#dataGridFoerdervertrag").datagrid('reload');
							ctrl.newFoerdervertrag();
							$scope.form_foerdervertrag.$setPristine();
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
					foerdervertrag_id: $scope.studienordnung_id
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
					};
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
			
			ctrl.deleteDokument = function (dms_id)
			{
				$http({
					method: "GET",
					url: "./api/studiengang/foerdervertrag/delete_dokument.php?dms_id="+dms_id+"&foerdervertrag_id="+ctrl.foerdervertrag.foerdervertrag_id,
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
			};
			
			$scope.validate = function(evt)
			{
				evt.preventDefault();
				var value = String.fromCharCode(evt.keyCode);
				if(!isNaN(value) && evt.keyCode > 48)
				{
					ctrl.foerdervertrag.foerdersatz += value;
					return true;
				}
				else if(!isNaN(evt.key) && evt.keyCode != 32)
				{
					ctrl.foerdervertrag.foerdersatz += evt.key;
					return true;
				}
				else if(evt.keyCode == 8)
				{
					var length = ctrl.foerdervertrag.foerdersatz.length;
					var newValue = ctrl.foerdervertrag.foerdersatz.substring(0,length-1);
					ctrl.foerdervertrag.foerdersatz = newValue;
					return true;
				}
				return false;
			};
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

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}