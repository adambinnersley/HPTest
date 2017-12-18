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
        if($this->report == false){return '<script type="text/javascript" src="'.$this->getJavascriptLocation().'hazard-perception-'.$this->scriptVar.'.js"></script>';}
        else{return '<script type="text/javascript" src="'.$this->getJavascriptLocation().'hazard-report-'.$this->scriptVar.'.js"></script>';}
    }
}
