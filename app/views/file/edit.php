<form enctype="multipart/form-data" method="post" class="studip_form"
      action="<?= $controller->url_for('/edit') ?>">

    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="fileref_id" value="<?=htmlReady($fileref_id)?>">
    <input type="hidden" name="folder_id" value="<?=htmlReady($folder_id)?>">
    <fieldset>
        <fieldset>
            <label>
                <?= _('Lizenz') ?>
                <select name="licence">
                	<option value="1">Placeholder1</option>
                	<option value="2">Placeholder2</option>
                </select>
            </label>
        </fieldset>

        <fieldset>
            <label>
                <?= _('Beschreibung') ?>
                <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"><?= htmlReady($description); ?></textarea>
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
        </fieldset>*/?>
    </fieldset>


        <div data-dialog-button>
            <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'),
                $controller->url_for('/index/' . $folder_id)) ?>
        </div>
</form>
