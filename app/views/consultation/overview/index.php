<? if (count($blocks) === 0): ?>

<?= MessageBox::info(_('Aktuell werden keine Sprechstunden angeboten.'))->hideClose() ?>

<? else: ?>

<table class="default">
    <colgroup>
        <col width="10%">
        <col width="10%">
        <col>
        <col width="24px">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Uhrzeit') ?></th>
            <th><?= _('Status') ?></th>
            <th><?= _('Grund') ?></th>
            <th></th>
        </tr>
    </thead>
<? foreach ($blocks as $block): ?>
    <tbody>
        <tr id="block-<?= htmlReady($block->id) ?>">
            <th colspan="4">
                <?= $this->render_partial('consultation/block-description.php', compact('block')) ?>
            </th>
        </tr>
    <? foreach ($block->slots as $slot): ?>
        <tr>
            <td>
                <?= date('H:i', $slot->start_time) ?>
                -
                <?= date('H:i', $slot->end_time) ?>

                <?= $displayNote($slot->note) ?>
            </td>
            <td>
                <?= $this->render_partial('consultation/slot-occupation.php', compact('slot')) ?>
            </td>
            <td>
            <? if (count($slot->bookings) === 0 || !$slot->isOccupied($GLOBALS['user']->id)): ?>
                &ndash;
            <? else: ?>
                <?= htmlReady($slot->bookings->findOneBy('user_id', $GLOBALS['user']->id)->reason) ?>
            <? endif; ?>
            </td>
            <td class="actions">
            <? if ($slot->isOccupied($GLOBALS['user']->id)): ?>
                <a href="<?= $controller->cancel($block, $slot) ?>" data-dialog="size=auto">
                    <?= Icon::create('trash')->asImg(tooltip2(_('Sprechstundentermin absagen'))) ?>
                </a>
            <? elseif (!$slot->isOccupied()): ?>
                <a href="<?= $controller->book($block, $slot) ?>" data-dialog="size=auto">
                    <?= Icon::create('add')->asImg(tooltip2(_('Sprechstundentermin reservieren'))) ?>
                </a>
            <? endif; ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
</table>

<? endif; ?>
