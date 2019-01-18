{strip}
<div class="row">
    <div class="col-md-6 col-md-offset-3 col-lg-5 col-lg-offset-4">
        <div class="panel panel-default">
            <div class="panel-heading">You have already taken this test</div>
            <div class="panel-body">
            <p>You have already taken this test and <strong>{$status}</strong> are you sure that you would like to take this test again?</p>
            <a href="hazard" title="Return to menu" class="return btn btn-danger">&laquo; Return to menu</a>
            <a href="{$smarty.server.REQUEST_URI|replace:"&confirm=true":""}&confirm=true" title="Start New test" class="startnew btn btn-success pull-right">Start New Test</a>
            </div>
        </div>
    </div>
</div>
{/strip}