<form name="configure_courses" action="<?= $controller->url_for('admission/courseset/configure_courses/' . $set_id) ?>" method="post">
<table class="default">
<thead>
<tr>
<th><?= _("Name")?></th>
<th><?= _("Dozenten")?></th>
<th><?= _("max. Teilnehmer")?></th>
<th><?= _("Teilnehmer aktuell")?></th>
<th><?= _("Warteliste")?></th>
</tr>
</thead>
<tbody>
<? foreach ($courses as $course) : ?>
<tr>
<td><?= htmlReady($course->name)?></td>
<td><?= htmlReady(join(', ', $course->members->findBy('status','dozent')->orderBy('position')->limit(3)->pluck('Nachname')))?></td>
<td><input type="text" size="2" name="configure_courses_turnout[<?= $course->id?>]" value="<?= (int)$course->admission_turnout ?>"></td>
<td><?= count($course->members->findBy('status', words('user autor')))?></td>
<td><input type="checkbox" name="configure_courses_disable_waitlist[<?= $course->id?>]" value="1" <?= $course->admission_disable_waitlist ? '' : 'checked' ?>></td>
</tr>
<? endforeach ?>
</tbody>
</table>
<div style="text-align:center">
<?= Studip\Button::create(_("Speichern"), 'configure_courses_save') ?>
</div>
<?= CSRFProtection::tokenTag()?>
</form>
<? 
