<?php
/**
 * Interface GlobalSearchFulltext
 *
 * Provides defeatable fulltext search with MATCH AGAINST. Only usable on MySQL 5.7.6+ or MariaDB 10.0.5+.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */

interface GlobalSearchFulltext
{
    public static function enable();

    public static function disable();

    public static function getFulltextSearch($search);
}
