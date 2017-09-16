<?php if($inlineSupport == true){echo('/* npm.im/iphone-inline-video */var makeVideoPlayableInline=function(){"use strict";function e(e){function r(t){n=requestAnimationFrame(r),e(t-(i||t)),i=t}var n,i;this.start=function(){n||r(0)},this.stop=function(){cancelAnimationFrame(n),n=null,i=0}}function r(e,r,n,i){function t(r){Boolean(e[n])===Boolean(i)&&r.stopImmediatePropagation(),delete e[n]}return e.addEventListener(r,t,!1),t}function n(e,r,n,i){function t(){return n[r]}function d(e){n[r]=e}i&&d(e[r]),Object.defineProperty(e,r,{get:t,set:d})}function i(e,r,n){n.addEventListener(r,function(){return e.dispatchEvent(new Event(r))})}function t(e,r){Promise.resolve().then(function(){e.dispatchEvent(new Event(r))})}function d(e){var r=new Audio;return i(e,"play",r),i(e,"playing",r),i(e,"pause",r),r.crossOrigin=e.crossOrigin,r.src=e.src||e.currentSrc||"data:",r}function a(e,r,n){(f||0)+200<Date.now()&&(e[h]=!0,f=Date.now()),n||(e.currentTime=r),T[++w%3]=100*r|0}function o(e){return e.driver.currentTime>=e.video.duration}function u(e){var r=this;r.video.readyState>=r.video.HAVE_FUTURE_DATA?(r.hasAudio||(r.driver.currentTime=r.video.currentTime+e*r.video.playbackRate/1e3,r.video.loop&&o(r)&&(r.driver.currentTime=0)),a(r.video,r.driver.currentTime)):r.video.networkState!==r.video.NETWORK_IDLE||r.video.buffered.length||r.video.load(),r.video.ended&&(delete r.video[h],r.video.pause(!0))}function s(){var e=this,r=e[g];return e.webkitDisplayingFullscreen?void e[b]():("data:"!==r.driver.src&&r.driver.src!==e.src&&(a(e,0,!0),r.driver.src=e.src),void(e.paused&&(r.paused=!1,e.buffered.length||e.load(),r.driver.play(),r.updater.start(),r.hasAudio||(t(e,"play"),r.video.readyState>=r.video.HAVE_ENOUGH_DATA&&t(e,"playing")))))}function c(e){var r=this,n=r[g];n.driver.pause(),n.updater.stop(),r.webkitDisplayingFullscreen&&r[E](),n.paused&&!e||(n.paused=!0,n.hasAudio||t(r,"pause"),r.ended&&(r[h]=!0,t(r,"ended")))}function v(r,n){var i=r[g]={};i.paused=!0,i.hasAudio=n,i.video=r,i.updater=new e(u.bind(i)),n?i.driver=d(r):(r.addEventListener("canplay",function(){r.paused||t(r,"playing")}),i.driver={src:r.src||r.currentSrc||"data:",muted:!0,paused:!0,pause:function(){i.driver.paused=!0},play:function(){i.driver.paused=!1,o(i)&&a(r,0)},get ended(){return o(i)}}),r.addEventListener("emptied",function(){var e=!i.driver.src||"data:"===i.driver.src;i.driver.src&&i.driver.src!==r.src&&(a(r,0,!0),i.driver.src=r.src,e?i.driver.play():i.updater.stop())},!1),r.addEventListener("webkitbeginfullscreen",function(){r.paused?n&&!i.driver.buffered.length&&i.driver.load():(r.pause(),r[b]())}),n&&(r.addEventListener("webkitendfullscreen",function(){i.driver.currentTime=r.currentTime}),r.addEventListener("seeking",function(){T.indexOf(100*r.currentTime|0)<0&&(i.driver.currentTime=r.currentTime)}))}function p(e){var i=e[g];e[b]=e.play,e[E]=e.pause,e.play=s,e.pause=c,n(e,"paused",i.driver),n(e,"muted",i.driver,!0),n(e,"playbackRate",i.driver,!0),n(e,"ended",i.driver),n(e,"loop",i.driver,!0),r(e,"seeking"),r(e,"seeked"),r(e,"timeupdate",h,!1),r(e,"ended",h,!1)}function l(e,r,n){void 0===r&&(r=!0),void 0===n&&(n=!0),n&&!y||e[g]||(v(e,r),p(e),e.classList.add("IIV"),!r&&e.autoplay&&e.play(),"MacIntel"!==navigator.platform&&"Windows"!==navigator.platform||console.warn("iphone-inline-video is not guaranteed to work in emulated environments"))}var f,m="undefined"==typeof Symbol?function(e){return"@"+(e||"@")+Math.random()}:Symbol,y=/iPhone|iPod/i.test(navigator.userAgent)&&void 0===document.head.style.grid,g=m(),h=m(),b=m("nativeplay"),E=m("nativepause"),T=[],w=0;return l.isWhitelisted=y,l}();');} ?>

var myVideo = document.getElementById("video");
<?php if($inlineSupport === true){echo('makeVideoPlayableInline(myVideo, false);');} ?>
var initialLoad = true;
var process = false;
var clicks = [];
var vidError;
var testended = false;

myVideo.oncanplaythrough = function(){
    if(initialLoad){
        myVideo.play();
        myVideo.pause();
        videoLoaded();
    }
};

try{
    myVideo.addEventListener('waiting', function() {
        $("#icon").html('<img src="/images/hloading.gif" alt="Loading" width="100" height="100" />');
        $("#video_overlay").show();
        myVideo.pause();
        try{
            myVideo.addEventListener('canplaythrough', function(){
                videoReady();
            }, false);
        }
        catch(e){console.log(e);}
    }, false);
    if(navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
        videoReady();
    }
}
catch(e){console.log(e);};

$('#exittest').click(function(){
    myVideo.pause();
    var r = confirm("If you exit this test you will score 0. Do you wish to continue?");
    if(r === true){
        testended = true;
        endTest();
    }
    else{
        $("#video_overlay").hide();
        myVideo.play();
    }
});

$("#video").bind("ended", function(){
    loadVideo($(".nextvideo").attr('id'), false);
});
myVideo.addEventListener('ended', function(){
    loadVideo($(".nextvideo").attr('id'), false);
}, false);

$("#video").click(function(){
    if(!myVideo.paused){
        $("#flags").append('<img src="/images/hpflag.png" alt="Flag" width="30" height="30" />');
        addFlagInfo(myVideo.currentTime.toFixed(2), $(".videoid").attr('id'));
    }
});

$(".nextvideo").click(function(){
    myVideo.pause();
    var r = confirm("If you skip this clip you will score 0. Do you wish to continue?");
    if(r === true){
        loadVideo($(".nextvideo").attr('id'), true);
    }
    else{
        $("#video_overlay").hide();
        myVideo.play();
    }
});

$("#video_overlay").click(function(){
    if($("#video_overlay").hasClass("vidready")){
        playVideo();
    }
    else{
        initVideo();
    }
});

function playVideo(){
    $("#icon").html('');
    $("#video_overlay").hide();
    myVideo.play();
}

function videoReady(){
    initialLoad = false;
    myVideo.pause();
    $("#flags").empty();
    $("#video_overlay").addClass('vidready');
    $("#icon").html('Click to start');
}

function videoLoaded(){
    myVideo.pause();
    try{
        video.addEventListener("canplaythrough", function(){
            videoReady();
        });
    }
    catch(e){console.log(e);};
}

function loadVideo(videoid, skipped){
    testended = true;
    makeVideoPlayableInline = null;
    delete makeVideoPlayableInline;
    if(videoid === 'none'){
        if(skipped === true){
            $.get('/modules/<?php echo($page); ?>?testid=' + $("#question-content").attr("data-test") + '&skipped=true&skipid=' + $(".videoid").attr('id'), function(){endTest();});
        }
        else{
            endTest();
        }
    }
    else{
        //if(skipped === true){var extra = '&skipped=true&skipid=' + $(".videoid").attr('id');}else{var extra = '';}
        //$.get('/modules/<?php echo($page); ?>?testid=' + $("#question-content").attr("data-test") + '&video=' + videoid + extra, function(data){
            window.location = '<?php if($page == "freehazupdate"){echo("/free-hazard-perception-test-demo.htm");}else{echo("/tests/hazard.htm");} ?>?test=' + $("#question-content").attr("data-test") + '&video=' + videoid + '&continue=true#content';
            /*data = $.parseJSON(data);
            $("#question").html(data.html);
            $("#qnum").html(data.questionnum);*/
        //});
    }
}

function endTest(){
    testended = true;
    $.get('/modules/<?php echo($page); ?>?testid=' + $("#question-content").attr("data-test") + '&endtest=true', function(){
        window.location = '<?php if($page == "freehazupdate"){echo("/free-hazard-perception-test-demo.htm");}else{echo("/tests/hazard.htm");} ?>?report=true&id=' + $("#question-content").attr("data-test");
    });
}

function clickPatternTest(){
    patternDetected = false;
    pattern = false;
    $.each(clicks, function(a, value){
        difference = (clicks[(a + 1)] - value).toFixed(1);
        if(difference === pattern && difference >= 0.4){patternDetected = true;}
        pattern = difference;
    });
    if(patternDetected === true && rapid === false){
        vidError = 'Please avoid clicking in a repetitive fashion during the clip.';
        return true;
    }
    else{return false;}
    
}

function totalClickTest(){
    if(clicks.length > 15){
        vidError = 'You clicked too many times during this clip.';
        return true;
    }
    else{return false;}
}

function rapidClickTest(){
    rapid = false;
    $.each(clicks, function(a, value){
       if((clicks[(a + 4)] - value) < 1){rapid = true;}
    });
    if(rapid === true){
        vidError = 'Please avoid a series of rapid clicks or double clicks.';
        return true;
    }
    else{return false;}
}

function cheatDetected(){
    $.get("/modules/<?php echo($page); ?>?testid=" + $("#question-content").attr("data-test") + "&cheatdetected=" + $(".videoid").attr('id'), function(){
        alert("You responded to this clip in an unacceptable manner.\r\n\r\n" + vidError + "\r\n\r\nYou will score 0 for this clip.");
    });
}

function addFlagInfo(clicktime, video){
    if(process === false){
        process = true;
        $.get("/modules/<?php echo($page); ?>?testid=" + $("#question-content").attr("data-test") + "&addflag=" + clicktime + "&question=" + video, function(){
            clicks.push(clicktime);
            if(clickPatternTest() || totalClickTest() || rapidClickTest()){
                myVideo.currentTime = myVideo.duration;
                cheatDetected();
            }
            process = false;
        });
    }
    else{
        setTimeout(function(){
            addFlagInfo(clicktime, video);
        }, 100);
    }
}

function initVideo(){
    //myVideo.play();

    if(myVideo.readyState !== 4){ //HAVE_ENOUGH_DATA
        try{
            myVideo.addEventListener('canplaythrough', onCanPlay, false);
            myVideo.addEventListener('load', onCanPlay, false); //add load event as well to avoid errors, sometimes 'canplaythrough' won't dispatch.
            setTimeout(function(){
                myVideo.pause(); //block play so it buffers before playing
            }, 1); //it needs to be after a delay otherwise it doesn't work properly.
        }
        catch(e){console.log(e);}
    }else{
        videoReady();//video is ready
    }
}
 
function onCanPlay(){
    myVideo.removeEventListener('canplaythrough', onCanPlay, false);
    myVideo.removeEventListener('load', onCanPlay, false);
    //video is ready
    videoReady();
}

setInterval(function(){
    //if(myVideo.buffered.length > 0){$("#loadingStatus").html(parseInt(((myVideo.buffered.end(0) / myVideo.duration) * 100)) + '%');}
    if(navigator.userAgent.match(/(iPod|iPhone|iPad)/)){
        if(myVideo.currentTime >= (myVideo.duration - 0.2)){
            loadVideo($(".nextvideo").attr('id'), false);
        }
    }
    if($("#video_overlay").is(":hidden")){
        if(myVideo.paused || myVideo.waiting){
            $("#icon").html('<img src="/images/hloading.gif" alt="Loading" width="100" height="100" />');
            $("#video_overlay").show();
        }
    }
    else{
        initVideo();
    }
}, 50);

$(document).ready(function(){
    $('#video').bind('contextmenu',function(){return false;});
    try{myVideo.load();}
    catch(e){console.log(e);}
    initVideo();
});

window.onbeforeunload = function(){
    try{myVideo.pause();}
    catch(e){
        console.log(e);
    };
    $('a').click(function(){testended = true;});
    if(testended === false){
        return "The results data for the hazards you have completed will be lost!";
    }
    myVideo.play();
};