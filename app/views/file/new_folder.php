<form method="post" class="studip_form"
      action="<?= $controller->url_for('/new') ?>"
      data-dialog="reload-on-close">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="parentFolderId" value="<?=htmlReady($parentFolderId)?>">
    <input type="hidden" name="rangeId" value="<?=htmlReady($rangeId)?>">
    <input type="hidden" name="submitted" value="1">
    <fieldset>
        <fieldset>
            <label>
                <?= _('Name') ?>
                <input name="folderName" type="text" required="required" value="<?= htmlReady($folderName) ?>">
            </label>
        </fieldset>
        <fieldset>
            <label>
                <?= _('Beschreibung') ?>
                <textarea name="description" placeholder="<?= _('Optionale Beschreibung') 
                    ?>"><?= htmlReady($folderDescription) ?></textarea>
            </label>
        </fieldset>
        </fieldset>
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Erstellen')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen')) ?>
    </div>    
</form>