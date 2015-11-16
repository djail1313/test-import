'use strict';

testAppController.config(['$routeProvider', function($routeProvider){
	$routeProvider
		.when('/', {
			templateUrl: 'partials/index.html',
        	controller: 'index'
		})
		.when('/transaction', {
			templateUrl: 'partials/index.html',
        	controller: 'index'
		})
	    .otherwise({
			templateUrl: 'partials/404.html'
	    });
}])
.controller('index', ['$scope', '$http','transactionServices', 
	function($scope, $http, transactionServices){
		loadData(['page=1']);
		var sortField = [];
		var strSortField = '';
		$scope.sort = function(id){
			var filter = getFilter();
			if(sortField[id] == undefined){
				sortField[id] = 0;
			} else {
				if(sortField[id] == 2){
					sortField[id] = 0;
				} else {
					sortField[id] = sortField[id] + 1;
				}
			}
			$('.glyphicon').attr('class', 'glyphicon');
			switch(sortField[id]){
				case 0:
					strSortField = 'sort=' + id;
					$('#' + id + '_').attr('class', 'glyphicon glyphicon-chevron-down');
					break;
				case 1:
					strSortField = 'sort=-' + id;
					$('#' + id + '_').attr('class', 'glyphicon glyphicon-chevron-up');
					break;
				case 2:
					strSortField = 'sort=';
					break;
			}
			filter[3] = strSortField;
			loadData(filter);
		};
		$scope.changeFilter = function(){
			loadData(getFilter());
		};
		$scope.changePage = function(id) {
			var id_ = $scope.searchID==undefined?'':$scope.searchID;
			var customer_name = $scope.searchCustomer==undefined?'':$scope.searchCustomer;
			var date_purchase = new Date($scope.searchDatePurchase);
			if(date_purchase == 'Invalid Date'){
				date_purchase = '';
			} else {
				var date = date_purchase.getMonth() + 1;
				date_purchase = date_purchase.getFullYear()+"-"+date+"-"+date_purchase.getDate();
			}
			var filter = getFilter();
			filter[3] = 'page=' + $('#'+id + ' a').attr('page');
			filter[4] = strSortField;
			loadData(filter);
			return false;
		};
		function getFilter(){
			var id_ = $scope.searchID==undefined?'':$scope.searchID;
			var customer_name = $scope.searchCustomer==undefined?'':$scope.searchCustomer;
			var date_purchase = new Date($scope.searchDatePurchase);
			if(date_purchase == 'Invalid Date'){
				date_purchase = '';
			} else {
				var date = date_purchase.getMonth() + 1;
				date_purchase = date_purchase.getFullYear()+"-"+date+"-"+date_purchase.getDate();
			}
			var filter = [
				'id=' + id_,
				'customer_name=' + customer_name,
				'date_purchase=' + date_purchase
			];
			return filter;
		}
		function loadData(filter){
			transactionServices.getTransactions(filter,
				function(data, status, headers, config){
					var currentPage = headers('x-pagination-current-page');
					var pageCount = headers('x-pagination-page-count');
					if(currentPage == 1){
						$('#first-page').attr('class', 'previous disabled');
						$('#prev-page').attr('class', 'disabled');
					} else {
						$('#first-page').attr('class', 'previous');
						$('#prev-page').attr('class', '');
					}
					if(currentPage == pageCount){
						$('#last-page').attr('class', 'next disabled');
						$('#next-page').attr('class', 'disabled');
					} else {
						$('#last-page').attr('class', 'next');
						$('#next-page').attr('class', '');
					}
					$('#last-page a').attr('page',pageCount);
					currentPage--;
					$('#prev-page a').attr('page',currentPage);
					currentPage+=2;
					$('#next-page a').attr('page',currentPage);
				}
			)
			.then(function(data){
				$scope.transactions = data.data;
			});
		}
	}
]);