<tr>
    <td>
        <label for="<?= htmlReady($termin->termin_id) ?>">
            <input class="<?= $class_ids ?>" type="checkbox" id="<?= htmlReady($termin->termin_id) ?>"
                   name="cycle_ids[]" <?= $termin->isExTermin() ? 'disabled' : '' ?> />
        </label>
    </td>
    <td class="<?= $termin->hasRoom() || $termin->getFreeRoomText() != '' ? 'green' : 'red'?>">
        <? if ($termin->isExTermin() || $termin->isHoliday()) : ?>
            <span style="color: #666666">
                <?= htmlReady($termin->toString()) ?>
            </span>
        <? else : ?>
            <a class="load-in-new-row"
               href="<?= $controller->url_for('course/timesrooms/editDate/' . $termin->termin_id . ($termin->metadate_id ? '/' . $termin->metadate_id : '')) ?>">
                <?= htmlReady($termin->toString()) ?>
            </a>
        <? endif ?>
    </td>
    <td>
        <? if($room_holiday = $termin->isHoliday()) : ?>
            <? $room_holiday = sprintf('<span style="color: #666666">(%s)</span>', htmlReady($room_holiday))?>
        <? endif?>

        <? if ($termin->isExTermin() && ($comment = $termin->getComment())) : ?>
            <span style="font-style: italic; color: #666666"><?= _("(fällt aus)") ?></span>
            <?= tooltipIcon($termin->getComment(), false) ?>
        <? elseif (($name = $termin->isHoliday()) && !is_null($termin->isExTermin())): ?>
            <span style="color: #666666">
                    (<?= htmlReady($name) ?>)
                </span>
        <? elseif ($room = $termin->getRoom()): ?>
            <?= htmlReady($room); ?>
            <?= $room_holiday ? : ''?>
        <? elseif
        ($freeTextRoom = $termin->getFreeRoomText()) : ?>
            <?= sprintf('(%s)', htmlReady($freeTextRoom)) ?>
        <? else : ?>
        <?= _('Keine Raumangabe') ?>
            <?= $room_holiday ? : ''?>
        <? endif ?>
    </td>
    <td class="actions">
        <? if (!$termin->isExTermin()) : ?>
        <a class="load-in-new-row"
           href="<?= $controller->url_for('course/timesrooms/editDate/' . $termin->termin_id . ($termin->metadate_id ? '/' . $termin->metadate_id : '')) ?>">
            <?= Assets::img('icons/blue/edit', tooltip2(_('Termin bearbeiten'))) ?>
        </a>
        <?= Assets::img('icons/blue/trash', array('title' => _('Termin löschen'))) ?>
        <? else : ?>

            <a data-dialog="size=big"
               href="<?= $controller->url_for('course/timesrooms/undeleteSingle/' . $termin->termin_id) ?>">
                <?= Assets::img('icons/grey/decline/trash', tooltip2(_('Termin wiederherstellen'))) ?>
            </a>

        <? endif ?>

    </td>
</tr>