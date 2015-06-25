<h1><?= _('Grunddaten') ?></h1>
<label class="caption">
    <?= _('Typ') ?>
    <span class="required">*</span>
    <select name="coursetype">
        <?php foreach ($types as $class => $subtypes) { ?>
            <optgroup label="<?= htmlReady($class) ?>">
                <?php foreach ($subtypes as $type) { ?>
                    <option value="<?= $type['id'] ?>"<?= $type['id'] == $values['coursetype'] ? ' selected="selected"' : '' ?>>
                        <?= htmlReady($type['name']) ?>
                    </option>
                <?php } ?>
            </optgroup>
        <?php } ?>
    </select>
</label>
<label class="caption">
    <?= _('Semester') ?>
    <span class="required">*</span>
    <select name="start_time">
        <?php foreach ($semesters as $semester) { ?>
            <option value="<?= $semester->beginn ?>"<?= $semester->beginn == $values['start_time'] ? ' selected="selected"' : '' ?>>
                <?= htmlReady($semester->name) ?>
            </option>
        <?php } ?>
    </select>
</label>
<label class="caption">
    <?= _('Name') ?>
    <span class="required">*</span>
    <input type="text" name="name" size="75" maxlength="254" value="<?= $values['name'] ?>"/>
</label>
<label class="caption">
    <?= _('Veranstaltungsnummer') ?>
    <input type="text" name="number" size="20" maxlength="99" value="<?= $values['number'] ?>"/>
</label>
<label class="caption">
    <?= _('Heimateinrichtung') ?>
    <span class="required">*</span>
    <select name="institute" onchange="STUDIP.CourseWizard.getLecturerSearch()" data-ajax-url="<?= URLHelper::getLink('dispatch.php/course/wizard/ajax') ?>">
        <?php
            $fak_id = '';
            foreach ($institutes as $inst) :
                if ($inst['is_fak']) {
                    $fak_id = $inst['Institut_id'];
                }
        ?>
            <option value="<?= $inst['Institut_id'] ?>"<?=
                $inst['Institut_id'] == $values['institute'] ? ' selected="selected"' : '' ?> class="<?=
                $inst['is_fak'] ? 'faculty' : ($inst['fakultaets_id'] == $fak_id ? 'sub_institute' : 'institute') ?>">
                <?= htmlReady($inst['Name']) ?>
            </option>
        <?php endforeach ?>
    </select>
</label>
<?= Assets::input('icons/yellow/arr_2right.svg',
    array('name' => 'select_institute', 'value' => '1', 'class' => 'hidden-js')) ?>
<label class="caption">
    <?= _('Dozent/-innen') ?>
    <span class="required">*</span>
    <div id="lecturersearch">
        <?= $lsearch ?>
    </div>
</label>
<?php if ($values['lecturer_id_parameter']) : ?>
<?= Assets::input('icons/yellow/arr_2down.svg',
    array('name' => 'add_lecturer', 'value' => '1', 'class' => 'hidden-js')) ?>
<?php endif ?>
<br/>
<div id="lecturers">
    <?php foreach ($values['lecturers'] as $id => $assigned) : ?>
        <?php if ($user = User::find($id)) : ?>
    <?= $this->render_partial('coursewizard/basicdata/_user',
            array('class' => 'lecturer', 'inputname' => 'lecturers', 'user' => $user)) ?>
        <?php endif ?>
    <?php endforeach ?>
</div>
<?php if ($dsearch) : ?>
<label class="caption">
    <?= _('Vertretungen') ?>
    <div id="deputysearch">
        <?= $dsearch ?>
    </div>
</label>
<?php if ($values['deputy_id_parameter']) : ?>
    <?= Assets::input('icons/yellow/arr_2down.svg',
        array('name' => 'add_deputy', 'value' => '1', 'class' => 'hidden-js')) ?>
<?php endif ?>
<br/>
<div id="deputies">
    <?php foreach ($values['deputies'] as $id => $assigned) : ?>
        <?php if ($user = User::find($id)) : ?>
            <?php if (!in_array($id, array_keys($values['lecturers']))) : ?>
    <?= $this->render_partial('coursewizard/basicdata/_user',
        array('class' => 'deputy', 'inputname' => 'deputies', 'user' => $user)) ?>
            <?php endif ?>
        <?php endif ?>
    <?php endforeach ?>
</div>
<?php endif ?>