<p><?= _('Bitte geben Sie hier Ihre Suchkriterien ein') . ':'; ?></p>
<form class="default" method="post" action="<?= URLHelper::getLink(); ?>">
    <label>
        <?= _('Name der Veranstaltung') . ':'; ?>
        <input type="text" size="30" maxlength="255" name="courseName"
            value="<?= htmlReady($courseName) ?>">
    </label>
    <label>
        <?= _('Lehrende der Veranstaltung') . ':'; ?>
        <input type="text" size="30" maxlength="255" name="courseDozenten"
            value="<?= htmlReady($courseDozenten) ?>">
    </label>
    <label>
        <?= _('Semester') . ':'; ?>
        <input type="text" size="30" maxlength="255" name="courseSemester"
            value="<?= htmlReady($courseSemester) ?>">
    </label>
    <label>
        <?= _('Heimat-Einrichtung') . ':'; ?>
        <select name="courseInstitute">
            <option value="#all#"><?= _('alle'); ?></option>
            <? foreach($availableInstitutes as $institute) : ?>
            <option value=""></option>
            <? endforeach ?>
        </select>
    </label>
    <label>
        <?= _('Fakultät') . ':'; ?>
        <select name="courseInstitute">
            <option value="#all#"><?= _('alle'); ?></option>
            <? foreach($availableFaculties as $faculty) : ?>
            <option value=""></option>
            <? endforeach ?>
        </select>
    </label>
    <label>
        <?= _('Beschreibung') . ':'; ?>
        <input type="text" size="30" maxlength="255" name="courseDescription"
            value="<?= htmlReady($courseDescription) ?>">
    </label>
    <label>
        <?= _('Suche über alle Felder') . ':'; ?>
        <input type="text" size="30" maxlength="255" name="courseAllFields"
            value="<?= htmlReady($courseAllFields) ?>">
    </label>
    <label>
        <?= _('Nur Veranstaltungen anzeigen, an denen ich teilgenommen habe') . ':'; ?>
        <input type="checkbox" name="courseOnlyParticipated"
            <?= $courseOnlyParticipated ? 'checked="checked"' : '' ?> >
    </label>
    <?= \Studip\Button::create(_('Suchen'), 'search', array('type' => 'submit')); ?>
</form>

<? if ($foundCourses) : ?>
    <? if (count($foundCourses) == 1) : ?>
        <?= MessageBox::info(_('Die folgende Veranstaltung wurde gefunden') . ':'); ?>
    <? else : ?>
        <?= MessageBox::info(_('Die folgenden Veranstaltungen wurde gefunden') . ':'); ?>
    <? endif ?>
    
    <? foreach ($foundCourses as $course) : ?>
        
    <? endforeach ?>
<? else : ?>
    <? if ($searchExecuted) : ?>
        <?= MessageBox::info(_('Es wurde keine Veranstaltung gefunden!')); ?>
    <? endif ?>
<? endif ?>