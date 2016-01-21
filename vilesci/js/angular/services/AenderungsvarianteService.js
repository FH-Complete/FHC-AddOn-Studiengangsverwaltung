angular.module('stgv2')
		.factory("AenderungsvarianteService", function ($http, $q, StoreService) {
			var storeId = "aenderungsvarianteList";

			var getAenderungsvarianteList = function ()
			{
				var def = $q.defer();
				if (StoreService.get(storeId) !== null)
				{
					console.log("data from store");
					def.resolve(StoreService.get(storeId));
					return def.promise;
				}
				else
				{
					return 	$http({
						method: "GET",
						url: "./api/helper/aenderungsvariante.php"
					}).then(function success(response) {
						if (response.data.erfolg)
						{
							console.log("data from http")
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

			var setAenderungsvarianteList = function (list)
			{
				StoreService.set(storeId, list);
			};

			return {
				getAenderungsvarianteList: getAenderungsvarianteList,
				setAenderungsvarianteList: setAenderungsvarianteList
			};
		});