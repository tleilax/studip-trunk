<? use Studip\Button, Studip\LinkButton; ?>

<?
    $days_of_week = array(
        1 => _('Montag'),
        2 => _('Dienstag'),
        3 => _('Mittwoch'),
        4 => _('Donnerstag'),
        5 => _('Freitag'),
        6 => _('Samstag'),
        7 => _('Sonntag'),
    );
?>

<form action="<?= $controller->url_for('admin/cronjobs/schedules/edit', $schedule->schedule_id) ?>" method="post" class="cronjobs-edit">
    <?= CSRFProtection::tokenTag() ?>

    <h2 class="topic"><?= _('Details') ?></h2>
    <table class="default zebra-hover settings">
        <colgroup>
            <col width="20%">
            <col width="80%">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Option') ?></th>
                <th><?= _('Wert') ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <label for="active"><?= _('Aktiv') ?></label>
                </td>
                <td>
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" id="active" value="1"
                           <? if ($schedule->active) echo 'checked'; ?>>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="priority"><?= _('Priorität') ?></label>
                </td>
                <td>
                    <select name="priority" id="priority">
                    <? foreach (CronjobSchedule::getPriorities() as $priority => $label): ?>
                        <option value="<?= $priority ?>" <? if ((!$schedule->priority && $priority === CronjobSchedule::PRIORITY_NORMAL) || $schedule->priority === $priority) echo 'selected'; ?>>
                            <?= htmlReady($label) ?>
                        </option>
                    <? endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="title"><?= _('Titel') ?></label>
                </td>
                <td>
                    <input type="text" name="title" id="title" value="<?= htmlReady($schedule->title ?: '') ?>">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="description"><?= _('Beschreibung') ?></label>
                </td>
                <td>
                    <textarea name="description"><?= htmlReady($schedule->description ?: '') ?></textarea>
                </td>
            </tr>
        </tbody>
    </table>

    <h2 class="topic"><?= _('Aufgabe') ?></h2>
    <table class="default zebra-big-hover cron-task settings" cellspacing="0" cellpadding="0">
        <colgroup>
            <col width="20px">
            <col width="100px">
            <col width="150px">
            <col>
            <col width="20px">
        </colgroup>
        <thead>
            <tr>
                <th colspan="2"><?= _('Klassenname') ?></th>
                <th><?= _('Name') ?></th>
                <th><?= _('Beschreibung') ?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <? foreach ($tasks as $task): ?>
        <tbody <? if ($task->task_id === $schedule->task_id) echo 'class="selected"'; ?>>
            <tr>
                <td>
                    <input required type="radio" name="task_id"
                           id="task-<?= $task->task_id ?>"
                           value="<?= $task->task_id ?>"
                           <? if ($task->task_id === $schedule->task_id) echo 'checked'; ?>>
                </td>
                <td>
                    <label for="task-<?= $task->task_id ?>">
                        <?= htmlReady($task->class) ?>
                    </label>
                </td>
                <td>
                    <label for="task-<?= $task->task_id ?>"><?= htmlReady($task->name) ?: '&nbsp;' ?></label>
                </td>
                <td colspan="2">
                    <label for="task-<?= $task->task_id ?>"><?= htmlReady($task->description) ?: '&nbsp;' ?></label>
                </td>
            </tr>
        <? if (count($task->parameters) > 0): ?>
            <tr>
                <td class="blank">&nbsp;</td>
                <td colspan="3">
                    <div class="parameters">
                        <?= $this->render_partial('admin/cronjobs/schedules/parameters', compact('task', 'schedule')) ?>
                    </div>
                </td>
                <td class="blank">&nbsp;</td>
            </tr>
        <? endif; ?>
        </tbody>
        <? endforeach; ?>
    </table>

    <h2 class="topic"><?= _('Zeitplan') ?></h2>
    <table class="default zebra-horizontal settings">
        <colgroup>
            <col width="50%">
            <col width="50%">
        </colgroup>
        <thead>
            <tr>
                <th>
                    <label>
                        <input type="radio" name="type" value="once"
                               <? if ($schedule->type === 'once') echo 'checked'; ?>>
                        <?= _('Einmalig') ?>
                    </label>
                </th>
                <th>
                    <label>
                        <input type="radio" name="type" value="periodic"
                               <? if ($schedule->type === 'periodic') echo 'checked'; ?>>
                        <?= _('Wiederholt') ?>
                    </label>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <label>
                        <?= _('Datum') ?>
                        <input type="date" name="once[date]" value="<? if ($schedule->next_execution) echo date('d.m.Y', $schedule->next_execution); ?>">
                    </label>

                    <br>

                    <label>
                        <?= _('Uhrzeit') ?>
                        <input type="time" name="once[time]" value="<? if ($schedule->next_execution) echo date('H:i', $schedule->next_execution) ?>">
                    </label>
                </td>
                <td>
                    <dl>
                        <dt><?= _('Minute') ?></dt>
                        <dd>
                            <label>
                                <input type="radio" name="periodic[minute][type]" value="">
                                <?= _('Jede Minute') ?>
                            </label>

                            <br>

                            <label>
                                <input type="radio" name="periodic[minute][type]" value="once">
                                <?= _('Alle X Minuten') ?>
                            </label>

                            <br>

                            <label>
                                <input type="radio" name="periodic[minute][type]" value="periodic">
                                <?= _('Minute') ?>
                            </label>
                        </dd>
                    </dl>
                    <label>
                        <?= _('Minute') ?>
                        <select name="periodic[minute][type]">
                            <option value=""><?= _('Jede Minute') ?></option>
                            <option value="once"><?= _('Alle X Minuten') ?></option>
                            <option value="peridoc"><?= _('Genau zur Minute X') ?></option>
                        </select>
                        <input type="text" name="periodic[minute][value]" value="<?= $schedule->minute ?>">
                    </label>

                    <br>

                    <label>
                        <?= _('Stunde') ?>
                        <input type="text" name="periodic[hour]" value="<?= $schedule->hour ?>">
                    </label>

                    <br>

                    <label>
                        <?= _('Tag') ?>
                        <input type="text" name="periodic[day]" value="<?= $schedule->day ?>">
                    </label>

                    <br>

                    <label>
                        <?= _('Monat') ?>
                        <input type="text" name="periodic[month]" value="<?= $schedule->month ?>">
                    </label>

                    <br>

                    <label>
                        <?= _('Wochentag') ?>
                        <select name="periodic[day_of_week]">
                            <option value=""><?= _('Jeden Tag') ?></option>
                        <? foreach ($days_of_week as $index => $label): ?>
                            <option value="<?= $index ?>" <? if ($schedule->day_of_week === $index) echo 'selected'; ?>>
                                <?= $label ?>
                            </option>
                        <? endforeach; ?>
                        </select>
                    </label>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="buttons" style="text-align: center;">
        <?= Button::createAccept(_('Speichern'), 'store') ?>
        <?= LinkButton::createCancel('Abbrechen', $controller->url_for('admin/cronjobs/schedules')) ?>
    </div>
</form>