angular.module('stgv2')
		.factory("successService", function ($rootScope) {
			var message = "";
			
			var setMessage = function(msg){
				//needed to reset default
				message = msg;
				notify();
			};
			var getMessage = function(){
				return message;
			};
			var subscribe = function (scope, callback){
				var handler = $rootScope.$on("success-service-event", callback);
				scope.$on('$destroy', handler);
			};
			var notify = function () {
				$rootScope.$emit("success-service-event");
			};
			return {
				getMessage: getMessage,
				setMessage: setMessage,
				subscribe: subscribe,
				notify: notify
			};
		});