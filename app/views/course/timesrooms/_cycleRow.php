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
            <span style="font-style: italic; color: #666666"><?= _("(f�llt aus)") ?></span>
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
            <a data-dialog="size=50%" href="
        <?= isset($termin->metadate_id) ?
                $controller->url_for('course/timesrooms/editTeacher/' . $termin->termin_id . '/' . $termin->metadate_id)
                : $controller->url_for('course/timesrooms/editTeacher/' . $termin->termin_id) ?>
           ">
                <?= Assets::img('icons/16/blue/add/person.png', tooltip2(_('Durchf�hrende Dozenten bearbeiten'))) ?>
            </a>
            <a data-dialog="size=50%" href="
        <?= isset($termin->metadate_id) ?
                $controller->url_for('course/timesrooms/editDate/' . $termin->termin_id . '/' . $termin->metadate_id)
                : $controller->url_for('course/timesrooms/editDate/' . $termin->termin_id) ?>
           ">
                <?= Assets::img('icons/16/blue/edit.png', tooltip2(_('Termin bearbeiten'))) ?>
            </a>

            <?= Assets::img('icons/16/blue/place.png', array('title' => _('Raumanfrage bearbeiten'))) ?>
            <?= Assets::img('icons/16/blue/trash.png', array('title' => _('Termin l�schen'))) ?>
        <? endif ?>
    </td>
</tr>