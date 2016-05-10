<form class="default" action="<?= $controller->url_for('course/statusgroups/save', $group->id) ?>" method="post">
    <section>
        <label for="name" class="required">
            <?= _('Name') ?>
        </label>
        <input type="text" name="name" size="75" maxlength="" value="<?= htmlReady($group->name) ?>">
    </section>
    <section>
        <label for="size">
            <?= _('Gruppengröße') ?>
        </label>
        <input type="number" name="size" value="<?= intval($group->size) ?>">
    </section>
    <section>
        <label for="selfassign">
            <?= _('Selbsteintrag') ?>
        </label>
        <input type="checkbox" name="selfassign" value="1"<?= $group->selfassign ? ' checked' : '' ?>>
    </section>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('course/statusgroups'),
            array('data-dialog' => 'close')) ?>
    </footer>
</form>
