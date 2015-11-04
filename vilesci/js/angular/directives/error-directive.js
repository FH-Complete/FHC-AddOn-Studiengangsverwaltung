angular.module("stgv2")
		.directive("error", function () {
			return{
				restrict: "E",
				replace: true,
				templateUrl: "./templates/pages/error.html"
			};
		});