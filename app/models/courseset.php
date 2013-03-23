<?php

class CoursesetModel {

    public function getInstCourses($instituteIds, $coursesetId='') {
        $parameters = array();
        $query = "SELECT `seminar_inst`.`seminar_id`, s.`VeranstaltungsNummer`, s.`Name`
                  FROM `seminar_inst`
                  LEFT JOIN `seminare` AS s ON (`seminar_inst`.`seminar_id` = s.`Seminar_id`)
                  LEFT JOIN `seminar_courseset` AS sc ON (s.`Seminar_id`=sc.`seminar_id`) 
                  WHERE `seminar_inst`.`Institut_id` IN ('".
                  implode("', '", array_keys($instituteIds))."')
                  AND (sc.`set_id` IS NULL";
        if ($coursesetId) {
            $query .= " OR sc.`set_id`=?";
            $parameters[] = $coursesetId;
        }
        $query .= ") ORDER BY s.start_time DESC, s.VeranstaltungsNummer ASC, s.Name ASC";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute($parameters);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>