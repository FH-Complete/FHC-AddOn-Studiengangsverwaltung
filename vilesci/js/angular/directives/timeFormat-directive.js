stgv2.directive('ngModel', function ($filter) {
	return {
		require: '?ngModel',
		link: function (scope, elem, attr, ngModel) {
			if (!ngModel)
				return;
			if (attr.type !== 'time')
				return;

			ngModel.$formatters.unshift(function (value) {
				return value.replace(/:00\.000$/, '')
			});
		}
	}
});