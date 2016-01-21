angular.module('stgv2')
		.factory("StoreService", function ($http, $q, store) {
			
			var stgvStore = store.getNamespacedStore("stgv");
			
			var set = function(id, object)
			{
				stgvStore.set(id, object);
			};
			
			var get = function(id)
			{
				return stgvStore.get(id);
			};
			
			var remove = function(id)
			{
				stgvStore.remove(id);
			};
			
			return {
				get: get,
				set: set,
				remove: remove
			};
		});