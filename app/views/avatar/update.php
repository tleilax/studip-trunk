<form class="default avatar" enctype="multipart/form-data"
        action="<?= $controller->url_for('avatar/upload', $type, $id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <section>
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
    </section>
    <section>
        <div id="avatar-preview">
            <img id="new-avatar" src="<?= $avatar ?>">
        </div>

        <label class="avatar-upload">
            <?= _('Laden Sie hier ein neues Bild hoch') ?>
            <input type="file" name="avatar-img" id="avatar-upload">
        </label>

        <input type="hidden" name="cropped-image" id="cropped-image" value="">
    </section>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Absenden'), 'upload', ['id' => 'submit-avatar']) ?>
        <? if ($customized): ?>
            <?= Studip\LinkButton::create(_('Aktuelles Bild löschen'),
                $controller->url_for('avatar/delete', $type, $id)) ?>
        <? endif ?>
    </footer>
</form>
