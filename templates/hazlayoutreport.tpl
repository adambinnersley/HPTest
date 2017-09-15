{strip}
{nocache}
<div id="question-content" data-test="{$testID}">
    <div class="row">
        <div class="report">
            <div class="col-md-12 reportheader">
                <div class="row">
                    <div class="col-md-3 reportbuttons">
                        {$prev_question} {$next_question} <a href="?report=true&amp;id={$smarty.get.review}" id="exittest"><span>Exit Test</span></a>
                        <div><strong>Clip <span id="qnum">{$question_no}</span> of <span id="totalq">{$no_questions}</span></strong></div>
                    </div>
                    <div class="col-md-9 col-sm-12 text-center" id="resultinfo">{$videotitle}</div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-3 text-center">{$videodesc|strip}</div> 
                    <div class="col-md-9">
                        <div id="{$vid_id}" class="videoid text-center">{$your_score}<div class="embed-responsive embed-responsive-4by3">{$video}</div>{$anti_cheat}</div>
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
                    <div id="reviewflags">{$review_flags}</div>
                    <div id="progress"></div>
                </div>
            </div>
        </div>
    </div>
</div>
{$script}
{/nocache}
{/strip}