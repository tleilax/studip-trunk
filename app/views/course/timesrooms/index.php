<form class="studip_form">
    <section class="contentbox">
        <header>
            <h1>
                <?= _('Allgemeine Einstellungen') ?>
            </h1>
        </header>
        <section>
            <label style="display: inline-block" for="startSemester"><?= _('Startsemester') ?>:
                <select name="startSemester" id="startSemester">
                    <? foreach ($semester as $sem) : ?>
                        <option value="<?= $sem->semester_id ?>" <?= $sem->semester_id == $course->start_semester->semester_id ? 'selected' : '' ?>>
                            <?= htmlReady($sem->name) ?>
                        </option>
                    <? endforeach; ?>
                </select>   
            </label>
            <label style="display: inline-block" for="endSemester"><?= _('Dauer') ?>:
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
            </label>        
        </section>

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