<?php
/**
 * Privacy.php - Privacy policy of Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class Privacy
{
    /**
     * Names of classes containing user data.
     */

    private static $privacy_core_classes = [
        'User',
        'DataField',
        'DatafieldEntryModel',
        'UserConfig',
        'HelpTourUser',
        'LogEvent'
    ];

    private static $privacy_date_classes = [
        'CalendarEvent',
        'EventData',
        'CourseDate',
        'CourseExDate'
    ];

    private static $privacy_message_classes = [
        'BlubberPosting',
        'StudipNews',
        'StudipComment',
        'Message',
        'MessageUser'
    ];

    private static $privacy_content_classes = [
        'FileRef',
        'ForumEntry',
        'WikiPage',
        'StudipLitList'
    ];

    private static $privacy_quest_classes = [
        'Evaluation',
        'Questionnaire',
        'QuestionnaireAnswer',
        'QuestionnaireAnonymousAnswer',
        'QuestionnaireAssignment',
        'eTask\Attempt',
        'eTask\Response',
        'eTask\Task',
        'eTask\Test',
        'Grading\Instance'
    ];

    private static $privacy_membership_classes = [
        'Course',
        'CourseMember',
        'AdmissionApplication',
        'ArchivedCourse',
        'ArchivedCourseMember',
        'Statusgruppen',
        'StatusgruppeUser',
        'InstituteMember',
        'UserStudyCourse',
        'Fach',
        'Abschluss'
    ];

    /**
     * Returns the tables containing user data.
     * the array consists of the tables containing user data
     * the expected format for each table is:
     * $array[ table display name ] = [ 'table_name' => name of the table, 'table_content' => array of db rows containing userdata]
     *
     * @param string $user_id
     * @return array
     */
    public static function getUserdataInformation($user_id, $section = null)
    {
        $core_data = [];
        $user = User::find($user_id);

        switch ($section) {
            case "core":
                $privacy_classes = self::$privacy_core_classes;
                break;
            case "date":
                $privacy_classes = self::$privacy_date_classes;
                break;
            case "message":
                $privacy_classes = self::$privacy_message_classes;
                break;
            case "content":
                $privacy_classes = self::$privacy_content_classes;
                break;
            case "quest":
                $privacy_classes = self::$privacy_quest_classes;
                break;
            case "membership":
                $privacy_classes = self::$privacy_membership_classes;
                break;
            default:
                $privacy_classes = array_merge(
                    self::$privacy_core_classes,
                    self::$privacy_date_classes,
                    self::$privacy_message_classes,
                    self::$privacy_content_classes,
                    self::$privacy_quest_classes,
                    self::$privacy_membership_classes);
        }



        foreach ($privacy_classes as $privacy_class) {
            if (class_exists($privacy_class) && in_array('PrivacyObject', class_implements($privacy_class))) {
                foreach ($privacy_class::getUserdata($user) as $label => $class_storage) {
                    if ($class_storage->hasData()) {
                        $storage = $class_storage->getStoredDataForContext($user);
                        foreach ($storage['tabular'] as $meta) {
                            $core_data[$label] = [
                                'table_name'    => $meta['key'],
                                'table_content' => $meta['value'],
                            ];
                        }
                    }
                }
            }
        }

        $field_data = DBManager::get()->fetchAll("SELECT * FROM object_user_visits WHERE user_id =?", [$user->user_id]);
        if ($field_data) {
            $core_data['Objekt Aufrufe'] = [
                'table_name'    => 'object_user_visits',
                'table_content' => $field_data,
            ];
        }

        return $core_data;
    }

    /**
     * Checks if current user is privileged to see the data of given user
     *
     * @param string $user_id
     * @return boolean
     */
    public static function isVisible($user_id)
    {
        $needed_perm = Config::get()->PRIVACY_PERM ?: 'root';
        $allowed_person = true;
        if (!in_array($needed_perm, ['root', 'admin'])) {
            if (!$GLOBALS['perm']->have_perm('admin')) {
                $allowed_person = $GLOBALS['user']->user_id === $user_id;
            }
        }

        return $GLOBALS['perm']->have_perm($needed_perm)
            && $allowed_person;
    }

}
