{strip}
{nocache}
<div id="question-content" data-test="{$testID}">
    <div class="row">
        <div class="report w-100">
            <div class="col-md-12 reportheader">
                <div class="row">
                    <div class="col-md-3 reportbuttons">
                        <div id="{$prev_question}" class="prevvideo"><span>Prev Clip</span></div> <div id="{$next_question}" class="nextvideo"><span class="sr-only">Skip Clip</span></div> <a href="?report=true&amp;id={if is_numeric($smarty.get.review)}{$smarty.get.review}{else}{$test_id}{/if}" id="exittest"><span>Exit Test</span></a>
                        <div><strong>Clip <span id="qnum">{$question_no}</span> of <span id="totalq">{$no_questions}</span></strong></div>
                    </div>
                    <div class="col-md-9 col-sm-12 text-center" id="resultinfo">{$videotitle}</div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-3 text-center">{$videodesc|strip}</div> 
                    <div class="col-md-9">
                        <div id="{$vid_id}" class="videoid text-center">
                            <div class="yourscore">You scored {$your_score.0}{if $your_score|@count == 1} for this hazard{else} for the first hazard and {$your_score.1} for the second hazard{/if}</div>
                            <div class="embed-responsive embed-responsive-{$ratio}">
                                <div id="video_overlay">
                                    <div id="icon">
                                        <img src="{$imagePath}hloading.gif" alt="Loading" width="100" height="100" />
                                    </div>
                                </div>
                                <video width="544" height="408" id="video" class="video embed-responsive-item" data-duration="{$video.endClip}" preload="auto" controlsList="nodownload nofullscreen noremoteplayback" {if $dashScript}data-dashjs-player {/if}muted playsinline webkit-playsinline disablePictureInPicture>
                                    {if $dashScript}<source src="{$video.videoLocation}dash/{$video.videoName}.mpd" type="application/dash+xml" />{/if}
                                    <source src="{$video.videoLocation}mp4/{$video.videoName}.mp4" type="video/mp4" />
                                    <source src="{$video.videoLocation}ogv/{$video.videoName}.ogv" type="video/ogg" />
                                </video>
                            </div>
                            {if $anti_cheat}<div id="anticheat">Anti-Cheat Activated</div>{/if}
                        </div>
                        <div class="videocontrols col-md-12 mt-2">
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
                    <div id="scorewindow">
                        {foreach $score_window as $var => $window}
                            <div id="{$window.name}" style="{if $window.left}margin-left:{$window.left}%;{/if}width:{$window.width}%"{if !$var|strstr:"pre"} data-score="{$window.score}"{if $window.scoreend} data-scoreend="{$window.scoreend}"{/if}{/if}></div>
                        {/foreach}
                    </div>
                    {if $review_flags|is_array}<div id="reviewflags">{foreach $review_flags as $f => $flag}<img src="{$imagePath}hpflag.png" alt="Flag" width="20" height="20" id="flag{$f}" class="reviewflag" style="left:{$flag.left}%" data-click="{$flag.click}" />{/foreach}</div>{/if}
                    <div id="progress"></div>
                </div>
            </div>
        </div>
    </div>
</div>
{if $dashScript}<script src="{$dashScript}"></script>{/if}
<script src="{$script}"></script>
{/nocache}
{/strip}