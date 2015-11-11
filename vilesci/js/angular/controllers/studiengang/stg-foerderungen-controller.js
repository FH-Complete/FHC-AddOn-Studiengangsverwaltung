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
					url: "./api/studiengang/foerdervertrag.php?stgkz=" + $stateParams.stgkz,
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
					onClickRow: function (row)
					{
						var row = $("#dataGridFoerdervertrag").datagrid("getSelected");
						ctrl.loadFoerdervertragDetails(row.foerdervertrag_id);
						if ($("#save").is(":visible"))
							ctrl.changeButtons();
					}
				});
			};
			
			ctrl.loadDataGrid();
			
			ctrl.save = function()
			{
				var saveData = ctrl.foerdervertrag;
				$http({
					method: 'POST',
					url: './api/studiengang/save_foerdervertrag.php',
					headers: {
						'Content-Type': 'application/json'
					},
					data: JSON.stringify(saveData)
				}).then(function success(response) {
					//TODO success 
					$("#dataGridFoerdervertrag").datagrid('reload');
					//TODO select recently added Reihungstest in Datagrid
					ctrl.foerdervertrag = new Foerdervertrag();
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
			
			ctrl.newFoerdervertrag = function()
			{
				ctrl.foerdervertrag = new Foerdervertrag();
				ctrl.foerdervertrag.studiengang_kz = $scope.stgkz;
				if(!$("#save").is(":visible"))
					ctrl.changeButtons();
				$("#foerdervertragDetails").show();
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