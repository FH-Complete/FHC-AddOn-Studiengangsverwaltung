angular.module("stgv2")
		.directive('charactersOnly', function () {
			return {
				require: 'ngModel',
				link: function (scope, element, attrs, modelCtrl) {
					modelCtrl.$parsers.push(function (inputValue) {
						var transformedInput;
						if(attrs.charactersOnly=="upperCase")
							transformedInput = inputValue ? inputValue.replace(/[^A-Z]/g, '') : null;
						else if(attrs.charactersOnly=="lowerCase")
							transformedInput = inputValue ? inputValue.replace(/[^\a-z]/g, '') : null;
						else
							transformedInput = inputValue ? inputValue.replace(/[^\a-zA-Z]/g, '') : null;

						if (transformedInput != inputValue) {
							modelCtrl.$setViewValue(transformedInput);
							modelCtrl.$render();
						}

						return transformedInput;
					});
				}
			};
		});