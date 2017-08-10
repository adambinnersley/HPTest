<?php
/**
 * Description of HazardPerception
 *
 * @author Adam Binnersley
 */
namespace HPTest;

use DBAL\Database;
use Smarty;

class HazardPerception implements HPInterface{
    protected static $template;
    protected static $db;
    protected static $user;
    protected $userprogress = false;

    public $videosTable = 'hazard_clips_new';
    public $progressTable = 'users_hazard_progress_new';
    
    public $testID = 1;
    public $numVideos = 14;
    public $passmark = 44;
    
    protected $userAnswers;
    protected $status;
    
    public $videoLocation = '/videos/';
    protected $videoInfo;
    protected $videodata;
    
    protected $report = true;
    protected $confirm = false;
    
    protected $userType = 'account';
    protected $testType = 'CAR';
    
    protected $windows = array(
        1 => array('five', 'four', 'three', 'two', 'one', 'endseq', 'prehazard'),
        2 => array('ten', 'nine', 'eight', 'seven', 'six', 'endseq2', 'prehazard2')
    );

    public function __construct(Database $db, Smarty $template, User $user){
        self::$db = $db;
        self::$user = $user;
        self::$template = $template;
        self::$template->addTemplateDir(dirname(__FILE__).DS.'templates');
    }

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

    public function getUserProgress($testID){
        if($this->getSessionInfo()){
            return $this->getSessionInfo();
        }
        else{
            $userProgress = self::$db->select($this->getProgressTable(), array('user_id' => self::$user->getUserID(), 'test_id' => $testID, 'test_type' => $this->getTestType()));
            $_SESSION['hptest'.$this->getTestID()] = unserialize(stripslashes($userProgress['progress']));
            return $this->getSessionInfo();
        }
    }

    public function getSessionInfo(){
        return $_SESSION['hptest'.$this->getTestID()];
    }

    protected function chooseVideos($testNo){
        $videos = self::$db->selectAll($this->getVideoTable(), array('hptestno' => $testNo), '*', array('hptestposition' => 'ASC'));
        if($this->report === false){
            unset($_SESSION['hptest'.$testNo]);
            self::$db->delete($this->getProgressTable(), array('user_id' => self::$user->getUserID(), 'test_id' => $testNo, 'test_type' => $this->getTestType()));
        }
        $v = 1;
        foreach($videos as $video){
            $this->userAnswers[$v]['id'] = $video['id'];
            $_SESSION['hptest'.$testNo]['videos'][$v] = $video['id'];
            $v++;
        }
    }

    public function confirmOverride(){
        $this->confirm = true;
    }

    public function setTestID($testID){
        $this->testID = intval($testID);
        return $this;
    }

    protected function getTestID(){
        return $this->testID;
    }

    public function setPassmark($passmark){
        $this->passmark = intval($passmark);
        return $this;
    }

    public function getPassmark() {
        return $this->passmark;
    }

    public function setVidLocation($location){
        $this->videoLocation = $location;
        return $this;
    }

    public function getVidLocation(){
        return $this->videoLocation;
    }

    public function setUserType($type){
        $this->userType = $type;
        return $this;
    }

    public function getUserType(){
        return $this->userType;
    }

    public function setTestType($type){
        $this->testType = strtoupper($type);
        return $this;
    }

    public function getTestType(){
        return strtoupper($this->testType);
    }
    
    public function setVideoTable($table){
        $this->videosTable = $table;
        return $this;
    }
    
    public function getVideoTable(){
        return $this->videosTable;
    }
    
    public function setProgressTable($table){
        $this->progressTable = $table;
        return $this;
    }
    
    public function getProgressTable(){
        return $this->progressTable;
    }
    
    protected function prevVideo($videoID){
        $prevID = ($this->currentVideoNo($videoID) - 1);
        if($prevID >= 1){$vidID = $this->getSessionInfo()['videos'][$prevID];}
        else{$vidID = 'none';}
        return '<div id="'.$vidID.'" class="prevvideo"><span>Prev Clip</span></div>';
    }
    
    protected function nextVideo($videoID){
        $nextID = ($this->currentVideoNo($videoID) + 1);
        if($nextID <= 14){$vidID = $this->getSessionInfo()['videos'][$nextID];}
        else{$vidID = 'none';}
        if(filter_input(INPUT_GET, 'review')){
            return '<div id="'.$vidID.'" class="nextvideo"><span class="sr-only">Skip Clip</span></div>';
        }
        else{
            return '<div id="'.$vidID.'" class="nextvideo"><div class="showbtn"></div>Skip Clip</div>';
        }
    }
    
    protected function currentVideoNo($videoID){
        foreach($this->getSessionInfo()['videos'] as $number => $value){
            if($value == $videoID){return intval($number);}
        }
        return false;
    }
    
    protected function getVideoInfo($videoID){
        $this->videoInfo = self::$db->select($this->getVideoTable(), array('id' => $videoID));
        return $this->videoInfo;
    }
    
    protected function getVideoName($videoID){
        $this->getVideoInfo($videoID);
        return strtolower($this->videoInfo['reference']);
    }
    
    private function getVideo($videoID){
        if($this->report === false){
            $width = 768;
            $height = 576;
        }
        else{
            $width = 544;
            $height = 408;
        }
        $videoName = $this->getVideoName($videoID);
        
        return '<div id="video_overlay"><div id="icon"><img src="/images/hloading.gif" alt="Loading" width="100" height="100" /></div></div><video width="'.$width.'" height="'.$height.'" id="video" class="video" data-duration="'.$this->videoInfo['endClip'].'" preload="auto" muted playsinline webkit-playsinline><source src="'.$this->videoLocation.'mp4/'.$videoName.'.mp4" type="video/mp4" /><source src="'.$this->videoLocation.'ogv/'.$videoName.'.ogv" type="video/ogg" /></video>';
    }
    
    protected function getScript(){
        if($this->report === false){return '<script type="text/javascript" src="/js/theory/hazard-perception-hazupdate.js"></script>';}
        else{return '<script type="text/javascript" src="/js/theory/hazard-report-hazupdate.js"></script>';}
    }
    
    public function addFlag($clickTime, $videoID){
        $this->getUserProgress($this->getTestID());
        $questionNo = $this->currentVideoNo($videoID);
        $clicks = unserialize($this->getSessionInfo()[$questionNo]['clicks']);
        $clicks[] = $clickTime;
        $_SESSION['hptest'.$this->getTestID()][$questionNo]['clicks'] = serialize(array_filter($clicks));
    }
    
    public function cheatDetected($videoID, $score){
        $questionNo = $this->currentVideoNo($videoID);
        $_SESSION['hptest'.$this->getTestID()][$questionNo]['score'] = $score;
        if($score == -2){$_SESSION['hptest'.$this->getTestID()][$questionNo]['clicks'] = NULL;}
    }
    
    public function markVideo($videoID){
        $this->getVideoInfo($videoID);
        $this->getUserProgress($this->getTestID());
        $questionNo = $this->currentVideoNo($videoID);
        if($this->getSessionInfo()[$questionNo]['score'] >= 0){
            $clicks = unserialize($this->getSessionInfo()[$questionNo]['clicks']);
            $score = false;
            $secscore = false;
            foreach($clicks as $click){
                if($score === false && $click >= $this->videoInfo['five'] && $click <= $this->videoInfo['endseq']){
                    $score = $this->markHazard($click);
                    $_SESSION['hptest'.$this->getTestID()][$questionNo]['score'] = intval($score);
                }
                if($secscore === false && $this->videoInfo['nohazards'] == 2 && $click >= $this->videoInfo['ten'] && $click <= $this->videoInfo['endseq2']){
                    $secscore = $this->markHazard($click, 2);
                    $_SESSION['hptest'.$this->getTestID()]['second_score'] = intval($secscore);
                }
            }
        }
    }
    
    protected function markHazard($click, $winNo = 1){
        $score = 5;
        for($h = 0; $h <= 4; $h++){
            if($click >= $this->videoInfo[$this->windows[$winNo][$h]] && $click < $this->videoInfo[$this->windows[$winNo][($h + 1)]]){return $score;}
            $score--;
        }
        return false;
    }
    
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
            self::$template->assign('score_window', $this->buildScoreWindow());
            self::$template->assign('review_flags', $this->getReviewFlags($prim));
            self::$template->assign('script', $this->getScript());
            self::$template->assign('testID', $this->getTestID());
            
            $this->videodata = ($this->report === false ? self::$template->fetch('hazlayout.tpl') : self::$template->fetch('hazlayoutreport.tpl'));
            return json_encode(array('html' => $this->videodata, 'questionnum' => $this->currentVideoNo($prim)));
        }
        return false;
    }
    
    private function dec($num){
        return number_format($num, 3);
    }
    
    protected function anyCompleteTests(){
        return self::$db->select($this->getProgressTable(), array('user_id' => self::$user->getUserID(), 'test_id' => $this->testID, 'test_type' => $this->getTestType()));
    }
    
    public function buildScoreWindow($winNo = 1){
        $widthperc = (100 / $this->videoInfo['endClip']);
        if($this->videoInfo[$this->windows[$winNo][6]] != NULL){
            $margin1 = ($winNo === 1 ? $this->dec(($this->videoInfo[$this->windows[$winNo][6]] / $this->videoInfo['endClip']) * 100) : $this->dec((($this->videoInfo[$this->windows[$winNo][6]] / $this->videoInfo['endClip']) - ($this->videoInfo[$this->windows[1][5]] / $this->videoInfo['endClip'])) * 100));
            $prewidth = $this->dec(($this->videoInfo[$this->windows[$winNo][0]] - $this->videoInfo[$this->windows[$winNo][6]]) * $widthperc);
            $pre = '<div id="pre'.$winNo.'" style="margin-left:'.$margin1.'%;width:'.$prewidth.'%"></div>';
            $marginleft = 0;
        }
        else{
            $marginleft = ($winNo === 1 ? (($this->videoInfo[$this->windows[$winNo][0]] / $this->videoInfo['endClip']) * 100) : $this->dec((($this->videoInfo[$this->windows[$winNo][0]] / $this->videoInfo['endClip']) - ($this->videoInfo[$this->windows[1][5]] / $this->videoInfo['endClip'])) * 100));
            $pre = '';
        }
        for($v = 0; $v <= 4; $v++){
            $pre.= '<div id="'.$this->windows[$winNo][0].'" style="'.($v === 0 ? 'margin-left:'.$marginleft.'%;' : '').'width:'.$this->dec(($this->videoInfo[$this->windows[$winNo][($v+1)]] - $this->videoInfo[$this->windows[$winNo][$v]]) * $widthperc).'%" data-score="'.$this->videoInfo[$this->windows[$winNo][0]].'">';
        }
        if($winNo === 1 && $this->videoInfo['nohazards'] == 2){
            $pre.= $this->buildScoreWindow(2);
        }
        return $pre;
    }
    
    protected function getReviewFlags($videoID){
        $questionNo = $this->currentVideoNo($videoID);
        $clicks = unserialize($this->getSessionInfo()[$questionNo]['clicks']);
        $flags = '';
        if(is_array($clicks)){
            foreach($clicks as $i => $click){
                $marginleft = ((($click / $this->videoInfo['endClip']) * 100) - 0.4);
                $flags.= '<img src="/images/hpflag.png" alt="Flag" width="20" height="20" id="flag'.($i + 1).'" class="reviewflag" style="left:'.$marginleft.'%" data-click="'.$click.'" />';
            }
        }
        return $flags;
    }
    
    protected function clipScore($prim){
        $clipNo = $this->currentVideoNo($prim);
        $this->getUserProgress($this->getTestID());
        $vidInfo = $this->getVideoInfo($prim);
        if($this->getSessionInfo()[$clipNo]['score'] < 0){$score = 0;}
        else{$score = $this->getSessionInfo()[$clipNo]['score'];}
        if($vidInfo['nohazards'] == 1){return '<div class="yourscore">You scored '.intval($score).' for this hazard</div>';}
        else{return '<div class="yourscore">You scored '.intval($score).' for the first hazard and '.intval($this->getSessionInfo()['second_score']).' for the second hazard</div>';}
    }
    
    protected function anyCheating($prim){
        $clipNo = $this->currentVideoNo($prim);
        $this->getUserProgress($this->getTestID());
        if($this->getSessionInfo()[$clipNo]['score'] == -1){return '<div id="anticheat">Anti-Cheat Activated</div>';}
        else{return false;}
    }
    
    public function loadNextVideo($testID, $videoID){
        $this->setTestID($testID);
        $this->report = false;
        return $this->buildTest($videoID);
    }
    
    protected function buildTest($prim = false){
        if(!is_numeric($prim)){$prim = $this->userAnswers[1]['id'];}
        $this->createHTML($prim, $this->report);
        
        self::$template->assign('question_no', $this->currentVideoNo($prim));
        self::$template->assign('no_questions', $this->numVideos);
        self::$template->assign('video_data', $this->videodata);
        if($this->report === false){return self::$template->fetch('hazardtest.tpl');}else{return self::$template->fetch('hazardtestreport.tpl');}
    }
    
    public function createReport($testID){
        $this->setTestID($testID);
        if($this->anyCompleteTests()){
            return $this->endTest(false);
        }
        return self::$template->fetch('..'.DS.'report'.DS.'report-unavail.tpl');
    }
    
    public function testStatus(){
        $testInfo = $this->anyCompleteTests();
        if($testInfo['status'] == 1){return 'passed';}
        else{return 'failed';}
    }
    
    public function endTest($mark){
        $this->getUserProgress($this->getTestID());
        if($mark === true){
            for($i = 1; $i <= $this->numVideos; $i++){
                $this->markVideo($this->getSessionInfo()['videos'][$i]);
            }
            $this->userprogress = false;
        }
        $score = 0;
        $windows = array();
        $videos = array();
        for($i = 1; $i <= $this->numVideos; $i++){
            $videoID = $this->getSessionInfo()['videos'][$i];
            $info = $this->getVideoInfo($videoID);
            $scoreInfo = $this->videoScore($i, $info['nohazards']);
            $videos[$i]['id'] = $videoID;
            $videos[$i]['no'] = $i;
            $videos[$i]['description'] = $info['title'];
            $videos[$i]['score'] = $scoreInfo['text_score'];
            $windows[$scoreInfo['score']]++;
            if($scoreInfo['second_score']){$windows[$scoreInfo['second_score']]++;}
            $score = $score + intval($scoreInfo['score']) + intval($scoreInfo['second_score']);
        }
        if($mark === true){
            if($score >= $this->getPassmark()){$this->status = 1;}else{$this->status = 2;}
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
    
    protected function videoScore($i, $hazards){
        $videos = array();
        $first_score = intval($this->getSessionInfo()[$i]['score']);
        if($hazards == 1){
            $videos['text_score'] = ($first_score < 0 ? 0 : $first_score);
            $videos['score'] = $videos['text_score'];
        }
        else{
            if($first_score < 0){
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
    
    protected function videoStatus($score){
        if($score == '-2'){return 'Skipped';}
        elseif($score == '-1'){return 'Cheat';}
        return false;
    }

    protected function addResultsToDB(){
        self::$db->delete($this->getProgressTable(), array('user_id' => self::$user->getUserID(), 'test_id' => $this->getTestID(), 'test_type' => $this->getTestType())); // Delete old tests
        self::$db->insert($this->getProgressTable(), array('user_id' => self::$user->getUserID(), 'test_id' => $this->getTestID(), 'progress' => serialize($this->getSessionInfo()), 'test_type' => $this->getTestType(), 'status' => $this->status));
    }
}