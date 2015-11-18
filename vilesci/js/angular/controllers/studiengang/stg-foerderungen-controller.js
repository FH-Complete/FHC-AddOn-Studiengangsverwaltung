angular.module('stgv2')
		.controller('StgFoerderungenCtrl', function ($scope, $http, $state, $stateParams, errorService) {
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.data = "";
			ctrl.foerdervertrag = new Foerdervertrag();
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
				$("#dataGridFoerdervertrag").datagrid({
					//TODO format Time in column
					url: "./api/studiengang/foerdervertrag/foerdervertrag.php?stgkz=" + $stateParams.stgkz,
					method: 'GET',
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
					onClickRow: function()
					{
						var row = $("#dataGridFoerdervertrag").datagrid("getSelected");
						ctrl.loadFoerdervertragDetails(row);
						if ($("#save").is(":visible"))
							ctrl.changeButtons();
					}
				});
			};
			
			ctrl.loadDataGrid();
			
			ctrl.loadFoerdervertragDetails = function(row)
			{
				ctrl.foerdervertrag = row;
				$scope.$apply();
				$("#foerdervertragDetails").show();
			}
			
			ctrl.save = function()
			{
				var saveData = {data: ""}
				saveData.data = ctrl.foerdervertrag;
				$http({
					method: 'POST',
					url: './api/studiengang/foerdervertrag/save_foerdervertrag.php',
					data: $.param(saveData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					//TODO success 
					$("#dataGridFoerdervertrag").datagrid('reload');
					//TODO select recently added Reihungstest in Datagrid
					ctrl.foerdervertrag = new Foerdervertrag();
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
			
			ctrl.update = function()
			{
				var updateData = {data: ""}
				updateData.data = ctrl.foerdervertrag;
				$http({
					method: 'POST',
					url: './api/studiengang/foerdervertrag/update_foerdervertrag.php',
					data: $.param(updateData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					//TODO success 
					$("#dataGridFoerdervertrag").datagrid('reload');
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
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
						//TODO success 
						$("#dataGridFoerdervertrag").datagrid('reload');
						ctrl.newFoerdervertrag();
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
}