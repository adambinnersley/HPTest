{strip}
{nocache}
<div id="question-content" data-test="{$testID}">
    <div class="row">
        <div id="buttons" class="col-12 col-lg-1 col-md-10 order-lg-2 text-center">
            {if isset($review)}{$review}{/if}
            <div id="{$next_question}" class="nextvideo mx-auto"><div class="showbtn"></div>Skip Clip</div>
            <div id="exittest"><div class="showbtn"></div>Exit Test</div>
        </div>
        <div id="{$vid_id}" class="videoid col-12 col-lg-10 offset-lg-1 text-center no-padding order-lg-1">
            <div class="embed-responsive embed-responsive-{$ratio}">
                <div id="video_overlay">
                    <div id="icon">
                        <img src="{$imagePath}hloading.gif" alt="Loading" width="100" height="100" />
                    </div>
                </div>
                <video width="544" height="408" id="video" class="video embed-responsive-item" data-duration="{$video.endClip}" preload="auto" controlsList="nodownload nofullscreen noremoteplayback" muted playsinline webkit-playsinline disablePictureInPicture>
                    <source src="{$video.videoLocation}dash/{$video.videoName}.mpd" type="application/dash+xml" />
                    <source src="{$video.videoLocation}mp4/{$video.videoName}.mp4" type="video/mp4" />
                    <source src="{$video.videoLocation}ogv/{$video.videoName}.ogv" type="video/ogg" />
                </video>
            </div>
        </div>
    </div>
</div>
<div class="row flagholder"><div id="flags" class="d-block w-100"></div></div>
<script src="{$script}"></script>
{/nocache}
{/strip}