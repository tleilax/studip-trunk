<?php

require 'lib/bootstrap.php';page_open(array('sess' => 'Seminar_Session','auth' => 'Seminar_Auth','perm' => 'Seminar_Perm','user' => 'Seminar_User'));include 'lib/seminar_open.php';PageLayout::setTitle(_("Anmeldesets"));PageLayout::setHelpKeyword("Basis.Irgendwas");//Navigation::activateItem('/irgendwo/irgendwas');include 'lib/include/html_head.inc.php';include 'lib/include/header.php';require_once('lib/classes/admission/AdmissionAlgorithm.class.php');
require_once('lib/classes/admission/AdmissionPriority.class.php');
require_once('lib/classes/admission/AdmissionRule.class.php');
require_once('lib/classes/admission/AdmissionUserList.class.php');
require_once('lib/classes/admission/ConditionField.class.php');
require_once('lib/classes/admission/CourseSet.class.php');
require_once('lib/classes/admission/StudipCondition.class.php');
require_once('lib/classes/admission/WaitingList.class.php');

echo '<font color="white">';
/*$cs = new CourseSet();
$cs->addCourse('1ec034e63b31d3a93e297f89ce2d6583');
$cs->addCourse('378090a13f823fc1db1cfb541e09eb2b');
$rules = AdmissionRule::getAvailableAdmissionRules();
echo 'Admission rules:<pre>'.print_r($rules, true).'</pre>';

//$la = new LockedAdmission();
//$cs->addAdmissionRule($la);

$ta = new TimedAdmission();
$ta->setStartTime(time()-7*24*3600);
$ta->setEndTime(time()+14*24*3600);
$cs->addAdmissionRule($ta);

$allowed = $cs->checkAdmission('05ab22a8f2dec5a3f2c41e66d2b5f76f', 
    '1ec034e63b31d3a93e297f89ce2d6583');
echo 'Uli may register at Klingonisch: '.intval($allowed).'<br/>';

$cs->store();
*/
$cs = new CourseSet('d5ac689ed3149af03e5be303a892958a');
/*
$ca = new ConditionalAdmission();
$c1 = new StudipCondition();
$cfa = new DegreeCondition();
$cfa->setValue('48cafe9347899c2797b298f321dac22d');
$cfa->setCompareOperator('=');
$c1->addField($cfa);
$cfs = new SubjectCondition();
$cfs->setValue('6a885981059fcac2b52a30e5ebbcd37f');
$cfs->setCompareOperator('=');
$c1->addField($cfs);
$cfsem = new SemesterOfStudyCondition();
$cfsem->setValue(1);
$cfsem->setCompareOperator('>');
$c1->addField($cfsem);
$ca->addCondition($c1);
$cs->addAdmissionRule($ca);
*/

$la = new LimitedAdmission();
$la->courseSetId = $cs->getId();
$la->setMaxNumber(1);
$cs->addAdmissionRule($la);

echo 'Course set:<pre>'.print_r($cs, true).'</pre>';

$allowed = $cs->checkAdmission('05ab22a8f2dec5a3f2c41e66d2b5f76f', 
    '1ec034e63b31d3a93e297f89ce2d6583');
echo 'Uli may register at Klingonisch: '.intval($allowed).'<br/>';
/*
$cs->store();
*/
echo '</font>';
include 'lib/include/html_end.inc.php';page_close();
?>