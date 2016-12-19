<fieldset>
    <legend>
        <?= _("Ordnereigenschaften") ?>
    </legend>
    <label>
        <?= _('Name') ?>
        <input id="edit_folder_name" type="text" name="name" placeholder="<?= _('Name') ?>" value="<?= htmlReady($name); ?>">
    </label>
    <label>
        <?= _('Beschreibung') ?>
        <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"><?= htmlReady($description); ?></textarea>
    </label>
    <?=$folder_template instanceof Flexi_Template ? $folder_template->render() : $folder_template ?>
    <? if($folder_types): ?>
        <?= _('Ordnertyp') ?>
        <div class="folder_type_select_possibilities">
        <? foreach ($folder_types as $folder_type) : ?>
            <input type="radio" onChange="$(this).closest('form').submit()" name="folder_type" value="<?= htmlReady($folder_type['class']) ?>"
                id="folder_type_-<?= htmlReady($folder_type['class']) ?>"
                <?= ($folder_type['class'] == $current_folder_type) ? 'checked="checked"' : '' ?> >
            <label for="folder_type_-<?= htmlReady($folder_type['class']) ?>">
                <? if ($folder_type['icon']) : ?>
                    <?= $folder_type['icon']->asImg(50) ?>
                <? endif ?>
                <?= htmlReady($folder_type['name']) ?>
            </label>
        <? endforeach ?>
        </div>
    <? endif ?>
</fieldset>
