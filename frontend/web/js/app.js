'use strict';

var serviceBaseUrl = 'http://test-backend.dev/'

var testApp = angular.module('testApp', [
    'ngRoute',
    'testApp.controller',
    'testApp.transactions'
]);

var testAppController = angular.module('testApp.controller', ['ngRoute']);
var testAppTransactions = angular.module('testApp.transactions', ['ngRoute']);

 testApp.directive('fileReader', function (transactionServices) {
    return {
        scope: true,
        link: function (scope, element, attr) {
            $("#submit-import").bind('click', function () {
                disablePage();
                var file = element[0].files[0];
                var fileReader = new FileReader();
                var randomHash = Math.random().toString(36).substring(3);
                fileReader.onload = function(e){
                	splitAndSendFile(new Uint8Array(e.target.result), file, randomHash);
                    $.ajax({
                        url: serviceBaseUrl+'transactions',
                        type:'POST',
                        data:{"type":"file", "name":randomHash},
                        async:true,
                        success:function(response){
                            enablePage();
                            window.location.reload();
                        },
                        error:function(err){
                            enablePage();
                        }
                    });
                };
                fileReader.readAsArrayBuffer(file);
            });

        }
    };
});

function splitAndSendFile(dataArray, file, randomHash) {
    var i = 0, formData, blob;
    for (; i < dataArray.length; i += 1e6) {
        blob = new Blob([dataArray.subarray(i, i + 1e6)]);
        formData = new FormData();
        formData.append("file", blob, randomHash + ".part" + (i / 1e6));
        $.ajax({
            url: serviceBaseUrl+'transactions',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            async: false,
            success: function(response){
                console.log(response);
            },
            error: function(data, status){
                alert("Fail: " + status);
            }
        });
    }
}

function disablePage(){
    $('#disable-div').fadeTo('slow',.4);
    $('#disable-div').append('<div id="inner-disable-div" style="position: absolute;top:0;left:0;width: 100%;height:100%;z-index:2;opacity:0.2;filter: alpha(opacity = 20)"></div>');
}

function enablePage(){
    $('#disable-div').fadeTo('slow',1);
    $('#inner-disable-div').remove();
}