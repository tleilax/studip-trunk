<div id="enrollment">
    <label for="admission_user_limit"><?= _("Ich möchte folgende Anzahl an Veranstaltungen belegen:") ?></label>
    <select name="admission_user_limit">
        <? foreach(range(1, $max_limit) as $max) : ?>
        <option <?= $user_max_limit == $max ? 'selected' : '' ?>>
            <?= $max ?>
        </option>
        <? endforeach ?>
    </select>
    <h2> <?= _("Verfügbare Veranstaltungen") ?></h2>

    <ul id="avaliable-courses">
        <?php $prios = array(); ?>
        <?php foreach ($priocourses as $course): ?>
            <?php $prios[$course->id] = htmlReady($course->name) ?>
            <li class="<?= htmlReady($course->id) ?>" <?= isset($user_prio[$course->id])?'style="display:none"':''?>><?= htmlReady($course->name) ?></li>
        <?php endforeach; ?>
    </ul>
    <h2><?= _("Ausgewählte Veranstaltungen") ?></h2>
    <ul id="selected-courses">
        <?php $hasUserPrios = count($user_prio) > 0 ?>

        <li class="empty" <?= $hasUserPrios ? 'style="display:none"' : '' ?>><?= _('Verfügbare Veranstaltungen hierhin droppen') ?></li>
            <?php
            asort($user_prio);
            if ($hasUserPrios):
                foreach ($user_prio as $id => $prio):
                    ?>
                <li class="<?= $id ?>"><?= $prios[$id] ?> <span class="<?= $id ?> delete">delete</span></li>
                <?php
            endforeach;
        endif;
        ?>
    </ul>

</div>