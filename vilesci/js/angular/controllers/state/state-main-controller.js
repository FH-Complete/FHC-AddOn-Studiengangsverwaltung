angular.module('stgv2')
		.controller('StateMainCtrl', function ($rootScope, $stateParams, errorService) {
			var ctrl = this;
			ctrl.url = './api/studienordnung/studienordnungTree.php?stgkz=' + $stateParams.stgkz + '&state=' + $stateParams.state;

			//Studienordnungsdaten in TreeGrid laden
			ctrl.loadTreegrid = function()
			{
				// Ueberschreiben der autoSizeColumn Funktion da es sonst zu Performanceproblemen kommt
				var origTreegrid_autoSizeColumn = $.fn.datagrid.methods['autoSizeColumn'];
				$.extend($.fn.treegrid.methods, {
				    autoSizeColumn: function(jq, field) {
				        $.each(jq, function() {
				            var opts = $(this).treegrid('options');
				            if (!opts.skipAutoSizeColumns) {
				                var tg_jq = $(this);
				                if (field) origTreegrid_autoSizeColumn(tg_jq, field);
				                else origTreegrid_autoSizeColumn(tg_jq);
				            }
				        });
				    }
				});
				$("#treeGrid").treegrid({
					method: 'GET',
					url: ctrl.url,
					idField: 'id',
					treeField: 'bezeichnung',
					fit: true,
					rownumbers: false,
					skipAutoSizeColumns: true,
					autoRowHeight: false,
					multiSort: true,
					columns: [[
						{field: 'bezeichnung', width: 250, title:'Version',sortable:true},
						{field: 'status_bezeichnung', align: 'left', title:'Status',sortable:true},
						{field: 'aenderungsvariante_bezeichnung', align: 'left', title:'Änderungsvariante',sortable:true},
						{field: 'studiengangbezeichnung', align: 'left', title:'Studiengangbezeichnung',sortable:true},
						{field: 'studiengang_kz', align:'left', title:'StgKz',sortable:true},
						{field: 'gueltigvon', align:'left', title:'gültig von',sortable:true},
						{field: 'gueltigbis', align:'left', title:'gültig bis',sortable:true},
						{field: 'orgform_kurzbz', align:'left', title:'Orgform',sortable:true},
						{field: 'ects_stpl', align:'left', title:'ECTS',
							formatter: function(val)
							{
								if(val != undefined)
									return val.split(".")[0];
								else
									return val;
							}
						},
						{field: 'regelstudiendauer', align:'left', title:'RStD'},
						{field: 'sprache', align:'left', title:'Sprache'},
						{field: 'aktiv', align:'left', title:'aktiv'},
						{field: 'onlinebewerbung_studienplan', align:'left', title:'onlinebewerbung'}
					]],
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
							$("#centerNorth").find(".datagrid-view2").find(".datagrid-body").html("<span>No records found.</span>");
						}
						else
						{
							//workaround to change icons
							$("#centerNorth").find('tr[node-id] td[field]:nth-child(1)').each(function(i,v)
							{
								var ele = $("#treeGrid").treegrid('find', $(v).parent().attr("node-id"));
								if(ele.attributes[0].value === "studienordnung")
								{
									var node = $(v).find("span.tree-icon");
									//change file icon to empty folder
									if($(node).hasClass("tree-file"))
									{
										$(node).removeClass("tree-file");
										$(node).addClass("tree-folder");
									}
								}
							});
						}
					},
					onLoadError: function (arguments) {
						console.log(arguments);
					}
				});
			};

			ctrl.loadTreegrid();

			$rootScope.$on("loadTreeGrid", function(event, args){
				ctrl.url = './api/studienordnung/studienordnungTree.php?stgkz=' + args.stgkz + '&state=' + args.state;
				ctrl.loadTreegrid();
			});
		});