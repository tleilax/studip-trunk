<div id="schedule-settings-dialog-shadow"></div>
<div id="schedule_settings" class="edit_entry">
    <div class="window_heading_nodrag">
        <?= _("Einstellungen des Stundenplans �ndern") ?>
    </div>
    <form method="post" action="<?= $controller->url_for('calendar/schedule/storesettings') ?>" style="margin: 10px;">
        <div class="settings" style="width: 100%">
            <div><?= _("Angezeigtes Semester") ?>:</div>
            <select name="semester_id">
            <? foreach ($semesters as $semester) : ?>
                <? if ($semester['ende'] > time()) : ?>
                <option value="<?= $semester['semester_id'] ?>" <?= $settings['glb_sem'] == $semester['semester_id'] ? 'selected="selected"' : '' ?>>
                    <?= $semester['name'] ?>
                    <?= $semester['beginn'] < time() && $semester['ende'] > time() ? '(aktuelles Semester)' : '' ?>
                </option>
                <? endif ?>
            <? endforeach ?>
            </select>
            <br>
            <br>
        </div>

        <div class="settings" style="width: 45%">
            <div><?= _("Angezeigter Zeitraum") ?>:</div>

            <?= _("von") ?>
            <select name="start_hour">
            <? for ($i = 0; $i <= 23; $i++) : ?>
                <option value="<?= $i ?>" <?= $settings['glb_start_time'] == $i ? 'selected="selected"' : '' ?>>
                    <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00
                </option>
            <? endfor ?>
            </select>
    
            <?= _("bis") ?>
    
            <select name="end_hour">
            <? for ($i = 0; $i <= 23; $i++) : ?>
                <option value="<?= $i ?>" <?= $settings['glb_end_time'] == $i ? 'selected="selected"' : '' ?>>
                    <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00
                    </option>
            <? endfor ?>
            </select>
    
            <?= _("Uhr") ?><br>
        </div>

        <div class="settings" style="width: 45%">
            <div><?= _("Angezeigte Wochentage") ?>:</div>
            <? foreach (array(1,2,3,4,5,6,0) as $day) : ?>
                <label>
                    <input type="checkbox" name="days[]" value="<?= $day ?>"
                        <?= in_array($day, $settings['glb_days']) !== false ? 'checked="checked"' : '' ?>>
                    <?= getWeekDay($day, false) ?>
                </label><br>
            <? endforeach ?>
        </div>

        <div style="text-align: center; clear: both">
            <br>
            <input type="image" <?= makebutton('speichern', 'src') ?>>
            <a href="<?= $controller->url_for('calendar/schedule') ?>" onClick="$('#schedule_settings').remove();$('#schedule-settings-dialog-shadow').remove(); return false">
                <?= makebutton('abbrechen') ?>
            </a>
        </div>
    </form>
</div>
