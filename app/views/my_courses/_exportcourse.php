<? foreach ($course_collection as $course)  : ?>
<?
    $teachers   = CourseMember::findByCourseAndStatus($course['seminar_id'], 'dozent');
    $collection = SimpleCollection::createFromArray($teachers);
    $dozenten =  $collection->map(function (CourseMember $teacher) {
        return array(
            'user_id'  => $teacher->user_id,
            'username' => $teacher->username,
            'Nachname' => $teacher->nachname,
            'fullname' => $teacher->getUserFullname('no_title_rev'),
        );
    });

    $trail_classes = array('Modulteil', 'StgteilabschnittModul', 'StgteilAbschnitt', 'StgteilVersion');
    $mvv_object_pathes = MvvCourse::get($course['seminar_id'])->getTrails($trail_classes);
    if ($mvv_object_pathes) {
        if (Config::get()->COURSE_SEM_TREE_DISPLAY) {
            $mvv_tree = array();
            foreach ($mvv_object_pathes as $mvv_object_path) {
                // show only complete pathes
                if (count($mvv_object_path) == 4) {
                    // flatten the pathes to a linked list
                    $stg = reset($mvv_object_path);
                    $parent_id = 'root';
                    foreach ($mvv_object_path as $mvv_object) {
                        $mvv_object_id = $mvv_object instanceof StgteilabschnittModul
                                ? $mvv_object->modul_id
                                : $mvv_object->id;
                        $mvv_tree[$parent_id][$mvv_object_id] =
                                ['id'    => $mvv_object_id,
                                    'name'  => $mvv_object->getDisplayName(),
                                    'class' => get_class($mvv_object)];
                        $parent_id = $mvv_object_id;
                    }
                }
            }
            if (count($mvv_tree)) {
                // add the root node
                $mvv_tree['start'][] = [
                    'id'    => 'root',
                    'name'  => Config::get()->UNI_NAME_CLEAN,
                    'class' => ''
                ];
            }
        } else {
            foreach ($mvv_object_pathes as $mvv_object_path) {
                // show only complete pathes
                if (count($mvv_object_path) == 4) {
                    $mvv_object_names = array();
                    $modul_id = '';
                    foreach ($mvv_object_path as $mvv_object) {
                        if ($mvv_object instanceof StgteilabschnittModul) {
                            $modul_id = $mvv_object->modul_id;
                        }
                        $mvv_object_names[] = $mvv_object->getDisplayName();
                    }
                    $mvv_pathes[] = array($modul_id => $mvv_object_names);
                }
            }
        }
        // to prevent collisions of object ids in the tree
        // in the case of same objects listed in more than one part
        // of the tree
        $id_sfx = new stdClass();
        $id_sfx->c = 1;
    }
    $sem_class = $course['sem_class'];

?>

<h3>
<? if ($course['veranstaltungsnummer']): ?>
<?= htmlReady($course['veranstaltungsnummer']).' ' ?>
<? endif; ?>
<?= htmlReady($course['name']) ?>
</h3>

<? if ($course['untertitel']): ?>
<p><?= htmlReady($course['untertitel']) ?></p>
<? endif; ?>

<? if ($dozenten): ?>
<p><b><?= _("Lehrende") ?></b>
<br>
<? foreach ($dozenten as $dozent): ?>
<?= $colon ? ', ' : '' ?><?= htmlReady($dozent["fullname"]) ?><? $this->colon = true; ?>
<? endforeach; ?>
</p>
<? endif; ?>

<? if ($mvv_object_pathes): ?>
<p><b><?= _("Module") ?></b></p>
<ul class="collapsable css-tree">
    <?= $this->render_partial('shared/mvv_tree.php', array('tree' => $mvv_tree, 'node' => 'start', 'id_sfx' => $id_sfx)) ?>
</ul>
<? endif; ?>

<br>

<? if ($course['children']) : ?>
    <?= $this->render_partial('my_courses/_exportcourse', [
        'course_collection' => $course['children'],
        'children'          => true,
        'gruppe'            => $course['gruppe'],
    ]) ?>
<? endif ?>
<? endforeach ?>
