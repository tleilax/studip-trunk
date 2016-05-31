<?php
namespace RESTAPI\Routes;

/**
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @author      Andr� Kla�en <klassen@elan-ev.de>
 * @license     GPL 2 or later
 *
 * @condition user_id ^[a-f0-9]{32}$
 */
class Activity extends \RESTAPI\RouteMap
{


    /**
     * required code before processing the route
     */
    public static function before()
    {
        require_once 'lib/activities/Filter.php';
        require_once 'lib/activities/Context.php';
        require_once 'lib/activities/SystemContext.php';
        require_once 'lib/activities/CourseContext.php';
        require_once 'lib/activities/InstituteContext.php';
        require_once 'lib/activities/UserContext.php';
        require_once 'lib/activities/Stream.php';
        require_once 'lib/activities/Activity.php';

        require_once 'lib/activities/ActivityProvider.php';
        require_once 'lib/activities/BlubberProvider.php';
        require_once 'lib/activities/DocumentsProvider.php';
        require_once 'lib/activities/ForumProvider.php';
        require_once 'lib/activities/LiteratureProvider.php';
        require_once 'lib/activities/MessageProvider.php';
        require_once 'lib/activities/NewsProvider.php';
        require_once 'lib/activities/ParticipantsProvider.php';
        require_once 'lib/activities/ScheduleProvider.php';
        require_once 'lib/activities/WikiProvider.php';
    }

    /**
     * List activities for an user
     *
     * @get /user/:user_id/activitystream
     *
     * @param string  $user_id   the user to get the activities for
     *
     * @return Array   the activities as array('collection' => array(...), 'pagination' => array())
     */
    public function getActivities($user_id)
    {
        // only root can retrieve arbitrary streams
        if (!$GLOBALS['perm']->have_perm('root')
                && $GLOBALS['user']->id != $user_id) {
            $this->error(401);
        }

        $contexts = array();

        // create system context
        $system_context = new \Studip\Activity\SystemContext();
        $contexts[] = $system_context;


        // create courses and institutes context
        $semesters   = \MyRealmModel::getSelectedSemesters('all');
        $min_sem_key = min($semesters);
        $max_sem_key = max($semesters);

        $courses = \MyRealmModel::getCourses($min_sem_key, $max_sem_key);

        foreach ($courses as $course) {
            $contexts[] = new \Studip\Activity\CourseContext($course->seminar_id);
        }


        $institutes = \MyRealmModel::getMyInstitutes();
        if (!$GLOBALS['perm']->have_perm('root') && !empty($institutes)) {
            foreach($institutes as $institute){
                $contexts[] = new \Studip\Activity\InstituteContext($institute['institut_id']);
            }
        }

        // #TODO: user_context (do we wanna add buddies as well?)
        $contexts[] = new \Studip\Activity\UserContext($GLOBALS['user']->id);

        // add filters
        $filter = new \Studip\Activity\Filter();

        $start = \Request::int('start', strtotime("-2 days"));
        $end   = \Request::int('end',   time());

        $filtertype = \Request::get('filtertype', '');


        $filter->setStartDate($start);
        $filter->setEndDate($end);

        if(!empty($filtertype)) {
            $filter->setType($filtertype);
        }



        $stream = new \Studip\Activity\Stream($user_id, $contexts, $filter);

        // set etag for preventing resending the same stuff over and over again
        $this->etag(md5(serialize($stream)));

        $data = $stream->asArray();


        foreach ($data as $key => $act) {

            $actor = array(
                        'type' => $data[$key]['actor_type'],
                        'id'   => $data[$key]['actor_id']);

            if ($data[$key]['actor_type'] == 'user') {
                $actor['details'] = User::getMiniUser($this, new \User($data[$key]['actor']['id']));
            }

            unset($data[$key]['actor_type']);
            unset($data[$key]['actor_id']);


            $data[$key]['actor'] = $actor;
        }


        return $data;
    }
}
