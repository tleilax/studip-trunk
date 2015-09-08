<tr>
    <td>
        <label for="<?= htmlReady($termin->termin_id) ?>">
            <input class="<?= $class_ids ?>" type="checkbox" id="<?= htmlReady($termin->termin_id) ?>"
                   value="<?= htmlReady($termin->termin_id) ?>"
                   name="single_dates[]"/>
        </label>
    </td>
    <td class="<?= $termin->hasRoom() ? 'green' : 'red' ?>">
        <? if ($termin->isExTermin() || $termin->isHoliday()) : ?>
            <span style="color: #666666">
                <?= htmlReady($termin->toString()) ?>
            </span>
        <? else : ?>
            <a class="load-in-new-row"
               href="<?= $controller->url_for('course/timesrooms/editDate/'
                                              . $termin->termin_id . ($termin->metadate_id ? '/' . $termin->metadate_id : ''), $editParams) ?>">
                <?= htmlReady($termin->toString()) ?>
            </a>
        <? endif ?>
    </td>
    <td>
        <? $dozenten = $termin->getRelatedPersons() ?>
        <? if (count($dozenten)) : ?>
            <ul class="list-unstyled list-csv">
                <? foreach ($dozenten as $key => $dozent) : ?>
                    <? $teacher = User::find($dozent) ?>
                    <li><?= $teacher ? htmlReady($teacher->getFullname()) : '' ?></li>
                <? endforeach ?>
            </ul>
        <? endif ?>
    </td>
    <td>
        <? if ($room_holiday = $termin->isHoliday()) : ?>
            <? $room_holiday = sprintf('<span style="color: #666666">(%s)</span>', htmlReady($room_holiday)) ?>
        <? endif ?>

        <? if ($termin->isExTermin() && ($comment = $termin->getComment())) : ?>
            <span style="font-style: italic; color: #666666"><?= _("(fällt aus)") ?></span>
            <?= tooltipIcon($termin->getComment(), false) ?>
        <? elseif (($name = $termin->isHoliday()) && !is_null($termin->isExTermin())): ?>
            <span style="color: #666666">
                    (<?= htmlReady($name) ?>)
                </span>
        <? elseif ($room = $termin->getRoom()): ?>
            <?= htmlReady($room); ?>
            <?= $room_holiday ?: '' ?>
        <? elseif
        ($freeTextRoom = $termin->getFreeRoomText()
        ) : ?>
            <?= sprintf('(%s)', htmlReady($freeTextRoom)) ?>
        <? else : ?>
            <?= _('Keine Raumangabe') ?>
            <?= $room_holiday ?: '' ?>
        <? endif ?>

        <? if ($request = $termin->getRoomRequest()) : ?>
            <? $msg_info = _('Für diesen Termin existiert eine Raumanfrage: ') . $request->getInfo() ?>
            <?= tooltipIcon($msg_info) ?>
        <? endif ?>
    </td>
    <td class="actions">

        <? if (!$termin->isExTermin()) : ?>
            <a class="load-in-new-row"
               href="<?= $controller->url_for('course/timesrooms/editDate/'
                                              . $termin->termin_id . ($termin->metadate_id ? '/' . $termin->metadate_id : ''), $editParams) ?>">
                <?= Assets::img('icons/blue/edit', tooltip2(_('Termin bearbeiten'))) ?>
            </a>

            <? $warning = array() ?>
            <? if ($termin->getIssueIDs()) : ?>
                <? if (Config::get()->RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW) : ?>
                    <? $warning[] = _('Diesem Termin ist im Ablaufplan ein Thema zugeordnet.
                        Titel und Beschreibung des Themas bleiben erhalten und können in der Expertenansicht des Ablaufplans einem anderen Termin wieder zugeordnet werden.'); ?>
                <? else : ?>
                    <? $warning[] = _('Diesem Termin ist ein Thema zugeordnet.'); ?>
                <? endif ?>
            <? endif ?>

            <? if (Config::get()->RESOURCES_ENABLE && $termin->hasRoom()) : ?>
                <? $warning[] = _('Dieser Termin hat eine Raumbuchung, welche mit dem Termin gelöscht wird.'); ?>
            <? endif ?>
            <a <?= Request::isXhr() ? 'data-dialog="size=big;reload-on-close"' : '' ?>
                href="<?= $controller->url_for('course/timesrooms/deleteSingle/' . $termin->termin_id, array('cycle_id' => $termin->metadate_id)) ?>" <? !empty($warning) ? 'data-confirm="' . implode("\n", $warning) . '"' : '' ?>>
                <?= Assets::img('icons/blue/trash', array('title' => _('Termin löschen'))) ?>
            </a>
        <? else : ?>

            <a class="load-in-new-row"
               href="<?= $controller->url_for('course/timesrooms/cancel/'
                                              . $termin->termin_id . ($termin->metadate_id ? '/' . $termin->metadate_id : ''), $editParams) ?>">
                <?= Assets::img('icons/grey/edit', tooltip2(_('Termin bearbeiten'))) ?>
            </a>


            <a <?= Request::isXhr() ? 'data-dialog="size=big;reload-on-close"' : '' ?> <? !empty($warning) ? 'data-confirm="' . implode("\n", $warning) . '"' : '' ?>
                href="<?= $controller->url_for('course/timesrooms/undeleteSingle/' . $termin->termin_id, $editParams) ?>">
                <?= Assets::img('icons/grey/decline/trash', tooltip2(_('Termin wiederherstellen'))) ?>
            </a>

        <? endif ?>

    </td>
</tr>