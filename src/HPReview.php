<?php

namespace HPTest;

use DBAL\Database;
use Configuration\Config;
use Smarty;

class HPReview {
    protected $db;
    protected $config;
    protected $template;
    protected $user;
    protected $userClone; 
    
    public $numberOfHPTests = 12;
    
    protected $testType = 'CAR';
    
    /**
     * Connects to the database sets the current user and gets any user answers
     * @param Database $db This needs to be an instance of the database class#
     * @param Config $config This needs to be an instance of the Config class
     * @param Smarty $template This needs to be an instance of the Smarty Templating class
     * @param object $user This should be the user class used
     * @param int|false $userID If you want to emulate a user set the user ID here
     * @param string|false $templateDir If you want to change the template location set this location here else set to false
     */
    public function __construct(Database $db, Config $config, Smarty $template, $user, $userID = false, $templateDir = false) {
        $this->db = $db;
        $this->config = $config;
        $this->user = $user;
        $this->template = $template;
        $this->template->addTemplateDir(($templateDir === false ? str_replace(basename(__DIR__), '', dirname(__FILE__)).'templates' : $templateDir), 'hazard');
        if(is_numeric($userID)){$this->userClone = $userID;}
    }
    
    /*
     * Setter Allows table names to be changed if needed
     */
    public function __set($name, $value) {
        if(isset($this->$name)){$this->$name = $value;}
    }
    
    /**
     * Returns the userID or the mock userID if you wish to look at users progress
     * @return int Returns the UserID or mocked up userID if valid
     */
    public function getUserID(){
        if(is_numeric($this->userClone)){
            return $this->userClone;
        }
        return $this->user->getUserID();
    }
    
    /**
     * Returns the number of distinct Hazard Perception Tests
     * @return int Returns the number of hazard perception tests available
     */
    public function numberOfHPTests(){
        if(!is_numeric($this->numberOfHPTests)){
            $this->db->query("SELECT DISTINCT `hptestno` FROM `{$this->config->table_hazard_videos}` WHERE `hptestno` IS NOT NULL;");
            $this->numberOfHPTests = $this->db->numRows();
        }
        return $this->numberOfHPTests;
    }
    
    /**
     * Returns the number of Hazard Perception tests passed
     * @return int Returns The number of Hazard Perception tests the user has passed
     */
    public function testsPassed(){
        return $this->db->count($this->config->table_hazard_progress, array('status' => 1, 'user_id' => $this->getUserID(), 'test_type' => strtoupper($this->testType)));
    }
    
    /**
     * Returns the number of Hazard Perception tests failed
     * @return int Returns The number of Hazard Perception tests the user has failed
     */
    public function testsFailed(){
        return $this->db->count($this->config->table_hazard_progress, array('status' => 2, 'user_id' => $this->getUserID(), 'test_type' => strtoupper($this->testType)));
    }
    
    /**
     * Returns the answers for each of the hazard perception tests ready to review
     * @return type Returns the answers for each of the hazard perception tests ready to review
     */
    public function reviewHPTests(){
        $answers = array();
        for($i = 1; $i <= $this->numberOfHPTests(); $i++){
            unset($_SESSION['hptest'.$i]);
            $info = $this->db->select($this->config->table_hazard_progress, array('user_id' => $this->getUserID(), 'test_id' => $i, 'test_type' => strtoupper($this->testType)), array('status', 'progress'));
            $answers[$i]['status'] = $info['status'];
            $userprogress = unserialize(stripslashes($info['progress']));
            $answers[$i]['totalscore'] = $userprogress['totalscore'];
        }
        return $answers;
    }
}
