<?php

class CoursesetModel {

    public function getInstCourses($instituteIds) {
        $query = "SELECT seminar_inst.seminar_id, s.VeranstaltungsNummer, s.Name
                  FROM seminar_inst
                  LEFT JOIN seminare AS s ON (seminar_inst.seminar_id = s.Seminar_id)
                  WHERE seminar_inst.Institut_id IN ('".
                  implode("', '", array_keys($instituteIds))."')
                  ORDER BY s.start_time DESC, s.VeranstaltungsNummer ASC, s.Name ASC";
        $stmt = DBManager::get()->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>