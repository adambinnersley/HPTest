{strip}
{nocache}
<div class="row">
    <div class="col-md-12">
        <ul class="pager">
            <li class="previous"><a href="hazard">&larr; Back to Hazard Tests</a></li>
        </ul>
        <div class="havepassed hp-{if $score < $passmark}failed{else}passed{/if}">You have {if $score < $passmark}failed{else}passed{/if} this time</div>
        <div class="card mb-3 border-primary">
            <div class="card-header bg-primary text-bold p-2">
                <div class="row">
                <div class="passmark col-md-6">The pass mark for this test was {$passmark}</div>
                <div class="passmark col-md-6 text-right">You have scored {$score} points out of a possible 75</div>
                </div>
            </div>
            <table class="table table-bordered styled resultstable mb-0" id="{$testID}">
            <thead>
            <tr>
            <th class="text-center" style="width:50px">Test</th>
            <th class="text-center" style="width:50px">No</th>
            <th class="text-center">Description</th>
            <th class="text-center" style="width:60px">Score</th>
            <th class="text-center" style="width:100px">Status</th>
            </tr>
            </thead>
            {foreach $videos as $video}
            <tr class="reviewhp text-center colour{$video.score}" id="{$video.id}">
                <td>{$testID}</td>
                <td>{$video.no}</td>
                <td>{$video.description}</td>
                <td>{$video.score}</td>
                <td>{$video.status}</td>
            </tr>    
            {/foreach}
            </table>
        </div>
    </div>
    <div class="clipanaysis text-center col-md-6 offset-md-3">
        <p>Analysis of all clips by score</p>
        <div id="score-analysis">
            {math assign="perclip" equation="(score / 14)" score=$score format="%.2f"}
            <div id="car" style="left:{if $perclip <= 5}{math equation="pos * 16.666" pos=$perclip}{else}{math equation="pos * 18.657" pos=$perclip}{/if}%">{$perclip} per clip</div>
            <div id="score-0">{$windows.0|string_format:"%d"}</div><div id="score-1">{$windows.1|string_format:"%d"}</div><div id="score-2">{$windows.2|string_format:"%d"}</div><div id="score-3">{$windows.3|string_format:"%d"}</div><div id="score-4">{$windows.4|string_format:"%d"}</div><div id="score-5">{$windows.5|string_format:"%d"}</div>
        </div>
    </div>
    <div class="col-md-12">
        <ul class="pager">
            <li class="previous"><a href="hazard">&larr; Back to Hazard Tests</a></li>
        </ul>
    </div>
</div>
<script type="text/javascript">
$('.reviewhp td').click(function(){
    window.location = '?review=' + $('.resultstable').attr('id') + '&prim=' + $(this).parent("tr").attr('id');
});
</script>
{/nocache}
{/strip}