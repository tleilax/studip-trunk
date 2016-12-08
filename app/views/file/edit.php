<?= $this->render_partial('file/_file_aside.php',
    [
        'file_ref' => $file_ref
    ])  ?>
<div id="file_management_forms">
    <form enctype="multipart/form-data"
        method="post"
        class="default"
        action="<?= $controller->url_for('/edit/' . $file_ref_id) ?>">

        <?= CSRFProtection::tokenTag() ?>
        <input type="hidden" name="fileref_id" value="<?=htmlReady($file_ref_id)?>">
        <input type="hidden" name="folder_id" value="<?=htmlReady($file_ref_id)?>">
        <fieldset>
            <legend><?= _("Datei bearbeiten") ?></legend>
            <label>
                <?= _('Name') ?>
                <input type="text" name="name" value="<?= htmlReady($name) ?>">
            </label>
            <label>
                <?= _('Beschreibung') ?>
                <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"><?= htmlReady($description); ?></textarea>
            </label>
            
            <? if ($content_terms_of_use_entries): ?>
            <?= _("Nutzungsbedingungen wählen") ?>
            
            <div class="file_select_possibilities" id="file_license_chooser_1">
                
                <? foreach ($content_terms_of_use_entries as $content_terms_of_use_entry) : ?>
                    <input type="radio" name="content_terms_of_use_id"
                        value="<?= htmlReady($content_terms_of_use_entry->id) ?>"
                        id="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>"
                        <?= ($content_terms_of_use_entry->id == $file_ref->content_terms_of_use_id) ? 'checked="checked"' : '' ?> >
                    <label for="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>">
                        <? if ($content_terms_of_use_entry['icon']) : ?>
                            <? if (filter_var($content_terms_of_use_entry['icon'], FILTER_VALIDATE_URL)) : ?>
                                <img src="<?= htmlReady($content_terms_of_use_entry['icon']) ?>">
                            <? else : ?>
                                <?= Icon::create($content_terms_of_use_entry['icon'], "clickable")->asImg(50) ?>
                            <? endif ?>
                        <? endif ?>
                        <?= htmlReady($content_terms_of_use_entry['name']) ?>
                    </label>
                    
                <? endforeach ?>
            </div>
            <? endif ?>
            
        </fieldset>
        
        <div data-dialog-button>
            <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'),
                $controller->url_for('/index/' . $folder_id)) ?>
        </div>
    </form>
</div>

