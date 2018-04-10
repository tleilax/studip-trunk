<form class="default settings-avatar" enctype="multipart/form-data" action="<?= $controller->url_for('settings/avatar/upload') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studipticket" value="<?= get_ticket() ?>">

    <fieldset>
        <legend> <?= _('Profilbild') ?> </legend>

        <div id="avatar-preview">
        </div>

        <label>
            <?= _('Laden Sie hier ein neues Profilbild hoch') ?>
            <input type="file" name="avatar-img" id="upload-avatar">
        </label>

    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Absenden'), 'upload') ?>
        <? if ($customized): ?>
            <?= Studip\Button::create(_('Aktuelles Bild löschen'), 'reset') ?>
        <? endif ?>
    </footer>
</form>
