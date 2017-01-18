<form class="default settings-avatar" enctype="multipart/form-data" action="<?= $controller->url_for('settings/avatar/upload') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studipticket" value="<?= get_ticket() ?>">

    <fieldset>
        <legend> <?= _('Profilbild') ?> </legend>

        <div class="form-group">
            <?= Avatar::getAvatar($user->user_id)->getImageTag(Avatar::NORMAL) ?>

            <label class="file-upload">
                <?= _('W�hlen Sie ein Bild von Ihrer Festplatte aus.') ?>
                <input name="imgfile" type="file" accept="image/gif,image/png,image/jpeg">

                <p class="form-text">
                    <?= sprintf(
                        _('Die Bilddatei darf max. %s gro� sein, es sind nur Dateien mit den Endungen .jpg, .png oder .gif erlaubt!'),
                        relsize(Avatar::MAX_FILE_SIZE)
                    ) ?>
                </p>

                <a onclick="javascript:void 0" class=button>Ausw�hlen</a>
            </label>
        </div>

    </fieldset>

    <footer>
        <?= Studip\Button::createAccept(_('Absenden'), 'upload') ?>
        <? if ($customized): ?>
            <?= Studip\Button::create(_('Aktuelles Bild l�schen'), 'reset') ?>
        <? endif; ?>
    </footer>
</form>
