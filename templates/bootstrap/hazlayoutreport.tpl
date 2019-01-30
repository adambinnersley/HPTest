{strip}
{nocache}
<div id="question-content" data-test="{$testID}">
    <div class="row">
        <div class="report">
            <div class="col-md-12 reportheader">
                <div class="row">
                    <div class="col-md-3 reportbuttons">
                        <div id="{$prev_question}" class="prevvideo"><span>Prev Clip</span></div>{$prev_question} <div id="{$next_question}" class="nextvideo"><span class="sr-only">Skip Clip</span></div> <a href="?report=true&amp;id={$smarty.get.review}" id="exittest"><span>Exit Test</span></a>
                        <div><strong>Clip <span id="qnum">{$question_no}</span> of <span id="totalq">{$no_questions}</span></strong></div>
                    </div>
                    <div class="col-md-9 col-sm-12 text-center" id="resultinfo">{$videotitle}</div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-3 text-center">{$videodesc|strip}</div> 
                    <div class="col-md-9">
                        <div id="{$vid_id}" class="videoid text-center"><div class="yourscore">You scored {$your_score.0}{if $your_score|@count == 1} for this hazard{else} for the first hazard and {$your_score.1} for the second hazard{/if}</div><div class="embed-responsive embed-responsive-4by3">{$video}</div>{if $anti_cheat}<div id="anticheat">Anti-Cheat Activated</div>{/if}</div>
                        <div class="videocontrols col-md-12">
                            <div id="playvideo"><div class="reportbtn"></div><div class="controltext">Play</div></div><div id="pausevideo"><div class="reportbtn"></div><div class="controltext">Pause<br />5,4,3,2,1</div></div>
                            <div id="hazardscore">Score window<div id="scorenum">0</div></div>
                            <span class="hidden-sm hidden-xs">
                                <div id="quarterspeed"><div class="speed"></div>&frac14;</div><div id="halfspeed"><div class="speed"></div>&frac12;</div><div id="fullspeed"><div class="speed selectedspeed"></div>1x</div>
                            </span>
                            <div class="hidden-xs" id="clickevent"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="row">
                    <div id="scorewindow">{$score_window}</div>
                    {if $review_flags|is_array}<div id="reviewflags">{foreach $review_flags as $f => $flag}<img src="{$imagePath}hpflag.png" alt="Flag" width="20" height="20" id="flag{$f}" class="reviewflag" style="left:{$flag.margin-left}%" data-click="{$flag.click}" />{/foreach}</div>{/if}
                    <div id="progress"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{$script}"></script>
{/nocache}
{/strip}