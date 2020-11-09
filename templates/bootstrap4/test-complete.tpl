{strip}
<div class="row">
    <div class="col-md-6 offset-md-3 col-lg-5 offset-lg-4">
        <div class="card mb-3 border-primary">
            <div class="card-header bg-primary text-bold p-2">You have already taken this test</div>
            <div class="card-body">
            <p>You have already taken this test and <strong>{$status}</strong> are you sure that you would like to take this test again?</p>
            <a href="hazard" title="Return to menu" class="return btn btn-danger">&laquo; Return to menu</a>
            <a href="{$smarty.server.REQUEST_URI|replace:"&amp;confirm=true":""}&amp;confirm=true" title="Start New test" class="startnew btn btn-success float-right">Start New Test</a>
            </div>
        </div>
    </div>
</div>
{/strip}