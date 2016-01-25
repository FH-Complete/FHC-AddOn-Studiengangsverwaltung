angular.module('stgv2')
		.controller('StateMainCtrl', function ($stateParams, errorService) {
			var ctrl = this;
			ctrl.url = './api/studienordnung/studienordnungTree.php?stgkz=' + $stateParams.stgkz + '&state=' + $stateParams.state;			
			//Studienordnungsdaten in TreeGrid laden
			$("#treeGrid").treegrid({
				method: 'GET',
				url: ctrl.url,
				idField: 'id',
				treeField: 'text',
				loadFilter: function (data)
				{
					if (data.erfolg)
					{
						$(data.info).each(function(i,v){
							/* removing state for elements without children;
							 * otherwise treegrid will crash when trying 
							 * to expand that node
							 */ 
							if(v.children.length === 0)
							{
								delete v.state;
							}
						})
						return data.info;
					}
					else
					{
						var response = {};
						response.data = data;
						errorService.setError(getErrorMsg(response));
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
				},
				onLoadSuccess: function (row, data) {
					if (data.length == 0)
					{
						//Wenn 0 Datensätze gefunden werden
						$("#centerNorth").find(".datagrid-view1").find(".datagrid-body-inner").html("<span>No records found.</span>");
					}
				},
				onLoadError: function (arguments) {
					//TODO error handling
					console.log(arguments);
				}
			});
		});