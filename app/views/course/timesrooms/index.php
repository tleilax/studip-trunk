<form class="studip-form" method="post">
    <section class="contentbox clearfix">
        <header>
            <h1>
                <?= _('Allgemeine Einstellungen') ?>
            </h1>
        </header>
        <section style="float: left; width: 45%">
            <label for="startSemester"><?= _('Startsemester') ?>:</label>
            <select name="startSemester" id="startSemester">
                <? foreach ($semester as $sem) : ?>
                    <option value="<?= $sem->semester_id ?>" <?= $sem->semester_id == $course->start_semester->semester_id ? 'selected' : '' ?>>
                        <?= htmlReady($sem->name) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </section>
        <section style="float: left; width: 45%">
            <label for="endSemester"><?= _('Dauer') ?>:</label>
            <select name="endSemester" id="endSemester">
                <option value="0"
                    <?= $course->__get('metadate')->seminarDurationTime == 0 ? 'selected' : '' ?>>
                    <?= _('ein Semester') ?></option>
                <? foreach ($semester as $sem) : ?>
                    <? if ($sem->beginn >= $current_semester->beginn) : ?>
                        <option value="<?= $sem->semester_id ?>"
                            <?= $course->__get('metadate')->seminarDurationTime == $sem->semester_id ? 'selected' : '' ?>>
                            <?= htmlReady($sem->name) ?>
                        </option>
                    <? endif; ?>
                <? endforeach; ?>
                <option value="-1"
                    <?= $course->__get('metadate')->seminarDurationTime == -1 ? 'selected' : '' ?>>
                    <?= _('unbegrenzt') ?></option>
            </select>
        </section>
        <footer>
            <?= Studip\Button::createAccept(_('Semester speichern'), 'save', $semesterFormParams) ?>
            <? if (Request::isXhr()) : ?>
                <?= Studip\Button::createAccept(_('Semester speichern & schließen'), 'save_close', $semesterFormParams) ?>
            <? endif ?>
        </footer>

    </section>
    <br>
    <? if ($show['regular']) : ?>
        <!--Regelmäßige Termine-->
        <?= $this->render_partial('course/timesrooms/_regularEvents.php', array()) ?>
    <? endif; ?>
    <br>
    <? if ($show['irregular']) : ?>
        <!--Unregelmäßige Termine-->
        <?= $this->render_partial('course/timesrooms/_irregularEvents', array()) ?>
    <? endif; ?>
    <br>
    <? if ($show['roomRequest']) : ?>
        <!--Raumanfrage-->
        <?= $this->render_partial('course/timesrooms/_roomRequest.php', array()) ?>
    <? endif; ?>
    <br>
</form>