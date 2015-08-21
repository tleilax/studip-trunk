<div class="clearfix">
    <div style="width: 50%; float: left">
        <section>
            <label for="date">
                <?= _('Datum') ?>
            </label>
            <input class="size-l has-date-picker" type="text" name="date" id="date"
                   value="<?= $date_info->date ? strftime('%d.%m.%G', $date_info->date) : 'tt.mm.jjjj' ?>" />
        </section>

        <section class="clearfix">
            <section style="width: 50%; float: left">
                <label for="start_time">
                    <?= _('Startzeit') ?>
                </label>
                <input class="size-m has-time-picker" type="time" name="start_time" id="start_time"
                       value="<?= $date_info->date ? strftime('%H:%M', $date_info->date) : '--:--' ?>">
            </section>
            <section style="width: 50%; float: right">
                <label for="end_time">
                    <?= _('Endzeit') ?>
                </label>
                <input class="size-m has-time-picker" type="time" name="end_time" id="end_time"
                       value="<?= $date_info->end_time ? strftime('%H:%M', $date_info->end_time) : '--:--' ?>">
            </section>
        </section>

        <section>
            <label id="course_type">
                <?= _('Art') ?>
            </label>
            <select class="size-s" name="course_type" id="course_type">
                <? foreach ($types as $id => $value) : ?>
                    <option value="<?= $id ?>"
                        <?= $date_info->date_typ == $id ? 'selected' : '' ?>>
                        <?= htmlReady($value['name']) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </section>



        <section style="margin: 20px 0">
            <p><strong><?= _('Durchführende Lehrende:') ?></strong></p>
            <? if (!empty($dozenten)) : ?>
                <ul>
                    <? foreach ($dozenten as $related_person => $dozent) : ?>
                        <? $related = false;
                        if (in_array($related_person, $related_persons) !== false) :
                            $related = true;
                        endif ?>
                        <li data-lecturerid="<?= $related_person ?>" <?= $related ? '' : 'style="display: none"' ?>>
                            <? $dozenten[$related_person]['hidden'] = $related ?>
                            <?= htmlReady(User::find($related_person)->getFullname()); ?>

                            <a href="javascript:" onClick="STUDIP.Raumzeit.removeLecturer('<?= $related_person ?>')">
                                <?= Assets::img('icons/blue/trash') ?>
                            </a>
                        </li>
                        <? unset($dozenten[$related_person]) ?>
                        <input type="hidden" name="related_teachers" value="<?= implode(',', $related_persons) ?>">
                    <? endforeach; ?>
                </ul>
            <? else : ?>
                <p><?= _('Keine Lehrende eingetragen') ?></p>
            <? endif ?>

            <? if (!empty($dozenten)) : ?>
                <section style="margin-top: 20px">
                    <label for="add_teacher">
                        <?= _('Lehrenden auswählen') ?>
                    </label>
                    <select id="add_teacher" name="add_teacher">
                        <option value="none"><?= _('-- Dozent/in auswählen --') ?></option>
                        <? foreach ($dozenten as $dozent) : ?>
                            <option value="<?= htmlReady($dozent['user_id']) ?>" <?= $dozent['hidden'] ? 'style="display: none"' : '' ?>>
                                <?= htmlReady($dozent['fullname']) ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                    <?= Assets::input('icons/16/blue/add.png',
                        array('title'       => _('Dozenten zu diesem Termin hinzufügen'),
                              'formaction'  => $controller->url_for('course/timesrooms/addRelatedPerson/' . $termin->id),
                              'data-dialog' => 'size=big')
                    ) ?>
                </section>
            <? endif ?>
        </section>


    </div>

    <div style="float: right; width: 50%">
        <p><strong><?= _('Raumangaben') ?></strong></p>
        <? if (Config::get()->RESOURCES_ENABLE && $resList->numberOfRooms()) : ?>
            <section class="clearfix" style="margin: 10px 0">

                <label class="horizontal">
                    <input style="display: inline;" type="radio" name="room"
                           id="room" <?= !empty($date_info->resource_id) ? 'checked' : '' ?> />
                </label>

                <select style="display: inline-block; margin-left: 40px" class="single_room size-m">
                    <option value=""><?= _('Wählen Sie einen Raum aus') ?></option>
                    <? foreach ($resList->resources as $room_id => $room) : ?>
                        <option value="<?= $room_id ?>"
                            <?= $date_info->resource_id == $room_id ? 'selected' : '' ?>>
                            <?= $room ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </section>
        <? endif; ?>
        <section style="margin: 10px 0">
            <label class="horizontal">
                <input type="radio" name="room" <?= !empty($date_info->raum) ? 'checked' : '' ?> style="display: inline" />
            </label>
            <input style="margin-left: 40px; display: inline-block" type="text" class="size-m"
                   placeholder="<?= _('freie Ortsangabe (keine Raumbuchung)') ?>"
                   value="<?= isset($date_info->raum) ? htmlReady($date_info->raum) : '' ?>">
        </section>
        <section style="margin: 10px 0">
            <label class="horizontal">
                <input type="radio" name="room" style="display:inline;"
                    <?= !empty($date_inf->resource_id) ? '' : (!empty($date_info->raum) ? '' : 'checked') ?>>
                <span style="display: inline-block; margin-left: 40px"><?= _('kein Raum') ?></span>
            </label>
        </section>

        <section style="margin: 20px 0">
            <p><strong><?= _('Beteiligte Gruppen') ?>:</strong></p>

            <ul class="termin_related groups">
                <? foreach ($gruppen as $index => $statusgruppe) : ?>
                    <? $related = false ?>
                    <? if (in_array($statusgruppe->getId(), $related_groups)) : ?>
                        <? $related = true; ?>
                    <? endif ?>
                    <li data-groupid="<?= htmlReady($statusgruppe->getId()) ?>" <?= $related ? '' : 'style="display: none"' ?>>
                        <?= htmlReady($statusgruppe['name']) ?>
                        <a href="javascript:" onClick="STUDIP.Raumzeit.removeGroup('<?= $statusgruppe->getId() ?>')">
                            <?= Assets::img('icons/blue/trash') ?>
                        </a>
                    </li>
                    <? unset($gruppen[$index]) ?>
                <? endforeach ?>
            </ul>

            <input type="hidden" name="related_statusgruppen" value="<?= implode(',', $related_groups) ?>">

            <? if (!empty($gruppen)) : ?>
                <select name="groups">
                    <option value="none"><?= _('-- Gruppen auswählen --') ?></option>
                    <? foreach ($gruppen as $gruppe) : ?>
                        <option value="<?= htmlReady($gruppe->getId()) ?>" style="<?= in_array($gruppe->getId(), $related_groups) ? 'display: none;' : '' ?>">
                            <?= htmlReady($gruppe['name']) ?>
                        </option>
                    <? endforeach ?>
                </select>
                <a href="javascript:" onClick="STUDIP.Raumzeit.addGroup()" title="<?= _('Gruppe hinzufügen') ?>">
                    <?= Assets::img('icons/16/yellow/arr_2up.png') ?>
                </a>
            <? endif ?>
        </section>
    </div>
</div>

<div data-dialog-button style="text-align: center">
    <?= Studip\Button::createAccept(_('Speichern'), 'save_dates',
        array('formaction'  => $controller->url_for('course/timesrooms/editSingleDate/' . $termin_id),
              'data-dialog' => 'size=big'
        )) ?>
</div>
