<form name="reason_form" action="<?= $controller->url_for("consultation/overview/book/{$slot->block_id}/{$slot->id}") ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Sprechstundentermin reservieren') ?></legend>

        <label class="col-3">
            <?= _('Termin') ?><br>
            <?= strftime('%A, %x', $slot->start_time) ?>
            <?= sprintf(
                _('%s bis %s Uhr'),
                date('H:i', $slot->start_time),
                date('H:i', $slot->end_time)
            ) ?>
        </label>

        <label class="col-3">
            <?= _('Ort') ?><br>
            <?= htmlready($slot->block->room) ?>
        </label>

        <label>
            <?= _('Grund') ?>
            <textarea name="reason"></textarea>
        </label>
    </fieldset>


    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Termin reservieren')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for("consultation/overview#block-{$slot->block_id}")
        ) ?>
    </footer>
</form>
