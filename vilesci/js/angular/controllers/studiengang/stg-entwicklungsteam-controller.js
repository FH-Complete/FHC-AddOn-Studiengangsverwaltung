angular.module('stgv2')
		.controller('StgEntwicklungsteamCtrl', function ($scope, $http, $state, $stateParams, errorService, successService, $filter) {

				$scope.stgkz = $stateParams.stgkz;
				var ctrl = this;
				ctrl.data = "";
				ctrl.entwicklungsteam = new Entwicklungsteam();
				ctrl.lastSelectedIndex = null;
				ctrl.besqualcode = null;
				ctrl.besqualcodes = null;
				ctrl.mitarbeiter_name = null;

			//loading besqualcodes
			$http({
				method: 'GET',
				url: './api/helper/besqualcode.php',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			}).then(function success(response)
			{
				if (response.data.erfolg)
				{
					ctrl.besqualcodes = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response)
			{
				errorService.setError(getErrorMsg(response));
			});

			$('#masuche').combobox({
				url:'./api/helper/mitarbeiterSearch.php',
				valueField:'v',
				textField:'l',
				filter: function (q, row) {
					var opts = $(this).combobox('options');
					return row[opts.textField].toLowerCase().indexOf(q.toLowerCase()) > -1;
				},
				loadFilter: function (data) {
					return data.info;
				},
				onChange: function (newValue,oldValue) {
					//console.log(newValue + ':' + oldValue);
					ctrl.entwicklungsteam.mitarbeiter_uid = newValue;
				}
			});

			ctrl.loadDataGrid = function ()
			{	$("#dataGridEntwicklungsteam").datagrid({
				url: "./api/studiengang/entwicklungsteam/entwicklungsteam.php?stgkz=" + $stateParams.stgkz,
				method: 'GET',
				singleSelect: true,
				multiSort: true,
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
				},
				loadFilter: function (data)
				{
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
					ctrl.lastSelectedIndex = index;
					ctrl.loadEntwicklungsteamDetails(row);
					if ($("#save").is(":visible"))
						ctrl.changeButtons();
				},
					columns: [[
						{field: 'entwicklungsteam_id', align:'right', title:'ID'},
						{field: 'mitarbeiter_label', align: 'left',  sortable: 'true', title:'Mitarbeiter*in'},
						{field: 'mitarbeiter_uid', align: 'left',  sortable: 'true',  title:'uid'},
						{field: 'beginn', align:'left',  sortable: 'true', formatter: dateTimeStringToGermanDateString, title:'Beginn'},
						{field: 'ende', align:'left',  sortable: 'true', formatter: dateTimeStringToGermanDateString, title:'Ende'},
						{field: 'studiengang_kz', align:'left', title:'STG KZ'},
						{field: 'besqualbez', align:'left',  sortable: 'true', title:'Besondere Qualifikation'},
						{field: 'besqualcode', align:'right', title:'Code'}
					]]
				});
				//hide studiengang_kz and besqualcode
				$('#dataGridEntwicklungsteam').datagrid('hideColumn', 'studiengang_kz');
				$('#dataGridEntwicklungsteam').datagrid('hideColumn', 'besqualcode');
				$('#dataGridEntwicklungsteam').datagrid('hideColumn', 'entwicklungsteam_id');
			};
			ctrl.loadDataGrid();

			//GERMAN
			$("#datepicker_beginn").datepicker({
				dateFormat: "dd.mm.yy",
				firstDay: 1
			});

			$("#datepicker_ende").datepicker({
				dateFormat: "dd.mm.yy",
				firstDay: 1
			});

			ctrl.loadEntwicklungsteamDetails = function(row)
			{
				$scope.besqualcode = row.besqualcode;
				ctrl.entwicklungsteam = row;

				//Iso Date to German String
				ctrl.entwicklungsteam.beginn = dateTimeStringToGermanDate(ctrl.entwicklungsteam.beginn);
				ctrl.entwicklungsteam.ende = dateTimeStringToGermanDate(ctrl.entwicklungsteam.ende);
				$scope.$apply();
				$('#masuche').combobox('setValue', row.mitarbeiter_uid);
				$("#entwicklungsteamDetails").show();
			}

			ctrl.save = function ()
			{
				var saveData = {data: ""};
				saveData.data = ctrl.entwicklungsteam;

				//GermanDateToISODate
				if(ctrl.entwicklungsteam.beginn != null && ctrl.entwicklungsteam.beginn != '')
					saveData.data.beginn = GermanDateToISODate(ctrl.entwicklungsteam.beginn);
				if(ctrl.entwicklungsteam.ende != null && ctrl.entwicklungsteam.ende != '')
					saveData.data.ende = GermanDateToISODate(ctrl.entwicklungsteam.ende);

				if(ctrl.entwicklungsteam.studiengang_kz == null || ctrl.entwicklungsteam.studiengang_kz == '')
					saveData.data.studiengang_kz = $scope.stgkz;

				saveData.data.insertamum = new Date().toISOString().slice(0, 19);

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
							$('#masuche').combobox('clear');
							alert(response.data.info);
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

					//GermanDateToISODate
					if(ctrl.entwicklungsteam.beginn != null && ctrl.entwicklungsteam.beginn != '')
						updateData.data.beginn = GermanDateToISODate(ctrl.entwicklungsteam.beginn);
					if(ctrl.entwicklungsteam.ende != null && ctrl.entwicklungsteam.ende != '')
						updateData.data.ende = GermanDateToISODate(ctrl.entwicklungsteam.ende);

					updateData.data.updateamum = new Date().toISOString().slice(0, 19);

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
								$("#dataGridEntwicklungsteam").datagrid("unselectAll");
								ctrl.lastSelectedIndex = null;
								ctrl.entwicklungsteam = new Entwicklungsteam();
								$scope.form_entwicklungsteam.$setPristine();


								$("#dataGridEntwicklungsteam").datagrid('reload');
								successService.setMessage(response.data.info);
								$('#masuche').combobox('clear');

								alert(response.data.info);
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

			ctrl.newEntwicklungsteam = function()
			{
				$("#dataGridEntwicklungsteam").datagrid("unselectAll");
				ctrl.entwicklungsteam = new Entwicklungsteam();
				ctrl.entwicklungsteam.studiengang_kz = $scope.stgkz;
				if(!$("#save").is(":visible"))
					ctrl.changeButtons();
				$("#entwicklungsteamDetails").show();
			};

			ctrl.delete = function ()
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
							$('#masuche').combobox('clear');
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

			ctrl.changeButtons = function ()
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

			$scope.validate = function (evt)
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
					var newValue = ctrl.entwicklungsteam.besqualcode.substring(0, length - 1);
					ctrl.entwicklungsteam.besqualcode = newValue;
					return true;
				}
				return false;
			};
		});

function Entwicklungsteam()
{
	this.entwicklungsteam_id = "";
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
