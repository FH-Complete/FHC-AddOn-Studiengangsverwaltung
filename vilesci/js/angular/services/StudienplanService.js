angular.module('stgv2')
		.factory("StudienplanService", function ($http, $q, StoreService) {
			var storeId = "studienplan";

			var getStudienplan = function (studienplan_id)
			{

				var def = $q.defer();
				var studienplan = StoreService.get(storeId);
				if ((studienplan !== null) && (studienplan.studienplan_id === studienplan_id))
				{
					def.resolve(studienplan);
					return def.promise;
				}
				else
				{
					return $http({
						method: "GET",
						url: "./api/studienplan/studienplan.php?studienplan_id=" + studienplan_id
					}).then(function success(response) {
						if (response.data.erfolg)
						{
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
				getStudienplan: getStudienplan
			};
		});