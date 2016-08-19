<form class="default" action="<?= $controller->url_for('course/statusgroups/batch_delete_groups') ?>" method="post">
    <header>
        <h1>
            <?= ngettext('Soll die folgende Gruppe wirklich gel�scht werden?',
                'Sollen die folgenden Gruppen wirklich gel�scht werden?', count($groups)) ?>
        </h1>
        <ul>
            <?php foreach ($groups as $g) : ?>
            <li>
                <input type="hidden" name="groups[]" value="<?= $g->id ?>">
                <?= htmlReady($g->name) ?>
            </li>
            <?php endforeach ?>
        </ul>
    </header>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('L�schen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('course/statusgroups')) ?>
    </footer>
</form>
