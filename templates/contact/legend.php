<div class="contact-legend">
    <?= _('Bedienung:') ?>
    <ul>
        <li>
            <?= Icon::create('mail', 'clickable')->asImg(16) ?>
            <?= _('Nachricht an Kontakt') ?>
        </li>
    <? if ($open): ?>
        <li>
            <?= Icon::create('arr_1up', 'clickable')->asImg(16) ?>
            <?= _('Kontakt zuklappen') ?>
        </li>
        <li>
            <?= Icon::create('person', 'clickable')->asImg(16) ?>
            <?= _('Buddystatus') ?>
        </li>
        <li>
            <?= Icon::create('edit', 'clickable')->asImg(16) ?>
            <?= _('Eigene Rubriken') ?>
        </li>
        <li>
            <?= Icon::create('trash', 'clickable')->asImg(16) ?>
            <?= _('Kontakt löschen') ?>
        </li>
    <? else: ?>
        <li>
            <?= Icon::create('arr_1down', 'clickable')->asImg(16) ?>
            <?= _('Kontakt aufklappen') ?>
        </li>
    <? endif; ?>

    <? if ($open || $contact['view'] == 'gruppen'): ?>
        <li>
            <?= Icon::create('vcard+export', 'clickable')->asImg(16) ?>
            <?= _('als vCard exportieren') ?>
        </li>
    <? endif; ?>
    </ul>
</div>
