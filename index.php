<html ng-app="rellutodo">
    <?php require_once ("assets/modules/head.php"); ?>
    <body>
        <?php require_once ("assets/modules/nav.php"); ?>
        <div class="container" ng-controller="todoList">
            <table class="table table-striped table-bordered todoList">
                <tr>
                    <th class="col-sm-12">
                        <textarea class="form-control input" name="todo" ng-model="todo" placeholder="Todo Description"></textarea>
                    </th>
                    <th class="col-sm-1 table-centered">
                        <button title="Search" type="button" ng-disabled="!todo" class="btn btn-primary" ng-click="search(todo)"><i class="fas fa-search"></i></button>
                        <button title="Reload Todos" type="button" class="btn btn-primary" ng-click="refresh()"><i class="fas fa-sync"></i></button>
                    </th>
                    <th class="col-sm-1 table-centered">
                        <button title="Save" type="button" ng-disabled="!todo" class="btn btn-success" ng-click="save(todo)" id="saveTodo"><i class="fas fa-paper-plane"></i></button>
                    </th>
                </tr>
                <tr>
                    <th colspan="3">
                        <?php require_once ("assets/modules/stats.php"); ?>
                    </th>
                </tr>
                <tr ng-repeat="todo in todos| orderBy:'-id'| orderBy:'deletedby'" id="{{todo.id}}">
                    <td class="col-sm-12">
                        <textarea ng-disabled="todo.deletedby" class="form-control" ng-model="todo.text">{{todo.text}}</textarea>
                        <a ng-repeat="link in todo.links" target="_blank" href="{{link.link}}" title="{{link.short}}"><span class="badge"><i class="fas fa-link"></i>&nbsp;{{link.short}}</span></a>
                        <span class="label label-primary" title="{{user}}"  ng-repeat="user in todo.users track by $index" ng-click="search('@' + user)"><i class="fas fa-user"></i>&nbsp;{{user}}</span>
                        <span class="label label-info" title="{{label}}"  ng-repeat="label in todo.searchlabels track by $index" ng-click="search(label)"><i class="fas fa-hashtag"></i>&nbsp;{{label}}</span>

                    </td>
                    <td class="col-sm-1">
                        <button title="Info ID: {{todo.id}}" type="button" class="btn btn-info infoTodo" ng-click="info(todo)" data-toggle="modal" data-target="#infoModal"><i class="fas fa-info-circle"></i></button>
                    </td>
                    <td class="col-sm-1">
                        <button title="Edit" ng-if="!todo.deletedby" type="button" class="btn btn-warning" ng-click="edit(todo)"><i class="fas fa-pen-square"></i></button>
                        <button title="Delete" ng-if="!todo.deletedby" type="button" class="btn btn-success" ng-click="delete(todo)"><i class="fas fa-check-square"></i></button>
                        <button title="Restore" ng-if="todo.deletedby" type="button" class="btn btn-primary" ng-click="restore(todo)"><i class="fas fa-square"></i></button>
                    </td>
                </tr>
            </table>
            <?php require_once ("assets/modules/modal.php"); ?>   
        </div>
        <div ng-controller="sideBar">
            <?php require_once ("assets/modules/sidebar.php"); ?>
        </div>
    </body>
</html>
