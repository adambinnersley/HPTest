<?php
namespace HPTest\Tests;

use DBAL\Database;
use Smarty;
use UserAuth\User;
use HPTest\HazardPerception;
use PHPUnit\Framework\TestCase;

class HazardPerceptionTest extends TestCase{
    protected static $db;
    protected static $user;
    protected static $hp;
    
    public function setUp() {
        self::$db = new Database('localhost', $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'], $GLOBALS['DB_DBNAME']);
        if(!self::$db->isConnected()){
             $this->markTestSkipped(
                'No local database connection is available'
            );
        }
        self::$user = new User(self::$db);
        self::$hp = new HazardPerception(self::$db, new Smarty(), self::$user);
    }
    
    public function tearDown() {
        unset(self::$db);
        unset(self::$hp);
        unset(self::$user);
    }
    
    public function testExample() {
        $this->markTestIncomplete();
    }
}
