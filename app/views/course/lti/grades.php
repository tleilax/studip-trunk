<?
if ($this->edit_perm) {
    $widget = new ExportWidget();
    $widget->addLink(_('Ergebnisse exportieren'), $controller->url_for('course/lti/export_grades'), Icon::create('download'));
    Sidebar::get()->addWidget($widget);
}

Helpbar::get()->addPlainText('', _('Auf dieser Seite können Sie die Ergebnisse sehen, die von LTI-Tools zurückgemeldet wurden.'));
?>

<table class="default">
    <caption>
        <?= _('Ergebnisse') ?>
    </caption>

    <thead>
        <tr>
            <th>
                <?= _('Teilnehmende') ?>
            </th>
            <? foreach ($lti_data_array as $lti_data): ?>
                <th style="text-align: right;">
                    <?= htmlReady($lti_data->title) ?>
                </th>
            <? endforeach ?>
        </tr>
    </thead>

    <tbody>
        <? foreach (CourseMember::findByCourseAndStatus($this->course_id, 'autor') as $member): ?>
            <tr>
                <td>
                    <?= htmlReady($member->nachname) ?>, <?= htmlReady($member->vorname) ?>
                </td>
                <? foreach ($lti_data_array as $lti_data): ?>
                    <td style="text-align: right;">
                        <? if ($grade = $lti_data->grades->findOneBy('user_id', $member->user_id)): ?>
                            <?= sprintf('%.0f%%', $grade->score * 100) ?>
                        <? else: ?>
                            &ndash;
                        <? endif ?>
                    </td>
                <? endforeach ?>
            </tr>
        <? endforeach ?>
    </tbody>
</table>
