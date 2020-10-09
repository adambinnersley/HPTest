<?php

namespace HPTest;

use DBAL\Database;
use Configuration\Config;

class DeleteData
{
    
    protected $db;
    protected $config;
    protected $user;
    
    /**
     * Connects to the database and passes the user class
     * @param Database $db This should e an instance of the Database class
     * @param Config $config This should be an instance of the config class
     * @param object $user This should be an instance of the user class
     */
    public function __construct(Database $db, Config $config, $user)
    {
        $this->db = $db;
        $this->config = $config;
        $this->user = $user;
    }
    
    /**
     * Deletes all of the hazard test data for a given user, if the user is not assigned will delete data for the current user
     * @param int|false $userID This should be the users ID if not deleting data for the current user else set to false
     * @return boolean If the information is deleted will return true else returns false
     */
    public function deleteData($userID = false)
    {
        if ($userID === false) {
            $userID = $this->user->getUserID();
        }
        if (is_numeric($userID)) {
            $this->db->delete($this->config->table_hazard_progress, ['user_id' => $userID]);
            return true;
        }
        return false;
    }
}
