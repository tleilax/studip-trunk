<? if ($course) : ?>
<?= $course->wikidump; ?>
<? else : ?>
<?= MessageBox::error(_('Es wurde keine Veranstaltung ausgew�hlt!')); ?>
<? endif ?>
