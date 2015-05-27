<h1><?= _('Grunddaten') ?></h1>
<label class="caption">
    <?= _('Typ') ?>
    <select name="type">
        <?php foreach ($types as $class => $subtypes) { ?>
            <optgroup label="<?= htmlReady($class) ?>">
                <?php foreach ($subtypes as $type) { ?>
                    <option value="<?= $type['id'] ?>"<?= $type['id'] == $values['type'] ? ' selected="selected"' : '' ?>>
                        <?= htmlReady($type['name']) ?>
                    </option>
                <?php } ?>
            </optgroup>
        <?php } ?>
    </select>
</label>
<label class="caption">
    <?= _('Semester') ?>
    <select name="start_time">
        <option value=""><?= _('bitte auswählen') ?></option>
        <?php foreach ($semesters as $semester) { ?>
            <option value="<?= $semester->beginn ?>"<?= $semester->beginn == $values['start_time'] ? ' selected="selected"' : '' ?>>
                <?= htmlReady($semester->name) ?>
            </option>
        <?php } ?>
    </select>
</label>
<label class="caption">
    <?= _('Titel') ?>
    <input type="text" name="name" size="75" maxlength="254" value="<?= $values['name'] ?>"/>
</label>
<label class="caption">
    <?= _('Veranstaltungsnummer') ?>
    <input type="text" name="number" size="20" maxlength="99" value="<?= $values['number'] ?>"/>
</label>
<label class="caption">
    <?= _('Heimateinrichtung') ?>
    <select name="institute" onchange="STUDIP.CourseWizard.getLecturerSearch()" data-ajax-url="<?= URLHelper::getLink('dispatch.php/course/wizard/ajax') ?>">
        <?php foreach ($institutes as $inst) { ?>
            <option value="<?= $inst['Institut_id'] ?>"<?= $inst['Institut_id'] == $values['institute'] ? ' selected="selected"' : '' ?>>
                <?= htmlReady($inst['Name']) ?>
            </option>
        <?php } ?>
    </select>
</label>
<label class="caption">
    <?= _('Dozent/-innen') ?>
    <span id="lecturersearch">
        <?= $lsearch ?>
    </span>
</label>
<div id="lecturers">
    <?php foreach ($values['lecturers'] as $id => $assigned) : $l = User::find($id); ?>
    <div class="lecturer">
        <input type="hidden" name="lecturers[]" value="<?= $id ?>"/>
        <?= Avatar::getAvatar($l->id)->getImageTag(Avatar::SMALL) ?>
        <?= htmlReady($l->getFullname('full_rev')) ?> (<?= htmlReady($l->username) ?>)
        <a href="" onclick="return STUDIP.CourseWizard.removePerson(this)">
            <?= Assets::img('icons/blue/trash.svg') ?></a>
    </div>
    <?php endforeach ?>
</div>
<?php if ($dsearch) : ?>
<label class="caption">
    <?= _('Vertretungen') ?>
    <?= $dsearch ?>
</label>
<div id="deputies">
    <?php foreach ($values['deputies'] as $id => $assigned) : $d = User::find($id); ?>
        <div class="deputy">
            <input type="hidden" name="deputies[]" value="<?= $id ?>"/>
            <?= Avatar::getAvatar($id)->getImageTag(Avatar::SMALL) ?>
            <?= htmlReady($d->getFullname('full_rev')) ?>
            <a href="" onclick="return STUDIP.CourseWizard.removePerson(this)">
                <?= Assets::img('icons/blue/trash.svg') ?></a>
        </div>
    <?php endforeach ?>
</div>
<?php endif ?>