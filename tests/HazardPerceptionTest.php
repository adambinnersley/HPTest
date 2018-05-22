<?php
namespace HPTest\Tests;

use DBAL\Database;
use Configuration\Config;
use Smarty;
use UserAuth\User;
use HPTest\HazardPerception;
use PHPUnit\Framework\TestCase;

class HazardPerceptionTest extends TestCase{
    protected static $db;
    protected static $config;
    protected static $user;
    protected static $hp;
    
    public function setUp() {
        self::$db = new Database('localhost', $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'], $GLOBALS['DB_DBNAME']);
        if(!self::$db->isConnected()){
             $this->markTestSkipped(
                'No local database connection is available'
            );
        }
        self::$db->query(file_get_contents(dirname(dirname(__FILE__)).'/vendor/adamb/user/database/database_mysql.sql'));
        self::$db->query(file_get_contents(dirname(dirname(__FILE__)).'/database/database_mysql.sql'));
        self::$config = new Config(self::$db);
        self::$user = new User(self::$db);
        self::$hp = new HazardPerception(self::$db, self::$config, new Smarty(), self::$user);
    }
    
    public function tearDown() {
        unset(self::$db);
        unset(self::$config);
        unset(self::$hp);
        unset(self::$user);
    }
    
    public function testExample() {
        $this->markTestIncomplete();
    }
}
