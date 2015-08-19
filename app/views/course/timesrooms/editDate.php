<form class="studip-form">
    <section class="contentbox">
        <header>
            <h1><?= _('Zeitangaben') ?></h1>
        </header>
        <section>
            <label for="date">
                <?= _('Datum') ?>
            </label>
            <input class="has-date-picker" type="text" name="date" id="date"
                   value="<?= $date_info->date ? strftime('%d.%m.%G', $date_info->date) : 'tt.mm.jjjj' ?>">

        </section>
        <section>
            <label for="start_time">
                <?= _('Uhrzeit von') ?>
            </label>
            <input type="time" name="start_time" id="start_time"
                   value="<?= $date_info->date ? strftime('%H:%M', $date_info->date) : '--:--' ?>">
        </section>
        <section>
            <label for="end_time">
                <?= _('Uhrzeit bis') ?>
            </label>
            <input type="time" name="end_time" id="end_time"
                   value="<?= $date_info->end_time ? strftime('%H:%M', $date_info->end_time) : '--:--' ?>">
        </section>
        <section>
            <label id="course_type">
                <?= _('Art') ?>
            </label>
            <select name="course_type" id="course_type">
                <? foreach ($types as $id => $value) : ?>
                    <option value="<?= $id ?>"
                        <?= $date_info->date_typ == $id ? 'selected' : '' ?>>
                        <?= htmlReady($value['name']) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </section>
    </section>


    <section class="contentbox">
        <header>
            <h1><?= _('Durchführende Dozenten') ?></h1>
        </header>
        <? if (!empty($dozenten)) : ?>
            <table class="default">
                <? foreach ($dozenten as $dozent) : ?>
                    <tr>
                        <td><?= htmlReady($dozent['Vorname']) ?> <?= htmlReady($dozent['Nachname']) ?></td>
                        <td><?=
                            Assets::img('icons/16/blue/remove.png', array('style' => 'float:right;', 'title' => sprintf(_('%s %s aus Termin austragen'), htmlReady($dozent['Vorname']), htmlReady($dozent['Nachname']))))
                            ?></td>
                    </tr>
                <? endforeach; ?>
            </table>
        <? else : ?>
            <?= _('Keine Dozenten eingetragen') ?>
        <? endif; ?>
        <section>
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
        </section>
    </section>

    <section class="contentbox">
        <header>
            <h1><?= _('Raumangaben') ?></h1>
        </header>
        <? if (Config::get()->RESOURCES_ENABLE && $resList->numberOfRooms()) : ?>
            <section>
                <label for="room">
                    <?= _('Raum') ?>
                </label>
                <input id="room" type="radio" name="room"
                    <?= !empty($date_info->resource_id) ? 'checked' : '' ?>>
            </section>

            <section>
                <select>
                    <option value=""><?= _('kein Raum gebucht') ?></option>
                    <? foreach ($resList->resources as $room_id => $room) : ?>
                        <option value="<?= $room_id ?>"
                            <?= $date_info->resource_id == $room_id ? 'selected' : '' ?>>
                            <?= $room ?>
                        </option>
                    <? endforeach; ?>
                </select>

            </section>
        <? endif; ?>
        <section>
            <label>
                <input type="radio" name="room" <?= !empty($date_info->raum) ? 'checked' : '' ?>>
                <input type="text" placeholder="<?= _('freie Ortsangabe (keine Raumbuchung)') ?>"
                       value="<?= isset($date_info->raum) ? htmlReady($date_info->raum) : '' ?>">
            </label>
        </section>
        <section>
            <label>
                <input type="radio" name="room"
                    <?= !empty($date_inf->resource_id) ? '' : (!empty($date_info->raum) ? '' : 'checked') ?>>
                <?= _('kein Raum') ?>
            </label>
        </section>
    </section>

    <section class="contentbox">
        <header>
            <h1><?= _('Beteiligte Gruppen') ?></h1>
        </header>

        <section>
            <table class="default">
                <? foreach ($groups_options as $group_option) : ?>
                    <? if (in_array($group_option->statusgruppe_id, $groups)) : ?>
                        <tr>
                            <td>
                                <?= htmlReady($group_option->name) ?>
                            </td>
                            <td><?= Assets::img('icons/16/blue/remove.png', array('style' => 'float:right', 'title' => _('Gruppe aus Termin entfernen')))
                                ?>
                            </td>
                        </tr>
                    <? endif; ?>
                <? endforeach; ?>
            </table>
        </section>
        <section>
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
        </section>

    </section>

</form>