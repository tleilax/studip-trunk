<form enctype="multipart/form-data" method="post" class="studip_form"
      action="<?= $controller->url_for('/upload') ?>">

    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="folder_id" value="<?=htmlReady($folder_id)?>">
    <fieldset>
        <fieldset>
            <label>
                <?= _('Datei(en) auswählen') ?>
                <input name="file[]" type="file" required multiple>
            </label>
        </fieldset>

        <fieldset>
            <label>
                <?= _('Beschreibung') ?>
                <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"></textarea>
            </label>
        </fieldset>

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
        </fieldset>
    </fieldset>
*/?>

        <div data-dialog-button>
            <?= Studip\Button::createAccept(_('Hochladen'), 'upload') ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'),
                $controller->url_for('/tree/' . $folder_id)) ?>
        </div>
</form>
