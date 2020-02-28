<?php
namespace HPTest;

class RandomHP extends HazardPerception {
    
    /**
     * Choose the videos for the given Hazard Perception Test number
     * @param int $testNo This should be the Test ID
     * @return void nothing is returned
     */
    protected function chooseVideos($testNo = 100) {
        $videos = $this->db->selectAll($this->config->table_hazard_videos, [], '*', 'RAND()', 14);
        if($this->report === false) {
            unset($_SESSION['hptestrand']);
            $this->db->delete($this->config->table_hazard_progress, ['user_id' => $this->getUserID(), 'test_id' => $testNo, 'test_type' => $this->getTestType()]);
        }
        $this->setVideos($videos, 'rand');
    }
}
