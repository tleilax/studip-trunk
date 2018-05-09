<form class="default settings-avatar" enctype="multipart/form-data" action="<?= $controller->url_for('settings/avatar/upload') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">

    <fieldset>
        <legend> <?= _('Profilbild') ?> </legend>

        <div id="avatar-preview">
            <img id="new-avatar">
        </div>
        <div id="avatar-buttons" class="hidden-js">
            <a href="" id="avatar-zoom-in" title="<?= _('Vergrößern') ?>">
                <?= Icon::create('add', 'clickable')->asImg(24) ?></a>
            <a href="" id="avatar-zoom-out" title="<?= _('Verkleinern') ?>">
                <?= Icon::create('remove', 'clickable')->asImg(24) ?></a>
            <a href="" id="avatar-rotate-clockwise" title="<?= _('Im Uhrzeigersinn drehen') ?>">
                <?= Icon::create('arr_1right', 'clickable')->asImg(24) ?></a>
            <a href="" id="avatar-rotate-counter-clockwise" title="<?= _('Gegen den Uhrzeigersinn drehen') ?>">
                <?= Icon::create('arr_1left', 'clickable')->asImg(24) ?></a>
        </div>

        <label class="avatar-upload">
            <?= _('Laden Sie hier ein neues Profilbild hoch') ?>
            <input type="file" name="avatar-img" id="avatar-upload">
        </label>

        <input type="hidden" name="cropped-image" id="cropped-image" value="">

    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Absenden'), 'upload', ['id' => 'submit-avatar']) ?>
        <? if ($customized): ?>
            <?= Studip\Button::create(_('Aktuelles Bild löschen'), 'reset') ?>
        <? endif ?>
    </footer>
</form>
