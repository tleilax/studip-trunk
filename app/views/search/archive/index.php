<p><?= _('Bitte geben Sie hier Ihre Suchkriterien ein') . ':'; ?></p>
<form class="default" method="post" action="<?= URLHelper::getLink(); ?>">
    <!--
    <label>
        <?= _('Name der Veranstaltung') . ':'; ?>
        <input type="text" size="30" maxlength="255" name="courseName"
            value="<?= htmlReady($courseName) ?>">
    </label>
    <label>
        <?= _('Lehrende der Veranstaltung') . ':'; ?>
        <input type="text" size="30" maxlength="255" name="courseDozenten"
            value="<?= htmlReady($courseDozenten) ?>">
    </label>
    <label>
        <?= _('Semester') . ':'; ?>
        <input type="text" size="30" maxlength="255" name="courseSemester"
            value="<?= htmlReady($courseSemester) ?>">
    </label>
    <label>
        <?= _('Heimat-Einrichtung') . ':'; ?>
        <select name="courseInstitute">
            <option value="#all#"><?= _('alle'); ?></option>
            <? foreach($availableInstitutes as $institute) : ?>
            <option value=""></option>
            <? endforeach ?>
        </select>
    </label>
    <label>
        <?= _('Fakultät') . ':'; ?>
        <select name="courseInstitute">
            <option value="#all#"><?= _('alle'); ?></option>
            <? foreach($availableFaculties as $faculty) : ?>
            <option value=""></option>
            <? endforeach ?>
        </select>
    </label>
    <label>
        <?= _('Beschreibung') . ':'; ?>
        <input type="text" size="30" maxlength="255" name="courseDescription"
            value="<?= htmlReady($courseDescription) ?>">
    </label>-->
    <label>
        <?= _('Suche über alle Felder') . ':'; ?>
        <input type="text" size="30" maxlength="255" name="courseAllFields"
            value="<?= htmlReady($courseAllFields) ?>">
    </label>
    <label>
        <?= _('Nur Veranstaltungen anzeigen, an denen ich teilgenommen habe') . ':'; ?>
        <input type="checkbox" name="courseOnlyParticipated"
            <?= $courseOnlyParticipated ? 'checked="checked"' : '' ?> >
    </label>
    <?= \Studip\Button::create(_('Suchen'), 'searchRequested', array('value' => '1')); ?>
</form>

<? if ($foundCourses) : ?>
    <? if (count($foundCourses) == 1) : ?>
        <?= MessageBox::info(_('Die folgende Veranstaltung wurde gefunden') . ':'); ?>
    <? else : ?>
        <?= MessageBox::info(_('Die folgenden Veranstaltungen wurde gefunden') . ':'); ?>
    <? endif ?>
    
    <table class="default withdetails">
        <tr>
            <th><?= _('Name'); ?></th>
            <th><?= _('Lehrende'); ?></th>
            <th><?= _('Einrichtungen'); ?></th>
            <th><?= _('Semester'); ?></th>
        </tr>
    <? foreach ($foundCourses as $course) : ?>
        <tr>
            <td>
                <a onclick="jQuery(this).closest('tr').toggleClass('open'); return false;" href="">
                    <?= $course->name; ?>
                </a>
            </td>
            <td><?= $course->dozenten; ?></td>
            <td><?= $course->institute; ?></td>
            <td><?= $course->semester; ?></td>
        </tr>
        <tr>
            <td colspan="4">
                <ul>
                    <li>
                        <strong><?= _('Fakultät'); ?></strong>
                    </li>
                    <li>
                        <strong><?= _('Bereich'); ?></strong>
                    </li>
                    <li>
                        <a href="<?= $controller->url_for(
                                        'search/archive/dump',
                                        array('dumpId' => $course->id)
                                        ); ?>">
                            <?= _('Übersicht der Veranstaltungsinhalte'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= $controller->url_for(
                                        'search/archive/forum',
                                        array('dumpId' => $course->id)
                                        ); ?>">
                            <?= _('Beiträge des Forums'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= $controller->url_for(
                                        'search/archive/wiki',
                                        array('dumpId' => $course->id)
                                        ); ?>">
                            <?= _('Wikiseiten'); ?>
                        </a>
                    </li>
                </ul>
            </td>
        </tr>
    <? endforeach ?>
    </table>
<? else : ?>
    <? if ($searchExecuted) : ?>
        <?= MessageBox::info(_('Es wurde keine Veranstaltung gefunden!')); ?>
    <? endif ?>
<? endif ?>