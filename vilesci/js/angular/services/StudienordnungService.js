angular.module('stgv2')
		.factory("StudienordnungService", function ($http, $q, StoreService) {
			var storeId = "studienordnung";
			var getStudienordnung = function (studienordnung_id)
			{
				var def = $q.defer();
				var studienordnung = StoreService.get(storeId);
				if ((studienordnung !== null) && (studienordnung.studienordnung_id === studienordnung_id))
				{
					def.resolve(studienordnung);
					return def.promise;
				}
				else
				{

					return $http({
						method: "GET",
						url: "./api/studienordnung/studienordnung.php?studienordnung_id=" + studienordnung_id
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
					});
				}
			};
			
			var getStudienordnungByStudienplan = function (studienplan_id)
			{
				var def = $q.defer();
				var studienordnung = StoreService.get(storeId);
				if ((studienordnung !== null) && (studienordnung.studienplan_id === studienplan_id))
				{
					def.resolve(studienordnung);
					return def.promise;
				}
				else
				{

					return $http({
						method: "GET",
						url: "./api/studienordnung/studienordnung.php?studienplan_id=" + studienplan_id
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
					});
				}
			};

			return {
				getStudienordnung: getStudienordnung,
				getStudienordnungByStudienplan: getStudienordnungByStudienplan
			};
		});