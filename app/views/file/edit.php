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
            <?= _('Lizenz') ?>
            <select name="licence">
                <option value="1">Placeholder1</option>
                <option value="2">Placeholder2</option>
            </select>
        </label>

        <label>
            <?= _('Beschreibung') ?>
            <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"><?= htmlReady($description); ?></textarea>
        </label>

        <?/*
        <fieldset>
            <label>
                <input type="radio" name="restricted" value="0">
                <?= _('Ja, dieses Dokument ist frei von Rechten Dritter.') ?>
            </label>
            <label>
                <input type="radio" name="restricted" value="1">
                <?= sprintf(_('Nein, dieses Dokumnt ist %snicht%s frei von Rechten Dritter.'), '<em>', '</em>') ?>
            </label>
        </fieldset>*/?>
    </fieldset>


        <div data-dialog-button>
            <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'),
                $controller->url_for('/index/' . $folder_id)) ?>
        </div>
</form>
