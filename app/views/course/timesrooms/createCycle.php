<form
    action="<?= $controller->url_for('course/timesrooms/' . ($cycle ? 'editCycle/' . $cycle->id : 'saveCycle'), $editParams) ?>"
    class="default" method="post"
    <?= Request::isXhr() ? 'data-dialog="size=big"' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>

    <? if ($has_bookings) : ?>
        <?= MessageBox::error(_('Wenn Sie die regelmäßige Zeit auf %s ändern, verlieren Sie die Raumbuchungen für alle in der Zukunft liegenden Termine!'),
            array(_('Sind Sie sicher, dass Sie die regelmäßige Zeit ändern möchten?'))) ?>
    <? endif ?>


    <label>
        <?= _('Starttag') ?>
        <select name="day">
            <? foreach (range(1, 6) + array(6 => 0) as $d) : ?>
                <option
                    value="<?= $d ?>"<?= (Request::int('day', !is_null($cycle) ? $cycle->weekday : null) === $d) ? 'selected' : (!Request::get('day', !is_null($cycle) ? $cycle->weekday : null) && $d == 1) ? 'selected' : '' ?>>
                    <?= getWeekday($d, false) ?>
                </option>
            <? endforeach; ?>
        </select>
    </label>
    <label>
        <?= _('Startzeit') ?>
        <input class="has-time-picker" type="text" name="start_time"
               value="<?= htmlReady(Request::get('start_time', !is_null($cycle) ? $cycle->start_time : null)) ?>"
               required>
    </label>

    <label>
        <?= _('Endzeit') ?>
        <input class="has-time-picker" type="text" name="end_time"
               value="<?= htmlReady(Request::get('end_time', !is_null($cycle) ? $cycle->end_time : null)) ?>"
               required>
    </label>

    <label>
        <?= _('Beschreibung') ?>
        <input type="text" name="description"
               value="<?= Request::get('description', !is_null($cycle) ? $cycle->description : null) ?>">
    </label>

    <label>
        <?= _('Turnus') ?>
        <select name="cycle">
            <option
                value="0"<?= Request::int('cycle', !is_null($cycle) ? $cycle->cycle : null) === 0 ? 'selected' : '' ?>><?= _("wöchentlich"); ?></option>
            <option
                value="1"<?= Request::int('cycle', !is_null($cycle) ? $cycle->cycle : null) == 1 ? 'selected' : '' ?>><?= _("zweiwöchentlich") ?></option>
            <option
                value="2"<?= Request::int('cycle', !is_null($cycle) ? $cycle->cycle : null) == 2 ? 'selected' : '' ?>><?= _("dreiwöchentlich") ?></option>
        </select>
    </label>

    <label>
        <?= _('Startwoche') ?>
        <select name="startWeek">
            <? foreach ($start_weeks as $value => $data) : ?>
                <option
                    value=<?= $value ?> <?= Request::get('startWeek', !is_null($cycle) ? $cycle->week_offset : null) == $value ? 'selected' : '' ?>>
                    <?= htmlReady($data['text']) ?>
                </option>
            <? endforeach ?>
        </select>
    </label>

    <label>
        <?= _('Endwoche') ?>
        <select name="endWeek">
            <option value="0"><?= _('Ganzes Semester') ?></option>
            <? foreach ($start_weeks as $value => $data) : ?>
                <option
                    value=<?= $value + 1 ?> <?= Request::get('endWeek', ($cycle ? $cycle->end_offset : null)) == $value + 1 ? 'selected' : '' ?>>
                    <?= htmlReady($data['text']) ?>
                </option>
            <? endforeach ?>
        </select>
    </label>
    <label for="teacher_sws">
        <?= _('SWS Dozent') ?>
        <input type="text" value="<?= htmlReady(Request::get('teacher_sws', !is_null($cycle) && $cycle->sws != 0 ? $cycle->sws : '')) ?>"
               name="teacher_sws"
               id="teacher_sws">
    </label>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
        <? if (Request::get('fromDialog') == 'true') : ?>
            <?= Studip\LinkButton::create(_('Zurück zur Übersicht'), $controller->url_for('course/timesrooms/index'), array('data-dialog' => 'size=big')) ?>
        <? endif ?>
    </footer>
</form>
