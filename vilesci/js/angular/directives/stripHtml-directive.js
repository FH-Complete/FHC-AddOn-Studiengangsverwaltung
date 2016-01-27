angular.module("stgv2")
		.directive('stripHtml', function () {
			return {
				require: 'ngModel',
				link: function (scope, element, attrs, modelCtrl) {
					modelCtrl.$parsers.push(function(inputValue){
						var strippedInput = inputValue ? String(inputValue).replace(/<[^>]+>/gm, '') : '';
						if (strippedInput != inputValue) {
							modelCtrl.$setViewValue(strippedInput);
							modelCtrl.$render();
						}
						return strippedInput;
					});
				}
			};
		});