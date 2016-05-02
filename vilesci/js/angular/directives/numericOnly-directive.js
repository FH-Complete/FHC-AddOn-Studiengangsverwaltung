angular.module("stgv2")
		.directive('numericOnly', function () {
			return {
				require: 'ngModel',
				link: function (scope, element, attrs, modelCtrl) {
					modelCtrl.$parsers.push(function (inputValue) {
						var transformedInput;
						if(attrs.numericOnly=="positive")
						{
							transformedInput = inputValue ? inputValue.replace(',', '.') : null;
							transformedInput = transformedInput ? transformedInput.replace(/[^\d.]/g, '') : null;
						}
						else
						{
							transformedInput = inputValue ? inputValue.replace(',', '.') : null;
							transformedInput = transformedInput ? transformedInput.replace(/[^,\d.-]/g, '') : null;
						}

						if (transformedInput != inputValue) {
							modelCtrl.$setViewValue(transformedInput);
							modelCtrl.$render();
						}

						return transformedInput;
					});
				}
			};
		});