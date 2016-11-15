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
        <? if ($content_terms_of_use_entries): ?>
        <label>
            <?= _('Nutzungsrechte') ?>
            <select name="content_terms_of_use_id">
                <? foreach ($content_terms_of_use_entries as $ctou): ?>
                    <option value="<?= $ctou->id ?>"><?= htmlReady($ctou->name) ?></option>
                <? endforeach ?>
            </select>
        </label>
        <? endif ?>
        
        <label>
            <?= _('Beschreibung') ?>
            <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"><?= htmlReady($description); ?></textarea>
        </label>
    </fieldset>

    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('/index/' . $folder_id)) ?>
    </div>
</form>
