<form class="default" action="<?= $controller->url_for('course/statusgroups/batch_create') ?>" method="post">
    <fieldset>
        <legend>
            <?= _('Wie sollen Gruppen angelegt werden?') ?>
        </legend>
        <label>
            <input type="radio" name="mode" value="numbering" checked onclick="$('.numbering-data').show();$('.course-data').hide();">
            <?= _('Erzeuge n Gruppen mit Namenspräfix') ?>
        </label>
        <label>
            <input type="radio" name="mode" value="coursedata" onclick="$('.numbering-data').hide();$('.course-data').show();">
            <?= _('Lege Gruppen zu bestehenden Veranstaltungsdaten an') ?>
        </label>
    </fieldset>
    <fieldset>
        <legend>
            <?= _('Lege folgende Gruppen an') ?>
        </legend>
        <section class="numbering-data">
            <label class="required">
                <?= _('Anzahl anzulegender Gruppen') ?>
            </label>
            <input type="number" name="number" value="5">
        </section>
        <section class="numbering-data">
            <label>
                <?= _('Beginne Nummerierung bei') ?>
            </label>
            <input type="number" name="startnumber" value="1">
        </section>
        <section class="numbering-data">
            <label class="required">
                <?= _('Namenspräfix') ?>
            </label>
            <input type="text" name="prefix" maxlength="200" value="<?= _('Gruppe') ?>">
        </section>
        <?php if ($has_topics) : ?>
            <section class="course-data">
                <label>
                    <input type="radio" name="createmode" value="topics">
                    <?= _('Lege eine Gruppe pro Thema an') ?>
                </label>
            </section>
        <?php endif ?>
        <?php if ($has_cycles || $has_singledates) : ?>
            <section class="course-data">
                <label>
                    <input type="radio" name="createmode" value="dates">
                    <?= _('Lege eine Gruppe pro regelmäßiger Zeit/Einzeltermin an') ?>
                </label>
            </section>
        <?php endif ?>
        <section class="course-data">
            <label>
                <input type="radio" name="createmode" value="lecturers">
                <?= _('Lege eine Gruppe pro Lehrendem an') ?>
            </label>
        </section>
    </fieldset>
    <fieldset>
        <legend>
            <?= _('Voreinstellungen für alle anzulegenden Gruppen') ?>
        </legend>
        <section>
            <label>
                <?= _('Gruppengröße') ?>
            </label>
            <input type="number" name="size" value="0">
        </section>
        <section>
            <label>
                <input type="checkbox" name="selfassign" value="1">
                <?= _('Selbsteintrag') ?>
            </label>
        </section>
        <section>
            <label>
                <input type="checkbox" name="exclusive" value="1">
                <?= _('Selbsteintrag in nur eine Gruppe') ?>
            </label>
        </section>
        <section>
            <label>
                <input type="checkbox" name="makefolder" value="1">
                <?= _('Dateiordner anlegen') ?>
            </label>
        </section>
    </fieldset>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Anlegen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('course/statusgroups'),
            array('data-dialog' => 'close')) ?>
    </footer>
</form>
<script type="text/javascript" language="JavaScript">
    //<!--
    $('.course-data').hide();
    //-->
</script>
