angular.module('stgv2')
		.controller('StgReihungstestCtrl', function ($scope, $http, $state, $stateParams,errorService) {
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.data = "";
			ctrl.reihungstest = new Reihungstest();
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
			
			ctrl.loadDataGrid = function()
			{
				$("#dataGridReihungstest").datagrid({
					//TODO format Time in column
					url: "./api/studiengang/reihungstest.php?stgkz=" + $stateParams.stgkz+"&studiensemester_kurzbz="+ctrl.selectedStudiensemester,
					method: 'GET',
					onLoadSuccess: function(data)
					{
						//Error Handling happens in loadFilter
						console.log(data);
					},
					onLoadError: function(){
						//TODO Error Handling
						console.log("fehler");
					},
					loadFilter: function(data){
						console.log(data);
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
						var row = $("#dataGridReihungstest").datagrid("getSelected");
						ctrl.loadReihungstestDetails(row.reihungstest_id);
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
			
			ctrl.loadReihungstestDetails = function(reihungstest_id)
			{
				$http({
					method: "GET",
					url: "./api/studiengang/reihungstestDetails.php?reihungstest_id=" + reihungstest_id
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.reihungstest = response.data.info;
						var currentDate = new Date();
						$("#reihungstestDetails").show();
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
			ctrl.update = function()
			{
				var updateData = ctrl.reihungstest;
				console.log(updateData);
				$http({
					method: 'POST',
					url: './api/studiengang/update_reihungstest.php',
					headers: {
						'Content-Type': 'application/json'
					},
					data: JSON.stringify(updateData)
				}).then(function success(response) {
					//TODO success 
					$("#dataGridReihungstest").datagrid('reload');
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
			
			ctrl.save = function()
			{
				var saveData = ctrl.reihungstest;
				console.log(saveData);
				$http({
					method: 'POST',
					url: './api/studiengang/save_reihungstest.php',
					headers: {
						'Content-Type': 'application/json'
					},
					data: JSON.stringify(saveData)
				}).then(function success(response) {
					//TODO success 
					$("#dataGridReihungstest").datagrid('reload');
					//TODO select recently added Reihungstest in Datagrid
					ctrl.reihungstest = new Reihungstest();
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
			
			ctrl.newReihungstest = function()
			{
				ctrl.reihungstest = new Reihungstest();
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
				}
				else
				{
					$("#save").show();
					$("#update").hide();
				}
			};
		});
		
function Reihungstest()
{
	this.reihungstest_id = "";
	this.studiengang_kz = "";
	this.ort_kurzbz = "";
	this.anmerkung = "";
	this.datum = "";
	this.uhrzeit = "";
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