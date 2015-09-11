<? $is_exTermin =  $termin instanceof CourseExDate ?>
<tr class="<?= $is_exTermin ? 'content_title_red' : '' ?> ">
    <td>
        <label for="<?= htmlReady($termin->termin_id) ?>">
            <input class="<?= $class_ids ?>" type="checkbox" id="<?= htmlReady($termin->termin_id) ?>"
                   value="<?= htmlReady($termin->termin_id) ?>"
                   name="single_dates[]"/>
        </label>
    </td>

    <td class="<?= $termin->getRoom() !== null ? 'green' : 'red' ?>">
        <? if ($is_exTermin) : ?>
            <span style="color: #666666">
                <?= htmlReady($termin->getFullname()) ?>
            </span>
        <? else : ?>
            <a class="load-in-new-row"
               href="<?= $controller->url_for('course/timesrooms/editDate/'
                                              . $termin->termin_id . ($termin->metadate_id ? '/' . $termin->metadate_id : ''), $editParams) ?>">
                <?= htmlReady($termin->getFullname()) ?>
            </a>
        <? endif ?>
    </td>
    
    <td>
        <? $dozenten = $termin->dozenten ?>
        <? if (count($dozenten)) : ?>
            <ul class="list-unstyled list-csv" <?= $is_exTermin ? 'style="color: #666666"' : ''?>>
                <? foreach ($dozenten as $key => $dozent) : ?>
                    <? $teacher = User::find($dozent) ?>
                    <li><?= $teacher ? htmlReady($teacher->getFullname()) : '' ?></li>
                <? endforeach ?>
            </ul>
        <? endif ?>
    </td>
    <td>
        <? if ($room_holiday = SemesterHoliday::isHoliday($termin->date,false)) : ?>
            <? $room_holiday = sprintf('<span style="color: #666666">(%s)</span>', htmlReady($room_holiday['name'])) ?>
        <? endif ?>

        <? if ($is_exTermin && ($comment = $termin->content)) : ?>
            <span style="font-style: italic; color: #666666"><?= _("(fällt aus)") ?></span>
            <?= tooltipIcon($termin->content, false) ?>
        <? elseif (($name = SemesterHoliday::isHoliday($termin->date, false))): ?>
            <span <?= $is_exTermin ? 'style="color: #666666"': ''?>>
                (<?= htmlReady($name['name']) ?>)
            </span>
        <? elseif ($room = $termin->getRoom()) : ?>
            <?= htmlReady($room->name); ?>
            <?= $room_holiday['name'] ?: '' ?>
        <? elseif ($freeTextRoom = $termin->getRoomName() ) : ?>
            <?= sprintf('(%s)', htmlReady($freeTextRoom)) ?>
        <? else : ?>
            <?= _('Keine Raumangabe') ?>
            <?= $room_holiday ?: '' ?>
        <? endif ?>

        <? if ($request = RoomRequest::existsByDate($termin->id, true)) : ?>
            <? $msg_info = _('Für diesen Termin existiert eine Raumanfrage: ') . $request->getInfo() ?>
            <?= tooltipIcon($msg_info) ?>
        <? endif ?>
    </td>
    <td class="actions">

        <? if ($is_exTermin) : ?>
            <a class="load-in-new-row"
               href="<?= $controller->url_for('course/timesrooms/editDate/'
                                              . $termin->termin_id . ($termin->metadate_id ? '/' . $termin->metadate_id : ''), $editParams) ?>">
                <?= Assets::img('icons/blue/edit', tooltip2(_('Termin bearbeiten'))) ?>
            </a>

            <? $warning = array() ?>
            <? if (!empty(CourseTopic::findByTermin_id($termin->id))) : ?>
                <? if (Config::get()->RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW) : ?>
                    <? $warning[] = _('Diesem Termin ist im Ablaufplan ein Thema zugeordnet.
                        Titel und Beschreibung des Themas bleiben erhalten und können in der Expertenansicht des Ablaufplans einem anderen Termin wieder zugeordnet werden.'); ?>
                <? else : ?>
                    <? $warning[] = _('Diesem Termin ist ein Thema zugeordnet.'); ?>
                <? endif ?>
            <? endif ?>

            <? if (Config::get()->RESOURCES_ENABLE && $termin->room_assignment) : ?>
                <? $warning[] = _('Dieser Termin hat eine Raumbuchung, welche mit dem Termin gelöscht wird.'); ?>
            <? endif ?>
            <a <?= Request::isXhr() ? 'data-dialog="size=big"' : '' ?>
                href="<?= $controller->url_for('course/timesrooms/deleteSingle/' . $termin->termin_id, array('cycle_id' => $termin->metadate_id)) ?>" <? !empty($warning) ? 'data-confirm="' . implode("\n", $warning) . '"' : '' ?>>
                <?= Assets::img('icons/blue/trash', array('title' => _('Termin löschen'))) ?>
            </a>
        <? else : ?>

            <a class="load-in-new-row"
               href="<?= $controller->url_for('course/timesrooms/cancel/'
                                              . $termin->termin_id . ($termin->metadate_id ? '/' . $termin->metadate_id : ''), $editParams) ?>">
                <?= Assets::img('icons/grey/edit', tooltip2(_('Termin bearbeiten'))) ?>
            </a>


            <a <?= Request::isXhr() ? 'data-dialog="size=big"' : '' ?> <? !empty($warning) ? 'data-confirm="' . implode("\n", $warning) . '"' : '' ?>
                href="<?= $controller->url_for('course/timesrooms/undeleteSingle/' . $termin->termin_id, $editParams) ?>">
                <?= Assets::img('icons/grey/decline/trash', tooltip2(_('Termin wiederherstellen'))) ?>
            </a>

        <? endif ?>

    </td>
</tr>