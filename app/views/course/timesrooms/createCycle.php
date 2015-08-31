<form
    action="<?= $controller->url_for('course/timesrooms/saveCycle' . ($cycle ? '/' . $cycle->getMetaDateID() : '')) ?>"
    class="studip-form" method="post"
    <?= Request::isXhr() ? 'data-dialog="size=big"' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>

    <? if ($has_bookings) : ?>
        <?= MessageBox::error(_('Wenn Sie die regelm��ige Zeit auf %s �ndern, verlieren Sie die Raumbuchungen f�r alle in der Zukunft liegenden Termine!'),
            array(_('Sind Sie sicher, dass Sie die regelm��ige Zeit �ndern m�chten?'))) ?>
    <? endif ?>


    <section>
        <label for="day">
            <?= _('Starttag') ?>
        </label>
        <select class="size-xl" name="day" id="day">
            <? foreach (range(1, 6) + array(6 => 0) as $d) : ?>
                <option
                    value="<?= $d ?>"<?= (Request::int('day', !is_null($cycle) ? $cycle->getDay() : null) === $d) ? 'selected' : (!Request::get('day', !is_null($cycle) ? $cycle->getDay() : null) && $d == 1) ? 'selected' : '' ?>>
                    <?= getWeekday($d, false) ?></option>
            <? endforeach; ?>
        </select>
    </section>
    <section class="clearfix">
        <div style="width: 150px; float: left">
            <label for="start_time">
                <?= _('Startzeit') ?>
            </label>
            <input class="size-m has-time-picker" type="time" name="start_time" id="start_time"
                   value="<?= htmlReady(Request::get('start_time', !is_null($cycle) ? $cycle->getStartTime() : null)) ?>"
                   required>
        </div>
        <div style="width: 150px; float: left">
            <label for="end_time">
                <?= _('Endzeit') ?>
            </label>
            <input class="size-m has-time-picker" type="time" name="end_time" id="end_time"
                   value="<?= htmlReady(Request::get('end_time', !is_null($cycle) ? $cycle->getEndTime() : null)) ?>"
                   required>
        </div>
    </section>

    <section>
        <label for="description">
            <?= _('Beschreibung') ?>
        </label>
        <input class="size-xl" type="text" name="description" id="description"
               value="<?= Request::get('description', !is_null($cycle) ? $cycle->getDescription() : null) ?>"/>
    </section>

    <section>
        <label for="cycle">
            <?= _('Turnus') ?>
        </label>
        <select name="cycle" id="cycle" class="size-xl">
            <option
                value="0"<?= Request::int('cycle', !is_null($cycle) ? $cycle->getTurnus() : null) === 0 ? 'selected' : '' ?>><?= _("w�chentlich"); ?></option>
            <option
                value="1"<?= Request::int('cycle', !is_null($cycle) ? $cycle->getTurnus() : null) == 1 ? 'selected' : '' ?>><?= _("zweiw�chentlich") ?></option>
            <option
                value="2"<?= Request::int('cycle', !is_null($cycle) ? $cycle->getTurnus() : null) == 2 ? 'selected' : '' ?>><?= _("dreiw�chentlich") ?></option>
        </select>
    </section>

    <section>
        <label for="startWeek">
            <?= _('Beginnt in der') ?>
        </label>
        <select name="startWeek" id="startWeek" class="size-xl">
            <? foreach ($start_weeks as $value => $data) : ?>
                <option
                    value=<?= $value ?> <?= Request::get('startWeek', !is_null($cycle) ? $cycle->week_offset : null) == $value ? 'selected' : '' ?>>
                    <?= htmlReady($data['text']) ?>
                </option>
            <? endforeach ?>
        </select>
    </section>

    <section>
        <label for="teacher_sws">
            <?= _('SWS Dozent') ?>
        </label>
        <input type="text" value="<?= htmlReady(Request::get('teacher_sws', !is_null($cycle) ? $cycle->sws : null)) ?>"
               class="size-s" name="teacher_sws"
               id="teacher_sws"/>
    </section>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
        <?= Studip\LinkButton::create(_('Zur�ck zur �bersicht'), $controller->url_for('course/timesrooms/index'), array('data-dialog' => 'size=big')) ?>
    </footer>
</form>
