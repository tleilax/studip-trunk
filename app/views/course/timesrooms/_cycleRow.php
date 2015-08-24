<tr>
    <td>
        <label for="<?= htmlReady($termin->termin_id) ?>">
            <input class="<?= $class_ids ?>" type="checkbox" id="<?= htmlReady($termin->termin_id) ?>"
                   name="cycle_ids[]" <?= $termin->isExTermin() ? 'disabled' : '' ?> />
        </label>
    </td>
    <td>
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
        <? if ($termin->isExTermin() && ($comment = $termin->getComment())) : ?>
            <span style="font-style: italic; color: #666666"><?= _("(fällt aus)") ?></span>
                <?= tooltipIcon($termin->getComment(), false) ?>
        <? elseif ($name = $termin->isHoliday()): ?>
            <span style="color: #666666">
                    (<?= htmlReady($name) ?>)
                </span>
        <? elseif ($room = $termin->getRoom()): ?>
            <?= htmlReady($room); ?>
        <? elseif ($freeTextRoom = $termin->getFreeRoomText()) : ?>
            <?= sprintf('(%s)', htmlReady($freeTextRoom)) ?>
        <? else : ?>
            (<?= _('Keine Raumangabe') ?>)
        <? endif ?>
    </td>
    <td class="actions">
        <? if (!$termin->isExTermin() && !$termin->isHoliday()) : ?>
            <a class="load-in-new-row"
               href="<?= $controller->url_for('course/timesrooms/editDate/' . $termin->termin_id . ($termin->metadate_id ? '/' . $termin->metadate_id : '')) ?>">
                <?= Assets::img('icons/blue/edit', tooltip2(_('Termin bearbeiten'))) ?>
            </a>
            <?= Assets::img('icons/blue/trash', array('title' => _('Termin löschen'))) ?>
        <? endif ?>

        <? if ($termin->isExTermin() || $termin->isHoliday()) : ?>
            <a class="load-in-new-row"
               href="<?= $controller->url_for('course/timesrooms/cancel/' . $termin->termin_id) ?>">
                <?= Assets::img('icons/blue/edit', tooltip2(_('Kommentar hinzufügen'))) ?>
            </a>
            <?= Assets::img('icons/grey/trash') ?>
        <? endif ?>
    </td>
</tr>