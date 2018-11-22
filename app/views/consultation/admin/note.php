<? if (!$slot_id): ?>
    <?= MessageBox::info(implode('<br>', [
        _('Die Änderungen wirken sich auf alle diesem Sprechstundenblock zugewiesenen Termine aus.'),
        _('In der Textbox unten steht der ursprüngliche Text, wie er beim Anlegen eingetragen wurde.')
    ]))->hideClose() ?>
<? endif; ?>

<form action="<?= $controller->url_for("consultation/admin/note/{$block->id}/{$slot_id}") ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
        <? if ($slot_id): ?>
            <?= _('Anmerkung zu diesem Sprechstundentermin bearbeiten') ?>
        <? else: ?>
            <?= _('Anmerkung zu diesem Sprechstundenblock bearbeiten') ?>
        <? endif; ?>
        </legend>

        <label>
            <?=_('Anmerkung') ?>
            <textarea name="note"><?= htmlReady($slot_id ? $block->slots->find($slot_id)->note : $block->note ) ?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for("consultation/admin#block-{$block->id}")
        ) ?>
    </footer>
</form>
