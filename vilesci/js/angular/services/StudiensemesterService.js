angular.module('stgv2')
		.factory("StudiensemesterService", function ($http, $q, StoreService, $filter) {
			var storeId = "studiensemesterList";
			var greaterThan = function (prop, val)
			{
				return function (item)
				{
					return (item[prop].substring(2)) >= val;
				};
			};

			var getStudiensemesterAfter = function (studiensemester_kurzbz)
			{
				var def = $q.defer();
				return getStudiensemesterList().then(function (result) {
					var filtered = $filter('filter')(result,('studiensemester_kurzbz', studiensemester_kurzbz));
					var filteredResult = [];
					for(var i = (result.indexOf(filtered[0])); i<result.length; i++)
					{
						filteredResult.push(result[i]);
					}
					def.resolve(filteredResult);
					return def.promise;
				}, function (error) {
					def.reject(error);
					return def.promise;
				});
			};

			var getStudiensemesterList = function ()
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
						url: "./api/helper/studiensemester.php"
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

			var setStudiensemesterList = function (list)
			{
				StoreService.set(storeId, list);
			};

			return {
				getStudiensemesterList: getStudiensemesterList,
				setStudiensemesterList: setStudiensemesterList,
				getStudiensemesterAfter: getStudiensemesterAfter
			};
		});