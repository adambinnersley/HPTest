{strip}
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><span class="page-header-text"><span class="fa fa-warning"></span> Hazard Perception Tests</span></h1>
    </div>
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">Hazard Perception Tests</div>
        <table class="table table-striped table-bordered table-condensed styled">
            <thead>
            <tr>
                <th>Hazard Perception Test</th>
                <th class="text-center">Mark</th>
                <th class="text-center">Status</th>
                <th class="text-center">Review</th>
            </tr>
            </thead>
            {foreach $testReports as $i => $test}
                {if $test.status == 1}{$result = "Passed"}{else}{$result = "Failed"}{/if}
            <tr>
                <td><a href="hazard?test={$i}">{if $test.status >= 1}Retake {/if}{if ($i == 11 OR $i == 12)}DVSA {if $i == 12}CGI {/if}{/if}Hazard Perception Test {$i}</a></td>
                <td class="text-center"><strong>{$test.totalscore|string_format:"%d"} / 75</strong></td>
                {if $test.status >= 1}<td class="text-center {$result|strtolower}">{$result}</td>
                {else}<td class="text-center">&nbsp;</td>{/if}
                <td class="text-center">{if $test.status >= 1}<a href="hazard?report=true&amp;id={$i}" title="Review Test">Review Test</a>{/if}</td>
            </tr>
            {/foreach}
        </table>
        </div>
    </div>
</div>
{/strip}