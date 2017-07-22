{strip}
{nocache}
<div id="question-content" data-test="{$testID}">
    <div class="row">
        <div id="buttons" class="col-lg-1 col-md-10 col-xs-8 pull-right text-center">{$review} {$next_question} <div id="exittest"><div class="showbtn"></div>Exit Test</div></div>
        <div id="{$vid_id}" class="col-xs-12 col-lg-10 col-lg-offset-1 videoid text-center no-padding"><div class="embed-responsive embed-responsive-4by3">{$video}</div></div>
    </div>
</div>
<div class="row flagholder"><div id="flags"></div></div>
{$script}
{/nocache}
{/strip}