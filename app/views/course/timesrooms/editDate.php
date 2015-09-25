<div style="width: 45%; float: left">
<?//var_dump($date);die;?>
    <section>
        <label for="date">
            <?= _('Datum') ?>
        </label>
        <input class="size-m has-date-picker" type="text" name="date" id="date"
               value="<?= $date->date ? strftime('%d.%m.%Y', $date->date) : '' ?>"/>
    </section>

    <section class="clearfix">
        <div style="display: inline-block; width: 130px">
            <label for="start_time">
                <?= _('Startzeit') ?>
            </label>
            <input class="size-l has-time-picker" type="time" name="start_time" id="start_time"
                   value="<?= $date->date ? strftime('%H:%M', $date->date) : '' ?>">
        </div>
        <div style="display: inline-block; width: 130px">
            <label for="end_time">
                <?= _('Endzeit') ?>
            </label>
            <input class="size-l has-time-picker" type="time" name="end_time" id="end_time"
                   value="<?= $date->end_time ? strftime('%H:%M', $date->end_time) : '' ?>">
        </div>
    </section>
    
    <section>
        <label id="course_type">
            <?= _('Art') ?>
        </label>
        <select class="size-m" name="course_type" id="course_type">
            <? foreach ($GLOBALS['TERMIN_TYP'] as $id => $value) : ?>
                <option value="<?= $id ?>"
                    <?= $date_info->date_typ == $id ? 'selected' : '' ?>>
                    <?= htmlReady($value['name']) ?>
                </option>
            <? endforeach; ?>
        </select>

    </section>
    
    <? if (!empty($dozenten)) : ?>
        <section style="margin: 15px 0">
            <p><strong><?= _('Durchführende Dozenten:') ?></strong></p>

            <ul class="termin_related teachers">
                <? foreach ($dozenten as $related_person => $dozent) : ?>

                    <? $related = false;
                    if (in_array($related_person, $related_persons) !== false || empty($related_persons)) :
                        $related = true;
                    endif ?>

                    <li data-lecturerid="<?= $related_person ?>" <?= $related ? '' : 'style="display: none"' ?>>
                        <? $dozenten[$related_person]['hidden'] = $related ?>
                        <?= htmlReady(User::find($related_person)->getFullname()); ?>

                        <a href="javascript:" onClick="STUDIP.Raumzeit.removeLecturer('<?= $related_person ?>')">
                            <?= Assets::img('icons/16/blue/trash.png') ?>
                        </a>
                    </li>
                <? endforeach ?>
            </ul>
            <input type="hidden" name="related_teachers" value="<?= implode(',', $related_persons) ?>"/>

            <label for="add_teacher">
                <?= _('Lehrenden auswählen') ?>
            </label>
            <select id="add_teacher" name="teachers" class="size-m">
                <option value="none"><?= _('Dozent/in auswählen') ?></option>
                <? foreach ($dozenten as $dozent) : ?>
                    <option
                        value="<?= htmlReady($dozent['user_id']) ?>" <?= $dozent['hidden'] ? 'style="display: none"' : '' ?>>
                        <?= htmlReady($dozent['fullname']) ?>
                    </option>
                <? endforeach; ?>
            </select>
            <a href="javascript:" onClick="STUDIP.Raumzeit.addLecturer()" title="<?= _('Lehrenden hinzufügen') ?>">
                <?= Assets::img('icons/16/yellow/arr_2up.png') ?>
            </a>
        </section>
    <? endif ?>
</div>


<div style="float: right; width: 45%">


    <p><strong><?= _('Raumangaben') ?></strong></p>

    <? if (Config::get()->RESOURCES_ENABLE && $resList->numberOfRooms()) : ?>

        <section>
            <label class="horizontal">
                <input style="display: inline;" type="radio" name="room" value="room"
                       id="room" <?= $date->room_assignment->resource_id ? 'checked' : '' ?> />
            </label>

            <select name="room_sd" style="display: inline-block; margin-left: 40px" class="single_room size-m">
                <option value=""><?= _('Wählen Sie einen Raum aus') ?></option>
                <? foreach ($resList->resources as $room_id => $room) : ?>
                    <option value="<?= $room_id ?>"
                        <?= $date->room_assignment->resource_id == $room_id ? 'selected' : '' ?>>
                        <?= $room ?>
                    </option>
                <? endforeach; ?>
            </select>
        </section>

    <? endif; ?>

    <section>
        <label class="horizontal">
            <input type="radio" name="room" value="freetext" <?= $date->raum ? 'checked' : '' ?>
                   style="display: inline"/>
        </label>
        <input style="margin-left: 40px; display: inline-block" type="text" class="size-m"
               name="freeRoomText_sd"
               placeholder="<?= _('freie Ortsangabe (keine Raumbuchung):') ?>"
               value="<?= $date->raum ? htmlReady($date->raum) : '' ?>"/>
    </section>

    <section>
        <label class="horizontal">
            <input type="radio" name="room" style="display:inline;" value="noroom"
                <?= (!empty($date->room_assignment->resource_id) || !empty($date->raum) ? '' : 'checked') ?> />
            <span style="display: inline-block; margin-left: 40px"><?= _('kein Raum') ?></span>
        </label>
    </section>

    <? if (!empty($gruppen)) : ?>
        <p><strong><?= _('Beteiligte Gruppen') ?>:</strong></p>

        <ul class="termin_related groups">
            <? foreach ($gruppen as $index => $statusgruppe) : ?>
                <? $related = false ?>
                <? if (in_array($statusgruppe->getId(), $related_groups) || empty($related_groups)) : ?>
                    <? $related = true; ?>
                <? endif ?>
                <li data-groupid="<?= htmlReady($statusgruppe->getId()) ?>" <?= $related ? '' : 'style="display: none"' ?>>
                    <?= htmlReady($statusgruppe['name']) ?>
                    <a href="javascript:" onClick="STUDIP.Raumzeit.removeGroup('<?= $statusgruppe->getId() ?>')">
                        <?= Assets::img('icons/blue/trash') ?>
                    </a>
                </li>
            <? endforeach ?>
        </ul>

        <input type="hidden" name="related_statusgruppen" value="<?= implode(',', $related_groups) ?>">

        <select name="groups">
            <option value="none"><?= _('Gruppen auswählen') ?></option>
            <? foreach ($gruppen as $gruppe) : ?>
                <option value="<?= htmlReady($gruppe->getId()) ?>"
                        style="<?= in_array($gruppe->getId(), $related_groups) ? 'display: none;' : '' ?>">
                    <?= htmlReady($gruppe['name']) ?>
                </option>
            <? endforeach ?>
        </select>
        <a href="javascript:" onClick="STUDIP.Raumzeit.addGroup()" title="<?= _('Gruppe hinzufügen') ?>">
            <?= Assets::img('icons/16/yellow/arr_2up.png') ?>
        </a>
    <? endif ?>
</div>

<footer data-dialog-button style="text-align: center; clear: both">
    <?= Studip\Button::createAccept(_('Speichern'), 'save_dates',
        array('formaction' => $controller->url_for('course/timesrooms/saveDate/' . $termin_id),
              'data-dialog' => 'size=big')) ?>
    <?= Studip\LinkButton::create(_('Raumanfrage erstellen'), $controller->url_for('course/room_requests/edit/' . $course->id, $params),
        array('data-dialog' => 'size=big')) ?>
</footer>
