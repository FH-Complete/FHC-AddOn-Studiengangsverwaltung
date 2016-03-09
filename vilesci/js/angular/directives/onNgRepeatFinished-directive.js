angular.module("stgv2")
		.directive('onNgRepeatFinished', function () {
			return function(scope)
			{
				console.log(scope);
				if(scope.$last)
				{
					scope.$emit("ngRepeatFinished");
				}
			}
		});