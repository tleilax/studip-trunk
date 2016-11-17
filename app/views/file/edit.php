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
            <!-- temporary JavaScript solution until the CSS solution works! -->
            <!--
            <input id="file_license_chooser_id_field_1" type="hidden" name="content_terms_of_use_id" value="<?= htmlReady($file_ref_id->content_terms_of_use_id) ?>">
            <? foreach ($content_terms_of_use_entries as $content_terms_of_use_entry) : ?>
                <div 
                    class="termsOfUseBox <?= ($content_terms_of_use_entry->id == $file_ref_id->content_terms_of_use_id) ? 'selected' : '' ?>"
                    onclick="STUDIP.Files.selectLicense(this, '1');"
                    data-id="<?= htmlReady($content_terms_of_use_entry->id) ?>">
                    <? if ($content_terms_of_use_entry['icon']) : ?>
                        <? if (filter_var($content_terms_of_use_entry['icon'], FILTER_VALIDATE_URL)) : ?>
                            <img src="<?= htmlReady($content_terms_of_use_entry['icon']) ?>" width="48px" height="48px">
                        <? else : ?>
                            <?= Icon::create($content_terms_of_use_entry['icon'], "clickable")->asImg(50) ?>
                        <? endif ?>
                    <? endif ?>
                    <?= htmlReady($content_terms_of_use_entry['name']) ?>
                </div>
            <? endforeach ?>
            -->
            
            <? foreach ($content_terms_of_use_entries as $content_terms_of_use_entry) : ?>
                <label for="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>">
                    <?= htmlReady($content_terms_of_use_entry['name']) ?>
                </label>
                <input type="radio" name="content_terms_of_use_id"
                    value="<?= htmlReady($content_terms_of_use_entry->id) ?>"
                    id="content_terms_of_use-<?= htmlReady($content_terms_of_use_entry->id) ?>"
                    <?= ($content_terms_of_use_entry->id == $file_ref_id->content_terms_of_use_id) ? 'checked="checked"' : '' ?> >
                <? if ($content_terms_of_use_entry['icon']) : ?>
                <div>
                    <? if (filter_var($content_terms_of_use_entry['icon'], FILTER_VALIDATE_URL)) : ?>
                        <img src="<?= htmlReady($content_terms_of_use_entry['icon']) ?>" width="48px" height="48px">
                    <? else : ?>
                        <?= Icon::create($content_terms_of_use_entry['icon'], "clickable")->asImg(50) ?>
                    <? endif ?>
                <? endif ?>
                </div>
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
