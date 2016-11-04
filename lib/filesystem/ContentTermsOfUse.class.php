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
 * @property string internal_name database column: unique name for the terms of use object
 * @property string description database column: Description text of the terms of use object
 * @property int download_condition: database column
 * 0 = no conditions (downloadable by anyone)
 * 1 = closed groups (e.g. courses with signup rules)
 * 2 = only for owner
 */
class ContentTermsOfUse extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'content_terms_of_use_entries';
        
        //TODO: define has_many relationship for FileRef
        //(and later: other object types)
    }
    
    
    /**
     * Determines if a user is permitted to download a file.
     */
    public function fileIsDownloadable(FileRef $file_ref, $user_id = null)
    {
        if($this->download_condition == 0) {
            return true;
        } elseif($this->download_condition == 1) {
            $folder = $file_ref->folder;
            if(!$folder) {
                //no folder: we can't check if the file is downloadable
                //when this download condition is set!
                return false;
            }
            
            //the content is only downloadable when the user is inside a closed group
            //(referenced by range_id). If download_condition is set to 2
            //the group must also have a terminated signup deadline.
            if($folder->range_id && $folder->range_type) {
                //check where this range_id comes from:
                if($folder->range_type == 'course') {
                    $course = Course::find($folder->range_id);
                    //ok, range is a course:
                    $status = $course->getParticipantStatus($user_id);
                    if(!$status || ($status == 'awaiting')) {
                        //the user is not a member in the course
                        //or he is awaiting membership to the course.
                        //However, this means the user isn't permitted
                        //to download the content!
                        return false;
                    } else {
                        //the user has the status 'accepted' (applicants_seminar_user table)
                        //or is one of 'user', 'autor', 'tutor', 'dozent' (seminar_user table).
                        //If download condition is set to 1 we must also check
                        //if the course admission is closed!
                        
                        //TODO: look at admission rules for the course
                        //and especially, if it is a timed admission.
                        //If it is, then check if the admission's end_time
                        //has passed. If so, the file is downloadable.
                        //Otherwise it isn't.
                        return false; //NOT IMPLEMENTED YET!
                    }
                }
            } else {
                //no range_id set: we can't check if the content is downloadable
                //so it won't be downloadable at all!
                return false;
            }
        } elseif($this->download_condition == 2) {
            //can only be downloaded if the user is the owner of the file:
            return ($file_ref->user_id == $user->id);
        } else {
            //invalid download_condition ID!
            return false;
        }
    }
}
