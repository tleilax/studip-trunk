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
</fieldset>

<?= $folder_template instanceof Flexi_Template ? $folder_template->render() : $folder_template ?>

<fieldset class="select_terms_of_use">
    <legend>
        <?= _("Ordnertyp auswählen") ?>
    </legend>
    <? foreach ($folder_types as $folder_type) : ?>
        <input type="radio" name="folder_type"
               value="<?= htmlReady($folder_type['class']) ?>"
               id="folder-type-<?= htmlReady($folder_type['class']) ?>"
            <?= ($content_terms_of_use_entry->id == $selected_terms_of_use_id) ? 'checked="checked"' : '' ?> >
        <label for="folder-type-<?= htmlReady($folder_type['class']) ?>">
            <div class="icon">
                <? if ($folder_type['icon']) : ?>
                    <?= $folder_type['icon']->asImg('32px') ?>
                <? endif ?>
            </div>
            <div class="text">
                <?= htmlReady($folder_type['name']) ?>
            </div>
            <?= Icon::create("arr_1down", "clickable")->asImg(24, array('class' => "arrow")) ?>
            <?= Icon::create("check-circle", "clickable")->asImg(32, array('class' => "check")) ?>
        </label>
        <? $folder_template = $folder_type['instance']->getEditTemplate() ?>
        <? if ($folder_template) : ?>
            <div class="terms_of_use_description">
                <div class="description">
                    <?= $folder_template->render() ?>
                </div>
            </div>
        <? endif ?>
    <? endforeach ?>
</fieldset>