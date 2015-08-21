<section class="clearfix">

    <section style="width: 47%; float:left;">
        <label for="date">
            <?= _('Datum') ?>
        </label>
        <input class="has-date-picker" type="text" name="date" id="date"
               value="<?= $date_info->date ? strftime('%d.%m.%G', $date_info->date) : 'tt.mm.jjjj' ?>"/>
    </section>

    <section style="width: 47%; float:right;">
        <label id="course_type">
            <?= _('Art') ?>
        </label>
        <select name="course_type" id="course_type">
            <? foreach ($types as $id => $value) : ?>
                <option value="<?= $id ?>"
                    <?= $date_info->date_typ == $id ? 'selected' : '' ?>>
                    <?= htmlReady($value['name']) ?>
                </option>
            <? endforeach; ?>
        </select>
    </section>

</section>
<section class="clearfix">
    <section style="width: 47%; float:left;">
        <label for="start_time">
            <?= _('Startzeit') ?>
        </label>
        <input type="time" name="start_time" id="start_time"
               value="<?= $date_info->date ? strftime('%H:%M', $date_info->date) : '--:--' ?>">
    </section>
    <section style="width: 47%; float:right;">
        <label for="end_time">
            <?= _('Endzeit') ?>
        </label>
        <input type="time" name="end_time" id="end_time"
               value="<?= $date_info->end_time ? strftime('%H:%M', $date_info->end_time) : '--:--' ?>">
    </section>
</section>

<div data-dialog-button>
    <?= Studip\Button::createAccept(_('Speichern'), 'save_dates', array('formaction' => $controller->url_for('course/timesrooms/editSingleDate/' . $termin_id), 'data-dialog' => 'size=50%')) ?>
</div>

<script>
    jQuery('#end_time').timepicker();
    jQuery('#start_time').timepicker();
</script>