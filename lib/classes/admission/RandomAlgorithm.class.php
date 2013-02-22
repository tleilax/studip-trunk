<?php

require_once('lib/classes/admission/AdmissionAlgorithm.class.php');

class RandomAlgorithm extends AdmissionAlgorithm {

    public function run($courseSet) {
        if ($courseSet->hasAdmissionRule('LimitedAdmission')) {
            return $this->distributeByPriorities($courseSet);
        } else {
            return $this->distributeByCourses($courseSet);
        }
    }
    
    private function distributeByCourses($courseSet)
    {
        
    }
    
    private function distributeByPriorities($courseSet)
    {
    
    }

}

?>