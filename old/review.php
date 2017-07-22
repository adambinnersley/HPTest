<?php
/**
 * Description of review
 *
 * @author Adam
 */
namespace Theory_Test\Car;

use DBAL\Database;
use Theory_Test\theoryTestAppLink;

class review{
    public $db;
    public $userID;
    
    public $where = array();
    
    protected $appData;
    public $noOfTests = 15;
    public $noOfHPTests = 12;
    public $type = 'CAR';
    
    protected $questionsTable;
    protected $DSACatTable;
    protected $progressTable;
    protected $testProgressTable;
    
    protected $newTestsAvailable = false;
    
    /**
     * Loads the required resouces and sets the table variables that are needed
     * @param Database $db This need to be an instance of the database class
     * @param resource $userID Should be an instance of the user class
     */
    public function __construct(Database $db, $userID){
        $this->db = $db;
        $this->userID = $userID;
        $this->setTables();
        $this->appData = new theoryTestAppLink($this->db);
    }   
    
    /**
     * If it is an standard Theory Test it will set the table to use the standard tables
     */
    protected function setTables(){
        $this->questionsTable = 'theory_questions_2016';
        $this->DSACatTable = 'theory_dsa_sections';
        $this->progressTable = 'user_progress';
        $this->testProgressTable = 'user_test_progress';
        $this->where = array('carquestion' => 'Y', 'alertcasestudy' => array('IS', 'NULL'));
    }
    
    /**
     * Sets the users tables to the ADI tables
     */
    public function setADITables(){
        $this->noOfTests = 6;
        $this->type = 'ADI';
        $this->questionsTable = 'adi_questions';
        $this->DSACatTable = 'adi_dsa_sections';
        $this->progressTable = 'adi_progress';
        $this->testProgressTable = 'adi_test_progress';
        $this->where = array();
    }
    
    /**
     * Sets the users tables to the Fleet tables
     */
    public function setFleetTables(){
        $this->noOfTests = 1;
        $this->type = 'Fleet';
        $this->questionsTable = 'fleet_questions';
        $this->DSACatTable = 'fleet_sections';
        $this->progressTable = 'fleet_progress';
        $this->testProgressTable = 'fleet_test_progress';
        $this->where = array();
    }
    
    /**
     * Set the Hazard Perception tables
     */
    private function setHPTables(){
        $this->questionsTable = 'hazard_clips';
        $this->testProgressTable = 'user_hazard_progress'; 
    }
    
    public function getSectionTables(){
        return array(
            array('table' => 'theory_hc_sections', 'name' => 'Highway Code Section', 'section' => 'hc', 'sectionNo' => 'hcsection'),
            array('table' => 'theory_dsa_sections', 'name' => 'DVSA Category', 'section' => 'dsa', 'sectionNo' => 'dsacat'),
            array('table' => 'theory_l2d_sections', 'name' => 'Learn to Drive Lesson', 'section' => 'l2d', 'sectionNo' => 'ldclessonno'),
            'case' => true
        );
    }
    
    /**
     * Returns the current users answers for the current test
     * @return array Returns the current users answers for the current test
     */
    public function getUserAnswers(){
        if(!isset($this->useranswers)){
            $answers = $this->db->select($this->progressTable, array('user_id' => $this->userID), array('progress'));
            $this->useranswers = unserialize(stripslashes($answers['progress']));
        }
        return $this->useranswers;
    }
    
    /*
     * Selects the number of unique test for a given test type
     * @return int Returns the number of unique test
     */
    public function numberOfTests(){
        if(!is_numeric($this->noOfTests)){
            $this->db->query('SELECT DISTINCT `mocktestcarno` FROM `theory_questions` WHERE `mocktestcarno` IS NOT NULL LIMIT 50;');
            $this->noOfTests = $this->db->numRows();
        }
        return $this->noOfTests;
    }
    
    /**
     * Returns the number of tests passed
     * @return int Returns The number of tests the user has passed
     */
    public function testsPassed(){
        return $this->db->count($this->testProgressTable, array('status' => 1, 'user_id' => $this->userID));
    }
    
    /**
     * Returns the number of tests failed
     * @return int Returns The number of tests the user has failed
     */
    public function testsFailed(){
        return $this->db->count($this->testProgressTable, array('status' => 2, 'user_id' => $this->userID));
    }
    
    /**
     * Returns the number of distinct Hazard Perception Tests
     * @return int Returns the number of hazard perception tests available
     */
    public function numberOfHPTests(){
        if(!is_numeric($this->noOfHPTests)){
            $this->db->query('SELECT DISTINCT `hptestno` FROM `'.$this->questionsTable.'` WHERE `hptestno` IS NOT NULL LIMIT 50;');
            $this->noOfHPTests = $this->db->numRows();
        }
        return $this->noOfHPTests;
    }
    
    /**
     * Returns the number of Hazard Perception tests passed
     * @return int Returns The number of Hazard Perception tests the user has passed
     */
    public function HPTestsPassed(){
        $this->setHPTables();
        return $this->testsPassed();
    }
    
    /**
     * Returns the number of Hazard Perception tests failed
     * @return int Returns The number of Hazard Perception tests the user has failed
     */
    public function HPTestsFailed(){
        $this->setHPTables();
        return $this->testsFailed();
    }
    
    /**
     * Build the review table for the given categories
     * @param string $table The table which should be used to get the information
     * @param string $tableSecNo The field which that table should be sorted by
     * @param string $title The title that should be given to the table
     * @return string|boolean Returns the table as a HTML string if the information exists else will return false
     */
    public function buildReviewTable($table, $tableSecNo, $title, $section){
        $this->getUserAnswers();
        $categories = $this->db->selectAll($table, '', '*', array('section' => 'ASC'));
        $review = array();
        $review['title'] = $title;
        $review['section'] = $section;
        foreach($categories as $cat){
            $review['ans'][$cat['section']] = $cat;
            $review['ans'][$cat['section']]['notattempted'] = 0;
            $review['ans'][$cat['section']]['incorrect'] = 0;
            $review['ans'][$cat['section']]['correct'] = 0;

            $questions = $this->db->selectAll($this->questionsTable, array_merge(array($tableSecNo => $cat['section']), $this->where), array('prim'));
            $review['ans'][$cat['section']]['numquestions'] = count($questions);
            foreach($questions as $question){
                if($this->useranswers[$question['prim']]['status'] == 0){$review['ans'][$cat['section']]['notattempted']++;}
                elseif($this->useranswers[$question['prim']]['status'] == 1){$review['ans'][$cat['section']]['incorrect']++;}
                elseif($this->useranswers[$question['prim']]['status'] == 2){$review['ans'][$cat['section']]['correct']++;}
            }
            $review['totalquestions'] = $review['totalquestions'] + $review['ans'][$cat['section']]['numquestions'];
            $review['totalcorrect'] = $review['totalcorrect'] + $review['ans'][$cat['section']]['correct'];
            $review['totalnotattempted'] = $review['totalnotattempted'] + $review['ans'][$cat['section']]['notattempted'];
            $review['totalincorrect'] = $review['totalincorrect'] + $review['ans'][$cat['section']]['incorrect'];
        }
        return $review;
    }
    
    /**
     * Build the case study review table
     * @return string|boolean If the case study information exists in the database the table will be returned as a HTML string else will return false
     */
    public function reviewCaseStudy(){
        $this->getUserAnswers();
        $categories = $this->db->selectAll($this->DSACatTable, '', '*', array('section' => 'ASC'));
        foreach($categories as $cat){
            $case[$cat['section']] = $cat;
            foreach($this->db->selectAll($this->questionsTable, array('casestudyno' => $cat['section']), '*', array('csqposition' => 'ASC')) as $num => $question){
                $case[$cat['section']]['q'][$num]['status'] = $this->useranswers[$question['prim']]['status'];
                $case[$cat['section']]['q'][$num]['num'] = ($num + 1);
            }
        }
        return $case;
    }
    
    /**
     * Returns the answers for each of the tests ready to review
     * @return type Returns the answers for each of the tests ready to review
     */
    public function reviewTests(){
        for($i = 1; $i <= $this->numberOfTests(); $i++){
            if($i == $this->numberOfTests() && ($this->type == 'CAR' || $this->type == 'Fleet')){$testID = 'random';}else{$testID = $i;}
            unset($_SESSION['test'.$i]);
            $answers[$testID] = $this->db->select($this->testProgressTable, array('user_id' => $this->userID, 'test_id' => $i, 'status' => array('>=', 1)), array('status', 'totalscore', 'complete'));
        }
        $this->checkForNewer();
        return $answers;
    }
    
    /**
     * Returns the answers for each of the hazard perception tests ready to review
     * @return type Returns the answers for each of the hazard perception tests ready to review
     */
    public function reviewHPTests(){
        $this->setHPTables();
        for($i = 1; $i <= $this->numberOfHPTests(); $i++){
            unset($_SESSION['hptest'.$i]);
            $info = $this->db->select($this->testProgressTable, array('user_id' => $this->userID, 'test_id' => $i), array('status', 'progress'));
            $answers[$i]['status'] = $info['status'];
            $userprogress = unserialize(stripslashes($info['progress']));
            $answers[$i]['totalscore'] = $userprogress['totalscore'];
        }
        return $answers;
    }
    
    /**
     * Returns a summary of how the user has done on the questions and how many they have correct, incorrect and how many are incompolete
     * @return array Returns a summary of how the user has done on the questions and how many they have correct, incorrect and how many are incompolete as an array of numbers
     */
    public function userTestInformation(){
        $this->getUserAnswers();
        $notattempted = 0;
        $incorrect = 0;
        $correct = 0;

        $questions = $this->db->selectAll($this->questionsTable, $this->where, array('prim'));
        $info['noQuestions'] = $this->db->rowCount();
        foreach($questions as $question){
            if($this->useranswers[$question['prim']]['status'] == 0){$notattempted++;}
            elseif($this->useranswers[$question['prim']]['status'] == 1){$incorrect++;}
            elseif($this->useranswers[$question['prim']]['status'] == 2){$correct++;}
        }
        $info['notAttempted'] = $notattempted;
        $info['Incorrect'] = $incorrect;
        $info['Correct'] = $correct;
        return $info;
    }
    
    /**
     * Checks for any newer test on the Server completed either on the app or the software
     * @return boolean If there are newer tests will return true else will return false
     */
    public function checkForNewer(){
        if($this->appData->getUniqueUser($this->userID) && $this->appData->checkForAnyNewer($this->userID)){
            $this->newTestsAvailable = true;
            return true;
        }
        return false;
    }
    
    /**
     * Return true if there are newer test else return false 
     * @return boolean Return true if there are newer test else return false
     */
    public function anyNewTests(){
        return $this->newTestsAvailable;
    }
    
    /**
     * Checks to see if there are any new tests to upload to the server
     * @return boolean|string If there are no new tests will return false else will return a list of the newer tests in the 1,3,11 format
     */
    public function anyToUpload(){
        if($this->appData->getUniqueUser($this->userID)){
            return $this->appData->anyNewToupload($this->userID);
        }
        return false;
    }
}