<form enctype="multipart/form-data" method="post" class="studip_form"
      action="<?= $controller->url_for('/new') ?>">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="parentFolderId" value="<?=htmlReady($parentFolderId)?>">
    <input type="hidden" name="rangeId" value="<?=htmlReady($rangeId)?>">
    <input type="hidden" name="context" value="<?=htmlReady($context)?>">
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