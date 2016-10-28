<form method="post" class="studip_form"
      action="<?= $controller->url_for('/edit/' . $folder_id) ?>"
      data-dialog="size=auto; reload-on-close">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="form_sent" value="1">
    <fieldset>
        <label>
            <?= _('Name') ?>
            <input type="text" name="name" placeholder="<?= _('Name') ?>" value="<?= htmlReady($name); ?>">
        </label>
    </fieldset>
    
    <fieldset>
        <label>
            <?= _('Beschreibung') ?>
            <textarea name="description" placeholder="<?= _('Optionale Beschreibung') ?>"><?= htmlReady($description); ?></textarea>
        </label>
    </fieldset>

    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('/index/' . $folder_id)) ?>
    </div>
</form>
