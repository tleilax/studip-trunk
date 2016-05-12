<form class="default" action="<?= $controller->url_for('course/statusgroups/batch_save_groups') ?>" method="post">
    <section>
        <label for="size">
            <?= _('Gruppengröße') ?>
        </label>
        <input type="number" name="size" value="<?= intval($group->size) ?>">
    </section>
    <section>
        <label>
            <input type="checkbox" name="selfassign" value="1"<?= $group->selfassign ? ' checked' : '' ?>>
            <?= _('Selbsteintrag erlaubt') ?>
        </label>
    </section>
    <section>
        <label>
            <input type="checkbox" name="exclusive" value="1"<?= ($group->selfassign == 2) ? ' checked' : '' ?>>
            <?= _('Exklusiver Selbsteintrag (in nur eine Gruppe)') ?>
        </label>
    </section>
    <?php foreach ($groups as $g) : ?>
        <input type="hidden" name="groups[]" value="<?= $g->id ?>">
    <?php endforeach ?>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('course/statusgroups'),
            array('data-dialog' => 'close')) ?>
    </footer>
</form>
