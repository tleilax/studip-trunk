<form action="<?= $controller->url_for("consultation/admin/cancel_block/{$block->id}") ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Sprechstundentermine absagen') ?></legend>

        <label class="col-3">
            <?= _('Termin' ) ?><br>
            <ul class="default">
            <? foreach ($block->slots as $slot): ?>
                <? if (count($slot->bookings) > 0): ?>
                    <li>
                        <?= strftime('%A, %x', $slot->start_time) ?>
                        <?= sprintf(
                            _('%s bis %s Uhr'),
                            date('H:i', $slot->start_time),
                            date('H:i', $slot->end_time)
                        ) ?>
                    </li>
                <? endif; ?>
            <? endforeach; ?>
            </ul>
        </label>

        <label class="col-3">
            <?= _('Ort') ?><br>
            <?= htmlready($block->room) ?>
        </label>

        <label>
            <?= _('Grund') ?>
            <textarea name="reason"></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Termine absagen')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->url_for("consultation/admin#block-{$block->id}")
        ) ?>
    </footer>
</form>
