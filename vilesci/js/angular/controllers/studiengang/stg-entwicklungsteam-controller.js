angular.module('stgv2')
		.controller('StgEntwicklungsteamCtrl', function ($scope, $http, $state, $stateParams, errorService, successService, $filter) {
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.data = "";
			//$scope.mitarbeiter_uid = "";
			//$scope.besqualcode = "";
			ctrl.entwicklungsteam = new Entwicklungsteam();
			ctrl.lastSelectedIndex = null;
			ctrl.besqualcode = ["0","1","2","3"];
			ctrl.selectedMa = null;
			ctrl.mitarbeiterList = "";
			ctrl.mitarbeiter_uid = $('#masuche').val;


			// $scope.$watch('mitarbeiter_uid', function(data)
			// {
			// 	ctrl.entwicklungsteam.mitarbeiter_uid = data;
			// });

			$scope.$watch('besqualcode', function(data)
			{
				ctrl.entwicklungsteam.besqualcode = data;
			});

			$('#masuche').combobox({
				url:'./api/helper/mitarbeiterSearch.php',
				valueField:'v',
				textField:'l',
				filter: function(q, row) {
					var opts = $(this).combobox('options');
					return row[opts.textField].toLowerCase().indexOf(q.toLowerCase()) > -1;
				},
				loadFilter: function(data) {
					return data.info;
				}
			});

			//loading Search Mitarbeiter
			// $http({
			// 	method: 'GET',
			// 	url: './api/helper/mitarbeiterSearch.php',
			// 	headers: {
			// 		'Content-Type': 'application/x-www-form-urlencoded'
			// 	}
			// }).then(function success(response) {
			// 	if (response.data.erfolg)
			// 	{
			// 		ctrl.mitarbeiterList = response.data.info;
			// 	}
			// 	else
			// 	{
			// 		errorService.setError(getErrorMsg(response));
			// 	}
			// }, function error(response) {
			// 	errorService.setError(getErrorMsg(response));
			// });

			ctrl.loadDataGrid = function ()
			{
				$("#dataGridEntwicklungsteam").datagrid({
					url: "./api/studiengang/entwicklungsteam/entwicklungsteam.php?stgkz=" + $stateParams.stgkz,
					method: 'GET',
					singleSelect: true,
					onLoadSuccess: function (data)
					{
						if(ctrl.lastSelectedIndex !== null)
						{
							$("#dataGridEntwicklungsteam").datagrid('selectRow', ctrl.lastSelectedIndex);
							var row = $("#dataGridEntwicklungsteam").datagrid("getSelected");
							ctrl.entwicklungsteam = row;
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
						ctrl.loadEntwicklungsteamDetails(row);
						if ($("#save").is(":visible"))
							ctrl.changeButtons();
					},
					columns: [[
						{field: 'mitarbeiter_uid', align: 'right', title:'Mitarbeiter'},
						{field: 'studiengang_kz', align:'left', title:'STG KZ'},
						{field: 'besqualcode', align:'left', title:'Besondere Qualifikation'},
						{field: 'beginn', align:'left', title:'Beginn'},
						{field: 'ende', align:'left', title:'Ende'}
					]]
				});
			};

			ctrl.loadDataGrid();

			$("#datepicker_beginn").datepicker({
				dateFormat: "yy-mm-dd",
				firstDay: 1
			});

			$("#datepicker_ende").datepicker({
				dateFormat: "yy-mm-dd",
				firstDay: 1
			});

			ctrl.loadEntwicklungsteamDetails = function(row)
			{
				//console.log("entwicklungsteam in Funktion loadEntwicklungsteamDetails");
				$scope.besqualcode = row.besqualcode;

				$scope.mitarbeiter_uid = row.mitarbeiter_uid;
				ctrl.entwicklungsteam = row;

				$scope.$apply();
				$("#entwicklungsteamDetails").show();
			}

			ctrl.save = function()
			{
				var saveData = {data: ""}
				saveData.data = ctrl.entwicklungsteam;
				//saveData.data = angular.copy(ctrl.entwicklungsteam);
				if($scope.form_entwicklungsteam.$valid)
				{
					$http({
						method: 'POST',
						url: './api/studiengang/entwicklungsteam/save_entwicklungsteam.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if(response.data.erfolg)
						{
							ctrl.entwicklungsteam = new Entwicklungsteam();
							$scope.form_entwicklungsteam.$setPristine();
							$("#dataGridEntwicklungsteam").datagrid('reload');
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
					$scope.form_entwicklungsteam.$setPristine();
				}
			};

			ctrl.update = function()
			{
				var updateData = {data: ""}
				updateData.data = ctrl.entwicklungsteam;
				if($scope.form_entwicklungsteam.$valid)
				{
					$http({
						method: 'POST',
						url: './api/studiengang/entwicklungsteam/update_entwicklungsteam.php',
						data: $.param(updateData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if(response.data.erfolg)
						{
							$("#dataGridEntwicklungsteam").datagrid('reload');
							$scope.form_entwicklungsteam.$setPristine();
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

			ctrl.newEntwicklungsteam = function()
			{
				$("#dataGridEntwicklungsteam").datagrid("unselectAll");
				ctrl.entwicklungsteam = new Entwicklungsteam();
				ctrl.entwicklungsteam.studiengang_kz = $scope.stgkz;
				ctrl.entwicklungsteam.besqualcode = $scope.besqualcode;
				// ctrl.entwicklungsteam.mitarbeiter_uid = response.data.info;
				if(!$("#save").is(":visible"))
					ctrl.changeButtons();
				$("#entwicklungsteamDetails").show();
			};

			ctrl.delete = function()
			{
				if(confirm("Wollen Sie das Teammitglied wirklich Löschen?"))
				{
					var deleteData = {data: ""}
					deleteData.data = ctrl.entwicklungsteam;
					$http({
						method: 'POST',
						url: './api/studiengang/entwicklungsteam/delete_entwicklungsteam.php',
						data: $.param(deleteData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if(response.data.erfolg)
						{
							ctrl.lastSelectedIndex = null;
							$("#dataGridEntwicklungsteam").datagrid('reload');
							ctrl.newEntwicklungsteam();
							$scope.form_entwicklungsteam.$setPristine();
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

			$scope.validate = function(evt)
			{
				evt.preventDefault();
				var value = String.fromCharCode(evt.keyCode);
				if(!isNaN(value) && evt.keyCode > 48)
				{
					ctrl.entwicklungsteam.besqualcode += value;
					return true;
				}
				else if(!isNaN(evt.key) && evt.keyCode != 32)
				{
					ctrl.entwicklungsteam.besqualcode += evt.key;
					return true;
				}
				else if(evt.keyCode == 8)
				{
					var length = ctrl.entwicklungsteam.besqualcode.length;
					var newValue = ctrl.entwicklungsteam.besqualcode.substring(0,length-1);
					ctrl.entwicklungsteam.besqualcode = newValue;
					return true;
				}
				return false;
			};
		});

function Entwicklungsteam()
{
	this.mitarbeiter_uid = "";
	this.studiengang_kz = "";
	this.besqualcode = "";
	this.beginn = "";
	this.ende = "";
	this.insertamum = "";
	this.insertvon = "";
	this.updateamum = "";
	this.updatevon = "";
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
