{strip}
<div class="row">
    <div class="col-lg-12">
        <table class="table table-striped table-bordered table-sm table-hover">
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
                <td><a href="?test={$i}">{if $test.status >= 1}Retake {/if}{if ($i == 11 OR $i == 12 OR $i == 13)}DVSA {if $i == 12 OR $i == 13}CGI {/if}{/if}Hazard Perception Test {$i}</a></td>
                <td class="text-center"><strong>{$test.totalscore|string_format:"%d"} / 75</strong></td>
                {if $test.status >= 1}<td class="text-center bg-{if $result|strtolower == 'failed'}danger{else}success{/if}">{$result}</td>
                {else}<td class="text-center">&nbsp;</td>{/if}
                <td class="text-center">{if $test.status >= 1}<a href="?report=true&amp;id={$i}" title="Review Test">Review Test</a>{/if}</td>
            </tr>
            {/foreach}
        </table>
    </div>
</div>
{/strip}