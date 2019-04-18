angular.module('stgv2')
		.controller('StgBewerbungCtrl', function ($scope, $http, $stateParams, errorService, successService, StudienplanService, StudiensemesterService) {
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.data = "";
			ctrl.bewerbungstermin = new Bewerbungstermin();
			ctrl.studiengangList = "";
			ctrl.studiensemesterList = [{
					studiensemester_kurzbz: null,
					beschreibung: "alle"
				}];
			ctrl.selectedStudiensemester = null;
			ctrl.selectedNationengruppe = null;
			ctrl.nationengruppeList = [{
				nationengruppe_kurzbz: null,
				nationengruppe_bezeichnung: "Keine"
			}];

			//loading Studiensemester list
			StudiensemesterService.getStudiensemesterList().then(function (result) {
				$.merge(ctrl.studiensemesterList, result);
			}, function (error) {
				errorService.setError(getErrorMsg(error));
			});

			// loading Studienplan List
			StudienplanService.getStudienplanList($stateParams.stgkz).then(function (result) {
				ctrl.studienplanList = result;
			}, function (error) {
				errorService.setError(getErrorMsg(error));
			});

			//loading Nationengruppe List
			$http({
				method: 'GET',
				url: './api/helper/nationengruppe.php',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					$.merge(ctrl.nationengruppeList, response.data.info);
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
				$("#dataGridBewerbungstermin").datagrid({
					url: "./api/studiengang/bewerbungstermin/bewerbungstermin.php?stgkz=" + $stateParams.stgkz + "&studiensemester_kurzbz=" + ctrl.selectedStudiensemester,
					method: 'GET',
					multiSort: true,
					singleSelect:true,
					onLoadSuccess: function (data)
					{
						//Error Handling happens in loadFilter
					},
					onLoadError: function ()
					{
						//TODO Error Handling
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
						ctrl.loadBewerbungsterminDetails(row);
						if ($("#save").is(":visible"))
							ctrl.changeButtons();
					},
					columns: [[
						{field: 'bewerbungstermin_id', align: 'right', title:'ID'},
						{field: 'studiengang_kz', align:'right', sortable: true, title:'STG KZ'},
						{field: 'studiensemester_kurzbz', align:'right', sortable: true, title:'Studiensemester'},
						{field: 'beginn', align:'left', sortable: true, formatter: dateTimeStringToGermanDateString, title:'Beginn'},
						{field: 'ende', align:'left',  sortable: true, formatter: dateTimeStringToGermanDateString, title:'Ende'},
						{field: 'nachfrist', align:'left', title:'Nachfrist', sortable: true},
						{field: 'nachfrist_ende', align:'left', formatter: dateTimeStringToGermanDateString, title:'Ende Nachfrist', sortable: true},
						{field: 'anmerkung', align:'left', title:'Anmerkung'},
						{field: 'stpl_bezeichnung', align:'left', title:'Studienplan'},
						{field: 'nationengruppe_kurzbz', align:'left', title:'Nationengruppe'}
					]]
				});
				$("#dataGridBewerbungstermin").datagrid('sort', {
					sortName: 'studiensemester_kurzbz,beginn',
					sortOrder: 'desc,desc'
				});
			};

			ctrl.loadDataGrid();

			$("#datepicker_beginn").datepicker({
				dateFormat: "dd.mm.yy",
				firstDay: 1
			});

			$("#timepicker_beginn").timepicker({
				showPeriodLabels: false,
				rows: 4
			});
			$("#timepicker_ende").timepicker({
				showPeriodLabels: false,
				rows: 4
			});
			$("#timepicker_nachfrist_ende").timepicker({
				showPeriodLabels: false,
				rows: 4
			});

			$("#datepicker_ende").datepicker({
				dateFormat: "dd.mm.yy",
				firstDay: 1
			});

			$("#nachfrist_ende").datepicker({
				dateFormat: "dd.mm.yy",
				firstDay: 1
			});

			ctrl.newBewerbungstermin = function ()
			{
				$scope.form.$setPristine();
				$("#dataGridBewerbungstermin").datagrid("unselectAll");
				ctrl.bewerbungstermin = new Bewerbungstermin();
				ctrl.bewerbungstermin.studiengang_kz = $scope.stgkz;
				ctrl.bewerbungstermin.studiensemester_kurzbz = ctrl.selectedStudiensemester;
				ctrl.bewerbungstermin.nationengruppe_kurzbz = ctrl.selectedNationengruppe;
				if (!$("#save").is(":visible"))
					ctrl.changeButtons();
				$("#bewerbungsterminDetails").show();
			};

			ctrl.changeButtons = function ()
			{
				if ($("#save").is(":visible"))
				{
					$("#save").hide();
					$("#update").show();
					$("#delete").show();
					$("#savenew").show();
				}
				else
				{
					$("#save").show();
					$("#update").hide();
					$("#delete").hide();
					$("#savenew").hide();
				}
			};

			ctrl.save = function ()
			{
				if ($scope.form.$valid)
				{
					var saveData = {data: ""};
					saveData.data = angular.copy(ctrl.bewerbungstermin);
					if(ctrl.bewerbungstermin.beginn != null && ctrl.bewerbungstermin.beginn != '')
						saveData.data.beginn = GermanDateToISODate(ctrl.bewerbungstermin.beginn) + ' '+ctrl.bewerbungstermin.beginn_time;
					if(ctrl.bewerbungstermin.ende != null && ctrl.bewerbungstermin.ende != '')
						saveData.data.ende = GermanDateToISODate(ctrl.bewerbungstermin.ende) +' '+ctrl.bewerbungstermin.ende_time;
					if(ctrl.bewerbungstermin.nachfrist_ende != null && ctrl.bewerbungstermin.nachfrist_ende != '')
						saveData.data.nachfrist_ende = GermanDateToISODate(ctrl.bewerbungstermin.nachfrist_ende)+' '+ctrl.bewerbungstermin.nachfrist_ende_time;

					$http({
						method: 'POST',
						url: './api/studiengang/bewerbungstermin/save_bewerbungstermin.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if (response.data.erfolg)
						{
							$("#dataGridBewerbungstermin").datagrid('reload');
							ctrl.bewerbungstermin = new Bewerbungstermin();
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

			ctrl.loadBewerbungsterminDetails = function (row)
			{
				ctrl.bewerbungstermin = angular.copy(row);

				ctrl.bewerbungstermin.beginn_time = dateTimeStringToTimeString(ctrl.bewerbungstermin.beginn,':');
				ctrl.bewerbungstermin.beginn = dateTimeStringToGermanDate(ctrl.bewerbungstermin.beginn);
				ctrl.bewerbungstermin.ende_time = dateTimeStringToTimeString(ctrl.bewerbungstermin.ende);
				ctrl.bewerbungstermin.ende = dateTimeStringToGermanDate(ctrl.bewerbungstermin.ende);

				if(ctrl.bewerbungstermin.nachfrist_ende!='')
				{
					ctrl.bewerbungstermin.nachfrist_ende_time = dateTimeStringToTimeString(ctrl.bewerbungstermin.nachfrist_ende);
					ctrl.bewerbungstermin.nachfrist_ende = dateTimeStringToGermanDate(ctrl.bewerbungstermin.nachfrist_ende);
				}

				$scope.$apply();
				$("#bewerbungsterminDetails").show();
			};

			ctrl.update = function ()
			{
				if ($scope.form.$valid)
				{
					var updateData = {data: ""};
					updateData.data = angular.copy(ctrl.bewerbungstermin);
					if(ctrl.bewerbungstermin.beginn != null && ctrl.bewerbungstermin.beginn != '')
						updateData.data.beginn = GermanDateToISODate(ctrl.bewerbungstermin.beginn)+' '+ctrl.bewerbungstermin.beginn_time;
					else
						updateData.data.beginn = '';

					if(ctrl.bewerbungstermin.ende != null && ctrl.bewerbungstermin.ende != '')
						updateData.data.ende = GermanDateToISODate(ctrl.bewerbungstermin.ende)+' '+ctrl.bewerbungstermin.ende_time;
					else
						updateData.data.ende = '';

					if(ctrl.bewerbungstermin.nachfrist_ende != null && ctrl.bewerbungstermin.nachfrist_ende != '')
						updateData.data.nachfrist_ende = GermanDateToISODate(ctrl.bewerbungstermin.nachfrist_ende)+' '+ctrl.bewerbungstermin.nachfrist_ende_time;
					else
						updateData.data.nachfrist_ende = '';

					$http({
						method: 'POST',
						url: './api/studiengang/bewerbungstermin/update_bewerbungstermin.php',
						data: $.param(updateData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if (response.data.erfolg)
						{
							$("#dataGridBewerbungstermin").datagrid('reload');
							ctrl.bewerbungstermin = new Bewerbungstermin();
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

			ctrl.delete = function ()
			{
				if (confirm("Wollen Sie den Bewerbungstermin wirklich Löschen?"))
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
						if (response.data.erfolg)
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

			$scope.UpdateNachfristBool = function()
			{
				if (ctrl.bewerbungstermin.nachfrist)
				{
					var date = new Date(GermanDateToISODate(ctrl.bewerbungstermin.ende));
					date.setDate(date.getDate() + 30);
					ctrl.bewerbungstermin.nachfrist_ende = formatDateAsGermanString(date);
					ctrl.bewerbungstermin.nachfrist_ende_time = '23:55';

				}
				else
				{
					ctrl.bewerbungstermin.nachfrist_ende = "";
					ctrl.bewerbungstermin.nachfrist_ende_time = "";
				}
			};
		});

function Bewerbungstermin()
{
	this.bewerbungstermin_id = "";
	this.studiengang_kz = "";
	this.studiensemester_kurzbz = "";
	var datum = new Date();
	this.beginn = formatDateAsGermanString(datum);
	this.beginn_time = "00:00";
	datum.setDate(datum.getDate() + 30);
	this.ende = formatDateAsGermanString(datum)
	this.ende_time = "23:55";
	this.nachfrist = false;
	this.nachfrist_ende = "";
	this.nachfrist_ende_time = "";
	this.anmerkung = "";
	this.insertvon = "";
	this.insertamum = "";
	this.updateamum = "";
	this.updatevon = "";
	this.studienplan_id = "";
	this.nationengruppe_kurzbz = "";
}
