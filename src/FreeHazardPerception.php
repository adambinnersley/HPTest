<?php

namespace HPTest;

class FreeHazardPerception extends HazardPerception{   
    protected $scriptVar = 'freehazupdate';
    
    /**
     * Returns the complete test HTML for the current question
     * @param int $testNo The number of the current test
     * @param boolean $report If the user is currently looking at the repost section
     * @param int|boolean $prim The prim number of the current question if not new test
     * @return string Returns the test HTML for the test
     */
    public function createTest($testNo = 1, $report = false, $prim = false) {
        $this->setTestID($testNo);
        $this->report = $report;
        if($this->report === false){$this->chooseVideos($testNo);}
        return $this->buildTest($prim, $report, $prim);
    }
    
    /**
     * Returns the user ID for the user (As it is a free test and no sign in is required will return the users session id)
     * @return Retuns the Users ID (Set as session id for the free test)
     */
    public function getUserID() {
        return session_id();
    }
    
    /**
     * Returns the user progress for a given test ID
     * @param int $testID This should be the test ID that you wish to retrieve the users progress for
     * @return array Returns the users test progress as an array
     */
    public function getUserProgress($testID){
        return $_SESSION['hptest'.$testID];
    }
    
    /**
     * Choose the videos for the given Hazard Perception Test number
     * @param int $testNo This should be the Test ID
     */
    protected function chooseVideos($testNo){
        $videos = self::$db->selectAll($this->videosTable, array('hptestno' => $testNo), '*', array('hptestposition' => 'ASC'));
        if($this->report === false){
            unset($_SESSION['hptest'.$testNo]);
        }
        $v = 1;
        foreach($videos as $video){
            $this->userAnswers[$v]['id'] = $video['id'];
            $_SESSION['hptest'.$testNo]['videos'][$v] = $video['id'];
            $v++;
        }
    }
    
    /**
     * Checks to see if the user has already completed this test
     * @return boolean Returns true if test already exist
     */
    protected function anyCompleteTests(){
        if(is_array($_SESSION['hptest'.$this->getTestID()])){return true;}
        return false;
    }
    
    /**
     * Ends the current test an starts the marking process if required
     * @param boolean $mark If the test needs marking set to true else should be false
     * @return string Returns the end test report HTML ready to be rendered
     */
    public function endTest($mark){
        self::$template->assign('free_test', 'Yes', true);
        return parent::endTest($mark);
    }
    
    /**
     * Inserts the users results into the database
     */
    protected function addResultsToDB(){
        // Don't store the results for the free Hazard Perception Test
    }
}