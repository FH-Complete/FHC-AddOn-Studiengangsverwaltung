angular.module('stgv2')
		.factory("StandortService", function ($http, $q, StoreService) {
			var storeId = "standortList";

			var getStandortList = function ()
			{
				var def = $q.defer();
				if (StoreService.get(storeId) !== null)
				{
					def.resolve(StoreService.get(storeId));
					return def.promise;
				}
				else
				{
					return 	$http({
						method: "GET",
						url: "./api/helper/standort.php"
					}).then(function success(response) {
						if (response.data.erfolg)
						{
							StoreService.set(storeId, response.data.info);
							def.resolve(response.data.info);
							return def.promise;
						}
						else
						{
							def.reject(response);
							return def.promise;
						}
					}, function error(response) {
						def.reject(response);
						return def.promise;
					});
				}
			};

			var setStandortList = function (list)
			{
				StoreService.set(storeId, list);
			};

			return {
				getStandortList: getStandortList,
				setStandortList: setStandortList
			};
		});