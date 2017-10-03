angular.module('stgv2').controller('StoTaetigkeitsfelderCtrl', function ($scope, $http, $state, $stateParams, errorService, successService, $compile) {
	$scope.studienordnung_id = $stateParams.studienordnung_id;
	var ctrl = this;
	var scope = $scope;
	ctrl.data = new Taetigkeitsfeld();
	ctrl.data.studienordnung_id = $scope.studienordnung_id;
	ctrl.temp = {
		branchen: "",
		positionen: "",
		aufgaben: ""
	};
	ctrl.inputids = ["branchen", "positionen", "aufgaben"];

	function initDnD()
	{
		$(".sortable").sortable({
			connectWith: ".sortable",
			helper: "clone",
			revert: false,
			over: function (event, ui)
			{
				$(this).addClass("hovered-list");
			},
			out: function (event, ui)
			{
				$(this).removeClass("hovered-list");
			}
		}).disableSelection();
	}

	//enable tooltips and editor
	$(document).ready(function ()
	{

		//hide edit button by default
		$(".editButton").hide();
		$(".editButtonList").hide();

		initDnD();

		$('[data-toggle="tooltip"]').tooltip();

		$("#ueberblick-editor").wysiwyg({
			toolbarSelector: '[data-role=ueberblick-editor-toolbar]'
		});
		$("#ueberblick-editor").on('paste', function (event)
		{
			setTimeout(function ()
			{
				$("#ueberblick-editor").html($("#ueberblick-editor").html());
			}, 100);
		});

		ctrl.deleteSelection = function ()
		{
			window.getSelection().deleteFromDocument();
		};

		$http({
			method: 'GET',
			url: './api/studienordnung/taetigkeitsfelder/taetigkeitsfelder.php?studienordnung_id=' + $scope.studienordnung_id
		}).then(function success(response)
		{
			if (response.data.erfolg)
			{
				if (response.data.info.length > 0)
				{
					ctrl.data = response.data.info[0];
					$("#ueberblick-editor").html(ctrl.data.ueberblick);
					$(ctrl.data.data.branchen.elements).each(function (key, value)
					{
						ctrl.drawList("branchen_lists", value.title, 'branchen');
						$(value.elements).each(function (k, v)
						{
							ctrl.drawListItem('branchen_lists', v, 'branchen');
						});
					});

					$(ctrl.data.data.positionen.elements).each(function (key, value)
					{
						ctrl.drawList("positionen_lists", value.title, 'positionen');
						$(value.elements).each(function (k, v)
						{
							ctrl.drawListItem('positionen_lists', v, 'positionen');
						});
					});

					$(ctrl.data.data.aufgaben.elements).each(function (key, value)
					{
						ctrl.drawList("aufgaben_lists", value.title, 'aufgaben');
						$(value.elements).each(function (k, v)
						{
							ctrl.drawListItem('aufgaben_lists', v, 'aufgaben');
						});
					});

					initDnD();
				}
			}
			else
			{
				errorService.setError(getErrorMsg(response));
			}
		}, function error(response)
		{
			errorService.setError(getErrorMsg(response));
		});
	});

	ctrl.save = function ()
	{
		var saveData = {data: ""};
		ctrl.parseJson();
		saveData.data = angular.copy(ctrl.data);
		saveData.data.data = JSON.stringify(saveData.data.data);
		$http({
			method: 'POST',
			url: './api/studienordnung/taetigkeitsfelder/save_taetigkeitsfelder.php',
			data: $.param(saveData),
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded'
			}
		}).then(function success(response)
		{
			if (response.data.erfolg)
			{
				successService.setMessage("Daten erfolgreich gespeichert.");
				ctrl.data.taetigkeitsfeld_id = response.data.info[0];
			}
			else
			{
				errorService.setError(getErrorMsg(response));
			}
		}, function error(response)
		{
			errorService.setError(getErrorMsg(response));
		});
	};

	ctrl.drawList = function (div_id, text, input_id)
	{
		if (text != "")
		{
			$("#" + div_id).append('<ul class="list-group dropzone"><li class="list-group-item"><span class="list_title">' + text + '</span><span class="badge" ng-click="ctrl.removeList($event);"><span class="glyphicon glyphicon-trash"></span></span><span class="badge" ng-click="ctrl.editList($event, \'' + input_id + '\')"><span class="glyphicon glyphicon-edit"></span></span><ul class="list-group sortable sortable-list"></ul></li></ul>');
			var tocompile = $("#" + div_id + "> ul:last-child");
			$compile(tocompile)(scope);
		}
	};

	ctrl.drawListItem = function (div_id, text, input_id)
	{
		if (text != "")
		{
			var list = $("#" + div_id).find("ul li ul").last();
			if (list.length === 0)
			{
				list = $("#" + div_id).append('<ul class="list-group dropzone"><li class="list-group-item"><span class="list_title">&nbsp;</span><span class="badge" ng-click="ctrl.removeList($event);"><span class="glyphicon glyphicon-trash"></span></span><span class="badge" ng-click="ctrl.editList($event, \'' + input_id + '\')"><span class="glyphicon glyphicon-edit"></span></span><ul class="list-group sortable sortable-list"></ul></li></ul>');
				list = $(list).find("ul li ul").last();
				var tocompile = $("#" + div_id + "> ul:last-child");
				$compile(tocompile)(scope);
			}
			var html = $(list).append('<li class="list-group-item draggable">' + text + '<span class="badge" ng-click="ctrl.removeListItem($event)"><span class="glyphicon glyphicon-trash"></span></span><span class="badge" ng-click="ctrl.editListItem($event, \'' + input_id + '\')"><span class="glyphicon glyphicon-edit"></span></span></li>');
			var tocompile = $(list).children("li").last();
			$compile(tocompile)(scope);
		}
	};

	ctrl.addList = function (div_id, input_id)
	{
		var listTitle = $("#" + input_id).val();
		if (listTitle != "")
		{
			ctrl.drawList(div_id, listTitle, input_id);
			$("#" + input_id).val("");
			initDnD();
			ctrl.save();
		}
	};

	ctrl.addListItem = function (div_id, input_id)
	{
		var value = $("#" + input_id).val();
		if (value != "")
		{
			ctrl.drawListItem(div_id, value, input_id);
			$("#" + input_id).val("");
			initDnD();
			ctrl.save();
		}
	};

	ctrl.editList = function (event, input_id)
	{
		//hide all other inputfields in edit mode
		ctrl.inputids.forEach(
			function (entry)
			{
				ctrl.toggleEditVisibility($("#" + entry), false, "editButtonList");
				ctrl.toggleEditVisibility($("#" + entry), false, "editButton");
			}
		);
		//show edit button
		ctrl.toggleEditVisibility($("#" + input_id), true, "editButtonList");
		var editedList = $(event.currentTarget).parent();
		var text = editedList.find('.list_title').text();
		$("#" + input_id).val(text);
		ctrl.editedList = editedList;
	};

	ctrl.saveEditedList = function (input_id)
	{
		ctrl.editedList.find('.list_title').text($("#" + input_id).val());
		ctrl.toggleEditVisibility($("#" + input_id), false, "editButtonList");
		ctrl.save();
	};

	ctrl.editListItem = function (event, input_id)
	{
		//hide all other inputfields in edit mode
		ctrl.inputids.forEach(
			function (entry)
			{
				ctrl.toggleEditVisibility($("#" + entry), false, "editButton");
				ctrl.toggleEditVisibility($("#" + entry), false, "editButtonList");
			}
		);
		//show edit button
		ctrl.toggleEditVisibility($("#" + input_id), true, "editButton");
		var editedItem = $(event.currentTarget).parent();
		$("#" + input_id).val(editedItem.text());
		ctrl.editedItem = editedItem;
	};

	ctrl.saveEditedListItem = function (input_id)
	{
		ctrl.editedItem.contents().each(
			function ()
			{
				if (this.nodeType === 3)
				{//3 = textnode
					this.nodeValue = $("#" + input_id).val();
					return false;
				}
			}
		);
		ctrl.toggleEditVisibility($("#" + input_id), false, "editButton");
		ctrl.save();
	};

	ctrl.toggleEditVisibility = function (input_element, editVisibility, editButtonClass)
	{
		if (editVisibility)
		{
			input_element.parent().find('.addButton').hide();//hide buttons for adding
			input_element.parent().find("." + editButtonClass).show();
		} else
		{
			input_element.parent().find('.addButton').show();//show buttons for adding
			input_element.parent().find("." + editButtonClass).hide();
			input_element.val("");
		}
	};

	ctrl.removeListItem = function (event)
	{
		$(event.target).parent().parent().remove();
		ctrl.save();
	};

	ctrl.removeList = function (event)
	{
		if (confirm("Wollen Sie die Liste wirklich löschen?"))
		{
			$(event.target).parent().parent().remove();
			ctrl.save();
		}
	};

	ctrl.parseJson = function ()
	{
		var branchen = [];
		var positionen = [];
		var aufgaben = [];

		ctrl.data.ueberblick = $("#ueberblick-editor").html();

		$("#branchen_lists>ul>li>span[class=list_title]").each(function (key, value)
		{
			var list = {
				title: "",
				elements: []
			};
			list.title = $(value).text();
			$(value).nextAll("ul").first().children().each(function (k, v)
			{
				list.elements.push($(v).text());
			});
			branchen.push(list);
		});

		$("#positionen_lists>ul>li>span[class=list_title]").each(function (key, value)
		{
			var list = {
				title: "",
				elements: []
			};
			list.title = $(value).text();
			$(value).nextAll("ul").first().children().each(function (k, v)
			{
				list.elements.push($(v).text());
			});
			positionen.push(list);
		});

		$("#aufgaben_lists>ul>li>span[class=list_title]").each(function (key, value)
		{
			var list = {
				title: "",
				elements: []
			};
			list.title = $(value).text();
			$(value).nextAll("ul").first().children().each(function (k, v)
			{
				list.elements.push($(v).text());
			});
			aufgaben.push(list);
		});

		ctrl.data.data.branchen.elements = branchen;

		ctrl.data.data.positionen.elements = positionen;

		ctrl.data.data.aufgaben.elements = aufgaben;
	};
});

function Taetigkeitsfeld()
{
	this.taetigkeitsfeld_id = null;
	this.studienordnung_id = null;
	this.ueberblick = "";
	this.data = {
		"branchen": {
			"fixed": "AbsolventInnen des Studienganges können in folgenden Kernbranchen tätig sein:",
			"elements": []
		},
		"positionen": {
			"fixed": "Aufgrund der Qualifikationsziele des Studienganges können die AbsolventInnen beispielhaft die folgenden Positionen und Funktionen durchführen:",//wahrnehmen
			"elements": []
		},
		"aufgaben": {
			"fixed": "Aufgrund der Qualifikationsziele des Studienganges können die AbsolventInnen beispielhaft die folgenden Positionen und Funktionen durchführen:",//wahrnehmen
			"elements": []
		}
	}
}