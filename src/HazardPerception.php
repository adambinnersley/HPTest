<?php
namespace HPTest;

use HPTest\Essential\HPInterface;
use DBAL\Database;
use Smarty;

class HazardPerception implements HPInterface{
    protected static $template;
    protected static $db;
    protected static $user;
    protected $userClone = false;
    protected $userprogress = false;

    public $videosTable = 'hazard_clips_new';
    public $progressTable = 'user_hazard_progress';
    
    public $testID = 1;
    public $numVideos = 14;
    public $passmark = 44;
    
    protected $userAnswers;
    protected $status;
    
    public $javascriptLocation = '/js/theory/';
    
    public $videoLocation = '/videos/';
    protected $videoInfo;
    protected $videodata;
    protected $scriptVar = 'hazupdate';
    
    protected $report = true;
    protected $confirm = false;
    
    protected $userType = 'account';
    protected $testType = 'CAR';
    
    protected $windows = array(
        1 => array('five', 'four', 'three', 'two', 'one', 'endseq', 'prehazard'),
        2 => array('ten', 'nine', 'eight', 'seven', 'six', 'endseq2', 'prehazard2')
    );

    /**
     * Sets the required variables for the test to be rendered
     * @param Database $db This should be an instance of Database
     * @param Smarty $template This should be the instance of Smarty Templating
     * @param object $user This should be an instance of User class
     * @param int|false If you want to emulate a user set this here
     * @param string|false If you want to change the template location set this location here else set to false
     */
    public function __construct(Database $db, Smarty $template, $user, $userID = false, $templateDir = false) {
        self::$db = $db;
        self::$user = $user;
        self::$template = $template;
        self::$template->addTemplateDir($templateDir === false ? str_replace(basename(__DIR__), '', dirname(__FILE__)).'templates' : $templateDir);
        if(!session_id()){session_start();}
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
        return self::$user->getUserID();
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
        self::$user->checkUserAccess($testNo);
        if(!$this->anyCompleteTests() || $this->confirm || $report === true) {
            $this->report = $report;
            if($report === false){$this->chooseVideos($testNo);}
            else{$this->getUserProgress($testNo);}
            return $this->buildTest($prim);
        }
        else{
            self::$template->assign('status', $this->testStatus(), true);
            return self::$template->fetch('test-complete.tpl');
        }
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
        else{
            $userProgress = self::$db->select($this->getProgressTable(), array('user_id' => $this->getUserID(), 'test_id' => $testID, 'test_type' => $this->getTestType()));
            $_SESSION['hptest'.$this->getTestID()] = unserialize(stripslashes($userProgress['progress']));
            return $_SESSION['hptest'.$this->getTestID()];
        }
    }
    
    /**
     * Retrieves the test information from the current session
     * @return array
     */
    public function getSessionInfo() {
        return $_SESSION['hptest'.$this->getTestID()];
    }
    
    /**
     * Choose the videos for the given Hazard Perception Test number
     * @param int $testNo This should be the Test ID
     * @return void nothing is returned
     */
    protected function chooseVideos($testNo) {
        $videos = self::$db->selectAll($this->getVideoTable(), array('hptestno' => $testNo), '*', array('hptestposition' => 'ASC'));
        if($this->report === false) {
            unset($_SESSION['hptest'.$testNo]);
            self::$db->delete($this->getProgressTable(), array('user_id' => $this->getUserID(), 'test_id' => $testNo, 'test_type' => $this->getTestType()));
        }
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
     * Sets the table name to look in for the list of videos
     * @param string $table This should be the table name where the videos are located
     * @return $this
     */
    public function setVideoTable($table) {
        $this->videosTable = $table;
        return $this;
    }
    
    /**
     * Return the videos table where the videos are located
     * @return string This will be the table name where the videos are
     */
    public function getVideoTable() {
        return $this->videosTable;
    }
    
    /**
     * Sets the table name where the users progress will be stored
     * @param string $table Sets the table name where the videos are store
     * @return $this
     */
    public function setProgressTable($table) {
        $this->progressTable = $table;
        return $this;
    }
    
    /**
     * Returns the table where the progress is stored
     * @return string This is the table where the progress is stored 
     */
    public function getProgressTable() {
        return $this->progressTable;
    }
    
    /**
     * Returns the HTML code for the previous video button
     * @param int $videoID This should be the current video id
     * @return string Returns the previous video button HTML code
     */
    protected function prevVideo($videoID) {
        $prevID = ($this->currentVideoNo($videoID) - 1);
        if($prevID >= 1) {$vidID = $this->getSessionInfo()['videos'][$prevID];}
        else{$vidID = 'none';}
        return '<div id="'.$vidID.'" class="prevvideo"><span>Prev Clip</span></div>';
    }
    
    /**
     * Returns the HTML code for the next video button
     * @param int $videoID This should be the current video id
     * @return string Returns the next video button HTML code
     */
    protected function nextVideo($videoID) {
        $nextID = ($this->currentVideoNo($videoID) + 1);
        if($nextID <= 14) {$vidID = $this->getSessionInfo()['videos'][$nextID];}
        else{$vidID = 'none';}
        if(filter_input(INPUT_GET, 'review')) {
            return '<div id="'.$vidID.'" class="nextvideo"><span class="sr-only">Skip Clip</span></div>';
        }
        else{
            return '<div id="'.$vidID.'" class="nextvideo"><div class="showbtn"></div>Skip Clip</div>';
        }
    }
    
    /**
     * Returns the current question number for a given video prim number
     * @param int $videoID The current video prim number
     * @return int|boolean Returns the current video id if progress exists else returns false
     */
    protected function currentVideoNo($videoID) {
        foreach($this->getSessionInfo()['videos'] as $number => $value) {
            if($value == $videoID) {return intval($number);}
        }
        return false;
    }
    
    /**
     * Returns the video information from the database for a given video ID
     * @param int $videoID This should be the id number for the video you wish to retrieve the video information for
     * @return array The video information will be returned as an array
     */
    protected function getVideoInfo($videoID) {
        $this->videoInfo = self::$db->select($this->getVideoTable(), array('id' => $videoID));
        return $this->videoInfo;
    }
    
    /**
     * Returns the title for the video
     * @param int $videoID This should be the ID of the video you are getting the information for
     * @return string|false If information exists with will be returned as a string else will return false
     */
    protected function getVideoName($videoID) {
        $this->getVideoInfo($videoID);
        return strtolower($this->videoInfo['reference']);
    }
    
    /**
     * Gets the video information and puts in into a string of HTML code ready to render
     * @param int $videoID This should be the ID of the video you wish to get the HTML code for
     * @return string Returns the HTML code as a string for the given video
     */
    private function getVideo($videoID) {
        $videoName = $this->getVideoName($videoID);
        return '<div id="video_overlay"><div id="icon"><img src="/images/hloading.gif" alt="Loading" width="100" height="100" /></div></div><video width="544" height="408" id="video" class="video" data-duration="'.$this->videoInfo['endClip'].'" preload="auto" muted playsinline webkit-playsinline><source src="'.$this->videoLocation.'mp4/'.$videoName.'.mp4" type="video/mp4" /><source src="'.$this->videoLocation.'ogv/'.$videoName.'.ogv" type="video/ogg" /></video>';
    }
    
    /**
     * Returns the required JavaScript files as a HTML code string
     * @return string Returns the required JavaScript files as a HTML code string ready to be output
     */
    protected function getScript() {
        if($this->report === false) {return '<script type="text/javascript" src="'.$this->getJavascriptLocation().'hazard-perception-'.$this->scriptVar.'.js"></script>';}
        else{return '<script type="text/javascript" src="'.$this->getJavascriptLocation().'hazard-report-'.$this->scriptVar.'.js"></script>';}
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
                    $_SESSION['hptest'.$this->getTestID()]['totalscore'] = intval($_SESSION['hptest'.$this->getTestID()]['totalscore']) + $score;
                    $_SESSION['hptest'.$this->getTestID()][$questionNo]['score'] = intval($score);
                }
                if($secscore === false && $this->videoInfo['nohazards'] == 2 && $click >= $this->videoInfo['ten'] && $click <= $this->videoInfo['endseq2']) {
                    $secscore = $this->markHazard($click, 2);
                    $_SESSION['hptest'.$this->getTestID()]['totalscore'] = intval($_SESSION['hptest'.$this->getTestID()]['totalscore']) + $secscore;
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
            self::$template->assign('videotitle', $videoInfo['title']."<br />".$videoInfo['title2']);
            self::$template->assign('videodesc', nl2br($videoInfo['description']."\r\n\r\n".$videoInfo['description2']));
            self::$template->assign('no_questions', $this->numVideos);
            self::$template->assign('your_score', $this->clipScore($prim));
            self::$template->assign('anti_cheat', $this->anyCheating($prim));
            self::$template->assign('question_no', $this->currentVideoNo($prim));
            self::$template->assign('vid_id', $prim);
            self::$template->assign('video', $this->getVideo($prim));
            self::$template->assign('prev_question', $this->prevVideo($prim));
            self::$template->assign('next_question', $this->nextVideo($prim));
            self::$template->assign('score_window', $this->buildScoreWindow());
            self::$template->assign('review_flags', $this->getReviewFlags($prim));
            self::$template->assign('script', $this->getScript());
            self::$template->assign('testID', $this->getTestID());
            
            $this->videodata = ($this->report === false ? self::$template->fetch('hazlayout.tpl') : self::$template->fetch('hazlayoutreport.tpl'));
            return json_encode(array('html' => $this->videodata, 'questionnum' => $this->currentVideoNo($prim)));
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
        return self::$db->select($this->getProgressTable(), array('user_id' => $this->getUserID(), 'test_id' => $this->testID, 'test_type' => $this->getTestType()));
    }
    
    /**
     * Returns the score window HTML code ready to be displayed
     * @param int $winNo The score window number for the hazards i.e. 1st or 2nd hazard
     * @return string Returns the score window HTML code
     */
    public function buildScoreWindow($winNo = 1) {
        $widthperc = (100 / $this->videoInfo['endClip']);
        if($this->videoInfo[$this->windows[$winNo][6]] != NULL) {
            $margin1 = ($winNo === 1 ? $this->dec(($this->videoInfo[$this->windows[$winNo][6]] / $this->videoInfo['endClip']) * 100) : $this->dec((($this->videoInfo[$this->windows[$winNo][6]] / $this->videoInfo['endClip']) - ($this->videoInfo[$this->windows[1][5]] / $this->videoInfo['endClip'])) * 100));
            $prewidth = $this->dec(($this->videoInfo[$this->windows[$winNo][0]] - $this->videoInfo[$this->windows[$winNo][6]]) * $widthperc);
            $pre = '<div id="pre'.$winNo.'" style="margin-left:'.$margin1.'%;width:'.$prewidth.'%"></div>';
            $marginleft = 0;
        }
        else{
            $marginleft = ($winNo === 1 ? (($this->videoInfo[$this->windows[$winNo][0]] / $this->videoInfo['endClip']) * 100) : $this->dec((($this->videoInfo[$this->windows[$winNo][0]] / $this->videoInfo['endClip']) - ($this->videoInfo[$this->windows[1][5]] / $this->videoInfo['endClip'])) * 100));
            $pre = '';
        }
        for($v = 0; $v <= 4; $v++) {
            $pre.= '<div id="'.$this->windows[$winNo][$v].'" style="'.($v === 0 ? 'margin-left:'.$marginleft.'%;' : '').'width:'.$this->dec(($this->videoInfo[$this->windows[$winNo][($v+1)]] - $this->videoInfo[$this->windows[$winNo][$v]]) * $widthperc).'%" data-score="'.$this->videoInfo[$this->windows[$winNo][$v]].'"'.($v === 4 ? ' data-scoreend="'.$this->videoInfo[$this->windows[$winNo][5]].'"' : '').'></div>';
        }
        if($winNo === 1 && $this->videoInfo['nohazards'] == 2) {
            $pre.= $this->buildScoreWindow(2);
        }
        return $pre;
    }
    
    /**
     * Returns the flags to be displayed on the score window
     * @param int $videoID The video ID to get the score window for
     * @return string This should be the flay with the styling added to the HTML code
     */
    protected function getReviewFlags($videoID) {
        $clicks = unserialize($this->getSessionInfo()[$this->currentVideoNo($videoID)]['clicks']);
        $flags = '';
        if(is_array($clicks)) {
            foreach($clicks as $i => $click) {
                $marginleft = ((($click / $this->videoInfo['endClip']) * 100) - 0.4);
                $flags.= '<img src="/images/hpflag.png" alt="Flag" width="20" height="20" id="flag'.($i + 1).'" class="reviewflag" style="left:'.$marginleft.'%" data-click="'.$click.'" />';
            }
        }
        return $flags;
    }
    
    /**
     * Will return the score HTML code test
     * @param int $prim The video ID you are getting the score HTML for
     * @return string Returns the score text HTML code
     */
    protected function clipScore($prim) {
        $clipNo = $this->currentVideoNo($prim);
        $this->getUserProgress($this->getTestID());
        $vidInfo = $this->getVideoInfo($prim);
        if($this->getSessionInfo()[$clipNo]['score'] < 0) {$score = 0;}
        else{$score = $this->getSessionInfo()[$clipNo]['score'];}
        if($vidInfo['nohazards'] == 1) {return '<div class="yourscore">You scored '.intval($score).' for this hazard</div>';}
        else{return '<div class="yourscore">You scored '.intval($score).' for the first hazard and '.intval($this->getSessionInfo()['second_score']).' for the second hazard</div>';}
    }
    
    /**
     * Checks to see if the ant cheat was activated to the given video ID
     * @param int $prim This should be the video ID
     * @return string|false If the anti-cheat was activated a string of HTML code will be returned else will return false
     */
    protected function anyCheating($prim) {
        $clipNo = $this->currentVideoNo($prim);
        $this->getUserProgress($this->getTestID());
        if($this->getSessionInfo()[$clipNo]['score'] == -1) {return '<div id="anticheat">Anti-Cheat Activated</div>';}
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
        
        self::$template->assign('question_no', $this->currentVideoNo($prim));
        self::$template->assign('no_questions', $this->numVideos);
        self::$template->assign('video_data', $this->videodata);
        if($this->report === false) {return self::$template->fetch('hazardtest.tpl');}else{return self::$template->fetch('hazardtestreport.tpl');}
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
        return self::$template->fetch('report'.DIRECTORY_SEPARATOR.'report-unavail.tpl');
    }
    
    /**
     * Returns the test status either passed or failed
     * @return string Returns either passed or failed depending on how the user did on the test
     */
    public function testStatus() {
        $testInfo = $this->anyCompleteTests();
        if($testInfo['status'] == 1) {return 'passed';}
        else{return 'failed';}
    }
    
    /**
     * Ends the current test an starts the marking process if required
     * @param boolean $mark If the test needs marking set to true else should be false
     * @return string Returns the end test report HTML ready to be rendered
     */
    public function endTest($mark) {
        $this->getUserProgress($this->getTestID());
        if($mark === true) {
            for($i = 1; $i <= $this->numVideos; $i++) {
                $this->markVideo($this->getSessionInfo()['videos'][$i]);
            }
            $this->userprogress = false;
        }
        $score = 0;
        $windows = array();
        $videos = array();
        for($i = 1; $i <= $this->numVideos; $i++) {
            $videoID = $this->getSessionInfo()['videos'][$i];
            $info = $this->getVideoInfo($videoID);
            $scoreInfo = $this->videoScore($i, $info['nohazards']);
            $videos[$i]['id'] = $videoID;
            $videos[$i]['no'] = $i;
            $videos[$i]['description'] = $info['title'];
            $videos[$i]['score'] = $scoreInfo['text_score'];
            $windows[$scoreInfo['score']]++;
            if($scoreInfo['second_score']) {$windows[$scoreInfo['second_score']]++;}
            $score = $score + intval($scoreInfo['score']) + intval($scoreInfo['second_score']);
        }
        if($mark === true) {
            if($score >= $this->getPassmark()) {$this->status = 1;}else{$this->status = 2;}
            $this->getSessionInfo()['totalscore'] = $score;
            $this->addResultsToDB();
        }
        self::$template->assign('windows', $windows);
        self::$template->assign('score', $score);
        self::$template->assign('passmark', $this->getPassmark());
        self::$template->assign('videos', $videos);
        self::$template->assign('testID', $this->getTestID());
        return self::$template->fetch('hazresult.tpl');
    }
    
    /**
     * Sets the correct score for the videos and removes and minus figures added for skipped clips or anti-cheat activations
     * @param int $i This should be the number of the video
     * @param int $hazards This should be the number of hazards that the video contains
     * @return array 
     */
    protected function videoScore($i, $hazards) {
        $videos = array();
        $first_score = intval($this->getSessionInfo()[$i]['score']);
        if($hazards == 1) {
            $videos['text_score'] = ($first_score < 0 ? 0 : $first_score);
            $videos['score'] = $videos['text_score'];
        }
        else{
            if($first_score < 0) {
                $score1 = 0;
                $score2 = 0;
            }
            else{
                $score1 = $first_score;
                $score2 = intval($this->getSessionInfo()['second_score']);
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
        self::$db->delete($this->getProgressTable(), array('user_id' => $this->getUserID(), 'test_id' => $this->getTestID(), 'test_type' => $this->getTestType())); // Delete old tests
        self::$db->insert($this->getProgressTable(), array('user_id' => $this->getUserID(), 'test_id' => $this->getTestID(), 'progress' => serialize($this->getSessionInfo()), 'test_type' => $this->getTestType(), 'status' => $this->status));
    }
}