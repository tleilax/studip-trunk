<?= $this->render_partial('course/grouping/_perm_level',
    array('level' => 'dozent', 'members' => $course->members->findBy('status', 'dozent'))) ?>
<?= $this->render_partial('course/grouping/_perm_level',
    array('level' => 'deputy', 'members' => Deputy::findByRange_id($course->id))) ?>
<?php foreach (words('tutor autor user') as $level) : $members = $course->members->findBy('status', $level) ?>
    <?php if (count($members) > 0) : ?>
        <?= $this->render_partial('course/grouping/_perm_level',
            array('level' => $level, 'members' => $members)) ?>
    <?php endif ?>
<?php endforeach ?>
