angular.module('stgv2')
	.controller('StplLehrveranstaltungCtrl', function($scope, $http, $state, $stateParams, errorService){
		$scope.stplid = $stateParams.stplid;
		$('#layoutWrapper').layout('collapse','west');
		$('#centerLayout').layout('collapse','north');
		var ctrl = this;
		ctrl.data = "";
		ctrl.oeList = "";
		ctrl.oe_kurzbz = "";
		
		//TODO load OES
		$http({
			method: 'GET',
			url: './api/helper/organisationseinheit.php'
		}).then(function success(response) {
			console.log(response);
			if (response.data.erfolg)
			{
				ctrl.oeList = response.data.info;
			}
			else
			{
				errorService.setError(getErrorMsg(response));
			}
		}, function error(response) {
			errorService.setError(getErrorMsg(response));
		});
		//TODO load Lehrtypen
		
		//TODO load Semester
		
		$("#lvTreeGrid").treegrid({
			url: "./api/helper/lehrveranstaltung.php",
			method: 'GET',
			idField: 'id',
			treeField: 'text',
			loadFilter: function (data)
			{
				if (data.erfolg)
				{
					console.log(data.info);
					return data.info;
				}
				else
				{
					//TODO Fehler ausgeben data.message
				}

			},
			onClick: function (node)
			{
				return true;
			},
			onClickRow: function (row)
			{

			},
		});
	});