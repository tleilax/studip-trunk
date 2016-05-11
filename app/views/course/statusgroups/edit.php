<form class="default" action="<?= $controller->url_for('course/statusgroups/save', $group->id) ?>" method="post">
    <fieldset>
        <legend>
            <?= _('Einstellungen') ?>
        </legend>
        <section>
            <label for="name" class="required">
                <?= _('Name') ?>
            </label>
            <input type="text" name="name" size="75" maxlength="255" value="<?= htmlReady($group->name) ?>">
        </section>
        <section>
            <label for="size">
                <?= _('Gruppengröße') ?>
            </label>
            <input type="number" name="size" value="<?= intval($group->size) ?>">
        </section>
        <section>
            <label>
                <input type="checkbox" name="selfassign" value="1"<?= $group->selfassign ? ' checked' : '' ?>>
                <?= _('Selbsteintrag') ?>
            </label>
        </section>
        <section>
            <label>
                <input type="checkbox" name="exclusive" value="1"<?= ($group->selfassign == 2) ? ' checked' : '' ?>>
                <?= _('Exklusiver Selbsteintrag (in nur eine Gruppe)') ?>
            </label>
        </section>
    </fieldset>
    <fieldset>
        <legend>
            <?= _('Zuordnung von Terminen') ?>
        </legend>
        <section>
            <?php if ($cycles || $singledates) : ?>
                <?php if ($cycles) : ?>
                    <section class="contentbox">
                        <header>
                            <h1><?= _('Regelmäßige Zeiten') ?></h1>
                        </header>
                        <?php foreach ($cycles as $c) : ?>
                            <article class="<?= ContentBoxHelper::classes($c->id) ?>" id="<?= $c->id ?>">
                                <header>
                                    <h1>
                                        <a href="<?= ContentBoxHelper::href($c->id, array('contentbox_type' => 'news')) ?>">
                                            <?= htmlReady($c->toString()) ?>
                                        </a>
                                    </h1>
                                </header>
                                <section>
                                    <?php foreach ($c->dates as $d) : ?>
                                        <label for="<?= $d->id ?>">
                                            <input type="checkbox" name="dates[]" value="<?= $d->id ?>" id="<?= $d->id?>"
                                            <?= $group->dates->find($d->id) ? ' checked' : '' ?>>
                                            <?= $d->getFullname() ?>
                                        </label>
                                    <?php endforeach ?>
                                </section>
                            </article>
                        <?php endforeach ?>
                    </section>
                <?php endif ?>
                <?php if ($singledates) : ?>
                    <section class="contentbox">
                        <header>
                            <h1><?= _('Einzeltermine') ?></h1>
                        </header>
                        <?php foreach ($singledates as $s) : ?>
                            <label for="<?= $s->id ?>">
                                <input type="checkbox" name="dates[]" value="<?= $s->id ?>" id="<?= $s->id?>"
                                    <?= $group->dates->find($s->id) ? ' checked' : '' ?>>
                                <?= $s->getFullname() ?>
                            </label>
                        <?php endforeach ?>
                    </section>
                <?php endif ?>
            <?php else : ?>
                <?= MessageBox::info(_('Diese Veranstaltung hat keine Termine.')); ?>
            <?php endif ?>
        </section>
    </fieldset>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('course/statusgroups'),
            array('data-dialog' => 'close')) ?>
    </footer>
</form>
