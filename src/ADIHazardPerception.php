<?php

namespace HPTest;

class ADIHazardPerception extends HazardPerception{
    public $passmark = 57;
    
    protected $userType = 'adi';
    protected $testType = 'ADI';
    
    protected $scriptVar = 'adihazard';
}
