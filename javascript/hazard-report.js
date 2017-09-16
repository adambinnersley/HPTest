<?php if($inlineSupport == true){echo('/* npm.im/iphone-inline-video */var makeVideoPlayableInline=function(){"use strict";function e(e){function r(t){n=requestAnimationFrame(r),e(t-(i||t)),i=t}var n,i;this.start=function(){n||r(0)},this.stop=function(){cancelAnimationFrame(n),n=null,i=0}}function r(e,r,n,i){function t(r){Boolean(e[n])===Boolean(i)&&r.stopImmediatePropagation(),delete e[n]}return e.addEventListener(r,t,!1),t}function n(e,r,n,i){function t(){return n[r]}function d(e){n[r]=e}i&&d(e[r]),Object.defineProperty(e,r,{get:t,set:d})}function i(e,r,n){n.addEventListener(r,function(){return e.dispatchEvent(new Event(r))})}function t(e,r){Promise.resolve().then(function(){e.dispatchEvent(new Event(r))})}function d(e){var r=new Audio;return i(e,"play",r),i(e,"playing",r),i(e,"pause",r),r.crossOrigin=e.crossOrigin,r.src=e.src||e.currentSrc||"data:",r}function a(e,r,n){(f||0)+200<Date.now()&&(e[h]=!0,f=Date.now()),n||(e.currentTime=r),T[++w%3]=100*r|0}function o(e){return e.driver.currentTime>=e.video.duration}function u(e){var r=this;r.video.readyState>=r.video.HAVE_FUTURE_DATA?(r.hasAudio||(r.driver.currentTime=r.video.currentTime+e*r.video.playbackRate/1e3,r.video.loop&&o(r)&&(r.driver.currentTime=0)),a(r.video,r.driver.currentTime)):r.video.networkState!==r.video.NETWORK_IDLE||r.video.buffered.length||r.video.load(),r.video.ended&&(delete r.video[h],r.video.pause(!0))}function s(){var e=this,r=e[g];return e.webkitDisplayingFullscreen?void e[b]():("data:"!==r.driver.src&&r.driver.src!==e.src&&(a(e,0,!0),r.driver.src=e.src),void(e.paused&&(r.paused=!1,e.buffered.length||e.load(),r.driver.play(),r.updater.start(),r.hasAudio||(t(e,"play"),r.video.readyState>=r.video.HAVE_ENOUGH_DATA&&t(e,"playing")))))}function c(e){var r=this,n=r[g];n.driver.pause(),n.updater.stop(),r.webkitDisplayingFullscreen&&r[E](),n.paused&&!e||(n.paused=!0,n.hasAudio||t(r,"pause"),r.ended&&(r[h]=!0,t(r,"ended")))}function v(r,n){var i=r[g]={};i.paused=!0,i.hasAudio=n,i.video=r,i.updater=new e(u.bind(i)),n?i.driver=d(r):(r.addEventListener("canplay",function(){r.paused||t(r,"playing")}),i.driver={src:r.src||r.currentSrc||"data:",muted:!0,paused:!0,pause:function(){i.driver.paused=!0},play:function(){i.driver.paused=!1,o(i)&&a(r,0)},get ended(){return o(i)}}),r.addEventListener("emptied",function(){var e=!i.driver.src||"data:"===i.driver.src;i.driver.src&&i.driver.src!==r.src&&(a(r,0,!0),i.driver.src=r.src,e?i.driver.play():i.updater.stop())},!1),r.addEventListener("webkitbeginfullscreen",function(){r.paused?n&&!i.driver.buffered.length&&i.driver.load():(r.pause(),r[b]())}),n&&(r.addEventListener("webkitendfullscreen",function(){i.driver.currentTime=r.currentTime}),r.addEventListener("seeking",function(){T.indexOf(100*r.currentTime|0)<0&&(i.driver.currentTime=r.currentTime)}))}function p(e){var i=e[g];e[b]=e.play,e[E]=e.pause,e.play=s,e.pause=c,n(e,"paused",i.driver),n(e,"muted",i.driver,!0),n(e,"playbackRate",i.driver,!0),n(e,"ended",i.driver),n(e,"loop",i.driver,!0),r(e,"seeking"),r(e,"seeked"),r(e,"timeupdate",h,!1),r(e,"ended",h,!1)}function l(e,r,n){void 0===r&&(r=!0),void 0===n&&(n=!0),n&&!y||e[g]||(v(e,r),p(e),e.classList.add("IIV"),!r&&e.autoplay&&e.play(),"MacIntel"!==navigator.platform&&"Windows"!==navigator.platform||console.warn("iphone-inline-video is not guaranteed to work in emulated environments"))}var f,m="undefined"==typeof Symbol?function(e){return"@"+(e||"@")+Math.random()}:Symbol,y=/iPhone|iPod/i.test(navigator.userAgent)&&void 0===document.head.style.grid,g=m(),h=m(),b=m("nativeplay"),E=m("nativepause"),T=[],w=0;return l.isWhitelisted=y,l}();');} ?>

var myVideo = document.getElementById("video");
<?php if($inlineSupport === true){echo('makeVideoPlayableInline(myVideo, false);');} ?>
var initialLoad = true;
var alertPause = false;
var flagNums = ['ten', 'nine', 'eight', 'seven', 'six', 'five', 'four', 'three', 'two', 'one'], numbers = [5, 4, 3, 2, 1, 5, 4, 3, 2, 1], pauseAlerts = [];
var numFlags = $(".reviewflag").length;
var videoDuration = $('#video').attr('data-duration');

myVideo.oncanplaythrough = function(){
    if(initialLoad){
        myVideo.play();
        myVideo.pause();
        videoLoaded();
    }
};

if(navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
    videoLoaded();
}

$("#video").bind("ended", function(){
    myVideo.currentTime = 0;
    pauseAlerts = [];
    pauseVideo();
});

$('#exittest').click(function(){
    myVideo.pause();
    endTest();
});

$(".nextvideo").click(function(){
    loadVideo($(".nextvideo").attr('id'));
});

$(".prevvideo").click(function(){
    loadVideo($(".prevvideo").attr('id'));
});

$("#playvideo").click(function(){
    if(myVideo.paused){
        playVideo();
    }
    else{
        pauseVideo();
    }
});

$("#video_overlay").click(function(){
    playVideo();
});

$("#pausevideo").click(function(){
    if($("#pausevideo").hasClass('pauseactive')){
        $("#pausevideo").removeClass('pauseactive');
        alertPause = false;
    }
    else{
        $("#pausevideo").addClass('pauseactive');
        alertPause = true;
    }
});

$("#quarterspeed").click(function(){
    changeVideoSpeed(0.25);
    $("#quarterspeed .speed").addClass("selectedspeed");
});

$("#halfspeed").click(function(){
    changeVideoSpeed(0.5);
    $("#halfspeed .speed").addClass("selectedspeed");
});

$("#fullspeed").click(function(){
    changeVideoSpeed(1);
    $("#fullspeed .speed").addClass("selectedspeed");
});

$("#video_overlay").click(function(){
    playVideo;
});

function playVideo(){
    $("#video_overlay").html('');
    $("#video_overlay").hide();
    videoStarted();
    changeVideoSpeed(1);
    $("#fullspeed .speed").addClass("selectedspeed");
    $("#playvideo").addClass('playing');
    $("#playvideo .controltext").html('Pause');
    myVideo.play();
}

function pauseVideo(){
    $("#playvideo").removeClass('playing');
    $("#playvideo .controltext").html('Play');
    myVideo.pause();
    clearInterval(detection);
}

function changeVideoSpeed(speed){
    $(".speed").removeClass("selectedspeed");
    myVideo.playbackRate = speed;
}

function videoLoaded(){
    initialLoad = false;
    $("#video_overlay").html('Click play button to start');
}

function loadVideo(videoid){
    if(videoid === 'none'){
        endTest();
    }
    else{
        $.get('/modules/<?php echo($page); ?>?testid=' + $("#question-content").attr("data-test") + '&review=true&video=' + videoid, function(data){
            data = $.parseJSON(data);
            $("#question").html(data.html);
            $("#qnum").html(data.questionnum);
        });
    }
}

function endTest(){
    window.location = '<?php if($page == "freehazupdate"){echo("/free-hazard-perception-test-demo.htm");}else{echo("/tests/hazard.htm");} ?>?report=true&id=' + $("#question-content").attr("data-test");
}

function detectPauseAlert(number, num){
    if(pauseAlerts[number] !== true && myVideo.currentTime >= $("#"+number).attr('data-score')){
        pauseAlerts[number] = true;
        $("#scorenum").html(num);
        if(alertPause === true){
            pauseVideo();
        }
    }
}

function scoreWindowEnd(){
    if(pauseAlerts['endscore'] !== true && myVideo.currentTime >= $("#one").attr('data-scoreend')){
        pauseAlerts['endscore'] = true;
        $("#scorenum").html(0);
    }
    if(pauseAlerts['endscore2'] !== true && myVideo.currentTime >= $("#six").attr('data-scoreend')){
        pauseAlerts['endscore2'] = true;
        $("#scorenum").html(0);
    }
}

function clickEvents(){
    for(i = 1; i <= numFlags; i++){
        if(pauseAlerts['click'+i] !== true && myVideo.currentTime >= $("#flag"+i).attr('data-click')){
            pauseAlerts['click'+i] = true;
            toggleClickEvent();
        }
    }
}

function toggleClickEvent(){
    var clickOn = false;
    var flashClick = setInterval(function(){
        if(clickOn === false){
            clickOn = true;
            $("#clickevent").addClass('clickedevent');
        }
        else{
            clickOn = false;
            $("#clickevent").removeClass('clickedevent');
            clearInterval(flashClick);
        }
    }, 250);
}

$("#progress").slider({
    orientation: "horizontal",
    max: 1000,
    value: 0,
    slide: refreshVideo
});

function refreshVideo(){
    var time = (videoDuration / 100) * ($("#progress").slider("value") / 10);
    myVideo.currentTime = time;
    resetClickEvents();
    pauseVideo();
}

function resetClickEvents(){
    pauseAlerts['endscore'] = false;
    pauseAlerts['endscore2'] = false;
    $("#scorenum").html(0);
    for(i = 1; i <= numFlags; i++){
        if(myVideo.currentTime <= $("#flag"+i).attr('data-click')){
            pauseAlerts['click'+i] = false;
        }
    }
    for(l = 0; l < flagNums.length; l++){
        if(myVideo.currentTime <= $("#"+flagNums[l]).attr('data-score')){
            pauseAlerts[flagNums[l]] = false;
            if(myVideo.currentTime >= $("#"+flagNums[l - 1]).attr('data-score') && myVideo.currentTime <= $("#"+flagNums[l]).attr('data-score')){
                $("#scorenum").html(numbers[l - 1]);
            }
        }
    }
    
}

function videoStarted(){
    detection = setInterval(function(){
        for(l = 0; l < flagNums.length; l++){
            detectPauseAlert(flagNums[l], numbers[l]);
        }
        scoreWindowEnd();
        clickEvents();
        $("#progress").slider("value", ((myVideo.currentTime / videoDuration) * 1000)); 
    }, 50);
}