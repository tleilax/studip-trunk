<? if (Config::get()->RESOURCES_ENABLE && $resList->numberOfRooms()) : ?>
    <section class="clearfix" style="margin: 10px 0">

        <label class="horizontal">
            <input style="display: inline;" type="radio" name="room"
                   id="room" <?= !empty($date_info->resource_id) ? 'checked' : '' ?> />
        </label>

        <select style="display: inline-block; margin-left: 40px"
                class="single_room" <?= empty($date_info->resource_id) ? 'disabled' : '' ?>>
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
<section class="clearfix" style="margin: 10px 0">
    <label class="horizontal">
        <input type="radio" name="room" <?= !empty($date_info->raum) ? 'checked' : '' ?> style="display: inline"/>
    </label>
    <input style="margin-left: 40px; display: inline-block" type="text"
           placeholder="<?= _('freie Ortsangabe (keine Raumbuchung)') ?>"
           value="<?= isset($date_info->raum) ? htmlReady($date_info->raum) : '' ?>">
</section>
<section class="clearfix" style="margin: 10px 0">
    <label class="horizontal">
        <input type="radio" name="room" style="display:inline;"
            <?= !empty($date_inf->resource_id) ? '' : (!empty($date_info->raum) ? '' : 'checked') ?>>
        <span style="display: inline-block; margin-left: 40px"><?= _('kein Raum') ?></span>
    </label>


</section>
<?= Studip\Button::createAccept(_('Raumangaben speichern'), 'save_room') ?>

