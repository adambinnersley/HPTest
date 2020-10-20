<?php

namespace HPTest\Tests\Classes;

class User extends \UserAuth\User{
    
    /**
     * Dummy check user access for tests
     * @param int $testNo The test number you are checking if the user has access to
     * @return boolean
     */
    public function checkUserAccess($testNo = 1){
        if($testNo <= 10){
            return true;
        }
        return false;
    }
}
