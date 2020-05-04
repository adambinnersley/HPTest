var myVideo = document.getElementById("video");
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
        $.get('<?php echo($page); ?>?testid=' + $("#question-content").attr("data-test") + '&review=true&video=' + videoid, function(data){
            data = $.parseJSON(data);
            $("#question").html(data.html);
            $("#qnum").html(data.questionnum);
        });
    }
}

function endTest(){
    window.location = '<?php echo($location); ?>?report=true&id=' + $("#question-content").attr("data-test");
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
