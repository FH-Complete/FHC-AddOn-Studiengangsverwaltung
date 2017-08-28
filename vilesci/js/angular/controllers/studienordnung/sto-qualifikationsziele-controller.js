angular.module('stgv2')
		.controller('StoQualifikationszieleCtrl', function ($scope, $http, $state, $stateParams, errorService, successService, $compile) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			var scope = $scope;
			var ctrl = this;
			ctrl.data = new Qualifikationsziel();
			ctrl.data.studienordnung_id = $scope.studienordnung_id;
			ctrl.temp = [];
			ctrl.inputids = ["input_1", "input_2"];
			
			//enable tooltips after ngRepeat finished
			$scope.$on("ngRepeatFinished",function(){
				$('[data-toggle="tooltip"]').tooltip();
			});

			//hide edit buttons by default
			$(".editButton").hide();

			$http({
				method: 'GET',
				url: './api/studienordnung/qualifikationsziele/qualifikationsziele.php?studienordnung_id=' + $scope.studienordnung_id
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					if (response.data.info.length > 0)
					{
						ctrl.data = response.data.info[0];
						$(ctrl.data.data[1].elements[1]).each(function(key, value)
						{
							ctrl.drawListItem('zielList_1', value, 'input_1');
						});
						$(ctrl.data.data[1].elements[2]).each(function(key, value)
						{
							ctrl.drawListItem('zielList_2', value, 'input_2');
						});
					}
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});

			ctrl.save = function () {
				var saveData = {data: ""};
				ctrl.parseJson();
				saveData.data = angular.copy(ctrl.data);
				saveData.data.data = JSON.stringify(saveData.data.data);
				$http({
					method: 'POST',
					url: './api/studienordnung/qualifikationsziele/save_qualifikationsziele.php',
					data: $.param(saveData),
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						successService.setMessage("Daten erfolgreich gespeichert.");
						ctrl.data.qualifikationsziel_id = response.data.info[0];
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};

			ctrl.drawListItem = function (div_id, text, input_id)
			{
				if(text!="")
				{
					var list = $("#"+div_id);
					var html = $(list).append('<li class="list-group-item">'+text+'<span class="badge" ng-click="ctrl.removeListItem($event)"><span class="glyphicon glyphicon-trash"></span></span><span class="badge" ng-click="ctrl.editListItem($event, \''+input_id+'\')"><span class="glyphicon glyphicon-edit"></span></span></li>');
					$compile(html)(scope);
				}
			};

			ctrl.addListItem = function (list_id, input_id)
			{
				var value = $("#"+input_id).val();
				if(value!="")
				{
					ctrl.drawListItem(list_id, value, input_id);
					$("#"+input_id).val("");
					ctrl.save();
				}
			};

			ctrl.editListItem = function (event, input_id)
			{
				ctrl.inputids.forEach(
					function(entry){
						ctrl.toggleEditVisibility($("#"+entry), false);
					}
				);
				ctrl.toggleEditVisibility($("#"+input_id), true);
				var editedItem = $(event.currentTarget).parent();
				$("#"+input_id).val(editedItem.text());
				ctrl.editedItem = editedItem;
			};

			ctrl.saveEditedListItem = function (input_id) {
				ctrl.editedItem.contents().each(
					function () {
						if (this.nodeType === 3) {//3 = textnode
							this.nodeValue = $("#" + input_id).val();
							return false;
						}
					}
				);
				ctrl.toggleEditVisibility($("#"+input_id), false);
				ctrl.save();
			};

			ctrl.toggleEditVisibility = function (input_element, visible){
				if(visible) {
					input_element.parent().find('span').hide();//hide buttons for adding
					input_element.parent().find('.editButton').show();
				}else{
					input_element.parent().find('span').show();//show buttons for adding
					input_element.parent().find('.editButton').hide();
					input_element.val("");
				}
			};

			ctrl.removeListItem = function (event)
			{
				$(event.target).parent().parent().remove();
				ctrl.save();
			};

			ctrl.parseJson = function ()
			{
				var elements = []
				$("#zielList_1 li").each(function (key, value)
				{
					elements.push($(value).text());
				});
				ctrl.data.data[1].elements[1] = elements;
				elements = [];
				$("#zielList_2 li").each(function (key, value)
				{
					elements.push($(value).text());
				});
				ctrl.data.data[1].elements[2] = elements;
			};
		});

function Qualifikationsziel()
{
	this.qualifikationsziel_id = null;
	this.studienordnung_id = null;
	this.data = [
		{
			"header": "Bildungsauftrag FH-Studiengänge",
			"fixed": 
			[
				"FH-Studiengänge haben die Aufgabe, eine wissenschaftlich fundierte Berufsausbildung anzubieten. Ihr Bildungsauftrag besteht in der Gewährleistung einer praxisbezogenen Ausbildung auf Hochschulniveau. Es geht um die Vermittlung der Fähigkeit, die Aufgaben des jeweiligen Berufsfeldes dem Stand der Wissenschaft und den aktuellen und zukünftigen Anforderungen der Praxis zu lösen. FH-Studiengänge sind curricular und didaktisch so zu gestalten, dass sich die Studierenden jene berufspraktisch relevanten Kenntnisse, Fertigkeiten und Kompetenzen auf wissenschaftlicher Grundlage aneignen können, die sie für eine erfolgreiche berufliche Tätigkeit benötigen. Im Rah-men eines integrativen Ansatzes, der wissenschaftliche Ansprüche und berufspraktische Anforde-rungen berücksichtigt, geht es um die Vermittlung zwischen Wissen und Anwendung, Theorie und Praxis, Reflexion und Handlung, Abstraktion und Problem, Bildung und Beruf."
			],
		},
		{
			"header": "Qualifikationsziele",
			"fixed": 
			[
				"Aus der Beschreibung der beruflichen Tätigkeitsfelder und der Modulbeschreibungen wurden die Qualifikationsziele (Lernergebnisse des Studienganges) extrahiert. Sie werden in fachliche sowie personale und sozial-kommunikative Kompetenzen gegliedert.",
				"Nach erfolgreichem Abschluss des Studiums sind die AbsolventInnen im Bereich der zentralen fachlichen Kompetenzen in der Lage, …",
				"Nach erfolgreichem Abschluss des Studiums sind die AbsolventInnen im Bereich der personalen und sozial-kommunikativen Kompetenzen in der Lage, … "
			],
			"elements": [[],[],[]]
		}
	]
}