<form class="default" action="<?= $controller->url_for('course/statusgroups/batch_move_members') ?>" method="post">
    <section>
        <label for="target_group">
            <?= ngettext('In welche Gruppe soll die gewählten Person verschoben werden?',
                sprintf('In welche Gruppe sollen die %u gewählten Personen verschoben werden?', count($members)),
                count($members)) ?>
            <select name="target_group">
                <?php foreach ($target_groups as $g) : ?>
                    <option value="<?= $g->id ?>"><?= htmlReady($g->name) ?></option>
                <?php endforeach ?>
            </select>
        </label>
    </section>
    <?php foreach ($members as $m) : ?>
        <input type="hidden" name="members[]" value="<?= $m ?>"/>
    <?php endforeach ?>
    <input type="hidden" name="source" value="<?= $source_group ?>"/>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Verschieben'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'),
            $controller->url_for('course/statusgroups'),
            array('data-dialog' => 'close')) ?>
    </footer>
</form>
