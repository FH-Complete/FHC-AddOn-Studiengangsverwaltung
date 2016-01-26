angular.module('stgv2')
		.controller('StgDoktoratCtrl', function ($scope, $http, $state, $stateParams,errorService,successService) {
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.data = "";
			ctrl.dokotrat = new Doktorat();
			ctrl.selectedStudiensemester = null;
			ctrl.studiensemesterList = "";

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
				row.datum_erlass = formatStringToDate(row.datum_erlass.split(" ")[0]);
				ctrl.doktorat = row;
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
							$("#dataGridDoktorat").datagrid('reload');
							//TODO select recently added Doktorat in Datagrid
							ctrl.doktorat = new Doktorat();
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
				var updateData = {data: ""}
				updateData.data = ctrl.doktorat;
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
						successService.setMessage(response.data.info);
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
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

		});

function Doktorat()
{
	this.doktorat_id = "";
	this.bezeichnung = "";
	this.datum_erlass = "";
	this.gueltigvon = "";
	this.gueltigbis = "";
	this.insertamum = "";
	this.insertvon = "";
	this.updateamum = "";
	this.updatevon = "";
}