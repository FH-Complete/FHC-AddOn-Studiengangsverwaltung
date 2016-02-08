//TODO check if lehreverzeichnis exists

angular.module('stgv2')
		.controller('NewLehrveranstaltungCtrl', function ($scope, $http, errorService, StudiengangService, OrgformService, SpracheService) {
			var ctrl = this;
			ctrl.data = new Lehrveranstaltung();
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
			StudiengangService.getStudiengangList().then(function(result){
				ctrl.studiengangList = result;
			},function(error){
				errorService.setError(getErrorMsg(error));
			});
			
//			$http({
//				method: "GET",
//				url: "./api/helper/studiengang.php"
//			}).then(function success(response) {
//				if (response.data.erfolg)
//				{
//					ctrl.studiengangList = response.data.info;
//				}
//				else
//				{
//					errorService.setError(getErrorMsg(response));
//				}
//			}, function error(response) {
//				errorService.setError(getErrorMsg(response));
//			});
			
			//loading orgform list
			OrgformService.getOrgformList().then(function(result){
				ctrl.orgformList = result;
			},function(error){
				errorService.setError(getErrorMsg(error));
			});
			
//			$http({
//				method: "GET",
//				url: "./api/helper/orgform.php"
//			}).then(function success(response) {
//				if (response.data.erfolg)
//				{
//					ctrl.orgformList = response.data.info;
//				}
//				else
//				{
//					errorService.setError(getErrorMsg(response));
//				}
//			}, function error(response) {
//				errorService.setError(getErrorMsg(response));
//			});
			
			//load lehrtypen
			$http({
				method: 'GET',
				url: './api/helper/lehrtyp.php'
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
				url: './api/helper/organisationseinheitByTyp.php?oetyp_kurzbz=Institut'
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
			
			//loading spracheList
			SpracheService.getSpracheList().then(function(result){
				ctrl.spracheList = result;
			},function(error){
				errorService.setError(getErrorMsg(error));
			});
			
//			$http({
//				method: 'GET',
//				url: './api/helper/sprache.php'
//			}).then(function success(response) {
//				if (response.data.erfolg)
//				{
//					ctrl.spracheList = response.data.info;
//				}
//				else
//				{
//					errorService.setError(getErrorMsg(response));
//				}
//			}, function error(response) {
//				errorService.setError(getErrorMsg(response));
//			});
			
			//loading raumtypList
			$http({
				method: 'GET',
				url: './api/helper/raumtyp.php'
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
				ctrl.data.lehreverzeichnis = string;
			};
			
			ctrl.loadSuggestion = function()
			{
				$scope.form.$setPristine();
				$http({
					method: 'GET',
					url: './api/helper/lehrveranstaltungSearch.php?lv='+ctrl.data.bezeichnung
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.lvSuggestionList = [];
						if(ctrl.data.ects !== null)
						{
							$(response.data.info).each(function(i,v){
								var ects = parseInt(ctrl.data.ects);
								if(v.ects === ects.toFixed(2))
								{
									ctrl.lvSuggestionList.push(v);
								}
							});
						}
						else
						{
							ctrl.lvSuggestionList = response.data.info;
						}
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};
			
			ctrl.loadLehrform = function()
			{
				//loading lehrformList
				$http({
					method: 'GET',
					url: './api/helper/lehrformByLehrtyp.php?lehrtyp_kurzbz='+ctrl.data.lehrtyp_kurzbz
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
			};
			
			//set predefined OE
			ctrl.data.oe_kurzbz = $("#oe").val();
			ctrl.data.lehrtyp_kurzbz = $("#lehrtyp").val();
			ctrl.loadLehrform();
			
			
			ctrl.saveLehrveranstaltung = function()
			{
				if($scope.form.$valid)
				{
					var saveData = {data: ""}
					saveData.data = ctrl.data;
					$http({
						method: 'POST',
						url: './api/studienplan/lehrveranstaltungen/save_lehrveranstaltung.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response){
						if(response.data.erfolg)
						{
							var args = {};
							args.lv_id = response.data.info;
							args.oe_kurzbz = ctrl.data.oe_kurzbz;
							args.lehrtyp_kurzbz = ctrl.data.lehrtyp_kurzbz;
							args.semester = ctrl.data.semester;
							$scope.$emit("setFilter", args);
						}
						else
						{
							errorService.setError(getErrorMsg(response));
						}
					}, function error(response){
						errorService.setError(getErrorMsg(response));
					});
				}
				else
				{
					$scope.form.$setPristine();
				}
			};
		});
		
	function Lehrveranstaltung()
	{
		this.studiengang_kz = null;
		this.bezeichnung = null;
		this.kurzbz = null;
		this.lehrform_kurzbz = null;
		this.semester = 0;
		this.ects = null;
		this.semesterstunden = null;
		this.anmerkung = null;
		this.lehre = true;
		this.lehreverzeichnis = null;
		this.aktiv = true;
		this.insertvon = null;
		this.planfaktor = null;
		this.planlektoren = null;
		this.planpersonalkosten = null;
		this.plankostenprolektor = null;
		this.sort = null;
		this.zeugnis = false;
		this.projektarbeit = false;
		this.sprache = null;
		this.koordinator = null;
		this.bezeichnung_english = null;
		this.orgform_kurzbz = null;
		this.incoming = null;
		this.lehrtyp_kurzbz = null;
		this.oe_kurzbz = null;
		this.raumtyp_kurzbz = null;
		this.anzahlsemester = null;
		this.semesterwochen = null;
		this.lvnr = null;
		this.semester_alternativ = null;
		this.farbe = null;
		this.sws = null;
		this.lvs = null;
		this.alvs = null;
		this.lvps = null;
		this.las = null;
		this.benotung = false;
		this.lvinfo = false;
	}