angular.module('stgv2')
		.controller('NewLehrveranstaltungCtrl', function ($scope, $http, errorService, StudiengangService, OrgformService, SpracheService, LehrveranstaltungService) {
			var ctrl = this;
			ctrl.data = new LehrveranstaltungService.getLVTemplate();
			ctrl.studiengangList =[];
			ctrl.orgformList = [{
					orgform_kurzbz: null,
					bezeichnung: ""
			}];
			ctrl.lehrtypList = "";
			ctrl.oeList = "";
			ctrl.lehrformList = "";
			ctrl.spracheList = "";
			ctrl.raumtypList = "";
			ctrl.semesterList = [0,1,2,3,4,5,6,7,8,9];
			ctrl.lvSuggestionList = "";

			$scope.$on("editLehrveranstaltung", function(event, data)
			{
				ctrl.data = data;
				if(ctrl.data.lehrtyp_kurzbz === "modul")
				{
					ctrl.loadOrganisationseinheitenList("Studiengang");
				}
				else
				{
					ctrl.loadOrganisationseinheitenList();
				}
				ctrl.loadLehrform();
			});

			//set fields dependent on Studiengang
			ctrl.setStudiengangDependencies = function()
			{
				//set oe_kurzbz which corresponds to studiengang if module
				if(ctrl.data.lehrtyp_kurzbz == "modul" && ctrl.data.oe_kurzbz == null && !ctrl.data.lehrveranstaltung_id) {
					angular.forEach(ctrl.studiengangList, function (item) {
						if (item.studiengang_kz == ctrl.data.studiengang_kz) {
							angular.forEach(ctrl.oeList, function(oe){
								if(item.oe_kurzbz == oe.oe_kurzbz) {
									ctrl.data.oe_kurzbz = item.oe_kurzbz;
								}
							});
						}
					});
				}
			};

			ctrl.setLehrformDependencies = function()
			{
				switch(ctrl.data.lehrform_kurzbz)
				{
					case 'iMod':
						ctrl.data.benotung = true;
						ctrl.data.zeugnis = false;
						ctrl.data.lehrauftrag = false;
						ctrl.data.lehre = true;
						break;
					case 'kMod':
						ctrl.data.benotung = false;
						ctrl.data.zeugnis = false;
						ctrl.data.lehrauftrag = false;
						ctrl.data.lehre = false;
						break;
					default:
						break;
				}
			};

			ctrl.setLehrtypDependencies = function()
			{
				if(ctrl.data.lehrtyp_kurzbz === "modul")
				{
                    if(!ctrl.data.lehrveranstaltung_id)
                    {
    					ctrl.data.zeugnis = false;
    					ctrl.data.lvinfo = false;
    					ctrl.data.benotung = false;
    					ctrl.data.lehrauftrag = false;
    					ctrl.data.lehre = false;
                    }
					ctrl.loadOrganisationseinheitenList("Studiengang");
				}
				else
				{
                    if(!ctrl.data.lehrveranstaltung_id)
                    {
    					ctrl.data.zeugnis = true;
    					ctrl.data.lvinfo = true;
    					ctrl.data.benotung = true;
    					ctrl.data.lehrauftrag = true;
    					ctrl.data.lehre = true;
                    }
					ctrl.loadOrganisationseinheitenList();
				}
			};

			//loading Studiengang list
			StudiengangService.getStudiengangList().then(function(result){
				ctrl.studiengangList = result;
			},function(error){
				errorService.setError(getErrorMsg(error));
			});

			//loading orgform list
			OrgformService.getOrgformList().then(function(result){
				ctrl.orgformList = ctrl.orgformList.concat(result);
			},function(error){
				errorService.setError(getErrorMsg(error));
			});

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
			ctrl.loadOrganisationseinheitenList = function(oetyp_kurzbz)
			{
				if(oetyp_kurzbz === undefined)
					url='./api/helper/organisationseinheit.php';
				else
					url='./api/helper/organisationseinheitByTyp.php?oetyp_kurzbz='+oetyp_kurzbz;

				$http({
					method: 'GET',
					url: url,
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.oeList = response.data.info;
						ctrl.setStudiengangDependencies();
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};

			//loading spracheList
			SpracheService.getSpracheList().then(function(result){
				ctrl.spracheList = result;
			},function(error){
				errorService.setError(getErrorMsg(error));
			});

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
			});

			ctrl.updateColor = function()
			{
				var val = $("#farbe").val();
				$("#farbevorschau").attr("style","background-color: #"+val+"; border: 1px solid #999999; cursor: default");
			};

			ctrl.updateLehreverzeichnis = function()
			{
				//var kurzbz = $('input[name="kurzbz"]').val();
				var kurzbz = (ctrl.data.kurzbz == null)?"":ctrl.data.kurzbz;
				kurzbz = kurzbz.replace(/\u00e4/g, "ae")
					.replace(/\u00f6/g, "oe")
					.replace(/\u00fc/g, "ue")
					.replace(/\u00df/g, "sz")
					.replace(/\u00c4/g, "ae")
					.replace(/\u00d6/g, "oe")
					.replace(/\u00dc/g, "ue")
					.replace(/[^a-z_\s]/gi, "");
				var orgform = (typeof ctrl.data.orgform_kurzbz === "undefined") ? "" : "_"+ctrl.data.orgform_kurzbz;
				var string = (kurzbz+orgform).toLowerCase();
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

			ctrl.onLehrtypChange = function(){
				//when new lv - reset kurzbz and lehrform kurzbz when lehrtyp changes -
				// otherwise value from other lehrtyp stays!
				if(!ctrl.data.lehrveranstaltung_id) {
					ctrl.data.oe_kurzbz = null;
					ctrl.data.lehrform_kurzbz = null;
				}
				ctrl.loadLehrform();
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

				ctrl.setLehrtypDependencies();
			};

			//set predefined OE
			ctrl.data.oe_kurzbz = $("#oe").val();
			ctrl.data.lehrtyp_kurzbz = $("#lehrtyp").val();
			ctrl.loadLehrform();

			ctrl.saveLehrveranstaltung = function()
			{
				ctrl.updateLehreverzeichnis();
				if($scope.form.$valid)
				{
					if (!ctrl.checkkurzbz())
						return;

					var saveData = {data: ""};
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
							args.lv_id = response.data.info[0];
							args.oe_kurzbz = ctrl.data.oe_kurzbz;
							args.lehrtyp_kurzbz = ctrl.data.lehrtyp_kurzbz;
							args.semester = ""+ctrl.data.semester;
							//$("#dialog").dialog("close");
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

			ctrl.updateLehrveranstaltung = function()
			{
				if($scope.form.$valid)
				{
					if (!ctrl.checkkurzbz() && !ctrl.data.gesperrt)
						return;

					var saveData = {data: ""};
					saveData.data = ctrl.data;
					$http({
						method: 'POST',
						url: './api/studienplan/lehrveranstaltungen/update_lehrveranstaltung.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response){
						if(response.data.erfolg)
						{
							var args = {};
							args.lv_id = response.data.info[0];
							args.oe_kurzbz = ctrl.data.oe_kurzbz;
							args.lehrtyp_kurzbz = ctrl.data.lehrtyp_kurzbz;
							args.semester = ctrl.data.semester;
							$scope.$emit("setFilter", args);
							$("#stplTreeGrid").treegrid("update",{id: ctrl.data.id, row: ctrl.data});
							//reload to sort again in case sort changed
							var parentNode = $('#stplTreeGrid').treegrid('getParent', ctrl.data.id);
							$("#stplTreeGrid").treegrid("reload", parentNode);
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

			ctrl.checkkurzbz = function()
			{
				var regex = '^[a-zA-Z0-9\-\u00c4\u00e4\u00d6\u00f6\u00dc\u00fc]*$';
				if (!ctrl.data.kurzbz.match(regex))
				{
					alert("kurzbz darf keine Leerzeichen und keine Sonderzeichen ausser \"-\" enthalten");
					var kurzbzinput = $("input[name=kurzbz]");
					kurzbzinput.parent().addClass('has-error');
					kurzbzinput.keyup(
						function()
						{
							if (ctrl.data.kurzbz.match(regex))
								$(this).parent().removeClass('has-error');
						}
					);
					return false;
				}
				return true;
			}
		});
