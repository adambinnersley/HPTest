<?php

namespace HPTest;

/**
 * Description of HPInterface
 *
 * @author Adams
 */
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
    public function setVideoTable($table);
    public function getVideoTable();
    public function setProgressTable($table);
    public function getProgressTable();
}