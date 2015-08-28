<? if (!Request::isXhr()) : ?>
    <h1><?= _('Neuen Blocktermin anlegen') ?></h1>
<? endif ?>

<form <?= Request::isXhr() ? 'data-dialog="size=big"' : '' ?> class="studip-form" action="<?= $controller->url_for('course/block_appointments/save/' . $course_id) ?>" method="post">
    <fieldset class="clearfix">
        <legend><?= _('Die Veranstaltung findet in folgendem Zeitraum statt') ?></legend>
        <section style="float: left; width:50%">
            <label for="block_appointments_start_day">
                <?= _('Startdatum') ?>
            </label>
            <input type="text" class="size-m has-date-picker" id="block_appointments_start_day" name="block_appointments_start_day" value="" />
        </section>
        <section style="float: right; width:50%">
            <label for="block_appointments_end_day">
                <?= _('Enddatum') ?>
            </label>
            <input type="text" class="size-m has-date-picker" id="block_appointments_end_day" name="block_appointments_end_day" value="" />
        </section>
    </fieldset>

    <fieldset class="clearfix">
        <legend><?= _('Die Veranstaltung findet zu folgenden Zeiten statt') ?></legend>
        <section style="float: left; width:50%">
            <label for="block_appointments_start_time">
                <?= _('Startzeit') ?>
            </label>
            <input type="text" class="size-m has-time-picker" id="block_appointments_start_time" name="block_appointments_start_time" value="" />
        </section>
        <section style="float: left; width:50%">
            <label for="block_appointments_end_time">
                <?= _('Endzeit') ?>
            </label>
            <input type="text" class="size-m has-time-picker" id="block_appointments_end_time" name="block_appointments_end_time" value="" />
        </section>
        </section>
    </fieldset>

    <fieldset>
        <legend><?= _('Weitere Daten') ?></legend>
        <section>
            <label for="block_appointments_termin_typ">
                <?= _('Art der Termine') ?>
            </label>
            <select clas="size-l" name="block_appointments_termin_typ" id="block_appointments_termin_typ">
                <? foreach ($GLOBALS['TERMIN_TYP'] as $key => $value) : ?>
                    <option value="<?= $key ?>">
                        <?= htmlReady($value['name']) ?>
                    </option>
                <? endforeach ?>
            </select>
        </section>
        <section>
            <label for="block_appointments_room_text">
                <?= _('freie Ortsangabe') ?>
            </label>
            <input type="text" name="block_appointments_room_text" id="block_appointments_room_text" value="" />
        </section>
    </fieldset>

    <fieldset>
        <legend><?= _('Mehrere Termine parallel anlegen') ?></legend>

        <section>
            <label for="block_appointments_date_count">
                <?= _('Anzahl') ?>
            </label>
            <select name="block_appointments_date_count" id="block_appointments_date_count" class="size-s">
                <? foreach (range(1, 5) as $day) : ?>
                    <option value="<?= $day ?>"><?= $day ?></option>
                <? endforeach ?>
            </select>
        </section>
    </fieldset>

    <fieldset id="block_appointments_days">
        <legend><?= _('Die Veranstaltung findet an folgenden Tagen statt') ?></legend>
        <div>
            <input class="block_appointments_days" name="block_appointments_days[]" id="block_appointments_days_0" type="checkbox" value="everyday" />
            <label for="block_appointments_days_0" class="horizontal" style="font-weight:normal">
                <?= _('Jeden Tag') ?>
            </label>
        </div>
        <div>
            <input class="block_appointments_days" name="block_appointments_days[]" id="block_appointments_days_1" type="checkbox" value="weekdays" />
            <label for="block_appointments_days_1" class="horizontal" style="font-weight:normal">
                <?= _('Mo-Fr') ?>
            </label>
        </div>
        <? foreach (range(0, 6) as $d) : ?>
            <? $id = 2 + $d?>
            <div>
                <input class="block_appointments_days" name="block_appointments_days[]" id="block_appointments_days_<?= $id ?>" type="checkbox" value="<?= $d + 1 ?>" />
                <label for="block_appointments_days_<?= $id ?>" class="horizontal" style="font-weight: normal">
                    <?= strftime('%A', strtotime("+$d day", $start_ts)) ?>
                </label>
            </div>
        <? endforeach ?>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
        <?= Studip\LinkButton::create(_('Zurück zur Übersicht'), $controller->url_for('course/timesrooms/index'), array('data-dialog' => 'size=big')) ?>
    </footer>
</form>