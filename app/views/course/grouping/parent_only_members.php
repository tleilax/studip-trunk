<?= $this->render_partial('course/grouping/_perm_level',
    ['level' => 'dozent', 'current' => $course, 'members' => $parentOnly->findBy('status', 'dozent')]) ?>
<?= $this->render_partial('course/grouping/_perm_level',
    ['level' => 'deputy', 'current' => $course, 'members' =>
        SimpleORMapCollection::createFromArray(Deputy::findByRange_id($course->id))
            ->orderBy('nachname vorname')]) ?>
<?php foreach (words('tutor autor user') as $level) : $members = $parentOnly->findBy('status', $level) ?>
    <?php if (count($members) > 0) : ?>
        <?= $this->render_partial('course/grouping/_perm_level',
            ['level' => $level, 'current' => $course, 'members' => $members]) ?>
    <?php endif ?>
<?php endforeach ?>
