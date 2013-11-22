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
            $free_seats = $course->getFreeSeats();
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
                Log::DEBUG('the die is cast: ' . print_r($claiming_users,1));
                $chosen_ones = array_slice(array_keys($claiming_users),0 , $free_seats);
                Log::DEBUG('chosen ones: ' . print_r($chosen_ones,1));
                $this->addUsersToCourse($chosen_ones, $course);
                if ($free_seats < count($claiming_users)) {
                    if (!$course->admission_disable_waitlist) {
                        $free_seats_waitlist = $course->admission_waitlist_max ?: count($claiming_users) - $free_seats;
                        $waiting_list_ones = array_slice(array_keys($claiming_users),$free_seats , $free_seats_waitlist);
                        Log::DEBUG('waiting list ones: ' . print_r($waiting_list_ones, 1));
                        $this->addUsersToWaitlist($waiting_list_ones, $course);
                    }
                    if (($free_seats_waitlist + $free_seats) < count($claiming_users)) {
                        $remaining_ones = array_slice(array_keys($claiming_users),$free_seats_waitlist + $free_seats);
                        Log::DEBUG('remaining ones: ' . print_r($remaining_ones, 1));
                        $this->notifyRemainingUsers($remaining_ones, $course);
                    }
                }
            } else {
                Log::WARNING(sprintf('could not distribute seats, no free in course %s', $course->id));
            }
        }
    }
    
    private function distributeByPriorities($courseSet)
    {
    
    }

    public function notifyRemainingUsers($user_list, $course)
    {
        foreach ($user_list as $chosen_one) {
            setTempLanguage($chosen_one);
            $message_title = sprintf(_('Teilnahme an der Veranstaltung %s'), $course->name);
            $message_body = sprintf(_('Sie wurden leider im Losverfahren der Veranstaltung **%s** __nicht__ ausgelost. Für diese Veranstaltung wurde keine Warteliste vorgesehen.'),
                                       $course->name);
            messaging::sendSystemMessage($chosen_one, $message_title, $message_body);
            restoreLanguage();
        }
    }

    private function addUsersToWaitlist($user_list, $course)
    {
        $maxpos = $course->admission_applicants->findBy('status', 'awaiting')->orderBy('position desc')->val('position');
        foreach ($user_list as $chosen_one) {
            $maxpos++;
            $new_admission_member = new AdmissionApplication();
            $new_admission_member->user_id = $chosen_one;
            $new_admission_member->position = $maxpos;
            $new_admission_member->status = 'awaiting';
            $course->admission_applicants[] = $new_admission_member;
            if ($new_admission_member->store()) {
                setTempLanguage($chosen_one);
                $message_title = sprintf(_('Teilnahme an der Veranstaltung %s'), $course->name);
                $message_body = sprintf(_('Sie wurden leider im Losverfahren der Veranstaltung **%s** __nicht__ ausgelost. Sie wurden jedoch auf Position %s auf die Warteliste gesetzt. Das System wird Sie automatisch eintragen und benachrichtigen, sobald ein Platz für Sie frei wird.'),
                                           $course->name,
                                           $maxpos);
                messaging::sendSystemMessage($chosen_one, $message_title, $message_body);
                restoreLanguage();
            }
        }
    }

    private function addUsersToCourse($user_list, $course)
    {
        $seminar = new Seminar($course->id);
        foreach ($user_list as $chosen_one) {
            setTempLanguage($chosen_one);
            $message_title = sprintf(_('Teilnahme an der Veranstaltung %s'), $seminar->getName());
            if ($seminar->admission_prelim) {
                if ($seminar->addPreliminaryMember($chosen_one)) {
                    $message_body = sprintf (_('Sie wurden als TeilnehmerIn der Veranstaltung **%s** ausgelost. Die endgültige Zulassung zu der Veranstaltung ist noch von weiteren Bedingungen abhängig, die Sie bitte der Veranstaltungsbeschreibung entnehmen.'),
                            $seminar->getName());
                }
            } else {
                if ($seminar->addMember($chosen_one, 'autor')) {
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
        foreach($user_list as $user_id => $factor) {
            $user_list[$user_id] = $factor * mt_rand(1, $max);
        }
        arsort($user_list, SORT_NUMERIC);
        return $user_list;
    }
    
}

?>