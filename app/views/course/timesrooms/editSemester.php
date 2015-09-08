<form action="<?= $controller->url_for('course/timesrooms/set_semester/' . $course->id) ?>" method="post"
      class="studip-form" <?= Request::isXhr() ? 'data-dialog="size=big"' : '' ?>>
    <section>
        <section <?= !Request::isXhr() ? 'style="display: inline-block; width: 45%"' : '' ?>>
            <label for="startSemester"><?= _('Startsemester') ?>:</label>
            <select class="size-xl" name="startSemester" id="startSemester">
                <? foreach ($semester as $sem) : ?>
                    <option
                        value="<?= $sem->semester_id ?>" <?= $sem->semester_id == $course->start_semester->semester_id ? 'selected' : '' ?>>
                        <?= htmlReady($sem->name) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </section>
        <section <?= !Request::isXhr() ? 'style="display: inline-block; width: 48%"' : '' ?>>
            <label for="endSemester"><?= _('Dauer') ?>:</label>
            <select class="size-xl" name="endSemester" id="endSemester">
                <option value="0"
                    <?= (int)$course->duration_time == 0 ? 'selected' : '' ?>>
                    <?= _('ein Semester') ?></option>
                <? foreach ($semester as $sem) : ?>
                    <? if ($sem->beginn >= $course->start_semester->beginn) : ?>
                        <option value="<?= $sem->semester_id ?>"
                            <?= (int)$course->duration_time != 0 && (($course->start_time + $course->duration_time) == $sem->beginn) ? 'selected' : '' ?>>
                            <?= htmlReady($sem->name) ?>
                        </option>
                    <? endif; ?>
                <? endforeach; ?>
                <option value="-1"
                    <?= (int)$course->duration_time == -1 ? 'selected' : '' ?>>
                    <?= _('unbegrenzt') ?></option>
            </select>
        </section>
    </section>
    <? if (!Request::isXhr()) : ?>
        <footer>
            <?= Studip\Button::createAccept(_('Semester speichern'), 'save', $semesterFormParams) ?>
            <? if (Request::isXhr()) : ?>
                <?= Studip\Button::createAccept(_('Semester speichern & schließen'), 'save_close', $semesterFormParams) ?>
            <? endif ?>
        </footer>
    <? else : ?>
        <div data-dialog-button>
            <?= Studip\Button::createAccept(_('Semester speichern'), 'save_close') ?>
        </div>
    <? endif ?>

</form>
