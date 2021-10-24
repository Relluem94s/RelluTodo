<div class="sidebar" ng-hide="isObjectEmpty(jenkins)">
    <ul class="job" ng-repeat="job in jenkins | orderBy:'name'">
        <li>
            <a href="{{job.url}}" target="_blank">{{job.name}}</a><button title="Build" type="button" class="btn build" ng-click="build(job.url)"><i class="fas fa-play-circle"></i></button>
        </li>
        <li ng-repeat="build in job.builds" ng-if="!(job.nextBuildNumber -1 !== build.number && job.lastFailedBuild.number !== build.number && job.lastUnstableBuild.number !== build.number && job.lastCompletedBuild.number !== build.number)">
            <i class="fas fa-check-circle stable" title="Stable" ng-if=" job.lastStableBuild.number === build.number"></i>
            <i class="fas fa-info-circle unstable" title="Unstable" ng-if="job.lastUnstableBuild.number === build.number"></i> 
            <i class="fas fa-times-circle failed" title="Failed" ng-if="job.lastFailedBuild.number === build.number"></i>
            <i class="fas fa-circle unsuccessful" title="Unsuccessful" ng-if="job.lastUnsuccessfulBuild.number === build.number && job.lastFailedBuild.number !== build.number"></i>
            <i class="fas fa-spinner fa-spin running" title="Running..." ng-if="job.nextBuildNumber -1 === build.number && job.lastUnsuccessfulBuild.number !== build.number && job.lastStableBuild.number !== build.number && job.lastFailedBuild.number !== build.number && job.lastUnstableBuild.number !== build.number"></i>
            <a href="{{build.url}}" target="_blank">{{build.number}}</a>
        </li>
    </ul>
    <div class="refresh">
        <button class="btn btn-primary" ng-click="load()"><i class="fas fa-sync"></i></button>
    </div>
</div>
