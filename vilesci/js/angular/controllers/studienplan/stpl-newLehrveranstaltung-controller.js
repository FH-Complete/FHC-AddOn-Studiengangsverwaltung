var stgv2 = angular.module("stgv2", [], function($httpProvider){
	 $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
});

angular.module('stgv2')
		.controller('NewLehrveranstaltungCtrl', function ($scope, $http, errorService) {
			var ctrl = this;
			ctrl.data = "";
			ctrl.studiengangList = "";
			ctrl.orgformList = "";
			ctrl.lehrtypList = "";
			ctrl.oeList = "";
			ctrl.lehrformList = "";
			ctrl.spracheList = "";
			ctrl.raumtypList = "";
			ctrl.semesterList = [0,1,2,3,4,5,6,7,8,9];
			ctrl.lvSuggestionList = "";
			
			//loading Studiengang list
			$http({
				method: "GET",
				url: "../../../../api/helper/studiengang.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.studiengangList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
			
			//loading orgform list
			$http({
				method: "GET",
				url: "../../../../api/helper/orgform.php"
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.orgformList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
			
			//load lehrtypen
			$http({
				method: 'GET',
				url: '../../../../api/helper/lehrtyp.php'
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.lehrtypList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
			
			//load organisationseinheiten
			$http({
				method: 'GET',
				url: '../../../../api/helper/organisationseinheitByTyp.php?oetyp_kurzbz=Institut'
			}).then(function success(response) {
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
			
			//loading lehrformList
			$http({
				method: 'GET',
				url: '../../../../api/helper/lehrform.php'
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.lehrformList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
			
			//loading spracheList
			$http({
				method: 'GET',
				url: '../../../../api/helper/sprache.php'
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.spracheList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
			
			//loading raumtypList
			$http({
				method: 'GET',
				url: '../../../../api/helper/raumtyp.php'
			}).then(function success(response) {
				if (response.data.erfolg)
				{
					ctrl.raumtypList = response.data.info;
				}
				else
				{
					errorService.setError(getErrorMsg(response));
				}
			}, function error(response) {
				errorService.setError(getErrorMsg(response));
			});
			
			//enable tooltips
			$(document).ready(function(){
				$('[data-toggle="tooltip"]').tooltip();
			});
			
			$("#farbe").ColorPicker(
			{
				onSubmit: function(hsb, hex, rgb, el) 
				{
					$(el).val(hex);
					$(el).ColorPickerHide();
					$("#farbevorschau").attr("style","background-color: #"+hex+"; border: 1px solid #999999; cursor: default");
				},
				onBeforeShow: function () 
				{
					$(this).ColorPickerSetColor(this.value);
				}
			})
			.bind("keyup", function()
			{
				$(this).ColorPickerSetColor(this.value);
			});
			
			ctrl.updateColor = function()
			{
				var val = $("#farbe").val();
				$("#farbevorschau").attr("style","background-color: #"+val+"; border: 1px solid #999999; cursor: default");
			}
			
			ctrl.updateLehreverzeichnis = function()
			{
				var kurzbz = $('input[name="kurzbz"]').val();
				kurzbz = kurzbz.replace(/\ä/g, "ae")
					.replace(/\ö/g, "oe")
					.replace(/\ü/g, "ue")
					.replace(/\ß/g, "sz")
					.replace(/\Ä/g, "ae")
					.replace(/\Ö/g, "oe")
					.replace(/\Ü/g, "ue")
					.replace(/[^a-z_\s]/gi, "");
				var orgform = ($('select[name="orgform"]').val() === "? undefined:undefined ?") ? "" : "_"+$('select[name="orgform"]').val();
				var string = (kurzbz+orgform).toLowerCase();;
				$("input[name=\'lehreverzeichnis\']").val(string);
			}
			
			ctrl.loadSuggestion = function()
			{
				var saveData = {data: ""};
				saveData.data = ctrl.data;
				$http({
					method: 'GET',
					url: '../../../../api/helper/lehrveranstaltungSearch.php?lv='+ctrl.data.bezeichnung,
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.lvSuggestionList = response.data.info;
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			}
		});