<form enctype="multipart/form-data" method="post" class="studip_form"
      action="<?= $controller->url_for('document/files/upload/' . $env_dir) ?>">

    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <fieldset>
            <label>
                <?= _('Datei(en) auswählen') ?>
                <input name="upfile" id="file" type="file" required>
            </label>
        </fieldset>

        <fieldset>
            <label>
                <?= _('Titel') ?>
                <input type="text" name="name" id="name" placeholder="<?= _('Titel') ?>">
            </label>
        </fieldset>

        <fieldset>
            <label>
                <?= _('Beschreibung') ?>
                <textarea name="description" placeholder="<?= _('Beschreibung') ?>"></textarea>
            </label>
        </fieldset>

        <fieldset>
            <label>
                <input type="radio" name="restricted" checked value="0">
                <?= _('Ja, dieses Dokument ist frei von Rechten Dritter.') ?>
            </label>
            <label>
                <input type="radio" name="restricted" value="1">
                <?= sprintf(_('Nein, dieses Dokumnt ist %snicht%s frei von Rechten Dritter.'), '<em>', '</em>') ?>
            </label>
        </fieldset>
    </fieldset>

        <?= Studip\Button::createAccept(_('Hochladen'), 'upload') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
                $controller->url_for('document/files/index/' . $env_dir)) ?>
</form>
