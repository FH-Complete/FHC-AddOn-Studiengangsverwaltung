angular.module('stgv2')
		.controller('StgReihungstestCtrl', function ($scope, $http, $state, $stateParams,errorService,successService) {
			$scope.stgkz = $stateParams.stgkz;
			$scope.minTime = "08:00:00";
			$scope.maxTime = "21:00:00";
			var ctrl = this;
			ctrl.data = "";
			ctrl.reihungstest = new Reihungstest();
			ctrl.selectedStudiensemester = null;
			ctrl.studiensemesterList = [{
					studiensemester_kurzbz : "null",
					beschreibung: "alle"
			}];
			ctrl.ortList = "";
			
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
			
			//loading Ort list
			$http({
				method: "GET",
				url: "./api/helper/ort.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.ortList = response.data.info;
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
				$("#dataGridReihungstest").datagrid({
					url: "./api/studiengang/reihungstest/reihungstest.php?stgkz=" + $stateParams.stgkz+"&studiensemester_kurzbz="+ctrl.selectedStudiensemester,
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
					onClickRow: function (index, row)
					{
//						var row = $("#dataGridReihungstest").datagrid("getSelected");
						ctrl.loadReihungstestDetails(row);
						if($("#save").is(":visible"))
							ctrl.changeButtons();
					}
				});
			};
			
			ctrl.loadDataGrid();
			
			$("input[name=datum]").datepicker({
				dateFormat: "yy-mm-dd",
				firstDay: 1
			});
			
			ctrl.loadReihungstestDetails = function(row)
			{
				row.datum = formatStringToDate(row.datum);
				row.uhrzeit = formatStringToTime(row.uhrzeit, ":");
				ctrl.reihungstest = row;
				$scope.$apply();
				$("#reihungstestDetails").show();
			};
			ctrl.update = function()
			{
				if($scope.form.$valid)
				{
					var updateData = { data: ""};
					updateData.data = ctrl.reihungstest;
					$http({
						method: 'POST',
						url: './api/studiengang/reihungstest/update_reihungstest.php',
						data: $.param(updateData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if(response.data.erfolg)
						{
							$("#dataGridReihungstest").datagrid('reload');
							successService.setMessage(response.data.info);
							$scope.form.$setPristine();
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
			
			ctrl.save = function()
			{
				console.log(ctrl.reihungstest);
				
				if($scope.form.$valid)
				{
					var saveData = {data: ""}
					saveData.data = angular.copy(ctrl.reihungstest);
					saveData.data.datum = formatDateToString(saveData.data.datum);
					saveData.data.uhrzeit = formatTimeToString(saveData.data.uhrzeit);
					$http({
						method: 'POST',
						url: './api/studiengang/reihungstest/save_reihungstest.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						console.log(response);
						if(response.data.erfolg)
						{
							$("#dataGridReihungstest").datagrid('reload');
							//TODO select recently added Reihungstest in Datagrid
							ctrl.reihungstest = new Reihungstest();
							successService.setMessage(response.data.info);
							$scope.form.$setPristine();
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
			
			ctrl.newReihungstest = function()
			{
				$("#dataGridReihungstest").datagrid("unselectAll");
				ctrl.reihungstest = new Reihungstest();
				$scope.form.$setPristine();
				ctrl.reihungstest.studiengang_kz = $scope.stgkz;
				ctrl.reihungstest.studiensemester_kurzbz = ctrl.selectedStudiensemester;
				if(!$("#save").is(":visible"))
					ctrl.changeButtons();
				$("#reihungstestDetails").show();
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
			
			ctrl.delete = function()
			{
				if(confirm("Wollen Sie den Fördervertrag wirklich Löschen?"))
				{
					var deleteData = {data: ""}
					deleteData.data = ctrl.reihungstest;
					$http({
						method: 'POST',
						url: './api/studiengang/reihungstest/delete_reihungstest.php',
						data: $.param(deleteData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						console.log(response);
						if(response.data.erfolg)
						{
							$("#dataGridReihungstest").datagrid('reload');
							ctrl.newReihungstest();
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
		
function Reihungstest()
{
	this.reihungstest_id = "";
	this.studiengang_kz = "";
	this.ort_kurzbz = "";
	this.anmerkung = "";
	this.datum = new Date(new Date().getFullYear(),new Date().getMonth(), new Date().getDate());
	this.uhrzeit;
	this.ext_id = "";
	this.insertamum = "";
	this.insertvon = "";
	this.updateamum = "";
	this.updatevon = "";
	this.max_teilnehmer = "";
	this.oeffentlich = false;
	this.freigeschaltet = false;
	this.studiensemester_kurzbz = "";
}