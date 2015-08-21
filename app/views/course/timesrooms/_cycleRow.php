<tr <?= !$termin->getRoom() ? 'class="red"' : '' ?>>
    <td>
        <label for="<?= htmlReady($termin->termin_id) ?>">
            <input type="checkbox" id="<?= htmlReady($termin->termin_id) ?>"
                   name="cycle_ids[]" <?= $termin->isExTermin() ? 'disabled' : '' ?> />
        </label>
    </td>
    <td>
        <? if ($termin->isExTermin() || $termin->isHoliday()) : ?>
            <span style="color: #666666">
                <?= htmlReady($termin->toString()) ?>
            </span>
        <? else : ?>
            <?= htmlReady($termin->toString()) ?>
        <? endif ?>
    </td>
    <td>
        <? if ($termin->isExTermin() && !$termin->isHoliday()) : ?>
            <span style="font-style: italic; color: #666666"><?= _("(fällt aus)") ?></span>
            <? if ($comment = $termin->getComment()) : ?>
                <?= tooltipIcon($termin->getComment(), false) ?>
            <? endif ?>
        <? elseif ($name = $termin->isHoliday()): ?>
            <span style="color: #666666">
                    (<?= htmlReady($name) ?>)
                </span>
        <? elseif ($room = $termin->getRoom()): ?>
            <?= htmlReady($room) ?>
        <? else : ?>
            (<?= _('Keine Raumangabe') ?>)
        <? endif ?>
    </td>
    <td>
        <? if (!$termin->isExTermin()) : ?>
            <a class="load-in-new-row" href="
        <?= isset($termin->metadate_id) ?
                $controller->url_for('course/timesrooms/editTeacher/' . $termin->termin_id . '/' . $termin->metadate_id)
                : $controller->url_for('course/timesrooms/editTeacher/' . $termin->termin_id) ?>
           ">
                <?= Assets::img('icons/blue/add/person', tooltip2(_('Durchführende Dozenten bearbeiten'))) ?>
            </a>
            <a class="load-in-new-row" href="
        <?= isset($termin->metadate_id) ?
                $controller->url_for('course/timesrooms/editDate/' . $termin->termin_id . '/' . $termin->metadate_id)
                : $controller->url_for('course/timesrooms/editDate/' . $termin->termin_id) ?>
           ">
                <?= Assets::img('icons/blue/edit', tooltip2(_('Termin bearbeiten'))) ?>
            </a>

            <?= Assets::img('icons/blue/resources', array('title' => _('Raumanfrage bearbeiten'))) ?>
            <?= Assets::img('icons/blue/trash', array('title' => _('Termin löschen'))) ?>
        <? endif ?>
    </td>
</tr>