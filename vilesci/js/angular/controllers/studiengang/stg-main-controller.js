angular.module('stgv2')
		.controller('StgMainCtrl', function ($scope, $http, $state, $stateParams, errorService) {
			$("#treeGrid").treegrid({
				url: "./api/studienordnung/studienordnung.php?stgkz=" + $stateParams.stgkz + "&state=all",
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
					if (row.attributes[0].name !== undefined && row.attributes[0].value !== undefined)
					{
						angular.element($("#treeGrid")).scope().load(row);
					}
				}
			});
			$state.go('stammdaten', {"stgkz": $stateParams.stgkz});
		});