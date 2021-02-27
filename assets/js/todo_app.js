var app = angular.module("rellutodo", []);

var transformRequest = transformrequest = function (obj) {
    var str = [];
    for (var p in obj)
        str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
    return str.join("&");
};

var headers = {
    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
};

app.controller("sideBar", function ($scope, $http, $interval, $timeout) {    
    $scope.isObjectEmpty = function (jenkins) {
        if (jenkins === undefined || jenkins === null || (Array.isArray(jenkins) && jenkins.length === 0)) {
            return true;
        } else {
            return false;

        }
    };
    
    $scope.build = function(url){
        $http({
            method: 'GET',
            url: 'api.php?jenkins&job='+url
        }).then(function successCallback(response) {
             $timeout(function (){
                $interval(function () {
                    $scope.load();
                }, 10000, 5);
            }, 1000);
        });
    };
    
    $scope.load = function () {
        $http({
            method: 'GET',
            url: 'api.php?jenkins'
        }).then(function successCallback(response) {
            $scope.jenkins = response.data;
        });
    };

    $scope.load();

    $interval(function () {
        $scope.load();
    }, 120000);

});

app.controller("todoList", function ($scope, $http) {
    $scope.load = function () {
        $http({
            method: 'GET',
            url: 'api.php?todo&todos'
        }).then(function successCallback(response) {
            $scope.generateLinks(response.data);
        });
        $http({
            method: 'GET',
            url: 'api.php?todo&stats'
        }).then(function successCallback(response) {
            $scope.stats = response.data;
        });
    };

    $scope.load();

    $scope.refresh = function (todo) {
        $scope.load();
    };

    $scope.save = function (todo) {
        $http({
            method: 'POST',
            url: 'api.php?todo',
            transformRequest: transformRequest,
            headers: headers,
            data: {
                todo: todo
            }
        }).then(function successCallback(response) {
            if (response.status !== 200) {
                alert(response.data);
            }
            $scope.todo = "";
            $scope.load();
        });
    };

    $scope.edit = function (todo) {
        $http({
            method: 'POST',
            url: 'api.php?todo',
            transformRequest: transformRequest,
            headers: headers,
            data: {
                id: todo.id,
                text: todo.text
            }
        }).then(function successCallback(response) {
            if (response.status !== 200) {
                alert(response.data);
            }
            $scope.load();
        });
    };

    $scope.search = function (todo) {
        $http({
            method: 'POST',
            url: 'api.php?todo',
            transformRequest: transformRequest,
            headers: headers,
            data: {
                search: todo
            }
        }).then(function successCallback(response) {
            $scope.generateLinks(response.data);
        });
        $scope.todo = "";
    };

    $scope.delete = function (todo) {
        $http({
            method: 'POST',
            url: 'api.php?todo',
            transformRequest: transformRequest,
            headers: headers,
            data: {
                id: todo.id
            }
        }).then(function successCallback(response) {
            if (response.status !== 200) {
                alert(response.data);
            }

            $scope.load();
        });
    };

    $scope.restore = function (todo) {
        $scope.delete(todo);
    };
    
    $scope.infoData = null;
    $scope.info = function (todo){
        $scope.infoData = todo;
    };
    

    $scope.copy = function (text) {
        var dummy = document.createElement("textarea");
        document.body.appendChild(dummy);
        dummy.value = text;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);
    };
    
    $scope.generateLinks = function(data){
        var todos = [];
            Object.keys(data).forEach(function (outerKey) {
                var entry = [];
                Object.keys(data[outerKey]).forEach(function (innerKey) {
                    if (innerKey === "text") {
                        entry["searchlabels"] = [];
                        entry["links"] = [];
                        var text = ((data[outerKey][innerKey]) +"") .split(/\s+/g);
                        for (var i = 0; i <= text.length; i++) {
                            if (text[i] !== undefined && text[i].startsWith("http")) {
                                entry["links"].push({"link": text[i], "short": text[i].split("//")[1].replace("www.", "")});
                            }
                            if (text[i] !== undefined && text[i].startsWith("#")) {
                                entry["searchlabels"].push(text[i].replace("#", ""));
                            }
                        }
                    }
                    entry[innerKey] = data[outerKey][innerKey];
                });
                todos.push(entry);
            });
            $scope.todos = todos;
        };
    });

app.controller("navigation", function ($scope, $http) {
    $http({
        method: 'GET',
        url: 'api.php?nav'
    }).then(function successCallback(response) {
        $scope.navigation = response.data;
    });
});
