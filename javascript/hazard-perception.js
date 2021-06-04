var myVideo = document.getElementById("video");
var initialLoad = true;
var process = false;
var clicks = [];
var vidError;
var testended = false;
var listener;
var loadDelay;

myVideo.oncanplaythrough = function(){
    if(initialLoad){
        myVideo.play();
        myVideo.pause();
        videoLoaded();
    }
};

myVideo.addEventListener('waiting', function() {
    $("#icon").html('<img src="<?php if($imgDir){echo($imgDir);}else{echo("/images/");} ?>hloading.gif" alt="Loading" width="100" height="100" />');
    $("#video_overlay").show();
    myVideo.pause();
    if(myVideo.currentTime >= (myVideo.duration - 1)){
        loadVideo($(".nextvideo").attr('id'), false);
    }
    try{
        myVideo.addEventListener('canplaythrough', function(){
            videoReady();
        }, false);
    }
    catch(e){console.log(e);}
}, false);

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
        $("#flags").append('<img src="<?php if($imgDir){echo($imgDir);}else{echo("/images/");} ?>hpflag.png" alt="Flag" width="30" height="30" />');
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

function videoReady(delay = false){
    if(delay === true && initialLoad === true){
        setTimeout(() => videoReady(false), 2500);
    }
    else{
		if(initialLoad === true){
			myVideo.pause();
			initialLoad = false;
		}
        $("#flags").empty();
        $("#video_overlay").addClass('vidready');
        $("#icon").html('Click to start');
    }
}

function videoLoaded(){
    myVideo.pause();
    try{
        video.addEventListener("canplaythrough", function(){
            videoReady(true);
        });
    }
    catch(e){console.log(e);};
}

function loadVideo(videoid, skipped){
    testended = true;
    if(videoid === 'none'){
        if(skipped === true){
            $.get('<?php echo($page); ?>?testid=' + $("#question-content").attr("data-test") + '&skipped=true&skipid=' + $(".videoid").attr('id'), function(){endTest();});
        }
        else{
            endTest();
        }
    }
    else{
        //if(skipped === true){var extra = '&skipped=true&skipid=' + $(".videoid").attr('id');}else{var extra = '';}
        //$.get('<?php echo($page); ?>?testid=' + $("#question-content").attr("data-test") + '&video=' + videoid + extra, function(data){
            clearInterval(listener);
            window.location = '<?php echo($location); ?>?test=' + $("#question-content").attr("data-test") + '&video=' + videoid + '&continue=true#content';
            /*data = $.parseJSON(data);
            $("#question").html(data.html);
            $("#qnum").html(data.questionnum);*/
        //});
    }
}

function endTest(){
    testended = true;
    $.get('<?php echo($page); ?>?testid=' + $("#question-content").attr("data-test") + '&endtest=true', function(){
        clearInterval(listener);
        window.location = '<?php echo($location); ?>?report=true&id=' + $("#question-content").attr("data-test");
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
    $.get("<?php echo($page); ?>?testid=" + $("#question-content").attr("data-test") + "&cheatdetected=" + $(".videoid").attr('id'), function(){
        alert("You responded to this clip in an unacceptable manner.\r\n\r\n" + vidError + "\r\n\r\nYou will score 0 for this clip.");
    });
}

function addFlagInfo(clicktime, video){
    if(process === false){
        process = true;
        $.get("<?php echo($page); ?>?testid=" + $("#question-content").attr("data-test") + "&addflag=" + clicktime + "&question=" + video, function(){
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
        videoReady(true);//video is ready
    }
}
 
function onCanPlay(){
    myVideo.removeEventListener('canplaythrough', onCanPlay, false);
    myVideo.removeEventListener('load', onCanPlay, false);
    //video is ready
    videoReady(true);
}

listener = setInterval(function(){
    //if(myVideo.buffered.length > 0){$("#loadingStatus").html(parseInt(((myVideo.buffered.end(0) / myVideo.duration) * 100)) + '%');}
    if(navigator.userAgent.match(/(iPod|iPhone|iPad)/)){
        if((myVideo.currentTime >= (myVideo.duration - 0.6)) && myVideo.paused || myVideo.waiting){
            loadVideo($(".nextvideo").attr('id'), false);
        }
    }
    if($("#video_overlay").is(":hidden")){
        if(myVideo.paused || myVideo.waiting){
            $("#icon").html('<img src="<?php if($imgDir){echo($imgDir);}else{echo("/images/");} ?>hloading.gif" alt="Loading" width="100" height="100" />');
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