<nav class="navbar navbar-inverse" role="navigation" ng-controller="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex2-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" title="16.12.2021" target="_blank" href="https://github.com/Relluem94s/RelluTodo"><img src="assets/img/rellutodo.png" height="30"></a>
    </div>
    <div class="collapse navbar-collapse navbar-ex2-collapse">
        <ul class="nav navbar-nav">
            <li ng-repeat="nav in navigation">
                <a ng-if="nav.link" target="_blank" href="{{nav.link}}"><i ng-if="nav.icon" class="{{nav.icon}}"></i> {{nav.title}}</a>
            </li>
        </ul>
    </div>
</nav>
