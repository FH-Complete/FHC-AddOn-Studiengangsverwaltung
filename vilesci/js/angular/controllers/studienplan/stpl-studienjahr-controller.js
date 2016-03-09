angular.module('stgv2')
		.controller('StplStudienjahrCtrl', function ($scope, $http, $stateParams, errorService, successService, StudienplanService, StudienordnungService) {
			$scope.studienplan_id = $stateParams.studienplan_id;
			var ctrl = this;
			ctrl.data = "";
			ctrl.spracheList = [];
			ctrl.studienjahrList = [];
			
			//enable tooltips
			$(document).ready(function(){
				$('[data-toggle="tooltip"]').tooltip();
			});
			
			ctrl.newStudienjahr = function ()
			{
				$scope.form.$setPristine();
				$("#dataGridStudienjahr").datagrid("unselectAll");
				ctrl.studienjahr = new Studienjahr();
				for(var i = 1; i<= ctrl.data.regelstudiendauer; i++)
				{
					ctrl.studienjahr.data.gueltigFuer[i] = false;
				};
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
			
			//loading StudienjahrList
			$http({
				method: 'GET',
				url: './api/helper/studienjahr.php',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.studienjahrList = response.data.info;
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
				$("#dataGridStudienjahr").datagrid({
					url: "./api/studienplan/studienjahr/studienjahr.php?studienplan_id=" + $stateParams.studienplan_id,
					method: 'GET',
					multiSort: true,
					singleSelect:true,
					onLoadSuccess: function (data)
					{
						return data.info;
						//Error Handling happens in loadFilter
					},
					onLoadError: function (error) {
						//TODO Error Handling
						console.log(error);
					},
					loadFilter: function (data) {
						var result = {};
						if (data.erfolg)
						{
							angular.forEach(data.info, function(value, index)
							{
								value.ausbildungssemester = value.data.gueltigFuer;
							});
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
						ctrl.loadStudienjahrDetails(row);
						if ($("#save").is(":visible"))
							ctrl.changeButtons();
					},
					columns: [[
						{field: 'studienjahr_id', align: 'right', title:'ID'},
						{field: 'studienjahr_kurzbz', align:'right', title:'Studienjahr'},
						{field: 'ausbildungssemester', align:'right', 
							formatter: function(value)
							{
								var string = "";
								angular.forEach(value, function(v, i)
								{
									if(v === true)
									{
										string+= i+" ";
									}
								});
								return string;
								
							},
							title:'Ausbildungssemester'}
					]]
				});
//				$("#dataGridStudienjahr").datagrid('sort', {
//					sortName: 'bezeichnung',
//					sortOrder: 'desc'
//				});
			};

			ctrl.loadDataGrid();
			
			ctrl.loadStudienjahrDetails = function(row)
			{
				ctrl.studienjahr = angular.copy(row);	
				$scope.$apply();
				$("#stplStudienjahrDetails").show();
			};

			ctrl.save = function () {
				if ($scope.form.$valid)
				{
					var saveData = {data: ""}
					saveData.data = angular.copy(ctrl.studienjahr);
					saveData.data.data = JSON.stringify(saveData.data.data);
					$http({
						method: 'POST',
						url: './api/studienplan/studienjahr/save_studienjahr.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if (response.data.erfolg)
						{
							$("#dataGridStudienjahr").datagrid('reload');
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
			
			ctrl.range = function (max)
			{
				var values = [];
				for (i = 1; i <= max; i++)
				{
					values.push(i);
				}
				return values;
			};
			
			ctrl.delete = function()
			{
				if (confirm("Wollen Sie das Studienjahr wirklich Löschen?"))
				{
					var deleteData = {data: ""}
					deleteData.data = ctrl.studienjahr;
					$http({
						method: 'POST',
						url: './api/studienplan/studienjahr/delete_studienjahr.php',
						data: $.param(deleteData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if (response.data.erfolg)
						{
							$("#dataGridStudienjahr").datagrid('reload');
							ctrl.newStudienjahr();
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
		weihnachtsferien: {
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
		erlaeuterungen: ""
	};
	
}