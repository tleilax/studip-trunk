<? if (array_key_exists($m['user_id'], $moderators) && $GLOBALS['perm']->have_studip_perm('admin', $sem_id)) : ?>
    &nbsp;<a href="<?= $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['username'].'/promote/tutor') ?>" alt="NutzerIn runterstufen">
        <?= makebutton('runterstufen') ?>
    </a>
<? elseif (array_key_exists($m['user_id'], $tutors)) : ?>
    <? if ($GLOBALS['perm']->have_studip_perm('admin', $sem_id)) : ?>
    &nbsp;<a href="<?= $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['username'].'/promote/dozent') ?>" alt="NutzerIn bef�rdern">
        <?= makebutton('hochstufen') ?>
    </a>
    
  <? endif; ?>

    &nbsp;<a href="<?= $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['username'].'/promote/autor') ?>" alt="NutzerIn runterstufen">
        <?= makebutton('runterstufen') ?>
    </a>
<? else : ?>
    &nbsp;<a href="<?= $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['username'].'/promote/tutor') ?>" alt="NutzerIn bef�rdern">
        <?= makebutton('hochstufen') ?>
    </a>
    &nbsp;<a href="<?= $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/'.$m['username'].'/remove') ?>" alt="NutzerIn runterstufen">
        <?= makebutton('rauswerfen') ?>
    </a>
<? endif ?>
