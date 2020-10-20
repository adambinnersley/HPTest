<?php
namespace HPTest\Tests;

use DBAL\Database;
use Configuration\Config;
use Smarty;
use HPTest\Tests\Classes\User;
use HPTest\HazardPerception;
use PHPUnit\Framework\TestCase;

class HazardPerceptionTest extends TestCase
{
    protected $db;
    protected $config;
    protected $user;
    protected $hp;
    
    public function setUp() : void
    {
        session_name($GLOBALS['SESSION_NAME']);
        session_set_cookie_params(0, '/', '.'.$GLOBALS['DOMAIN']);
        session_start();
        $this->db = new Database('localhost', $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'], $GLOBALS['DB_DBNAME']);
        if (!$this->db->isConnected()) {
             $this->markTestSkipped(
                 'No local database connection is available'
             );
        }
        $this->db->query(file_get_contents(dirname(dirname(__FILE__)).'/vendor/adamb/user/database/database_mysql.sql'));
        $this->db->query(file_get_contents(dirname(dirname(__FILE__)).'/database/database_mysql.sql'));
        $this->config = new Config($this->db);
        $this->user = new User($this->db);
        $this->hp = new HazardPerception($this->db, $this->config, new Smarty(), $this->user);
        $this->db->truncate('hazard_clips');
        $this->db->query(file_get_contents(dirname(__FILE__).'/sample_data/data.sql'));
    }
    
    public function tearDown() : void
    {
        unset($this->db);
        unset($this->config);
        unset($this->hp);
        unset($this->user);
    }
    
    /**
     * @covers HPTest\HazardPerception::__construct
     * @covers HPTest\HazardPerception::anyCheating
     * @covers HPTest\HazardPerception::anyCompleteTests
     * @covers HPTest\HazardPerception::buildScoreWindow
     * @covers HPTest\HazardPerception::buildTest
     * @covers HPTest\HazardPerception::chooseVideos
     * @covers HPTest\HazardPerception::clipScore
     * @covers HPTest\HazardPerception::createHTML
     * @covers HPTest\HazardPerception::createTest
     * @covers HPTest\HazardPerception::currentVideoNo
     * @covers HPTest\HazardPerception::dec
     * @covers HPTest\HazardPerception::getImageLocation
     * @covers HPTest\HazardPerception::getJavascriptLocation
     * @covers HPTest\HazardPerception::getReviewFlags
     * @covers HPTest\HazardPerception::getScript
     * @covers HPTest\HazardPerception::getSessionInfo
     * @covers HPTest\HazardPerception::getTestID
     * @covers HPTest\HazardPerception::getTestType
     * @covers HPTest\HazardPerception::getUserID
     * @covers HPTest\HazardPerception::getUserProgress
     * @covers HPTest\HazardPerception::getVidLocation
     * @covers HPTest\HazardPerception::getVideo
     * @covers HPTest\HazardPerception::getVideoInfo
     * @covers HPTest\HazardPerception::getVideoName
     * @covers HPTest\HazardPerception::nextVideo
     * @covers HPTest\HazardPerception::prevVideo
     * @covers HPTest\HazardPerception::setTestID
     * @covers HPTest\HazardPerception::setVideos
     */
    public function testCreateTest()
    {
        $hpTest = $this->hp->createTest(1);
        $this->assertStringStartsWith('<div class="row">', $hpTest);
    }
}
