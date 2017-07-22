<?php
namespace TheoryTest;

use DBAL\Database;
Use User;
/**
 * Description of HazardPerception
 *
 * @author Adam
 */

class HazardPerception{
    protected static $template;
    protected static $db;
    public static $user;
    protected $userprogress = false;

    public $videosTable = 'hazard_clips_new';
    public $progressTable = 'users_hazard_progress_new';
    
    public $testID = 1;
    public $numVideos = 14;
    public $passmark = 44;
    
    public $videoLocation = '/videos/';
    
    protected $videodata;
    protected $userAnswers;
    protected $status;
    protected $videoInfo;
    
    protected $report = true;
    protected $confirm = false;
    
    protected $userType = 'account';
    protected $testType = 'car';

    /**
     * Sets the required variables for the test to be rendered
     * @param Database $db This should be an instance of Database
     * @param resource $template This should be the template class or instance of
     */
    public function __construct(Database $db, $template){
        self::$db = $db;
        self::$user = new User($db);
        self::$template = $template;
        self::$template->addTemplateDir(FILEROOT.DS.'includes'.DS.'templates'.DS.'hazard');
    }
    
    /**
     * Creates the hazard perception test HTML code
     * @param int $testNo This should be the test number
     * @param boolean $report If you are generating the report for the test should be true else should be false
     * @param int|boolean $prim The prim number to start the report on, if not in a report should be false
     * @return string Returns the HTML code for the current HTML test
     */
    public function createTest($testNo = 1, $report = false, $prim = false){
        $this->setTestID($testNo);
        self::$user->checkUserAccess($testNo);
        if(!$this->anyCompleteTests() || $this->confirm || $report === true){
            $this->report = $report;
            $this->chooseVideos($testNo);
            return $this->buildTest($prim);
        }
        else{
            self::$template->assign('status', $this->testStatus(), true);
            return self::$template->fetch('test-complete.tpl');
        }
    }
    
    /**
     * Returns the current users ID
     * @return int Returns the current users ID
     */
    public function getUserID(){
        return self::$user->getUserID();
    }
    
    /**
     * Returns the user progress for a given test ID
     * @param int $testID This should be the test ID that you wish to retrieve the users progress for
     * @return array Returns the users test progress as an array
     */
    public function getUserProgress($testID){
        if($_SESSION['hptest'.$this->getTestID()]){
            return $_SESSION['hptest'.$this->getTestID()];
        }
        else{
            $userProgress = self::$db->select($this->progressTable, array('user_id' => $this->getUserID(), 'test_id' => $testID, 'test_type' => strtoupper($this->testType)));
            $_SESSION['hptest'.$this->getTestID()] = unserialize(stripslashes($userProgress['progress']));
            return $_SESSION['hptest'.$this->getTestID()];
        }
    }
    
    /**
     * Choose the videos for the given Hazard Perception Test number
     * @param int $testNo This should be the Test ID
     * @return void nothing is returned
     */
    protected function chooseVideos($testNo){
        $videos = self::$db->selectAll($this->videosTable, array('hptestno' => $testNo), '*', array('hptestposition' => 'ASC'));
        if($this->report == false){
            unset($_SESSION['hptest'.$testNo]);
            self::$db->delete($this->progressTable, array('user_id' => $this->getUserID(), 'test_id' => $testNo, 'test_type' => strtoupper($this->testType)));
        }
        $v = 1;
        foreach($videos as $video){
            $this->userAnswers[$v]['id'] = $video['id'];
            $_SESSION['hptest'.$testNo]['videos'][$v] = $video['id'];
            $v++;
        }
    }
    
    /**
     * Sets the variable to confirm the user wishes to override the test
     */
    public function confirmOverride(){
        $this->confirm = true;
    }
    
    /**
     * Sets the current test ID
     * @param int $testID This should be the current test ID
     */
    public function setTestID($testID){
        if(is_numeric($testID)){
            $this->testID = $testID;
        }
    }
    
    /**
     * Returns the current test ID
     * @return int Returns the current test ID
     */
    protected function getTestID(){
        return $this->testID;
    }
    
    /**
     * Returns the HTML code for the previous video button
     * @param int $videoID This should be the current video id
     * @return string Returns the previous video button HTML code
     */
    protected function prevVideo($videoID){
        $prevID = ($this->currentVideoNo($videoID) - 1);
        if($prevID >= 1){$vidID = $_SESSION['hptest'.$this->getTestID()]['videos'][$prevID];}
        else{$vidID = 'none';}
        return '<div id="'.$vidID.'" class="prevvideo"><span>Prev Clip</span></div>';
    }
    
    /**
     * Returns the HTML code for the next video button
     * @param int $videoID This should be the current video id
     * @return string Returns the next video button HTML code
     */
    protected function nextVideo($videoID){
        $nextID = ($this->currentVideoNo($videoID) + 1);
        if($nextID <= 14){$vidID = $_SESSION['hptest'.$this->getTestID()]['videos'][$nextID];}
        else{$vidID = 'none';}
        if($_GET['review']){
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
    protected function currentVideoNo($videoID){
        foreach($_SESSION['hptest'.$this->getTestID()]['videos'] as $number => $value){
            if($value == $videoID){return intval($number);}
        }
        return false;
    }
    
    /**
     * Returns the video information from the database for a given video ID
     * @param int $videoID This should be the id number for the video you wish to retrieve the video information for
     * @return array The video information will be returned as an array
     */
    protected function getVideoInfo($videoID){
        $this->videoInfo = self::$db->select($this->videosTable, array('id' => $videoID));
        return $this->videoInfo;
    }
    
    /**
     * Returns the title for the video
     * @param int $videoID This should be the ID of the video you are getting the information for
     * @return boolean|string If information exists with will be returned as a string else will return false
     */
    protected function getVideoName($videoID){
        $this->getVideoInfo($videoID);
        if($this->videoInfo['reference']){
            return strtolower($this->videoInfo['reference']);
        }
        return false;
    }
    
    /**
     * Gets the video information and puts in into a string of HTML code ready to render
     * @param int $videoID This should be the ID of the video you wish to get the HTML code for
     * @return string Returns the HTML code as a string for the given video
     */
    private function getVideo($videoID){
        if($this->report == false){
            $width = 768;//640;
            $height = 576;//480;
        }
        else{
            $width = 544;
            $height = 408;
        }
        $videoName = $this->getVideoName($videoID);
        
        return '<div id="video_overlay"><div id="icon"><img src="/images/hloading.gif" alt="Loading" width="100" height="100" /></div></div><video width="'.$width.'" height="'.$height.'" id="video" class="video" data-duration="'.$this->videoInfo['endClip'].'" preload="auto" muted webkit-playsinline><source src="'.$this->videoLocation.'mp4/'.$videoName.'.mp4" type="video/mp4" /><source src="'.$this->videoLocation.'ogv/'.$videoName.'.ogv" type="video/ogg" /></video>';
    }
    
    /**
     * Returns the required JavaScript files as a HTML code string
     * @return string Returns the required JavaScript files as a HTML code string ready to be output
     */
    protected function getScript(){
        if($this->report == false){return '<script type="text/javascript" src="/js/theory/hazard-perception-hazupdate.js"></script>';}
        else{return '<script type="text/javascript" src="/js/theory/hazard-report-hazupdate.js"></script>';}
    }
    
    /**
     * Adds a flag at a given point on the Hazard Test
     * @param int $clickTime The time the click was made
     * @param int $videoID The ID of the video where the click is being added to
     * @return void Nothing is returned
     */
    public function addFlag($clickTime, $videoID){
        $this->getUserProgress($this->getTestID());
        $questionNo = $this->currentVideoNo($videoID);
        $clicks = unserialize($_SESSION['hptest'.$this->getTestID()][$questionNo]['clicks']);
        $clicks[] = $clickTime;
        $_SESSION['hptest'.$this->getTestID()][$questionNo]['clicks'] = serialize($clicks);
    }
    
    /**
     * Updates the database to show that cheating was detected for the current question
     * @param int $videoID The ID of the video which cheating was detected
     * @param int $score The score which should be added to the video depending on the type of cheating
     */
    public function cheatDetected($videoID, $score){
        $questionNo = $this->currentVideoNo($videoID);
        $_SESSION['hptest'.$this->getTestID()][$questionNo]['score'] = $score;
        if($score == -2){$_SESSION['hptest'.$this->getTestID()][$questionNo]['clicks'] = NULL;}
    }
    
    /**
     * Mark the current test video
     * @param int $videoID The ID of the video you wish to mark and update the database
     * @return void Nothing is returned
     */
    public function markVideo($videoID){
        $this->getVideoInfo($videoID);
        $this->getUserProgress($this->getTestID());
        $questionNo = $this->currentVideoNo($videoID);
        if($_SESSION['hptest'.$this->getTestID()][$questionNo]['score'] != -1){
            $clicks = unserialize($_SESSION['hptest'.$this->getTestID()][$questionNo]['clicks']);
            $score = false;
            $secscore = false;
            foreach($clicks as $click){
                if(!$score && $click >= $this->videoInfo['five'] && $click <= $this->videoInfo['endseq']){
                    $score = $this->markHazard($click);
                    $_SESSION['hptest'.$this->getTestID()][$questionNo]['score'] = intval($score);
                }
                if(!$secscore && $this->videoInfo['nohazards'] == 2 && $click >= $this->videoInfo['ten'] && $click <= $this->videoInfo['endseq2']){
                    $secscore = $this->markHazard2($click);
                    $_SESSION['hptest'.$this->getTestID()]['second_score'] = intval($secscore);
                }
            }
        }
    }
    
    /**
     * Returns the mark for the click location
     * @param int $click The click location to score
     * @return int|boolean If the click scores any marks that score will be returned else will return false
     */
    protected function markHazard($click){
        if($click >= $this->videoInfo['five'] && $click < $this->videoInfo['four']){return 5;}
        elseif($click >= $this->videoInfo['four'] && $click < $this->videoInfo['three']){return 4;}
        elseif($click >= $this->videoInfo['three'] && $click < $this->videoInfo['two']){return 3;}
        elseif($click >= $this->videoInfo['two'] && $click < $this->videoInfo['one']){return 2;}
        elseif($click >= $this->videoInfo['one'] && $click <= $this->videoInfo['endseq']){return 1;}
        return false;
    }
    
    /**
     * Returns the mark for the click location for the second hazard window if it exists
     * @param int $click The click location to score
     * @return int|boolean If the click scores any marks that score will be returned else will return false
     */
    protected function markHazard2($click){
        if($click >= $this->videoInfo['ten'] && $click < $this->videoInfo['nine']){return 5;}
        elseif($click >= $this->videoInfo['nine'] && $click < $this->videoInfo['eight']){return 4;}
        elseif($click >= $this->videoInfo['eight'] && $click < $this->videoInfo['seven']){return 3;}
        elseif($click >= $this->videoInfo['seven'] && $click < $this->videoInfo['six']){return 2;}
        elseif($click >= $this->videoInfo['six'] && $click <= $this->videoInfo['endseq2']){return 1;}
        return false;
    }
    
    /**
     * Creates the HTML code for the current test
     * @param int $prim This should be the prim number of the first question
     * @param boolean $review If the user is in the review section should be set to true else should be false
     * @return string|boolean Returns the HTML code and question number as a JSON encoded string if that prim number exists else return false
     */
    public function createHTML($prim, $review = false){
        $this->report = $review;
        if(is_numeric($prim)){
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
            self::$template->assign('score_window', $this->getScoreWindows());
            self::$template->assign('review_flags', $this->getReviewFlags($prim));
            self::$template->assign('script', $this->getScript());
            self::$template->assign('testID', $this->getTestID());
            
            if($this->report == false){$this->videodata = self::$template->fetch('hazlayout.tpl');}else{$this->videodata = self::$template->fetch('hazlayoutreport.tpl');}
            return json_encode(array('html' => $this->videodata, 'questionnum' => $this->currentVideoNo($prim)));
        }
        return false;
    }
    
    /**
     * Converts the number to 3 decimal places used for the time
     * @param string $num This is the number you wish to convert to 3 decimal places
     * @return string Returns the number as a decimal with 3 decimal places
     */
    private function dec($num){
        return number_format($num, 3);
    }
    
    /**
     * Checks to see if the user has already completed this test
     * @return boolean Returns true if test already exist
     */
    protected function anyCompleteTests(){
        return self::$db->select($this->progressTable, array('user_id' => $this->getUserID(), 'test_id' => $this->testID, 'test_type' => strtoupper($this->testType)));
    }
    
    /**
     * Returns the score window HTML code ready to be displayed
     * @return string Returns the first score window HTML code
     */
    private function getScoreWindows(){
        $widthperc = (100 / $this->videoInfo['endClip']);
        if($this->videoInfo['prehazard'] != NULL){
            $margin1 = $this->dec(($this->videoInfo['prehazard'] / $this->videoInfo['endClip']) * 100);
            $prewidth = $this->dec(($this->videoInfo['five'] - $this->videoInfo['prehazard']) * $widthperc);
            $pre1 = '<div id="pre1" style="margin-left:'.$margin1.'%;width:'.$prewidth.'%"></div>';
            $marginleft = 0;
        }
        else{$marginleft = (($this->videoInfo['five'] / $this->videoInfo['endClip']) * 100);}
        $five = $this->dec(($this->videoInfo['four'] - $this->videoInfo['five']) * $widthperc);
        $four = $this->dec(($this->videoInfo['three'] - $this->videoInfo['four']) * $widthperc);
        $three = $this->dec(($this->videoInfo['two'] - $this->videoInfo['three']) * $widthperc);
        $two = $this->dec(($this->videoInfo['one'] - $this->videoInfo['two']) * $widthperc);
        $one = $this->dec(($this->videoInfo['endseq'] - $this->videoInfo['one']) * $widthperc);
        
        return $pre1.'<div id="five" style="margin-left:'.$marginleft.'%;width:'.$five.'%" data-score="'.$this->videoInfo['five'].'"></div><div id="four" style="width:'.$four.'%" data-score="'.$this->videoInfo['four'].'"></div><div id="three" style="width:'.$three.'%" data-score="'.$this->videoInfo['three'].'"></div><div id="two" style="width:'.$two.'%" data-score="'.$this->videoInfo['two'].'"></div><div id="one" style="width:'.$one.'%" data-score="'.$this->videoInfo['one'].'" data-scoreend="'.$this->videoInfo['endseq'].'"></div>'.$this->getSecondScoreWindow($widthperc);
    }
    
    /**
     * Returns the second score window HTML code ready to be displayed
     * @param int Should be the width of the time to work out the positioning of the score windows
     * @return string Returns the second score window HTML code
     */
    private function getSecondScoreWindow($widthperc){
        if($this->videoInfo['nohazards'] == 2){
            if($this->videoInfo['prehazard2'] != NULL){
                $margin1 = $this->dec((($this->videoInfo['prehazard2'] / $this->videoInfo['endClip']) - ($this->videoInfo['endseq'] / $this->videoInfo['endClip'])) * 100);
                $prewidth = $this->dec(($this->videoInfo['ten'] - $this->videoInfo['prehazard2']) * $widthperc);
                $pre2 = '<div id="pre2" style="margin-left:'.$margin1.'%;width:'.$prewidth.'%"></div>';
                $extramargin = 0;
            }
            else{$extramargin = $this->dec((($this->videoInfo['ten'] / $this->videoInfo['endClip']) - ($this->videoInfo['endseq'] / $this->videoInfo['endClip'])) * 100);}
            $ten = $this->dec(($this->videoInfo['nine'] - $this->videoInfo['ten']) * $widthperc);
            $nine = $this->dec(($this->videoInfo['eight'] - $this->videoInfo['nine']) * $widthperc);
            $eight = $this->dec(($this->videoInfo['seven'] - $this->videoInfo['eight']) * $widthperc);
            $seven = $this->dec(($this->videoInfo['six'] - $this->videoInfo['seven']) * $widthperc);
            $six = $this->dec(($this->videoInfo['endseq2'] - $this->videoInfo['six']) * $widthperc);
            return $pre2.'<div id="ten" style="margin-left:'.$extramargin.'%;width:'.$ten.'%" data-score="'.$this->videoInfo['ten'].'"></div><div id="nine" style="width:'.$nine.'%" data-score="'.$this->videoInfo['nine'].'"></div><div id="eight" style="width:'.$eight.'%" data-score="'.$this->videoInfo['eight'].'"></div><div id="seven" style="width:'.$seven.'%" data-score="'.$this->videoInfo['seven'].'"></div><div id="six" style="width:'.$six.'%" data-score="'.$this->videoInfo['six'].'" data-scoreend="'.$this->videoInfo['endseq2'].'"></div>';
        }
        return false;
    }
    
    /**
     * Returns the flags to be displayed on the score window
     * @param int $videoID The video ID to get the score window for
     * @return string This should be the flay with the styling added to the HTML code
     */
    protected function getReviewFlags($videoID){
        $questionNo = $this->currentVideoNo($videoID);
        $clicks = unserialize($_SESSION['hptest'.$this->getTestID()][$questionNo]['clicks']);
        if(is_array($clicks)){
            foreach($clicks as $i => $click){
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
    protected function clipScore($prim){
        $clipNo = $this->currentVideoNo($prim);
        $this->getUserProgress($this->getTestID());
        $vidInfo = $this->getVideoInfo($prim);
        if($_SESSION['hptest'.$this->getTestID()][$clipNo]['score'] < 0){$score = 0;}
        else{$score = $_SESSION['hptest'.$this->getTestID()][$clipNo]['score'];}
        if($vidInfo['nohazards'] == 1){return '<div class="yourscore">You scored '.intval($score).' for this hazard</div>';}
        else{return '<div class="yourscore">You scored '.intval($score).' for the first hazard and '.intval($_SESSION['hptest'.$this->getTestID()]['second_score']).' for the second hazard</div>';}
    }
    
    /**
     * Checks to see if the ant cheat was activated to the given video ID
     * @param int $prim This should be the video ID
     * @return string|boolean If the anti-cheat was activated a string of HTML code will be returned else will return false
     */
    protected function anyCheating($prim){
        $clipNo = $this->currentVideoNo($prim);
        $this->getUserProgress($this->getTestID());
        if($_SESSION['hptest'.$this->getTestID()][$clipNo]['score'] == -1){return '<div id="anticheat">Anti-Cheat Activated</div>';}
        else{return false;}
    }
    
    /**
     * Load a video rather than build a test from the start
     * @param int $testID The Test id number
     * @param int $videoID The Video ID number 
     * @return string Returns the Hazard Perception Test HTML code
     */
    public function loadNextVideo($testID, $videoID){
        $this->setTestID($testID);
        $this->report = false;
        return $this->buildTest($videoID);
    }
    
    /**
     * Builds the test and sets the required template values
     * @param int $prim The prim number normally for the first question in the test
     * @return string Returns the Hazard Perception Test HTML code
     */
    protected function buildTest($prim = false){
        if(!is_numeric($prim)){$prim = $this->userAnswers[1]['id'];}
        $this->createHTML($prim, $this->report);
        
        self::$template->assign('question_no', $this->currentVideoNo($prim));
        self::$template->assign('no_questions', $this->numVideos);
        self::$template->assign('video_data', $this->videodata);
        if($this->report == false){return self::$template->fetch('hazardtest.tpl');}else{return self::$template->fetch('hazardtestreport.tpl');}
    }
    
    /**
     * Creates the report information for a given testID
     * @param int $testID This should be the testID you wish to retrieve the report information for
     * @return string This will return the retort HTML code ready to be rendered in the page
     */
    public function createReport($testID){
        $this->setTestID($testID);
        if($this->anyCompleteTests()){
            return $this->endTest(false);
        }
        return self::$template->fetch('..'.DS.'report'.DS.'report-unavail.tpl');
    }
    
    /**
     * Returns the test status either passed or failed
     * @return string Returns either passed or failed depending on how the user did on the test
     */
    public function testStatus(){
        $testInfo = $this->anyCompleteTests();
        if($testInfo['status'] == 1){return 'passed';}
        else{return 'failed';}
    }
    
    /**
     * Ends the current test an starts the marking process if required
     * @param boolean $mark If the test needs marking set to true else should be false
     * @return string Returns the end test report HTML ready to be rendered
     */
    public function endTest($mark){
        if($mark == true){
            $this->getUserProgress($this->getTestID());
            for($i = 1; $i <= $this->numVideos; $i++){
                $this->markVideo($_SESSION['hptest'.$this->getTestID()]['videos'][$i]);
            }
            $this->userprogress = false;
        }
        $this->getUserProgress($this->getTestID());
        $score = 0;
        $windows = array();
        for($i = 1; $i <= $this->numVideos; $i++){
            $videoID = $_SESSION['hptest'.$this->getTestID()]['videos'][$i];
            $info = $this->getVideoInfo($videoID);
            $videos[$i]['id'] = $videoID;
            $videos[$i]['no'] = $i;
            $videos[$i]['description'] = $info['title'];
            if($info['nohazards'] == 1 && intval($_SESSION['hptest'.$this->getTestID()][$i]['score']) < 0){
                $videos[$i]['score'] = 0;
                $windows[0]++;
            }
            elseif($info['nohazards'] == 1){
                $videos[$i]['score'] = intval($_SESSION['hptest'.$this->getTestID()][$i]['score']);
                $score = $score + intval($_SESSION['hptest'.$this->getTestID()][$i]['score']);
                $windows[intval($_SESSION['hptest'.$this->getTestID()][$i]['score'])]++;
            }
            elseif(intval($_SESSION['hptest'.$this->getTestID()][$i]['score']) < 0){
                $videos[$i]['score'] = '0 + 0';
                $windows[0]++;
                $windows[0]++;
            }
            else{
                $videos[$i]['score'] = intval($_SESSION['hptest'.$this->getTestID()][$i]['score']).' + '.intval($_SESSION['hptest'.$this->getTestID()]['second_score']);
                $score = $score + (intval($_SESSION['hptest'.$this->getTestID()][$i]['score']) + intval($_SESSION['hptest'.$this->getTestID()]['second_score']));
                $windows[intval($_SESSION['hptest'.$this->getTestID()][$i]['score'])]++;
                $windows[intval($_SESSION['hptest'.$this->getTestID()]['second_score'])]++;
            }
            
            if(intval($_SESSION['hptest'.$this->getTestID()][$i]['score']) == '-2'){$videos[$i]['status'] = 'Skipped';}
            elseif(intval($_SESSION['hptest'.$this->getTestID()][$i]['score']) == '-1'){$videos[$i]['status'] = 'Cheat';}
        }
        if($mark == true){
            if($score >= $this->passmark){$this->status = 1;}else{$this->status = 2;}
            $_SESSION['hptest'.$this->getTestID()]['totalscore'] = $score;
            $this->addResultsToDB();
        }
        self::$template->assign('windows', $windows);
        self::$template->assign('score', $score);
        self::$template->assign('passmark', $this->passmark);
        self::$template->assign('videos', $videos);
        self::$template->assign('testID', $this->getTestID());
        return self::$template->fetch('hazresult.tpl');
    }
    
    /**
     * Inserts the users results into the database
     */
    protected function addResultsToDB(){
        self::$db->delete($this->progressTable, array('user_id' => $this->getUserID(), 'test_id' => $this->getTestID(), 'test_type' => strtoupper($this->testType))); // Delete old tests
        self::$db->insert($this->progressTable, array('user_id' => $this->getUserID(), 'test_id' => $this->getTestID(), 'progress' => serialize($_SESSION['hptest'.$this->getTestID()]), 'test_type' => strtoupper($this->testType), 'status' => $this->status));
    }
}