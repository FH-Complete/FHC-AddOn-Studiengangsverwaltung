angular.module('stgv2').config(function ($stateProvider) {
	$stateProvider
//			.state('home', {
//				name: "home",
//				url: "/:studienordnung_id"
//			})
			.state('studiengang', {
				name: 'studiengang',
				url: '/studiengang/:stgkz',
				templateUrl: './templates/pages/studiengang/stammdaten/stammdaten.html',
				controller: function($state)
				{
					console.log($state);
				}
			})
			.state('betriebsdaten', {
				name: 'betriebsdaten',
				url: 'studiengang/:stgkz/betriebsdaten',
				templateUrl: './templates/pages/studiengang/betriebsdaten/betriebsdaten.html',
				controller: function ($scope, $state, $stateParams) {
					console.log($state);
					$state.go('betriebsdaten.bewerbung');
					
//					if($state.current.name === "studienordnung")
				}
			})
			.state('betriebsdaten.studiengangsgruppen', {
				name: 'betriebsdaten.studiengangsgruppen',
				url: '/studiengangsgruppen',
				templateUrl: './templates/pages/studiengang/stammdaten/stammdaten.html'
			})
			.state('betriebsdaten.bewerbung', {
				name: 'betriebsdaten.bewerbung',
				url: '/bewerbung',
				templateUrl: './templates/pages/studiengang/betriebsdaten/bewerbung/bewerbung.html'
			})
			.state('betriebsdaten.reihungstest', {
				name: 'betriebsdaten.reihungstest',
				url: '/reihungstest',
				templateUrl: './templates/pages/studiengang/betriebsdaten/reihungstest/reihungstest.html'
			})
			.state('betriebsdaten.kosten', {
				name: 'betriebsdaten.kosten',
				url: '/kosten',
				templateUrl: './templates/pages/studiengang/betriebsdaten/kosten/kosten.html'
			})
			.state('betriebsdaten.foerderungen', {
				name: 'betriebsdaten.foerderungen',
				url: '/foerderungen',
				templateUrl: './templates/pages/studiengang/betriebsdaten/foerderungen/foerderungen.html'
			})
			.state('betriebsdaten.doktorat', {
				name: 'betriebsdaten.doktorat',
				url: '/doktorat',
				templateUrl: './templates/pages/studiengang/betriebsdaten/doktorat/doktorat.html'
			})
			.state('studienordnung', {
				name: 'studienordnung',
				url: '/studienordnung/:studienordnung_id',
				templateUrl: './templates/pages/studienordnung/studienordnung.html',
				controller: function ($scope, $state, $stateParams) {
					if($state.current.name === "studienordnung")
						$state.go('studienordnung.metadaten');
					
				}
			})
			.state('studienordnungNeu', {
				name: 'studienordnungNeu',
				url: '/studienordnungNeu',
				templateUrl: './templates/pages/studienordnung/newStudienordnung.html'
			})
			.state('studienordnungDiff', {
				name: 'studienordnungDiff',
				url: '/studienordnungDiff/:studienordnung_id/:stgkz',
				templateUrl: './templates/pages/studienordnung/diffStudienordnung.html'
			})
			.state('studienordnung.metadaten', {
				name: 'studienordnung.metadaten',
				url: '/metadaten',
				templateUrl: './templates/pages/studienordnung/metadaten/stoMetadaten.html'
			})
			.state('studienordnung.dokumente', {
				name: 'studienordnung.dokumente',
				url: '/dokumente',
				templateUrl: './templates/pages/studienordnung/dokumente/stoDokumente.html'
			})
			.state('studienordnung.eckdaten', {
				name: 'studienordnung.eckdaten',
				url: '/eckdaten',
				templateUrl: './templates/pages/studienordnung/eckdaten/stoEckdaten.html'
			})
			.state('studienordnung.taetigkeitsfelder', {
				name: 'studienordnung.taetigkeitsfelder',
				url: '/taetigkeitsfelder',
				templateUrl: './templates/pages/studienordnung/taetigkeitsfelder/stoTaetigkeitsfelder.html'
			})
			.state('studienordnung.qualifikationsziele', {
				name: 'studienordnung.qualifikationsziele',
				url: '/qualifikationsziele',
				templateUrl: './templates/pages/studienordnung/qualifikationsziele/stoQualifikationsziele.html'
			})
			.state('studienordnung.zgv', {
				name: 'studienordnung.zgv',
				url: '/zgv',
				templateUrl: './templates/pages/studienordnung/zgv/stoZgv.html'
			})
			.state('studienordnung.aufnahmeverfahren', {
				name: 'studienordnung.aufnahmeverfahren',
				url: '/aufnahmeverfahren',
				templateUrl: './templates/pages/studienordnung/aufnahmeverfahren/stoAufnahmeverfahren.html'
			})
			.state('studienplan', {
				name: 'studienplan',
				url: '/studienplan/:studienplan_id',
				templateUrl: './templates/pages/studienplan/studienplan.html',
				controller: function ($scope, $state, $stateParams, $rootScope) {
					if($state.current.name === "studienplan")
						$state.go('studienplan.metadaten');
				}
			})
			.state('studienplanNeu', {
				name: 'studienplanNeu',
				url: '/studienplanNeu/:studienordnung_id',
				templateUrl: './templates/pages/studienplan/newStudienplan.html'
			})
			.state('studienplan.metadaten', {
				name: 'studienplan.metadaten',
				url: '/metadaten',
				templateUrl: './templates/pages/studienplan/metadaten/stplMetadaten.html'
			})
			.state('studienplan.eckdaten', {
				name: 'studienplan.eckdaten',
				url: '/eckdaten',
				templateUrl: './templates/pages/studienplan/eckdaten/stplEckdaten.html'
			})
			.state('studienplan.gueltigkeit', {
				name: 'studienplan.gueltigkeit',
				url: '/gueltigkeit',
				templateUrl: './templates/pages/studienplan/gueltigkeit/stplGueltigkeit.html'
			})
			.state('studienplan.module', {
				name: 'studienplan.module',
				url: '/module',
				templateUrl: './templates/pages/studienplan/module/stplModule.html'
			})
			.state('studienplan.lehrveranstaltungen', {
				name: 'studienplan.lehrveranstaltungen',
				url: '/lehrveranstaltungen',
				templateUrl: './templates/pages/studienplan/lehrveranstaltungen/stplLehrveranstaltungen.html'
			})
			.state('studienplan.auslandssemester', {
				name: 'studienplan.auslandssemester',
				url: '/auslandssemester',
				templateUrl: './templates/pages/studienplan/auslandssemester/stplAuslandssemester.html'
			})
			.state('studienplan.berufspraktikum', {
				name: 'studienplan.berufspraktikum',
				url: '/berufspraktikum',
				templateUrl: './templates/pages/studienplan/berufspraktikum/stplBerufspraktikum.html'
			})
			.state('studienplan.studienjahr', {
				name: 'studienplan.studienjahr',
				url: '/studienjahr',
				templateUrl: './templates/pages/studienplan/studienjahr/stplStudienjahr.html'
			})
			.state('studienplan.studienprogramm', {
				name: 'studienplan.studienprogramm',
				url: '/studienprogramm',
				templateUrl: './templates/pages/studienplan/studienprogramm/stplStudienprogramm.html'
			})
			.state('state', {
				url: '/state/:stgkz/:state',
				name: 'state',
				controller: "StateMainCtrl"
			}).state('stateAll', {
				url: '/state/:stgkz/all',
				name: 'stateAll',
				controller: "StateMainCtrl"
			});
});