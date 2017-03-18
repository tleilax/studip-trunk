<?php

/**
 * Context.php - Helper class to handle the currently selected Stud.IP-object
 *
 * Usage:
 * Context::getId()
 *    -> to retrieve id of current Stud.IP-object
 *
 * Context::getType() == Context::COURSE
 *    -> check if context is of type course/seminar
 *
 * Context::get()
 *    -> get sorm-object for current context
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class Context {
    /**
     * Constants to check for type of context
     */
    const COURSE    = 'course';
    const INSTITUTE = 'institute';
    const USER      = 'user';

    /**
     * storage for the current context
     */
    private static
        $context        = null,
        $type           = null;

    /**
     * Determine which context is currently select.  User context is only
     * assumed if the profile navigation is active!
     */
    private static function loadContext($id)
    {
        if (Request::get('username') && Navigation::isActive('/profile')) {
            if (self::$context = User::findByUsername(Request::get('username'))) {
                self::$type = 'user';
            }
        } else {
            if ($id) {
                $possible_sorms = "User Course Institute";
                foreach(words($possible_sorms) as $sorm) {
                    if (self::$context = $sorm::find($id)) {
                        self::$type = strtolower($sorm);
                    }
                }
            }
        }
    }

    /**
     * Return sorm-object of currently active Stud.IP object
     *
     * @return Object sorm-object of current context
     */
    public static function get()
    {
        return self::$context;
    }

    /**
     * Return id of currently active Stud.IP object
     *
     * @return string md5-hash
     */
    public static function getId()
    {
        if (!self::$context) {
            return null;
        }

        return self::$context->getId();
    }

    /**
     * Return type of currently active Stud.IP object. To easily check what type
     * of object that is, check the return value against
     * Context::COURSE, Context::INSTITUTE or Context::USER
     *
     * @return mixed one of Context::COURSE, Context::INSTITUTE or Context::USER
     */
    public static function getType()
    {
        return self::$type;
    }

    public static function isCourse()
    {
        return self::getType() === self::COURSE;
    }

    public static function isInstitute()
    {
        return self::getType() === self::INSTITUTE;
    }

    public static function isUser()
    {
        return self::getType() === self::USER;
    }

    /**
     * Get string representation of object-type fpr legacy support
     *
     * @deprecated
     */
    public static function getClass()
    {
        switch (self::getType()) {
            case self::COURSE:
                return 'sem';
                break;

            case self::INSTITUTE:
                return 'inst';
                break;
        }
    }

    /**
     * @deprecated
     */
    public static function getArtNum()
    {
        if (self::isCourse()) {
            return self::get()->status;
        } else if (Context::isInstitute()) {
            return self::get()->type;
        }
    }

    // TODO: Ersatz für get_object_name?
    public static function getTypeName()
    {
        switch (self::getType()) {
            case self::COURSE:
                return _('Veranstaltung');
                break;

            case self::INSTITUTE:
                return _('Einrichtung');
                break;
        }
    }

    public static function getHeaderLine()
    {
        return self::get()->getFullname();
    }

    public static function set($id)
    {
        self::close();
        self::loadContext($id);

        if (!self::getType()) {
            throw new CheckObjectException(_('Sie haben kein Objekt gewählt.'));
        }

        if (self::isCourse() || self::isInstitute()) {
            $GLOBALS['SessionSeminar']  =  $id;
            $_SESSION['SessionSeminar'] =& $GLOBALS['SessionSeminar'];
        }

        URLHelper::addLinkParam('cid', $GLOBALS['SessionSeminar']);

        if (self::isCourse()) {
            $course = self::get();

            // check if current user can access the object
            if (!$perm->get_studip_perm($course["Seminar_id"])) {
                if ($course['lesezugriff'] > 0 || !get_config('ENABLE_FREE_ACCESS')) {
                    // redirect to login page if user is not logged in
                    $auth->login_if($auth->auth["uid"] == "nobody");
                    throw new AccessDeniedException();
                }
            }

            // if the aux data is forced for this seminar forward all user that havent made an input to this site
            if ($course["aux_lock_rule_forced"] && !$perm->have_studip_perm('tutor', $course["Seminar_id"])
                    && !in_array($_SERVER['PATH_INFO'], array('/course/members/additional_input', '/course/change_view'))) {

                $statement = DBManager::get()->prepare("SELECT 1 FROM datafields_entries WHERE range_id = ? AND sec_range_id = ? LIMIT 1");
                $statement->execute(array($GLOBALS['user']->id, $course["Seminar_id"]));

                if (!$statement->rowCount()) {
                    header('location: ' . URLHelper::getURL('dispatch.php/course/members/additional_input'));
                    page_close();
                    die;
                }
            }
        } else if (self::isInstitute()) {
            // check if current user can access the object
            if (!get_config('ENABLE_FREE_ACCESS') && !$perm->have_perm('user')) {
                // redirect to login page if user is not logged in
                $auth->login_if($auth->auth["uid"] == "nobody");
                throw new AccessDeniedException();
            }
        } else if (self::getType() == self::USER) {

        }

        return true;
    }

    function close()
    {
        self::$context        = null;
        self::$type           = null;

        URLHelper::removeLinkParam('cid');
    }
}


// TODO: remove the following global variables from Stud.IP
// $SessionSeminar, $rechte
// selectSem, selectInst, openSem, openInst rauswerfen und ersetzen
