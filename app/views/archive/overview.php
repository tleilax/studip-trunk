<? if ($course) : ?>
<?= $course->dump; ?>
<? else : ?>
<?= MessageBox::error(_('Es wurde keine Veranstaltung ausgew�hlt!')); ?>
<? endif ?>
