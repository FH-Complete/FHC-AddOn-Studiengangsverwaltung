angular.module('stgv2')
		.controller('StgReihungstestCtrl', function ($scope, $http, $state, $stateParams,errorService) {
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.data = "";
			ctrl.reihungstest = "";
			
			$("#dataGridReihungstest").datagrid({
				url: "./api/studiengang/reihungstest.php?stgkz=" + $stateParams.stgkz,
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
					var row = $("#dataGridReihungstest").datagrid("getSelected");
					ctrl.loadReihungstestDetails(row.reihungstest_id);
				}
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
			ctrl.save = function()
			{
				var saveData = ctrl.reihungstest;
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
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
		});