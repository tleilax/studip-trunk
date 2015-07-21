<div style="width: 47%; float: left">
    <label for="date">
        <b><?= _('Datum') ?></b>
        <input class="has-date-picker" style="display: block" type="text" name="date" id="date"
               value="<?= $date_info->date ? strftime('%d.%m.%G', $date_info->date) : 'tt.mm.jjjj' ?>">

    </label>
    <b><?= _('Uhrzeit') ?></b>
    <label for="start_time">
        <?= _('von') ?>
        <input style="display: block" type="time" name="start_time" id="start_time"
               value="<?= $date_info->date ? strftime('%H:%M', $date_info->date) : '--:--' ?>">
    </label>
    <label for="end_time">
        <?= _('bis') ?>
        <input style="display: block" type="time" name="end_time" id="end_time"
               value="<?= $date_info->end_time ? strftime('%H:%M', $date_info->end_time) : '--:--' ?>">
    </label>    
    <label id="course_type">
        <b><?= _('Art') ?></b>
        <select style="display: block" name="course_type" id="course_type">
            <? foreach ($types as $id => $value) : ?>
                <option value="<?= $id ?>"
                        <?= $date_info->date_typ == $id ? 'selected' : '' ?>>
                            <?= htmlReady($value['name']) ?>
                </option>
            <? endforeach; ?>
        </select>
    </label>
    <b><?= _('Durchführende Dozenten') ?></b><br>
    <? if (!empty($dozenten)) : ?>
        <ul style="list-style-type: none; width: 50%">
            <? foreach ($dozenten as $dozent) : ?>
                <li>
                    <?= htmlReady($dozent['Vorname']) ?> <?= htmlReady($dozent['Nachname']) ?>
                    <?=
                    Assets::img('icons/16/blue/remove.png', array('style' => 'float:right;', 'title' => sprintf(_('%s %s aus Termin austragen'), htmlReady($dozent['Vorname']), htmlReady($dozent['Nachname']))))
                    ?>
                </li>
            <? endforeach; ?>
        </ul>
    <? else : ?>
        <?= _('Keine Dozenten eingetragen') ?>
    <? endif; ?>
    <select name="addDozent">
        <option><?= _('Dozent/in auswählen') ?></option>
        <? foreach ($dozenten_options as $doz_id => $dozent_option) : ?>
            <? if (!key_exists($doz_id, $dozenten)) : ?>
                <option value="<?= $doz_id ?>">
                    <?= htmlReady($dozent_option['Vorname']) ?> <?= htmlReady($dozent_option['Nachname']) ?>
                </option>
            <? endif; ?>
        <? endforeach; ?>
    </select>
    <?= Assets::img('icons/16/blue/add.png', array('title' => _('Dozenten zu diesem Termin hinzufügen'))) ?>
</div>
<div style="width: 47%; float: right">
    <b><?= _('Raumangabe') ?></b>
    <? if (Config::get()->RESOURCES_ENABLE && $resList->numberOfRooms()) : ?>
        <label>
            <input type="radio" name="room"
                   <?= !empty($date_info->resource_id) ? 'checked' : '' ?>>
                   <?= _('Raum') ?>
            <select>
                <option value=""><?= _('kein Raum gebucht') ?></option>
                <? foreach ($resList->resources as $room_id => $room) : ?>
                    <option value="<?= $room_id ?>"
                            <?= $date_info->resource_id == $room_id ? 'selected' : '' ?>>
                                <?= $room ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>
    <? endif; ?>
    <label>
        <input type="radio" name="room" <?= !empty($date_info->raum) ? 'checked' : '' ?>>
        <input type="text" placeholder="<?= _('freie Ortsangabe (keine Raumbuchung)') ?>"
               value="<?= isset($date_info->raum) ? htmlReady($date_info->raum) : '' ?>">
    </label>
    <label>
        <input type="radio" name="room"
               <?= !empty($date_inf->resource_id) ? '' : (!empty($date_info->raum) ? '' : 'checked') ?>>
               <?= _('kein Raum') ?>
    </label>
    <b><?= _('Beteiligte Gruppen') ?></b>
    <ul style="list-style-type: none; width: 60%">
        <? foreach ($groups_options as $group_option) : ?>
            <? if (in_array($group_option->statusgruppe_id, $groups)) : ?>
                <li>
                    <?= htmlReady($group_option->name) ?>
                    <?= Assets::img('icons/16/blue/remove.png', array('style' => 'float:right', 'title' => _('Gruppe aus Termin entfernen')))
                    ?>
                </li>
            <? endif; ?>
        <? endforeach; ?>
    </ul>
    <select style="width: 300px">
        <option><?= _('Gruppe auswählen') ?></option>
        <? foreach ($groups_options as $group_option) : ?>
            <option value="<?= $group_option->statusgruppe_id ?>" style="
                    <?= in_array($group_option->statusgruppe_id, $groups) ? 'display:none' : '' ?>">
                        <?= htmlReady($group_option->name) ?>
            </option>
        <? endforeach; ?>
    </select>
    <?= Assets::img('icons/16/blue/add.png', array('title' => _('Gruppe zu Termin hinzufügen'))) ?>
</div>
