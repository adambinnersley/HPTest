<?php
namespace HPTest;

use HPTest\Essential\HPInterface;
use DBAL\Database;
use Configuration\Config;
use Smarty;

class HazardPerception implements HPInterface{
    protected $db;
    protected $config;
    protected $user;
    protected $template;
    
    protected $userClone = false;
    protected $userprogress = false;
    
    public $testID = 1;
    public $numVideos = 14;
    public $passmark = 44;
    
    protected $userAnswers;
    protected $status;
    protected $currentVideo = [];
    
    public $javascriptLocation = '/js/theory/';
    public $videoLocation = '/videos/';
    public $imgLocation = '/images/';
    
    protected $videoInfo;
    protected $videodata;
    protected $scriptVar = 'hazupdate';
    
    protected $report = true;
    protected $confirm = false;
    
    protected $userType = 'account';
    protected $testType = 'CAR';
    
    protected $windows = [
        1 => ['five', 'four', 'three', 'two', 'one', 'endseq', 'prehazard'],
        2 => ['ten', 'nine', 'eight', 'seven', 'six', 'endseq2', 'prehazard2']
    ];

    /**
     * Sets the required variables for the test to be rendered
     * @param Database $db This should be an instance of Database
     * @param Config $config This should be the instance of Config
     * @param Smarty $template This should be the instance of Smarty Template
     * @param object $user This should be an instance of User class
     * @param int|false If you want to emulate a user set this here
     * @param string|false If you want to change the template location set this location here else set to false
     * @param string If the template directory is not set set the default theme directory (Currently: bootstrap or bootstrap4)
     */
    public function __construct(Database $db, Config $config, Smarty $template, $user, $userID = false, $templateDir = false, $theme = 'bootstrap') {
        $this->db = $db;
        $this->config = $config;
        $this->user = $user;
        $this->template = $template;
        $this->template->addTemplateDir((is_string($templateDir) ? $templateDir : str_replace(basename(__DIR__), '', dirname(__FILE__)).'templates'.DIRECTORY_SEPARATOR.$theme), 'hazard');
        if(!session_id()){
            if(defined(SESSION_NAME)){session_name(SESSION_NAME);}
            session_set_cookie_params(0, '/', '.'.(defined('DOMAIN') ? DOMAIN : str_replace(['http://', 'https://', 'www.'], '', $_SERVER['SERVER_NAME'])), (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? true : false),  (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? true : false));
            session_start();
        }
        if(is_numeric($userID)){$this->userClone = (int) $userID;}
    }
    
    /**
     * Returns the userID or the mock userID if you wish to look at users progress
     * @return int Returns the UserID or mocked up userID if valid
     */
    public function getUserID(){
        if(is_int($this->userClone)){
            return $this->userClone;
        }
        return $this->user->getUserID();
    }
    
    /**
     * Creates the hazard perception test HTML code
     * @param int $testNo This should be the test number
     * @param boolean $report If you are generating the report for the test should be true else should be false
     * @param int|false $prim The prim number to start the report on, if not in a report should be false
     * @return string Returns the HTML code for the current HTML test
     */
    public function createTest($testNo = 1, $report = false, $prim = false) {
        $this->setTestID($testNo);
        $this->user->checkUserAccess($testNo);
        if(!$this->anyCompleteTests() || $this->confirm || $report === true) {
            $this->report = $report;
            if($report === false){$this->chooseVideos($testNo);}
            else{$this->getUserProgress($testNo);}
            return $this->buildTest($prim);
        }
        $this->template->assign('status', $this->testStatus(), true);
        return $this->template->fetch('test-complete.tpl');
    }
    
    /**
     * Returns the user progress for a given test ID
     * @param int $testID This should be the test ID that you wish to retrieve the users progress for
     * @return array Returns the users test progress as an array
     */
    public function getUserProgress($testID) {
        if($this->getSessionInfo()) {
            return $this->getSessionInfo();
        }
        $userProgress = $this->db->select($this->config->table_hazard_progress, ['user_id' => $this->getUserID(), 'test_id' => $testID, 'test_type' => $this->getTestType()]);
        $_SESSION['hptest'.$this->getTestID()] = unserialize(stripslashes($userProgress['progress']));
        return $_SESSION['hptest'.$this->getTestID()];
    }
    
    /**
     * Retrieves the test information from the current session
     * @return array|false
     */
    public function getSessionInfo() {
        return isset($_SESSION['hptest'.$this->getTestID()]) ? $_SESSION['hptest'.$this->getTestID()] : false;
    }
    
    /**
     * Choose the videos for the given Hazard Perception Test number
     * @param int $testNo This should be the Test ID
     * @return void nothing is returned
     */
    protected function chooseVideos($testNo) {
        $videos = $this->db->selectAll($this->config->table_hazard_videos, ['hptestno' => $testNo], '*', ['hptestposition' => 'ASC']);
        if($this->report === false) {
            unset($_SESSION['hptest'.$testNo]);
            $this->db->delete($this->config->table_hazard_progress, ['user_id' => $this->getUserID(), 'test_id' => $testNo, 'test_type' => $this->getTestType()]);
        }
        $this->setVideos($videos, $testNo);
    }
    
    /**
     * Sets the videos in the current session
     * @param array $videos This should be an array of the video information
     * @param int This should be the current test number
     */
    protected function setVideos($videos, $testNo) {
        $v = 1;
        foreach($videos as $video) {
            $this->userAnswers[$v]['id'] = $video['id'];
            $_SESSION['hptest'.$testNo]['videos'][$v] = $video['id'];
            $v++;
        }
    }
    
    /**
     * Sets the variable to confirm the user wishes to override the test
     */
    public function confirmOverride() {
        $this->confirm = true;
    }
    
    /**
     * Sets the current test ID
     * @param int $testID This should be the current test ID
     */
    public function setTestID($testID) {
        $this->testID = intval($testID);
        return $this;
    }
    
    /**
     * Returns the current test ID
     * @return int Returns the current test ID
     */
    protected function getTestID() {
        return $this->testID;
    }
    
    /**
     * Sets the passmark for the test
     * @param int $passmark This should be the score the user needs to get to pass the test
     * @return $this
     */
    public function setPassmark($passmark) {
        $this->passmark = intval($passmark);
        return $this;
    }
    
    /**
     * Returns the passmark the user need to get to pass the test
     * @return int This is the number the user needs to score to pass
     */
    public function getPassmark() {
        return $this->passmark;
    }
    
    /**
     * The location where the videos are located (can be absolute or root path)
     * @param string $location The path to the video clips
     * @return $this
     */
    public function setVidLocation($location) {
        $this->videoLocation = $location;
        return $this;
    }
    
    /**
     * Returns the video path
     * @return string This is the path to where the videos are located (minus the mp4 and ogv)
     */
    public function getVidLocation() {
        return $this->videoLocation;
    }
    
    /**
     * Sets the location where the JavaScript files can be found
     * @param string $location The should either be a URL or a relative position
     * @return $this
     */
    public function setJavascriptLocation($location) {
        $this->javascriptLocation = $location;
        return $this;
    }
    
    /**
     * Returns the currents set location of the JavaScript files
     * @return string This should be the folder where all the JavaScript files can be found
     */
    public function getJavascriptLocation() {
        return $this->javascriptLocation;
    }
    
    /**
     * Sets the location for any image files
     * @param string $location This should either be the URL or the relative path of the image directory 
     * @return $this
     */
    public function setImageLocation($location) {
        $this->imgLocation = $location;
        return $this;
    }
    
    /**
     * Returns the image directory path
     * @return string Returns the image directory path
     */
    public function getImageLocation() {
        return $this->imgLocation;
    }
    
    /**
     * Sets the user type
     * @param string $type This should be the type of user in the database to check for upgrade status
     * @return $this
     */
    public function setUserType($type) {
        $this->userType = $type;
        return $this;
    }
    
    /**
     * Gets the user type to check for upgrade status
     * @return string This should be the field name to check for the upgrade status (currently account, adi, fleet, bike)
     */
    public function getUserType() {
        return $this->userType;
    }
    
    /**
     * Sets the test type
     * @param string $type Sets the type of test the user is taking (car, bike, fleet, adi)
     * @return $this
     */
    public function setTestType($type) {
        $this->testType = strtoupper($type);
        return $this;
    }
    
    /**
     * Returns the current type of test the user is taking
     * @return string Will return the test type
     */
    public function getTestType() {
        return strtoupper($this->testType);
    }
    
    /**
     * Returns the HTML code for the previous video button
     * @param int $videoID This should be the current video id
     * @return string|int Returns the previous video id or none
     */
    protected function prevVideo($videoID) {
        $prevID = ($this->currentVideoNo($videoID) - 1);
        if($prevID >= 1) {return $this->getSessionInfo()['videos'][$prevID];}
        return 'none';
    }
    
    /**
     * Returns the HTML code for the next video button
     * @param int $videoID This should be the current video id
     * @return string|int Returns the next video id or none
     */
    protected function nextVideo($videoID) {
        $nextID = ($this->currentVideoNo($videoID) + 1);
        if($nextID <= 14) {return $this->getSessionInfo()['videos'][$nextID];}
        return 'none';
    }
    
    /**
     * Returns the current question number for a given video prim number
     * @param int $videoID The current video prim number
     * @return int|boolean Returns the current video id if progress exists else returns false
     */
    protected function currentVideoNo($videoID) {
        if(isset($this->currentVideo[$videoID]) && is_numeric($this->currentVideo[$videoID])){
            return $this->currentVideo[$videoID];
        }
        foreach($this->getSessionInfo()['videos'] as $number => $value) {
            if($value == $videoID) {
                $this->currentVideo[$videoID] = intval($number);
                return $this->currentVideo[$videoID];
            }
        }
        return 1;
    }
    
    /**
     * Returns the video information from the database for a given video ID
     * @param int $videoID This should be the id number for the video you wish to retrieve the video information for
     * @return array The video information will be returned as an array
     */
    protected function getVideoInfo($videoID) {
        $this->videoInfo = $this->db->select($this->config->table_hazard_videos, ['id' => $videoID]);
        return $this->videoInfo;
    }
    
    /**
     * Returns the title for the video
     * @param int $videoID This should be the ID of the video you are getting the information for
     * @return string|false If information exists with will be returned as a string else will return false
     */
    protected function getVideoName($videoID) {
        $this->getVideoInfo($videoID);
        return $this->videoInfo['reference'];
    }
    
    /**
     * Gets the video information and puts in into a string of HTML code ready to render
     * @param int $videoID This should be the ID of the video you wish to get the HTML code for
     * @return array Returns the video information
     */
    private function getVideo($videoID) {
        $this->videoInfo['videoName'] = $this->getVideoName($videoID);
        $this->videoInfo['videoLocation'] = $this->getVidLocation();
        return $this->videoInfo;
    }
    
    /**
     * Returns the required JavaScript files as a HTML code string
     * @return string Returns the required JavaScript file location
     */
    protected function getScript() {
        if($this->report === false) {return $this->getJavascriptLocation().'hazard-perception-'.$this->scriptVar.'.js';}
        return $this->getJavascriptLocation().'hazard-report-'.$this->scriptVar.'.js';
    }
    
    /**
     * Adds a flag at a given point on the Hazard Test
     * @param int|false $clickTime The time the click was made or false if anti-cheat activated
     * @param int $videoID The ID of the video where the click is being added to
     * @return void Nothing is returned
     */
    public function addFlag($clickTime, $videoID) {
        $this->getUserProgress($this->getTestID());
        $questionNo = $this->currentVideoNo($videoID);
        $clicks = unserialize($this->getSessionInfo()[$questionNo]['clicks']);
        $clicks[] = $clickTime;
        $_SESSION['hptest'.$this->getTestID()][$questionNo]['clicks'] = serialize(array_filter($clicks));
    }
    
    /**
     * Updates the database to show that cheating was detected for the current question
     * @param int $videoID The ID of the video which cheating was detected
     * @param int $score The score which should be added to the video depending on the type of cheating
     */
    public function cheatDetected($videoID, $score) {
        $questionNo = $this->currentVideoNo($videoID);
        $_SESSION['hptest'.$this->getTestID()][$questionNo]['score'] = $score;
        $this->addFlag(false, $videoID);
    }
    
    /**
     * Mark the current test video
     * @param int $videoID The ID of the video you wish to mark and update the database
     * @return void Nothing is returned
     */
    public function markVideo($videoID) {
        $this->getVideoInfo($videoID);
        $this->getUserProgress($this->getTestID());
        $questionNo = $this->currentVideoNo($videoID);
        if($this->getSessionInfo()[$questionNo]['score'] >= 0) {
            $clicks = unserialize($this->getSessionInfo()[$questionNo]['clicks']);
            $score = false;
            $secscore = false;
            foreach($clicks as $click) {
                if($score === false && $click >= $this->videoInfo['five'] && $click <= $this->videoInfo['endseq']) {
                    $score = $this->markHazard($click);
                    $_SESSION['hptest'.$this->getTestID()]['totalscore'] = intval($this->getSessionInfo()['totalscore']) + $score;
                    $_SESSION['hptest'.$this->getTestID()][$questionNo]['score'] = intval($score);
                }
                if($secscore === false && $this->videoInfo['nohazards'] == 2 && $click >= $this->videoInfo['ten'] && $click <= $this->videoInfo['endseq2']) {
                    $secscore = $this->markHazard($click, 2);
                    $_SESSION['hptest'.$this->getTestID()]['totalscore'] = intval($this->getSessionInfo()['totalscore']) + $secscore;
                    $_SESSION['hptest'.$this->getTestID()]['second_score'] = intval($secscore);
                }
            }
        }
    }
    
    /**
     * Returns the mark for the click location
     * @param int $click The click location to score
     * @param int $winNo
     * @return int|boolean If the click scores any marks that score will be returned else will return false
     */
    protected function markHazard($click, $winNo = 1) {
        $score = 5;
        for($h = 0; $h <= 4; $h++) {
            if($click >= $this->videoInfo[$this->windows[$winNo][$h]] && $click < $this->videoInfo[$this->windows[$winNo][($h + 1)]]) {return $score;}
            $score--;
        }
        return false;
    }
    
    /**
     * Creates the HTML code for the current test
     * @param int $prim This should be the prim number of the first question
     * @param boolean $review If the user is in the review section should be set to true else should be false
     * @return string|false Returns the HTML code and question number as a JSON encoded string if that prim number exists else return false
     */
    public function createHTML($prim, $review = false) {
        $this->report = $review;
        if(is_numeric($prim)) {
            $videoInfo = $this->getVideoInfo($prim);
            $this->template->assign('videotitle', $videoInfo['title']."<br />".$videoInfo['title2']);
            $this->template->assign('videodesc', nl2br($videoInfo['description']."\r\n\r\n".$videoInfo['description2']));
            $this->template->assign('ratio', $videoInfo['ratio']);
            $this->template->assign('test_id', $this->testID);
            $this->template->assign('no_questions', $this->numVideos);
            $this->template->assign('your_score', $this->clipScore($prim));
            $this->template->assign('anti_cheat', $this->anyCheating($prim));
            $this->template->assign('question_no', $this->currentVideoNo($prim));
            $this->template->assign('vid_id', $prim);
            $this->template->assign('video', $this->getVideo($prim));
            $this->template->assign('prev_question', $this->prevVideo($prim));
            $this->template->assign('next_question', $this->nextVideo($prim));
            $this->template->assign('score_window', $this->buildScoreWindow());
            $this->template->assign('review_flags', $this->getReviewFlags($prim));
            $this->template->assign('script', $this->getScript());
            $this->template->assign('testID', $this->getTestID());
            $this->template->assign('imagePath', $this->getImageLocation());
            
            $this->videodata = ($this->report === false ? $this->template->fetch('hazlayout.tpl') : $this->template->fetch('hazlayoutreport.tpl'));
            return json_encode(['html' => $this->videodata, 'questionnum' => $this->currentVideoNo($prim)]);
        }
        return false;
    }
    
    /**
     * Converts the number to 3 decimal places used for the time
     * @param string $num This is the number you wish to convert to 3 decimal places
     * @return string Returns the number as a decimal with 3 decimal places
     */
    private function dec($num) {
        return number_format($num, 3);
    }
    
    /**
     * Checks to see if the user has already completed this test
     * @return boolean Returns true if test already exist
     */
    protected function anyCompleteTests() {
        return $this->db->select($this->config->table_hazard_progress, ['user_id' => $this->getUserID(), 'test_id' => $this->testID, 'test_type' => $this->getTestType()]);
    }
    
    /**
     * Returns the score window HTML code ready to be displayed
     * @param int $winNo The score window number for the hazards i.e. 1st or 2nd hazard
     * @return string Returns the score window HTML code
     */
    public function buildScoreWindow($winNo = 1) {
        $windows = [];
        $widthperc = (100 / $this->videoInfo['endClip']);
        if($this->videoInfo[$this->windows[$winNo][6]] != NULL) {
            $margin1 = ($winNo === 1 ? $this->dec(($this->videoInfo[$this->windows[$winNo][6]] / $this->videoInfo['endClip']) * 100) : $this->dec((($this->videoInfo[$this->windows[$winNo][6]] / $this->videoInfo['endClip']) - ($this->videoInfo[$this->windows[1][5]] / $this->videoInfo['endClip'])) * 100));
            $prewidth = $this->dec(($this->videoInfo[$this->windows[$winNo][0]] - $this->videoInfo[$this->windows[$winNo][6]]) * $widthperc);
            $windows['pre'.$winNo] = ['name' => 'pre'.$winNo, 'left' => $margin1, 'width' => $prewidth, 'score' => 0];
            $marginleft = 0;
        }
        else{
            $marginleft = ($winNo === 1 ? (($this->videoInfo[$this->windows[$winNo][0]] / $this->videoInfo['endClip']) * 100) : $this->dec((($this->videoInfo[$this->windows[$winNo][0]] / $this->videoInfo['endClip']) - ($this->videoInfo[$this->windows[1][5]] / $this->videoInfo['endClip'])) * 100));
        }
        for($v = 0; $v <= 4; $v++) {
            $scoreend = ($v === 4 ? $this->videoInfo[$this->windows[$winNo][5]] : false);
            $windows[$this->windows[$winNo][$v]] = ['name' => $this->windows[$winNo][$v], 'left' => ($v === 0 ? $marginleft : false), 'width' => $this->dec(($this->videoInfo[$this->windows[$winNo][($v+1)]] - $this->videoInfo[$this->windows[$winNo][$v]]) * $widthperc), 'score' => $this->videoInfo[$this->windows[$winNo][$v]], 'scoreend' => $scoreend];
        }
        if($winNo === 1 && $this->videoInfo['nohazards'] == 2) {
            $windows = array_merge($windows, $this->buildScoreWindow(2));
        }
        return $windows;
    }
    
    /**
     * Returns the flags to be displayed on the score window
     * @param int $videoID The video ID to get the score window for
     * @return array|false This should be the flag information for margin-left and click
     */
    protected function getReviewFlags($videoID) {
        $clicks = (isset($this->getSessionInfo()[$this->currentVideoNo($videoID)]['clicks']) ? unserialize($this->getSessionInfo()[$this->currentVideoNo($videoID)]['clicks']) : false);
        if(is_array($clicks)) {
            $flags = [];
            foreach($clicks as $i => $click) {
                $flags[($i + 1)] = [
                    'left' => ((($click / $this->videoInfo['endClip']) * 100) - 0.4),
                    'click' => $click
                ];
            }
            return $flags;
        }
        return false;
    }
    
    /**
     * Will return the score HTML code test
     * @param int $prim The video ID you are getting the score HTML for
     * @return array Returns the score marks as an array item per hazard
     */
    protected function clipScore($prim) {
        $clipNo = $this->currentVideoNo($prim);
        $this->getUserProgress($this->getTestID());
        $vidInfo = $this->getVideoInfo($prim);
        $score = [];
        if(isset($this->getSessionInfo()[$clipNo]['score'])) {
            if($this->getSessionInfo()[$clipNo]['score'] < 0) {$score[] = 0;}
            else{$score[] = intval($this->getSessionInfo()[$clipNo]['score']);}
        }
        else{$score[] = 0;}
        if($vidInfo['nohazards'] != 1) {$score[] = intval(isset($this->getSessionInfo()['second_score']) ? $this->getSessionInfo()['second_score'] : 0);}
        return $score;
    }
    
    /**
     * Checks to see if the ant cheat was activated to the given video ID
     * @param int $prim This should be the video ID
     * @return string|false If the anti-cheat was activated a string of HTML code will be returned else will return false
     */
    protected function anyCheating($prim) {
        $clipNo = $this->currentVideoNo($prim);
        $this->getUserProgress($this->getTestID());
        if(isset($this->getSessionInfo()[$clipNo]['score'])) {
            if($this->getSessionInfo()[$clipNo]['score'] == -1) {return true;}
        }
        return false;
    }
    
    /**
     * Load a video rather than build a test from the start
     * @param int $testID The Test id number
     * @param int $videoID The Video ID number 
     * @return string Returns the Hazard Perception Test HTML code
     */
    public function loadNextVideo($testID, $videoID) {
        $this->setTestID($testID);
        $this->report = false;
        return $this->buildTest($videoID);
    }
    
    /**
     * Builds the test and sets the required template values
     * @param int|false $prim The prim number normally for the first question in the test
     * @return string Returns the Hazard Perception Test HTML code
     */
    protected function buildTest($prim = false) {
        if(!is_numeric($prim)) {$prim = $this->userAnswers[1]['id'];}
        $this->createHTML($prim, $this->report);
        
        $this->template->assign('question_no', $this->currentVideoNo($prim));
        $this->template->assign('no_questions', $this->numVideos);
        $this->template->assign('video_data', $this->videodata);
        if($this->report === false) {return $this->template->fetch('hazardtest.tpl');}
        return $this->template->fetch('hazardtestreport.tpl');
    }
    
    /**
     * Creates the report information for a given testID
     * @param int $testID This should be the testID you wish to retrieve the report information for
     * @return string This will return the retort HTML code ready to be rendered in the page
     */
    public function createReport($testID) {
        $this->setTestID($testID);
        if($this->anyCompleteTests()) {
            return $this->endTest(false);
        }
        return $this->template->fetch('report'.DIRECTORY_SEPARATOR.'report-unavail.tpl');
    }
    
    /**
     * Returns the test status either passed or failed
     * @return string Returns either passed or failed depending on how the user did on the test
     */
    public function testStatus() {
        $testInfo = $this->anyCompleteTests();
        if($testInfo['status'] == 1) {return 'passed';}
        return 'failed';
    }
    
    /**
     * Ends the current test an starts the marking process if required
     * @param boolean $mark If the test needs marking set to true else should be false
     * @return string Returns the end test report HTML ready to be rendered
     */
    public function endTest($mark) {
        $this->getUserProgress($this->getTestID());
        if($mark === true) {
            unset($this->getSessionInfo()['totalscore']);
            for($i = 1; $i <= $this->numVideos; $i++) {
                $this->markVideo($this->getSessionInfo()['videos'][$i]);
            }
            $this->userprogress = false;
        }
        $score = 0;
        $windows = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $videos = [];
        for($i = 1; $i <= $this->numVideos; $i++) {
            $videoID = $this->getSessionInfo()['videos'][$i];
            $info = $this->getVideoInfo($videoID);
            $scoreInfo = $this->videoScore($i, $info['nohazards']);
            $videos[$i]['id'] = $videoID;
            $videos[$i]['no'] = $i;
            $videos[$i]['description'] = $info['title'];
            $videos[$i]['score'] = $scoreInfo['text_score'];
            $windows[$scoreInfo['score']]++;
            if(isset($scoreInfo['second_score'])) {$windows[$scoreInfo['second_score']]++;}
            $score = $score + intval($scoreInfo['score']) + intval(isset($scoreInfo['second_score']) ? $scoreInfo['second_score'] : 0);
        }
        if($mark === true) {
            if($score >= $this->getPassmark()) {$this->status = 1;}else{$this->status = 2;}
            $this->getSessionInfo()['totalscore'] = $score;
            $this->addResultsToDB();
        }
        $this->template->assign('windows', $windows);
        $this->template->assign('score', $score);
        $this->template->assign('passmark', $this->getPassmark());
        $this->template->assign('videos', $videos);
        $this->template->assign('testID', $this->getTestID());
        return $this->template->fetch('hazresult.tpl');
    }
    
    /**
     * Sets the correct score for the videos and removes and minus figures added for skipped clips or anti-cheat activations
     * @param int $i This should be the number of the video
     * @param int $hazards This should be the number of hazards that the video contains
     * @return array 
     */
    protected function videoScore($i, $hazards) {
        $videos = [];
        $first_score = intval(isset($this->getSessionInfo()[$i]['score']) ? $this->getSessionInfo()[$i]['score'] : 0);
        if($hazards == 1) {
            $videos['text_score'] = ($first_score < 0 ? 0 : $first_score);
            $videos['score'] = $videos['text_score'];
            unset($videos['second_score']);
        }
        else{
            if($first_score < 0) {
                $score1 = 0;
                $score2 = 0;
            }
            else{
                $score1 = $first_score;
                $score2 = intval(isset($this->getSessionInfo()['second_score']) ? $this->getSessionInfo()['second_score'] : 0);
            }
            $videos['text_score'] = $score1.' + '.$score2;
            $videos['score'] = $score1;
            $videos['second_score'] = $score2;
        }
        $videos['status'] = $this->videoStatus($first_score);
        return $videos;
    }
    
    /**
     * returns the status of the video if there is one else returns false
     * @param int $score This should be the score assigns to the first score window
     * @return boolean|string
     */
    protected function videoStatus($score) {
        if($score == '-2') {return 'Skipped';}
        elseif($score == '-1') {return 'Cheat';}
        return false;
    }

    /**
     * Inserts the users results into the database
     */
    protected function addResultsToDB() {
        $this->db->delete($this->config->table_hazard_progress, ['user_id' => $this->getUserID(), 'test_id' => $this->getTestID(), 'test_type' => $this->getTestType()]); // Delete old tests
        $this->db->insert($this->config->table_hazard_progress, ['user_id' => $this->getUserID(), 'test_id' => $this->getTestID(), 'progress' => serialize($this->getSessionInfo()), 'test_type' => $this->getTestType(), 'status' => $this->status]);
    }
}