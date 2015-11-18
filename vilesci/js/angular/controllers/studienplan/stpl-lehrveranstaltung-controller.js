angular.module('stgv2')
	.controller('StplLehrveranstaltungCtrl', function($scope, $http, $state, $stateParams, errorService){
		$scope.stplid = $stateParams.stplid;
		$('#layoutWrapper').layout('collapse','west');
		$('#centerLayout').layout('collapse','north');
		var ctrl = this;
		ctrl.data = "";
		
		
		$("#lvTreeGrid").treegrid({
			url: "./api/studienordnung/studienordnung.php?stgkz=257&state=all",
			method: 'GET',
			idField: 'id',
			treeField: 'text',
			loadFilter: function (data)
			{
				if (data.erfolg)
				{
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