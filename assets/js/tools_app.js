var app = angular.module("tools", []);

var transformRequest = transformrequest = function (obj) {
    var str = [];
    for (var p in obj)
        str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
    return str.join("&");
};

var headers = {
    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
};

app.controller("sideBar", function ($scope, $http) {
    $scope.load = function () {
        $http({
            method: 'GET',
            url: 'api.php?ip'
        }).then(function successCallback(response) {
            $scope.ip = response.data;
        });
    };

    $scope.load();
});

app.controller("todoList", function ($scope, $http) {
    $scope.load = function () {
        $http({
            method: 'GET',
            url: 'api.php?todo&todos'
        }).then(function successCallback(response) {
            $scope.todos = response.data;
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
            $scope.todos = response.data;
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




    $scope.copy = function (text) {
        var dummy = document.createElement("textarea");
        document.body.appendChild(dummy);
        dummy.value = text;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);
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
