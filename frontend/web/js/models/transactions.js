'use strict';

testAppTransactions.factory("transactionServices", ['$http','$location','$route',
	function($http,$location,$route){
		var obj = {};
		var objName = 'transactions';
		obj.getTransactions = function(filter, callback){
			var q = filter.join('&');
			return $http.get(serviceBaseUrl + objName + '?' + q).
						success(function(data, status, headers, config){
							callback(data, status, headers, config);
						});
		};
		obj.getTransaction = function(id){
			return $http.get(serviceBaseUrl + objName + '/' + id);
		};
		obj.createTransaction = function(transaction){
			return $http.post( serviceBaseUrl + objName, transaction )
	            .then( successHandler )
	            .catch( errorHandler );
	        function successHandler( result ) {
	            alert("Success data");
	            $location.path('/');            
	        }
	        function errorHandler( result ){
	            alert("Failed data");
	            $location.path('/');
	        }
		};
		return obj;
	}
]);