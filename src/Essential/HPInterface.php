<?php

namespace HPTest\Essential;

interface HPInterface{
    public function createTest();
    public function createHTML($prim);
    public function getUserProgress($testID);
    public function setTestID($testID);
    public function addFlag($clickTime, $videoID);
    public function cheatDetected($videoID, $score);
    public function markVideo($videoID);
    public function endTest($mark);
    public function confirmOverride();
    
    public function setPassmark($passmark);
    public function getPassmark();
    public function setVidLocation($location);
    public function getVidLocation();
    public function setUserType($type);
    public function getUserType();
    public function setTestType($type);
    public function getTestType();
}
