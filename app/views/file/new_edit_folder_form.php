<?= CSRFProtection::tokenTag() ?>
<input type="hidden" name="form_sent" value="1">
<fieldset>
    <legend>
        <?= _("Ordnereigenschaften") ?>
    </legend>
    <label>
        <?= _('Name') ?>
        <input type="text" name="name" placeholder="<?= _('Name') ?>" value="<?= htmlReady($name); ?>">
    </label>
    <label>
        <?= _('Beschreibung') ?>
        <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"><?= htmlReady($description); ?></textarea>
    </label>
    <label>
        <?= _('Ordnertyp') ?>
        <select name="folder_type">
        <? if ($folder_types): ?>
        <? foreach ($folder_types as $folder_type) : ?>
        <option value="<?= $folder_type['class'] ?>"
            <?= ($folder_type['class'] == $current_folder_type) ? 'selected="selected"' : '' ?>
            ><?= $folder_type['name'] ?></option>
        <? endforeach ?>
        <? endif ?>
        </select>
    </label>
</fieldset>
