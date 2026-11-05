angular.module('stgv2')
		.controller('StgKarrierepfadeCtrl', function ($scope, $http, $stateParams, errorService, successService) {
			$scope.stgkz = $stateParams.stgkz;
			var ctrl = this;
			ctrl.karrierepfad = null;
			ctrl.selectedId = null;
			ctrl.keepForm = false;
			ctrl.typen = [];
			ctrl.studienplaene = [];

			// the datagrid renders its cells as HTML
			function escapeHtml(value)
			{
				return $("<div>").text(value === null || value === undefined ? "" : value).html();
			}

			$http({
				method: "GET",
				url: "./api/studiengang/karrierepfad/typen.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.typen = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});

			// the datagrid shows the Studienplan names, so it loads after the Studienpläne
			$http({
				method: "GET",
				url: "./api/studiengang/karrierepfad/studienplaene.php?stgkz=" + $stateParams.stgkz
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.studienplaene = response.data.info;
					ctrl.loadDataGrid();
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});

			// Studienpläne in the order of ctrl.studienplaene, newest Gültigkeit first
			ctrl.getStudienplaene = function (studienplan_ids)
			{
				var ids = studienplan_ids.map(String);
				return ctrl.studienplaene.filter(function (sp) {
					return ids.indexOf(String(sp.studienplan_id)) !== -1;
				});
			};

			ctrl.getStudienplaeneLabel = function ()
			{
				if (ctrl.karrierepfad === null)
					return '';
				var bezeichnungen = ctrl.getStudienplaene(ctrl.karrierepfad.studienplan_ids).map(function (sp) {
					return sp.bezeichnung;
				});
				return bezeichnungen.length > 0 ? bezeichnungen.join(', ') : 'Keine Studienpläne gewählt';
			};

			ctrl.loadDataGrid = function ()
			{
				$("#dataGridKarrierepfad").datagrid({
					url: "./api/studiengang/karrierepfad/karrierepfad.php?stgkz=" + $stateParams.stgkz,
					method: 'GET',
					singleSelect: true,
					onLoadSuccess: function (data)
					{
						// keep the selection after a save, and the unsaved form after a move
						for (var i = 0; i < data.rows.length; i++)
						{
							if (String(data.rows[i].karrierepfad_id) === String(ctrl.selectedId))
							{
								$("#dataGridKarrierepfad").datagrid('selectRow', i);
								if (!ctrl.keepForm)
									ctrl.showDetails(data.rows[i]);
							}
						}
						ctrl.keepForm = false;

						// bound on the arrows, so the click does not select the row
						$("#dataGridKarrierepfad").datagrid('getPanel').find('.karrierepfadMove').click(function (event) {
							event.stopPropagation();
							var index = parseInt($(this).closest('tr.datagrid-row').attr('datagrid-row-index'), 10);
							var direction = parseInt($(this).attr('data-direction'), 10);
							$scope.$evalAsync(function () {
								ctrl.move(index, direction);
							});
						});
					},
					loadFilter: function (data) {
						var result = {rows: [], total: 0};
						if (data.erfolg)
						{
							result.rows = data.info;
							result.total = data.info.length;
						}
						else
						{
							errorService.setError("<p>" + escapeHtml(data.message && data.message.message ? data.message.message : "Fehler beim Laden der Karrierepfade") + "</p>");
						}
						return result;
					},
					onClickRow: function (index, row)
					{
						ctrl.selectedId = row.karrierepfad_id;
						ctrl.showDetails(row);
					},
					columns: [[
						{field: 'move', align: 'center', title: 'Reihenfolge', formatter: function () {
							return '<span class="karrierepfadMove glyphicon glyphicon-arrow-up" data-direction="-1" title="nach oben" style="cursor: pointer"></span>' +
								'&nbsp;&nbsp;<span class="karrierepfadMove glyphicon glyphicon-arrow-down" data-direction="1" title="nach unten" style="cursor: pointer"></span>';
						}},
						{field: 'karrierepfad_kurzbz', align: 'left', title: 'Kurzbezeichnung', formatter: escapeHtml},
						{field: 'bezeichnung_german', align: 'left', title: 'Bezeichnung', formatter: function (value, row) {
							return escapeHtml(row.bezeichnung_mehrsprachig.German);
						}},
						{field: 'bezeichnung_english', align: 'left', title: 'Bezeichnung Englisch', formatter: function (value, row) {
							return escapeHtml(row.bezeichnung_mehrsprachig.English);
						}},
						{field: 'karrierepfad_typ_kurzbz', align: 'left', title: 'Typ', formatter: escapeHtml},
						{field: 'aktiv', align: 'left', title: 'Aktiv', formatter: function (value) {
							return value ? 'Ja' : 'Nein';
						}},
						{field: 'studienplan_ids', align: 'left', title: 'Studienpläne', formatter: function (value) {
							return ctrl.getStudienplaene(value).map(function (sp) {
								return escapeHtml(sp.bezeichnung);
							}).join(', ');
						}},
						{field: 'anzahl_prestudenten', align: 'right', title: 'Bewerbungen'}
					]]
				});
			};

			ctrl.showDetails = function (row)
			{
				$scope.$evalAsync(function () {
					ctrl.karrierepfad = angular.copy(row);
					ctrl.karrierepfad.studienplan_ids = row.studienplan_ids.map(String);
					ctrl.form.$setPristine();
				});
			};

			ctrl.newKarrierepfad = function ()
			{
				$("#dataGridKarrierepfad").datagrid("unselectAll");
				ctrl.selectedId = null;
				ctrl.karrierepfad = {
					karrierepfad_id: "",
					karrierepfad_kurzbz: "",
					karrierepfad_typ_kurzbz: ctrl.typen.length > 0 ? ctrl.typen[0].karrierepfad_typ_kurzbz : "",
					studiengang_kz: $scope.stgkz,
					bezeichnung_mehrsprachig: {German: "", English: ""},
					aktiv: true,
					studienplan_ids: [],
					anzahl_prestudenten: 0
				};
				ctrl.form.$setPristine();
			};

			ctrl.hasStudienplan = function (studienplan_id)
			{
				// the hidden form also evaluates before the first selection
				return ctrl.karrierepfad !== null && ctrl.karrierepfad.studienplan_ids.indexOf(String(studienplan_id)) !== -1;
			};

			ctrl.toggleStudienplan = function (studienplan_id)
			{
				var index = ctrl.karrierepfad.studienplan_ids.indexOf(String(studienplan_id));
				if (index === -1)
					ctrl.karrierepfad.studienplan_ids.push(String(studienplan_id));
				else
					ctrl.karrierepfad.studienplan_ids.splice(index, 1);
			};

			ctrl.move = function (index, direction)
			{
				var rows = $("#dataGridKarrierepfad").datagrid('getRows');
				var target = index + direction;
				if (target < 0 || target >= rows.length)
					return;

				var karrierepfad_ids = rows.map(function (row) {
					return row.karrierepfad_id;
				});
				karrierepfad_ids.splice(target, 0, karrierepfad_ids.splice(index, 1)[0]);

				$http({
					method: 'POST',
					url: './api/studiengang/karrierepfad/save_sort.php',
					data: $.param({data: {studiengang_kz: $scope.stgkz, karrierepfad_ids: karrierepfad_ids}}),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.keepForm = true;
						$("#dataGridKarrierepfad").datagrid('reload');
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};

			ctrl.save = function ()
			{
				if (!ctrl.form.$valid)
					return;

				var saveData = {
					data: {
						karrierepfad_id: ctrl.karrierepfad.karrierepfad_id,
						karrierepfad_kurzbz: ctrl.karrierepfad.karrierepfad_kurzbz,
						karrierepfad_typ_kurzbz: ctrl.karrierepfad.karrierepfad_typ_kurzbz,
						studiengang_kz: ctrl.karrierepfad.studiengang_kz,
						bezeichnung_mehrsprachig: {
							German: ctrl.karrierepfad.bezeichnung_mehrsprachig.German,
							English: ctrl.karrierepfad.bezeichnung_mehrsprachig.English
						},
						aktiv: ctrl.karrierepfad.aktiv,
						studienplan_ids: ctrl.karrierepfad.studienplan_ids
					}
				};
				$http({
					method: 'POST',
					url: './api/studiengang/karrierepfad/save_karrierepfad.php',
					data: $.param(saveData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.selectedId = response.data.info;
						$("#dataGridKarrierepfad").datagrid('reload');
						successService.setMessage("Karrierepfad gespeichert.");
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};

			ctrl.delete = function ()
			{
				if (!confirm("Wollen Sie den Karrierepfad wirklich löschen?"))
					return;

				$http({
					method: 'POST',
					url: './api/studiengang/karrierepfad/delete_karrierepfad.php',
					data: $.param({data: {karrierepfad_id: ctrl.karrierepfad.karrierepfad_id}}),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.karrierepfad = null;
						ctrl.selectedId = null;
						$("#dataGridKarrierepfad").datagrid('reload');
						successService.setMessage(response.data.info);
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
		});
