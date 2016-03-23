angular.module('stgv2')
		.controller('StoTaetigkeitsfelderCtrl', function ($scope, $http, $state, $stateParams, errorService, successService, $compile) {
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
			
			function initDnD()
			{
				$(".sortable").sortable({
					connectWith: ".sortable",
					helper: "clone",
					revert: false,
					over: function(event, ui)
					{
						$(this).addClass("hovered-list");
					},
					out: function(event, ui)
					{
						$(this).removeClass("hovered-list");
					}
				}).disableSelection();
			}
			
			//enable tooltips and editor
			$(document).ready(function(){
				
				initDnD();
				
				$('[data-toggle="tooltip"]').tooltip();
				
				$("#ueberblick-editor").wysiwyg({
					toolbarSelector: '[data-role=ueberblick-editor-toolbar]'
				});
				$("#ueberblick-editor").on('paste', function(event){
					setTimeout(function(){
							$("#ueberblick-editor").html($("#ueberblick-editor").html());
					},100);
				});
                                
				ctrl.deleteSelection = function()
				{
					window.getSelection().deleteFromDocument();
				};
			
				$http({
					method: 'GET',
					url: './api/studienordnung/taetigkeitsfelder/taetigkeitsfelder.php?studienordnung_id=' + $scope.studienordnung_id
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						if (response.data.info.length > 0)
						{
							ctrl.data = response.data.info[0];
							$("#ueberblick-editor").html(ctrl.data.ueberblick);
							$(ctrl.data.data.branchen.elements).each(function(key, value)
							{
								ctrl.drawList("branchen_lists",value.title);
								$(value.elements).each(function(k, v){
									ctrl.drawListItem('branchen_lists', v);
								});
							});
							
							$(ctrl.data.data.positionen.elements).each(function(key, value)
							{
								ctrl.drawList("positionen_lists",value.title);
								$(value.elements).each(function(k, v){
									ctrl.drawListItem('positionen_lists', v);
								});
							});
							
							$(ctrl.data.data.aufgaben.elements).each(function(key, value)
							{
								ctrl.drawList("aufgaben_lists",value.title);
								$(value.elements).each(function(k, v){
									ctrl.drawListItem('aufgaben_lists', v);
								});
							});
							
							initDnD();
						}
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			});

			ctrl.save = function () {
				var saveData = {data: ""}
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
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						successService.setMessage("Daten erfolgreich gespeichert.");
						ctrl.data.taetigkeitsfeld_id = response.data.info[0];
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};

			ctrl.drawList = function (div_id, text)
			{
				if(text!="")
				{
					var html = $("#"+div_id).append('<ul class="list-group dropzone"><li class="list-group-item"><span class="list_title">'+text+'</span><span class="badge" ng-click="ctrl.removeList($event)"><span class="glyphicon glyphicon-trash"></span></span><ul class="list-group sortable sortable-list"></ul></li></ul>');
					$compile(html)(scope);
				}
			};
			
			ctrl.drawListItem = function (div_id, text)
			{
				if(text!="")
				{
					var list = $("#"+div_id).find("ul li ul").last() ;
					if(list.length === 0)
					{
						list = $("#"+div_id).append('<ul class="list-group dropzone"><li class="list-group-item"><span class="list_title">&nbsp;</span><span class="badge" ng-click="ctrl.removeList($event)"><span class="glyphicon glyphicon-trash"></span></span><ul class="list-group sortable sortable-list"></ul></li></ul>');
						list = $(list).find("ul li ul").last();
					}
					var html = $(list).append('<li class="list-group-item draggable">'+text+'<span class="badge" ng-click="ctrl.removeListItem($event)"><span class="glyphicon glyphicon-trash"></span></span></li>');
					$compile(html)(scope);
				}
			};
			

			ctrl.addList = function(div_id, input_id)
			{
				var listTitle = $("#"+input_id).val();
				if(listTitle!="")
				{
					ctrl.drawList(div_id, listTitle);
					$("#"+input_id).val("");
					initDnD();
					ctrl.save();
				}
			};

			ctrl.addListItem = function (div_id, input_id)
			{
				var value = $("#"+input_id).val();
				if(value!="")
				{
					ctrl.drawListItem(div_id, value);
					$("#"+input_id).val("");
					initDnD();
					ctrl.save();
				}
			};

			ctrl.removeListItem = function (event)
			{
				$(event.target).parent().parent().remove();
				ctrl.save();
			};
			
			ctrl.removeList = function(event)
			{
				if(confirm("Wollen Sie die Liste wirklich löschen?"))
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
				
				$("#branchen_lists>ul>li>span[class=list_title]").each(function(key, value){
					var list = {
						title: "",
						elements: []
					};
					list.title = $(value).text();
					$(value).next().next().children().each(function(k, v){
						list.elements.push($(v).text());
					});
					branchen.push(list);
				});
				
				$("#positionen_lists>ul>li>span[class=list_title]").each(function(key, value){
					var list = {
						title: "",
						elements: []
					};
					list.title = $(value).text();
					$(value).next().next().children().each(function(k, v){
						list.elements.push($(v).text());
					});
					positionen.push(list);
				});
				
				$("#aufgaben_lists>ul>li>span[class=list_title]").each(function(key, value){
					var list = {
						title: "",
						elements: []
					};
					list.title = $(value).text();
					$(value).next().next().children().each(function(k, v){
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
				"fixed": "Aufgrund der Qualifikationsziele des Studienganges können die AbsolventInnen beispielhaft die folgenden Positionen und Funktionen wahrnehmen:",
				"elements": []
			},
			"aufgaben": {
				"fixed": "Aufgrund der Qualifikationsziele des Studienganges können die AbsolventInnen beispielhaft die folgenden Positionen und Funktionen wahrnehmen:",
				"elements": []
			}
		}
}