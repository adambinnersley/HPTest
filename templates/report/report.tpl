{strip}
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><span class="page-header-text">{if $misccontent.type == 'theory'}<span class="fa fa-pencil"></span> Theory Tests{else}<span class="fa fa-warning"></span> Hazard Perception Tests{/if}</span></h1>
    </div>
    <div class="col-lg-12">
        {if $misccontent.type == 'theory'}{*{if $newToUpload}<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>You have tests not yet uploaded to your online account storage to share with the LDC <a href="https://itunes.apple.com/gb/artist/learner-driving-centres/id566280394" title="iOS Apps" target="_blank">iOS</a>, <a href="https://play.google.com/store/apps/developer?id=Teaching+Driving+Limited" title="Android Apps" target="_blank">Android Apps</a> and LDC PC software <a href="/student/syncappdata?upload=true" title="Sync app data" class="btn btn-success">Upload new tests</a></div>
        {elseif $newTestsAvailable}*}{if $newTestsAvailable}<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>You have newer tests taken available on the server to download do you want to <a href="/student/syncappdata" title="Sync app data" class="btn btn-success">Download new tests</a> now?</div>{/if}{/if}
        <div class="panel panel-default">
            <div class="panel-heading">{if $misccontent.type == 'theory'}Theory Tests{else}Hazard Perception Tests{/if}</div>
        <table class="table table-striped table-bordered table-condensed styled">
            <thead>
            <tr>
                <th>{$misccontent.testType}</th>
                <th class="text-center">Mark</th>
                <th{*{if $misccontent.type == 'theory'} colspan="2"{/if}*} class="text-center">Status</th>
                <th class="text-center">Review</th>
            </tr>
            </thead>
            {foreach $testReports as $i => $test}
                {if $test.status == 1}{$result = "Passed"}{else}{$result = "Failed"}{/if}
            <tr>
                <td><a href="/student/{$misccontent.type}?test={$i}">{if $test.status >= 1}Retake {/if}{if $misccontent.type != 'theory' && ($i == 11 OR $i == 12)}DVSA {if $misccontent.type != 'theory' && $i == 12}CGI {/if}{/if}{$misccontent.testType} {$i}</a></td>
                <td class="text-center"><strong>{$test.totalscore|string_format:"%d"} / {if $misccontent.type == 'theory'}{if !$adiReport}50{else}100{/if}{else}75{/if}</strong></td>
                {if $test.status >= 1}<td class="text-center {$result|strtolower}">{$result}</td>{*{if $type == 'theory'}<td class="text-center"><a href="/certificate.pdf?type={$misccontent.type}&amp;testID={$i}" title="View PDF" target="_blank"><span class="fa fa-file-pdf-o"></span> View PDF {if $test.status == 1}Certificate{else}Report{/if}</a></td>{/if}*}
                {else}<td class="text-center"{*{if $misccontent.type == 'theory'} colspan="2"{/if}*}>&nbsp;</td>{/if}
                <td class="text-center">{if $test.status >= 1}<a href="/student/{$misccontent.type}?report=true&amp;id={$i}" title="Review Test">Review Test</a>{/if}</td>
            </tr>
            {/foreach}
        </table>
        </div>
    </div>
    {if $misccontent.type == 'theory'}<div class="col-md-12 text-center">
        <small>Crown copyright material reproduced under licence from the Driver and Vehicle Standards Agency, which does not accept any responsibility for the accuracy of the reproduction.</small>
    </div>{/if}
</div>
{/strip}