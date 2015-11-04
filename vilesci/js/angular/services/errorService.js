angular.module('stgv2')
		.factory("errorService", function ($rootScope) {
			var err = {};
			err.message = "";
			err.type = "warn";
			
			var setError = function(message, type){
				//needed to reset default
				err.type = "warn";
				
				err.message = message;
				if(type != undefined)
					err.type = type;
				notify();
			};
			var getError = function(){
				return err;
			};
			var subscribe = function (scope, callback){
				var handler = $rootScope.$on("error-service-event", callback);
				scope.$on('$destroy', handler);
			};
			var notify = function () {
				$rootScope.$emit("error-service-event");
			};
			return {
				getError: getError,
				setError: setError,
				subscribe: subscribe,
				notify: notify
			};
		});