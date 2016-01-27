angular.module("stgv2")
		.directive('integerOnly', function () {
			return {
				require: 'ngModel',
				link: function (scope, element, attrs, modelCtrl) {
					modelCtrl.$parsers.push(function (inputValue) {
						var transformedInput;
						if(attrs.integerOnly=="positive")
							transformedInput = inputValue ? inputValue.replace(/[^\d]/g, '') : null;
						else
							transformedInput = inputValue ? inputValue.replace(/[^\d-]/g, '') : null;
						
						if (transformedInput != inputValue) {
							modelCtrl.$setViewValue(transformedInput);
							modelCtrl.$render();
						}

						return transformedInput;
					});
				}
			};
		});