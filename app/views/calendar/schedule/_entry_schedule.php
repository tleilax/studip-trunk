<div id="edit_entry" class="schedule_edit_entry" <?= $show_entry ? '' : 'style="display: none"' ?>>
	<div id="edit_entry_drag" class="window_heading">Termindetails bearbeiten</div>
	<form action="<?= $controller->url_for('calendar/schedule/addentry'. ($show_entry['id'] ? '/'. $show_entry['id'] : '') ) ?>" method="post" name="edit_entry" style="padding-left: 10px; padding-top: 10px; margin-right: 10px;" onSubmit="return STUDIP.Schedule.checkFormFields()">
		<b><?= _("Tag") ?>:</b> <select name="entry_day">
			<? foreach (array(1,2,3,4,5,6,0) as $index) : ?>
			<option value="<?= $index ?>" <?= (isset($show_entry['day']) && $show_entry['day'] == $index) ? 'selected="selected"' : '' ?>><?= getWeekDay($index, false) ?></option>
			<? endforeach ?>
		</select>

        <div id="schedule_entry_hours">
    		<?= _("von") ?>
    		<input type="text" size="2" name="entry_start_hour" value="<?= $show_entry['start_hour'] ?>" 
                onChange="STUDIP.Calendar.validateHour(this)"> :
    		<input type="text" size="2" name="entry_start_minute" value="<?= $show_entry['start_minute'] ?>"
                onChange="STUDIP.Calendar.validateMinute(this)">
    
    		<?= _("bis") ?>
    		<input type="text" size="2" name="entry_end_hour" value="<?= $show_entry['end_hour'] ?>"
                onChange="STUDIP.Calendar.validateHour(this)"> :
    		<input type="text" size="2" name="entry_end_minute" value="<?= $show_entry['end_minute'] ?>" style="margin-right: 10px"
                onChange="STUDIP.Calendar.validateMinute(this)">

            <span class="invalid_message"><?= _("Die Startzeit liegt vor der Endzeit!") ?></span>
        </div>

        <div id="color_picker">
    		<b><?= _("Farbe des Termins") ?>:</b>
    		<? foreach ($GLOBALS['PERS_TERMIN_KAT'] as $data) : ?>
    		<span style="background-color: <?= $data['color'] ?>; vertical-align: middle; padding-top: 3px;">
    			<input type="radio" name="entry_color" value="<?= $data['color'] ?>" <?= ($data['color'] == $show_entry['color']) ? 'checked="checked"' : '' ?>>
    		</span>
    		<? endforeach ?>
        </div>

		<br>

		<b><?= _("Titel") ?>:</b>
		<input type="text" name="entry_title" style="width: 98%" value="<?= $show_entry['title'] ?>">
		<b><?= _("Beschreibung") ?>:</b>
		<textarea name="entry_content" style="width: 98%" rows="7"><?= $show_entry['content'] ?></textarea>
		<br>
		<div style="text-align: center">
			<input type="image" <?= makebutton('speichern', 'src') ?> style="margin-right: 20px;">

			<? if ($show_entry['id']) : ?>
			<a href="<?= $controller->url_for('calendar/schedule/delete/'. $show_entry['id']) ?>" style="margin-right: 20px;"><?= makebutton('loeschen') ?></a>
			<? endif ?>

            <? if ($show_entry) : ?>
			<a href="<?= $controller->url_for('calendar/schedule') ?>" onClick="$('#edit_entry').fadeOut('fast');return false"><?= makebutton('abbrechen') ?></a>
            <? else: ?>
			<a href="javascript:STUDIP.Calendar.cancelNewEntry(true)"><?= makebutton('abbrechen') ?></a>
            <? endif ?>
		</div>
	</form>
</div>
<script>
	$('#edit_entry').draggable({ handle: 'edit_entry_drag' });
</script>
