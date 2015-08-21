<div class="clearfix">

    <div style="width: 33.33333%; float:left;">
        <label for="date">
            <?= _('Datum') ?>
        </label>
        <input class="size-l has-date-picker" type="text" name="date" id="date"
               value="<?= $date_info->date ? strftime('%d.%m.%G', $date_info->date) : 'tt.mm.jjjj' ?>"/>
    </div>

    <div style="width: 33.33333%; float:left;">
        <label for="start_time">
            <?= _('Startzeit') ?>
        </label>
        <input class="size-m has-time-picker" type="time" name="start_time" id="start_time"
               value="<?= $date_info->date ? strftime('%H:%M', $date_info->date) : '--:--' ?>">
    </div>
    <div style="width: 33.33333%; float:left;">
        <label for="end_time">
            <?= _('Endzeit') ?>
        </label>
        <input class="size-m has-time-picker" type="time" name="end_time" id="end_time"
               value="<?= $date_info->end_time ? strftime('%H:%M', $date_info->end_time) : '--:--' ?>">
    </div>

</div>
<div class="clearfix">
    <label id="course_type">
        <?= _('Art') ?>
    </label>
    <select class="size-m" name="course_type" id="course_type">
        <? foreach ($types as $id => $value) : ?>
            <option value="<?= $id ?>"
                <?= $date_info->date_typ == $id ? 'selected' : '' ?>>
                <?= htmlReady($value['name']) ?>
            </option>
        <? endforeach; ?>
    </select>
</div>

<div data-dialog-button>
    <?= Studip\Button::createAccept(_('Speichern'), 'save_dates', array('formaction'  => $controller->url_for('course/timesrooms/editSingleDate/' . $termin_id),
                                                                        'data-dialog' => 'size=50%'
    )) ?>
</div>
