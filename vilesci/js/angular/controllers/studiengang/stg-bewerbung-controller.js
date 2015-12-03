angular.module('stgv2')
		.controller('StgBewerbungCtrl', function ($scope, $http, $state, $stateParams, errorService, successService) {
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.data = "";
			ctrl.bewerbungstermin = new Bewerbungstermin();
			ctrl.studiengangList = "";
			ctrl.studiensemesterList = [{
					studiensemester_kurzbz : "null",
					beschreibung: "alle"
			}];
			ctrl.selectedStudiensemester = null;
			
			//loading Studiensemester list
			$http({
				method: "GET",
				url: "./api/helper/studiensemester.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					$.merge(ctrl.studiensemesterList, response.data.info);
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
			
			//loading StudiengangList
			$http({
				method: "GET",
				url: "./api/helper/studiengang.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.studiengangList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
			
			ctrl.loadDataGrid = function()
			{
				$("#dataGridBewerbungstermin").datagrid({
					//TODO format Time in column
					url: "./api/studiengang/bewerbungstermin/bewerbungstermin.php?stgkz=" + $stateParams.stgkz+"&studiensemester_kurzbz="+ctrl.selectedStudiensemester,
					method: 'GET',
					onLoadSuccess: function(data)
					{
						//Error Handling happens in loadFilter
					},
					onLoadError: function(){
						//TODO Error Handling
					},
					loadFilter: function(data){
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
					onClickRow: function (row)
					{
						var row = $("#dataGridBewerbungstermin").datagrid("getSelected");
						ctrl.loadBewerbungsterminDetails(row);
						if($("#save").is(":visible"))
							ctrl.changeButtons();
					}
				});
			};
			
			ctrl.loadDataGrid();
			
			$(".datepicker").datepicker({
				dateFormat: "yy-mm-dd",
				firstDay: 1
			});

			ctrl.newBewerbungstermin = function()
			{
				$("#dataGridBewerbungstermin").datagrid("unselectAll");
				ctrl.bewerbungstermin = new Bewerbungstermin();
				ctrl.bewerbungstermin.studiengang_kz = $scope.stgkz;
				ctrl.bewerbungstermin.studiensemester_kurzbz = ctrl.selectedStudiensemester;
				if(!$("#save").is(":visible"))
					ctrl.changeButtons();
				$("#bewerbungsterminDetails").show();
			};
			
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
			
			ctrl.save = function()
			{
				var saveData = {data: ""}
				saveData.data = ctrl.bewerbungstermin;
				$http({
					method: 'POST',
					url: './api/studiengang/bewerbungstermin/save_bewerbungstermin.php',
					data: $.param(saveData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if(response.data.erfolg)
					{
						$("#dataGridBewerbungstermin").datagrid('reload');
						ctrl.bewerbungstermin = new Bewerbungstermin();
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
			
			ctrl.loadBewerbungsterminDetails = function(row)
			{
				ctrl.bewerbungstermin = row;
				$scope.$apply();
				$("#bewerbungsterminDetails").show();
			};
			
			ctrl.update = function()
			{
				
				var updateData = { data: ""};
				updateData.data = ctrl.bewerbungstermin;
				$http({
					method: 'POST',
					url: './api/studiengang/bewerbungstermin/update_bewerbungstermin.php',
					data: $.param(updateData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if(response.data.erfolg)
					{
						$("#dataGridBewerbungstermin").datagrid('reload');
						ctrl.bewerbungstermin = new Bewerbungstermin();
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
			
			ctrl.delete = function()
			{
				if(confirm("Wollen Sie den Bewerbungstermin wirklich Löschen?"))
				{
					var deleteData = {data: ""}
					deleteData.data = ctrl.bewerbungstermin;
					$http({
						method: 'POST',
						url: './api/studiengang/bewerbungstermin/delete_bewerbungstermin.php',
						data: $.param(deleteData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if(response.data.erfolg)
						{
							$("#dataGridBewerbungstermin").datagrid('reload');
							ctrl.newBewerbungstermin();
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
		});
		
function Bewerbungstermin()
{
	this.bewerbungstermin_id = "";
	this.studiengang_kz = "";
	this.studiensemester_kurzbz = "";
	this.beginn = "";
	this.ende = "";
	this.nachfrist = "";
	this.nachfrist_ende = "";
	this.anmerkung = "";
	this.insertvon = "";
	this.insertamum = "";
	this.updateamum = "";
	this.updatevon = "";
}