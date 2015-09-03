<form action="<?= $controller->url_for('course/timesrooms/saveSingleDate', $editParams) ?>" method="post"
      class="studip-form" <?= Request::isXhr() ? 'data-dialog=size=big"' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>
    <section>
        <label for="date">
            <?= _('Datum') ?>
        </label>
        <input class="size-l has-date-picker" type="text" name="date" id="date"
               value="<?= htmlReady(Request::get('date')) ?>" required />
    </section>

    <section class="clearfix">
        <div style="width: 100px; float: left">
            <label for="start_time">
                <?= _('Startzeit') ?>
            </label>
            <input class="size-m has-time-picker" type="time" name="start_time" id="start_time"
                   value="<?= htmlReady(Request::get('start_time')) ?>" required>
        </div>
        <div style="width: 100px; float: left">
            <label for="end_time">
                <?= _('Endzeit') ?>
            </label>
            <input class="size-m has-time-picker" type="time" name="end_time" id="end_time"
                   value="<?= htmlReady(Request::get('end_time')) ?>" required>
        </div>
    </section>
    <? if (Config::get()->RESOURCES_ENABLE) : ?>
        <section>
            <label for="room">
                <?= _('Raum') ?>
            </label>
            <select name="room" id="room" class="size-l">
                <option value="nothing"><?= _("KEINEN Raum buchen") ?></option>
                <? $resList->reset();
                if ($resList->numberOfRooms()) : ?>
                    <? while ($res = $resList->next()) : ?>
                        <option
                            value="<?= $res['resource_id'] ?>" <?= in_array($res['resource_id'], Request::getArray('room')) ? 'selected' : '' ?>>
                            <?= my_substr(htmlReady($res["name"]), 0, 30) ?> <?= $seats[$res['resource_id']] ? '(' . $seats[$res['resource_id']] . ' ' . _('Sitzplätze') . ')' : '' ?>
                        </option>
                    <? endwhile ?>
                <? endif ?>
            </select>

            <a href="#" class="bookable_rooms_action" title="<?= _("Nur buchbare Räume anzeigen") ?>">
                <?= Assets::img('icons/16/blue/room-clear.png') ?>
            </a>
        </section>
    <? endif ?>


    <section>
        <label for="freeRoomText">
            <?= _('Freie Ortsangabe') ?>
        </label>
        <input value="<?= htmlReady(Request::get('freeRoomText')) ?>" class="size-l" id="freeRoomText"
               name="freeRoomText" type="text" maxlength="255">
        <? if (Config::get()->RESOURCES_ENABLE) : ?>
            <small style="display: block"><?= _('(führt <em>nicht</em> zu einer Raumbuchung)') ?></small>
        <? endif ?>
    </section>

    <section>
        <label for="related_teachers"><?= _('Durchführende Lehrende') ?></label>
        <? if (count($teachers)) : ?>
            <select id="related_teachers" name="related_teachers[]" multiple class="size-l multiple">
                <? foreach ($teachers as $dozent) : ?>
                    <option <?= in_array($dozent['user_id'], Request::getArray('related_teachers')) ? 'selected' : '' ?>
                        value="<?= $dozent['user_id'] ?>"><?= htmlReady($dozent['fullname']) ?></option>
                <? endforeach; ?>
            </select>
        <? endif; ?>
    </section>

    <? if (count($groups) > 0) : ?>
        <section>
            <label for="related_statusgruppen"><?= _('Beteiligte Gruppen') ?></label>

            <select id="related_statusgruppen" name="related_statusgruppen[]" multiple class="size-l multiple">
                <? foreach ($groups as $group) : ?>
                    <option <?= in_array($group->getId(), Request::getArray('related_statusgruppen')) ? 'selected' : '' ?>
                        value="<?= $group->getId() ?>"><?= htmlReady($group['name']) ?></option>
                <? endforeach; ?>
            </select>
        </section>
    <? endif; ?>

    <section>
        <label for="dateType">
            <?= _('Art'); ?>
        </label>
        <select class="size-l" id="dateType" name="dateType">
            <? foreach ($GLOBALS['TERMIN_TYP'] as $key => $val) : ?>
                <option <?= Request::get('dateType') == $key ? 'selected' : '' ?>
                    value="<?= $key ?>"><?= htmlReady($val['name']) ?></option>
            <? endforeach ?>
        </select>
    </section>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
        <?= Studip\LinkButton::create(_('Zurück zur Übersicht'), $controller->url_for('course/timesrooms/index'), array('data-dialog' => 'size=big')) ?>
    </footer>
</form>
