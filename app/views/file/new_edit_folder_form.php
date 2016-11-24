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
    <? if($new_folder_form && $folder_types): ?>
        <?= _('Ordnertyp') ?>
        <div class="folder_type_select_possibilities">
        <? foreach ($folder_types as $folder_type) : ?>
            <input type="radio" name="folder_type" value="<?= htmlReady($folder_type['class']) ?>"
                id="folder_type_-<?= htmlReady($folder_type['class']) ?>"
                <?= ($folder_type['class'] == $current_folder_type) ? 'checked="checked"' : '' ?> >
            <label for="folder_type_-<?= htmlReady($folder_type['class']) ?>">
                <? if ($folder_type['icon']) : ?>
                    <?= $folder_type['icon']->asImg(50) ?>
                <? endif ?>
                <?= htmlReady($folder_type['name']) ?>
            </label>
        <? endforeach ?>
    <? endif ?>
    </div>
</fieldset>
