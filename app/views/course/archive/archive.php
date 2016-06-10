<?= MessageBox::info(_('Veranstaltung(en) erfolgreich archiviert!')); ?>
<p>Die folgenden Veranstaltungen wurden erfolgreich archiviert:</p>

<table class="default">
    <tr>
        <th><?= _('Name der Veranstaltung'); ?></th>
        <th><?= _('Semester'); ?></th>
    </tr>
<? foreach ($deletedCourses as $course) : ?>
    <tr>
        <td><?= $course->name ?></td>
        <td><?= $course->start_semester->name ?></td>
    </tr>
<? endforeach ?>
</table>