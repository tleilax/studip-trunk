<?php

/**
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

interface Activities
{
    function getActivityObjects($course_id, $user_id, $filter);
}
