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
        Log::DEBUG('start seat distribution for course set: ' . $courseSet->getId());
        foreach ($courseSet->getCourses() as $course_id) {
            $course = Course::find($course_id);
            $seminar = new Seminar($course_id);
            $num_participants = $course->members->find('status', words('user autor'))->count();
            $num_participants += $course->admission_members->find('status', 'accepted')->count();
            $free_seats = $course->admission_turnout - $num_participants;
            $claiming_users = AdmissionPriority::getPrioritiesByCourse($courseSet->getId(), $course->id);
            $factored_users = $courseSet->getUserFactorList();
            foreach(array_keys($claiming_users) as $user_id) {
                $claiming_users[$user_id] = 1;
                if (isset($factored_users[$user_id])) {
                    $claiming_users[$user_id] *= $factored_users[$user_id];
                }
                Log::DEBUG(sprintf('user %s gets factor %s', $user_id, $claiming_users[$user_id]));
            }
            if ($free_seats > 0) {
                Log::DEBUG(sprintf('distribute %s seats on %s claiming in course %s', $free_seats, count($claiming_users), $course->id));
                $claiming_users = $this->rollTheDice($claiming_users);
                Log::DEBUG(print_r($claiming_users,1));
                $this->addUsersToCourse(array_slice(array_keys($claiming_users),0 , $free_seats), $course->id);
                
            } else {
                Log::WARNING(sprintf('could not distribute seats, no free in course %s', $course->id));
            }
        }
    }
    
    private function distributeByPriorities($courseSet)
    {
    
    }

    private function addUsersToCourse($user_list, $course_id)
    {
        $seminar = new Seminar($course_id);
        foreach ($user_list as $chosen_one) {
            setTempLanguage($chosen_one);
            $message_title = sprintf(_('Teilnahme an der Veranstaltung %s'), $seminar->getName());
            if ($seminar->admission_prelim) {
                if ($seminar->addPreliminaryMember($chosen_one)) {
                    $message_body = sprintf (_('Sie wurden als TeilnehmerIn der Veranstaltung **%s** ausgelost. Die endgültige Zulassung zu der Veranstaltung ist noch von weiteren Bedingungen abhängig, die Sie bitte der Veranstaltungsbeschreibung entnehmen.'),
                            $seminar->getName());
                }
            } else {
                if ($seminar->addMember($user_id, 'autor')) {
                    $message_body = sprintf (_("Sie wurden als TeilnehmerIn der Veranstaltung **%s** ausgelost. Ab sofort finden Sie die Veranstaltung in der Übersicht Ihrer Veranstaltungen. Damit sind Sie auch als TeilnehmerIn der Präsenzveranstaltung zugelassen."),
                            $seminar->getName());
                }
            }
            messaging::sendSystemMessage($chosen_one, $message_title, $message_body);
            restoreLanguage();
        }
    }

    /**
     * Caedite eos. Novit enim Dominus qui sunt eius.
     *  
     * @param array $user_list
     */
    private function rollTheDice($user_list)
    {
        $max = count($user_list);
        foreach(array_keys($user_list) as $user_id => $factor) {
            $user_list[$user_id] = $factor * mt_rand(1, $max);
        }
        arsort($user_list, SORT_NUMERIC);
        return $user_list;
    }
    
}

?>