<?php
namespace RESTAPI\Routes;

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 *
 * @condition user_id ^[a-f0-9]{32}$
 */
class Activity extends \RESTAPI\RouteMap
{
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
        if (!$GLOBALS['perm']->have_perm('root') && $GLOBALS['user']->id != $user_id) {
            $this->error(401);
        }

        $contexts = array();

        $user = \User::find($user_id);

        // create system context
        $system_context = new \Studip\Activity\SystemContext($user);
        $contexts[] = $system_context;

        $contexts[] = new \Studip\Activity\UserContext($user, $user);
        $user->contacts->each(function($another_user) use (&$contexts, $user) {
            $contexts[] = new \Studip\Activity\UserContext($another_user, $user);
        });

        if (!in_array($user->perms, ['admin','root'])) {
            // create courses and institutes context
            foreach (\Course::findMany($user->course_memberships->pluck('seminar_id')) as $course) {
                    $contexts[] = new \Studip\Activity\CourseContext($course, $user);
            }
            foreach (\Institute::findMany($user->institute_memberships->pluck('institut_id')) as $institute) {
                $contexts[] = new \Studip\Activity\InstituteContext($institute, $user);
            }
        }


        // add filters
        $filter = new \Studip\Activity\Filter();

        $start = \Request::int('start', strtotime('-2 days'));
        $end   = \Request::int('end',   time());

        $filtertype = \Request::get('filtertype', '');

        $filter->setStartDate($start);
        $filter->setEndDate($end);

        if (!empty($filtertype)) {
            $filter->setType($filtertype);
        }

        $stream = new \Studip\Activity\Stream($contexts, $filter);

        $data = $stream->toArray();
        // set etag for preventing resending the same stuff over and over again
        $this->etag(md5(serialize($data)));

        foreach ($data as $key => $act) {
            $actor = array(
                'type' => $data[$key]['actor_type'],
                'id'   => $data[$key]['actor_id']
            );

            if ($data[$key]['actor_type'] == 'user') {
                $a_user = \User::findFull($data[$key]['actor_id']);
                $actor['details'] = User::getMiniUser($this, $a_user ?: new \User());
            }

            unset($data[$key]['actor_type']);
            unset($data[$key]['actor_id']);

            $data[$key]['actor'] = $actor;
        }

        return $data;
    }
}
