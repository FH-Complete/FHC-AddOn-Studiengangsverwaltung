angular.module("stgv2")
		.controller("ErrorCtrl", function ($scope, $state, errorService) {
			//opens the dialog
			$scope.open = function () {
				$("#modalDiv").show();
				$("#error").show();
				$scope.setBoxPos();
			};

			//hides the dialog
			$scope.close = function () {
				$("#error").hide();
				$("#modalDiv").hide();
			};

			//resizes the dialog
			$scope.setBoxPos = function () {
				var bx = $('#alrt_box');
				var ww = $(window).width();
				var bw = $('#alrt_box').width();

				//Fix for first loading of error div
				if(ww === bw)
				{
					bw = 200;
				}
				var x = ww / 2 - bw / 2;
				bx.css("left", x + "px");
				bx.css("top", "200px");
			};
			//subscribes the controller to the service
			errorService.subscribe($scope, function () {
				$scope.error = errorService.getError();
				$scope.open();
			});
		});