<?php
/**
 * Global search module for calendar.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchCalendar extends GlobalSearchModule
{

    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return _('Termine');
    }

    /**
     * Returns the URL that can be called for a full search.
     *
     * This could become obsolete when we have a real global search page.
     *
     * @param string $searchterm what to search for?
     */
    public static function getSearchURL($searchterm)
    {
        return URLHelper::getURL("dispatch.php/calendar/single/week", [
            'atime' => strtotime($searchterm)
        ]);
    }

    /**
     * Transforms the search request into an sql statement, that provides the id (same as getId) as type and
     * the object id, that is later passed to the filter.
     *
     * This function is required to make use of the mysql union parallelism
     *
     * @param $search the input query string
     * @return String SQL Query to discover elements for the search
     */
    public static function getSQL($search)
    {
        $time = strtotime($search);
        $endtime = $time + 86400;
        $user_id = DBManager::get()->quote($GLOBALS['user']->id);
        if ($time) {
            return "SELECT `name`, `date`, `end_time`, `seminar_id`
                FROM `termine`
                    JOIN `seminar_user` ON (`range_id` = `seminar_id`)
                WHERE user_id = $user_id
                    AND `date` BETWEEN $time AND $endtime
                ORDER BY `date`
                LIMIT " . (4 * Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE);
        }
    }

    /**
     * Returns an array of information for the found element. Following informations (key: description) are necessary
     *
     * - name: The name of the object
     * - url: The url to send the user to when he clicks the link
     *
     * Additional informations are:
     *
     * - additional: Subtitle for the hit
     * - expand: Url if the user further expands the search
     * - img: Avatar for the
     *
     * @param $id
     * @param $search
     * @return mixed
     */
    public static function filter($termin, $search)
    {
        return array(
            'name' => htmlReady($termin['name']),
            'url' => URLHelper::getURL("dispatch.php/course/details", array('cid' => $termin['seminar_id'])),
            'img' => Icon::create('schedule', 'info')->asImagePath(),
            'additional' => strftime('%H:%M', $termin['date']) . " - " .
                strftime('%H:%M', $termin['end_time']) . ", " .
                strftime('%x', $termin['date']),
            'expand' => URLHelper::getURL('dispatch.php/calendar/single/week',
                ['atime' => strtotime($search)])
        );
    }
}