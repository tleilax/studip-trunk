<?php
    if ($edit) {
        echo $this->render_partial('course/statusgroups/_edit_groups', array('groups' => $groups));
    } else if ($askdelete) {
        echo $this->render_partial('course/statusgroups/_askdelete_groups', array('groups' => $groups));
    }
