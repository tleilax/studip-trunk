<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<? if (array_key_exists($m['user_id'], $moderators) && $GLOBALS['perm']->have_studip_perm('admin', $sem_id)) : ?>
    <?= LinkButton::create(_("Runterstufen"), $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/promote/tutor?user='.$m['username'])) ?>
<? elseif (array_key_exists($m['user_id'], $tutors)) : ?>
    <?= LinkButton::create(_("Runterstufen"), $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/promote/autor?user='.$m['username'])) ?>
<? else : ?>
    <?= LinkButton::create(_("Hochstufen"), $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/promote/tutor?user='.$m['username'])) ?>
    <?= LinkButton::create(_("Rauswerfen"), $controller->url_for('course/studygroup/edit_members/'.$sem_id.'/remove?user='.$m['username'])) ?>
<? endif ?>
