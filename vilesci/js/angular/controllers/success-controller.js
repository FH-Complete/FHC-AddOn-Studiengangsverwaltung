angular.module("stgv2")
		.controller("SuccessCtrl", function ($scope, $state, successService) {
			
			//subscribes the controller to the service
			successService.subscribe($scope, function () {
				var ctrl = this;
				ctrl.message = successService.getMessage();
				$("#success_data").html(ctrl.message);
				$("#success_data").show();
			});
		});