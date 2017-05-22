<?php
/**
 * ContentTermsOfUse.class.php
 * model class for table licenses
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2016 data-quest
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


/**
 * The ContentTermsOfUse class provides information about the terms under which
 * a content object in Stud.IP can be used. Each entry in the database table
 * content_terms_of_use_entries corresponds to one terms of use variant.
 *
 * Content can be a file or another Stud.IP object that is capable
 * of storing copyrighted material.
 *
 * @property string id database column: ID of the content terms of use object
 * @property string name database column: Short name of the terms of use object
 * @property string position database column: sorting of the entries can be made possible with this attribute
 * @property string description database column: Description text of the terms of use object
 * @property int download_condition: database column
 *      0 = no conditions (downloadable by anyone)
 *      1 = closed groups (e.g. courses with signup rules)
 *      2 = only for owner
 * @property string icon database column: either the name of the icon or the URL that points to the icon
 */
class ContentTermsOfUse extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'content_terms_of_use_entries';
        $config['i18n_fields']['name'] = true;
        $config['i18n_fields']['description'] = true;
        //TODO: define has_many relationship for FileRef
        //(and later: other object types)
        parent::configure($config);
    }

    public static function findAll()
    {
        return self::findBySQL("1 ORDER by position,id");
    }

    /**
     * Determines if a user is permitted to download a file.
     *
     * Depening on the value of the download_condition attribute a decision
     * is made regarding the permission of the given user to download
     * a file, given by one of its associated FileRef objects.
     *
     * The folder condition can have the values 0, 1 and 2.
     * - 0 means that there are no conditions for downloading, therefore the
     *   file is downloadable by anyone.
     * - 1 means that the file is only downloadable inside a closed group.
     *   Such a group can be a course or study group with closed admission.
     *   In this case this method checks if the user is a member of the
     *   course or study group.
     * - 2 means that the file is only downloadable for the owner.
     *   The user's ID must therefore match the user_id attribute
     *   of the FileRef object.
     */
    public function fileIsDownloadable(FileRef $file_ref, $allow_owner = true, $user_id = null)
    {
        $user_id = $user_id || $GLOBALS['user_id'];
        if ($allow_owner && ($file_ref->user_id == $GLOBALS['user']->id || $GLOBALS['perm']->have_perm('root', $user_id))) {
            return true;
        }
        if ($this->download_condition == 1) {
            $folder = $file_ref->folder;

            //the content is only downloadable when the user is inside a closed group
            //(referenced by range_id). If download_condition is set to 2
            //the group must also have a terminated signup deadline.
            if ($folder->range_id && $folder->range_type) {
                //check where this range_id comes from:
                if ($folder->range_type == 'course') {
                    $seminar = Seminar::GetInstance($folder->range_id);
                    $timed_admission = $seminar->getAdmissionTimeFrame();

                    if ($seminar->admission_prelim == 1 || $seminar->isPasswordProtected() ||
                        $seminar->isAdmissionLocked()
                        || (is_array($timed_admission) && $timed_admission['end_time'] > 0 && $timed_admission['end_time'] < time())) {
                        return true;
                    }
                } else {
                    return false;
                }
            } else {
                //no range_id set: we can't check if the content is downloadable
                //so it won't be downloadable at all!
                return false;
            }
        } elseif ($this->download_condition == 2) {
            return (bool)Config::get()->ALLOW_DOWNLOAD_FOR_UNKNOWN_LICENSE;
        } elseif ($this->download_condition == 3) {
            return false;
        }
        return true;
    }
}
