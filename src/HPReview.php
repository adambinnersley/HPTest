<?php

namespace HPTest;

use DBAL\Database;
use Smarty;

class HPReview {
    protected static $db;
    protected static $layout;
    protected static $user;
    protected $userClone;
    
    protected $questionsTable = 'hazard_clips_new';
    protected $testProgressTable = 'users_hazard_progress_new'; 
    
    public $numberOfHPTests = 12;
    
    /**
     * Connects to the database sets the current user and gets any user answers
     * @param Database $db This needs to be an instance of the database class
     * @param Smarty $layout This needs to be an instance of the Smarty Templating class
     * @param object $user This should be the user class used
     * @param int|false $userID If you want to emulate a user set the user ID here
     * @param string|false $templateDir If you want to change the template location set this location here else set to false
     */
    public function __construct(Database $db, Smarty $layout, $user, $userID = false, $templateDir = false) {
        self::$db = $db;
        self::$layout = $layout;
        self::$user = $user;
        self::$layout->addTemplateDir($templateDir === false ? str_replace(basename(__DIR__), '', dirname(__FILE__)).'templates' : $templateDir);
        if(is_numeric($userID)){$this->userClone = $userID;}
    }
    
    /**
     * Returns the userID or the mock userID if you wish to look at users progress
     * @return int Returns the UserID or mocked up userID if valid
     */
    public function getUserID(){
        if(is_numeric($this->userClone)){
            return $this->userClone;
        }
        return self::$user->getUserID();
    }
    
    /**
     * Returns the number of distinct Hazard Perception Tests
     * @return int Returns the number of hazard perception tests available
     */
    public function numberOfHPTests(){
        if(!is_numeric($this->numberOfHPTests)){
            self::$db->query("SELECT DISTINCT `hptestno` FROM `{$this->questionsTable}` WHERE `hptestno` IS NOT NULL;");
            $this->numberOfHPTests = self::$db->numRows();
        }
        return $this->numberOfHPTests;
    }
    
    /**
     * Returns the number of Hazard Perception tests passed
     * @return int Returns The number of Hazard Perception tests the user has passed
     */
    public function testsPassed(){
        return self::$db->count($this->testProgressTable, array('status' => 1, 'user_id' => $this->getUserID()));
    }
    
    /**
     * Returns the number of Hazard Perception tests failed
     * @return int Returns The number of Hazard Perception tests the user has failed
     */
    public function testsFailed(){
        return self::$db->count($this->testProgressTable, array('status' => 2, 'user_id' => $this->getUserID()));
    }
    
    /**
     * Returns the answers for each of the hazard perception tests ready to review
     * @return type Returns the answers for each of the hazard perception tests ready to review
     */
    public function reviewHPTests(){
        $answers = array();
        for($i = 1; $i <= $this->numberOfHPTests(); $i++){
            unset($_SESSION['hptest'.$i]);
            $info = self::$db->select($this->testProgressTable, array('user_id' => $this->getUserID(), 'test_id' => $i), array('status', 'progress'));
            $answers[$i]['status'] = $info['status'];
            $userprogress = unserialize(stripslashes($info['progress']));
            $answers[$i]['totalscore'] = $userprogress['totalscore'];
        }
        return $answers;
    }
}
