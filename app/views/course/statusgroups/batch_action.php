<?php
    if ($edit) {
        echo $this->render_partial('course/statusgroups/_edit_groups', array('groups' => $groups));
    } else if ($askdelete) {
        echo $this->render_partial('course/statusgroups/_askdelete_groups', array('groups' => $groups));
    } else if ($movemembers) {
        echo $this->render_partial('course/statusgroups/_move_members',
            array('target_groups' => $target_groups, 'members' => $members, 'source_group' => $source_group));
    } else if ($deletemembers) {
        echo $this->render_partial('course/statusgroups/_askdelete_members',
            array('members' => $members, 'source_group' => $source_group));
    }
