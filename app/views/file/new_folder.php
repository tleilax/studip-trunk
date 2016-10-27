<form method="post" class="studip_form"
      action="<?= $controller->url_for('/new') ?>"
      data-dialog="1">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="parentFolderId" value="<?=htmlReady($parentFolderId)?>">
    <input type="hidden" name="rangeId" value="<?=htmlReady($rangeId)?>">
    <fieldset>
        <fieldset>
            <label>
                <?= _('Name') ?>
                <input name="folderName" type="text" required>
            </label>
        </fieldset>
        <fieldset>
            <label>
                <?= _('Beschreibung') ?>
                <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"></textarea>
            </label>
        </fieldset>
        </fieldset>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Erstellen'), 'create_folder') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen')) ?>
    </div>    
</form>