angular.module('stgv2').config(function($routeProvider){
	$routeProvider
	.when('/',{
		templateUrl: './templates/pages/studienordnung/index.html'
	})
	.when('/studienordnung', {
		templateUrl: './templates/pages/studienordnung/metadaten/index.html'
	})
	.when('/studienordnung/metadaten', {
		templateUrl: './templates/pages/studienordnung/metadaten/index.html',
		controller: 'MetadatenIndexController',
		controllerAs: 'indexController'
	})
	.when('/studienordnung/dokumente', {
		templateUrl: './templates/pages/studienordnung/dokumente/index.html'
	})
	.otherwise({redirectTo: '/'});
});