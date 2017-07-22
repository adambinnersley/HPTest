{strip}
{nocache}
<div class="row">
    {if $instructor}
    <div class="col-md-12">
        <a href="/student/hazard?pupil={$smarty.get.pupil}&report=true&id={$smarty.get.review}" title="Back" class="btn btn-danger" id="backButton"><span class="fa fa-angle-left fa-fw"></span> Back to Hazard Test Report</a>
        <h3>Results for {$pupilInfo.firstname} {$pupilInfo.surname}</h3>
    </div>
    {/if}
    <div id="hazardTest" class="col-lg-10 col-lg-offset-1 col-md-12">
    <div id="question">{$video_data}</div>
    </div>
</div>
{/nocache}
{/strip}