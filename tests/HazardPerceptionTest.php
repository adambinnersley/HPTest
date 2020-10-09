<?php
namespace HPTest\Tests;

use DBAL\Database;
use Configuration\Config;
use Smarty;
use UserAuth\User;
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
    }
    
    public function tearDown() : void
    {
        unset($this->db);
        unset($this->config);
        unset($this->hp);
        unset($this->user);
    }
    
    public function testExample()
    {
        $this->markTestIncomplete();
    }
}
