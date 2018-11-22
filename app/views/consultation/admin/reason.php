<form action="<?= $controller->url_for("consultation/admin/reason/{$booking->slot->block_id}/{$booking->slot_id}/{$booking->id}") ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Grund für die Sprechstundenbuchung bearbeiten') ?></legend>

        <label>
            <?= _('Grund') ?>
            <textarea name="reason"><?= htmlReady($booking->reason) ?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for("consultation/admin#block-{$booking->slot->block_id}")
        ) ?>
    </footer>
</form>
