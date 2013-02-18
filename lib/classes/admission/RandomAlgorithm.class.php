<?php

require_once('lib/classes/admission/AdmissionAlgorithm.class.php');

class RandomAlgorithm extends AdmissionAlgorithm {

    public function run($courseSetId) {
        $courses = CourseSet::getCoursesByCourseSetId($courseSetId);
        return true;
    }

}

?>