angular.module('stgv2')
		.controller('StplStudienjahrCtrl', function ($scope, $http, $stateParams, errorService, successService, StudienplanService, StudienordnungService, SpracheService) {
			$scope.studienplan_id = $stateParams.studienplan_id;
			var ctrl = this;
			ctrl.data = "";
			ctrl.spracheList = [];
			
			//enable tooltips
			$(document).ready(function(){
				$('[data-toggle="tooltip"]').tooltip();
			});
			
			ctrl.newStudienjahr = function ()
			{
				$scope.form.$setPristine();
				$("#dataGridStudienjahr").datagrid("unselectAll");
				ctrl.studienjahr = new Studienjahr();
				ctrl.studienjahr.studienplan_id = $scope.studienplan_id;
				if (!$("#save").is(":visible"))
					ctrl.changeButtons();
				$("#stplStudienjahrDetails").show();
			};
			
			ctrl.changeButtons = function ()
			{
				if ($("#save").is(":visible"))
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
			
			//loading SpracheList
//			SpracheService.getSpracheList().then(function(result){
//				ctrl.spracheList = result;
//			},function(error){
//				errorService.setError(getErrorMsg(error));
//			});

			//loading data
			StudienplanService.getStudienplan($scope.studienplan_id).then(function (result) {
				ctrl.data = result;
				StudienordnungService.getStudienordnungByStudienplan($scope.studienplan_id).then(function (result) {
					ctrl.data.status_kurzbz = result.status_kurzbz;
				}, function (error) {
					errorService.setError(getErrorMsg(error));
				});
			}, function (error) {
				errorService.setError(getErrorMsg(error));
			});
			
			ctrl.loadDataGrid = function ()
			{
				$("#dataGridStudienjahr").datagrid({
					url: "./api/studienplan/studienjahr/studienjahr.php?studienplan_id=" + $stateParams.studienplan_id,
					method: 'GET',
					multiSort: true,
					singleSelect:true,
					onLoadSuccess: function (data)
					{
						console.log(data);
						return data.info;
						//Error Handling happens in loadFilter
					},
					onLoadError: function (error) {
						//TODO Error Handling
						console.log(error);
					},
					loadFilter: function (data) {
						console.log(data);
						var result = {};
						if (data.erfolg)
						{
							ctrl.data = data.info;
							result.rows = data.info;
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
						ctrl.loadBewerbungsterminDetails(row);
						if ($("#save").is(":visible"))
							ctrl.changeButtons();
					},
					columns: [[
						{field: 'studienjahr_id', align: 'right', title:'ID'},
						{field: 'studienjahr', align:'right', title:'Studienjahr'},
						{field: 'ausbildungssemester', align:'right', title:'Ausbildungssemester'}
					]]
				});
//				$("#dataGridStudienjahr").datagrid('sort', {
//					sortName: 'bezeichnung',
//					sortOrder: 'desc'
//				});
			};

			ctrl.loadDataGrid();

			ctrl.save = function () {
				var saveData = {data: ""}
				saveData.data = ctrl.data;
				console.log(ctrl.studienjahr);
//				$http({
//					method: 'POST',
//					url: './api/studienplan/eckdaten/save_eckdaten.php',
//					data: $.param(saveData),
//					headers: {
//						'Content-Type': 'application/x-www-form-urlencoded'
//					}
//				}).then(function success(response) {
//					if (response.data.erfolg)
//					{
//						$("#treeGrid").treegrid('reload');
//						successService.setMessage(response.data.info);
//					}
//					else
//					{
//						errorService.setError(getErrorMsg(response));
//					}
//				}, function error(response) {
//					errorService.setError(getErrorMsg(response));
//				});
			};
		});
		
function Studienjahr()
{
	this.studienjahr_id = "";
	this.studienplan_id = "";
	this.bezeichnung = "";
	this.data = {
		allgemeines: "",
		gueltigFuer: [],
		studienjahr:{
			beginn: "",
			ende: "",
			wochenAnzahl: ""
		},
		wintersemester: {
			beginn: "",
			ende: "",
			wochenAnzahl: "",
			lvWochenAnzahl: ""
		},
		weichnachtsferien: {
			beginn: "",
			ende: "",
			wochenAnzahl: ""
		},
		semesterferien: {
			beginn: "",
			ende: "",
			wochenAnzahl: ""
		},
		sommersemester: {
			beginn: "",
			ende: "",
			wochenAnzahl: "",
			lvWochenAnzahl: ""
		},
		osterferien: {
			beginn: "",
			ende: "",
			wochenAnzahl: ""
		},
		sommerferien: {
			beginn: "",
			ende: "",
			wochenAnzahl: ""
		},
		erlaueuterungen: ""
	};
	
}