<?php

/**
 * Interface GlobalSearchModule
 *
 * Module for global search extensions, e.g. forum, files or users
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
interface GlobalSearchModule
{

    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName();

    /**
     * Has to return a SQL Query that discovers all objects. All retrieved data is passed row by row to getGlobalSearchFilter
     *
     * @param $search the input query string
     * @return String SQL Query to discover elements for the search
     */
    public static function getSQL($search);

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
     * @param $data One row returned from getSQL SQL Query
     * @param $search The searchstring (Use for markup e.g. GlobalSearch::mark)
     * @return mixed Information Array
     */
    public static function filter($data, $search);

}