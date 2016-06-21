<form class="default" method="post" 
    action="<?= URLHelper::getLink('dispatch.php/admin/courses/sidebar'); ?>" >
    
    <label>
        <?= _('Freie Suche'); ?>
        <input name="search" type="checkbox" value="1"
            <?= in_array('search', $userSelectedElements) ? 'checked="checked"' : '' ?>
            >
    </label>
    
    <label>
        <?= _('Einrichtung'); ?>
        <input name="institute" type="checkbox" value="1"
            <?= in_array('institute', $userSelectedElements) ? 'checked="checked"' : '' ?>
            >
    </label>
    <label>
        <?= _('Semester'); ?>
        <input name="semester" type="checkbox" value="1"
            <?= in_array('semester', $userSelectedElements) ? 'checked="checked"' : '' ?>
            >
    </label>
    <label>
        <?= _('Veranstaltungstyp-Filter'); ?>
        <input name="courseType" type="checkbox" value="1"
            <?= in_array('courseType', $userSelectedElements) ? 'checked="checked"' : '' ?>
            >
    </label>
    <label>
        <?= _('Dozent'); ?>
        <input name="teacher" type="checkbox" value="1"
            <?= in_array('teacher', $userSelectedElements) ? 'checked="checked"' : '' ?>
            >
    </label>
    <label>
        <?= _('Aktionsfeld'); ?>
        <input name="actionArea" type="checkbox" value="1"
            <?= in_array('actionArea', $userSelectedElements) ? 'checked="checked"' : '' ?>
            >
    </label>
    <label>
        <?= _('Darstellungs-Filter'); ?>
        <input name="viewFilter" type="checkbox" value="1"
            <?= in_array('viewFilter', $userSelectedElements) ? 'checked="checked"' : '' ?>
            >
    </label>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Speichern')); ?>
    </div>
</form>