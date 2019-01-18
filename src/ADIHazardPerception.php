<?php

namespace HPTest;

class ADIHazardPerception extends HazardPerception{
    public $passmark = 57;
    
    protected $userType = 'adi';
    protected $testType = 'ADI';
    
    protected $scriptVar = 'adihazard';
    
    /**
     * Returns the required JavaScript files as a HTML code string
     * @return string Returns the required JavaScript files as a HTML code string ready to be output
     */
    protected function getScript(){
        if($this->report == false){return $this->getJavascriptLocation().'hazard-perception-'.$this->scriptVar.'.js';}
        else{return $this->getJavascriptLocation().'hazard-report-'.$this->scriptVar.'.js';}
    }
}
