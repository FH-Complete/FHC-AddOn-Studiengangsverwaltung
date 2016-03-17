angular.module('stgv2')
		.factory("StudiengangService", function ($http, $q, StoreService) {
			var storeId = "studiengangList";

			var getStudiengangList = function ()
			{
				var def = $q.defer();
				if (StoreService.get(storeId) !== null)
				{
					def.resolve(StoreService.get(storeId));
					return def.promise;
				}
				else
				{
					return $http({
						method: "GET",
						url: "./api/helper/studiengang.php"
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

			var setStudiengangList = function (list)
			{
				StoreService.set(storeId, list);
			};

			return {
				getStudiengangList: getStudiengangList,
				setStudiengangList: setStudiengangList
			};
		});