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
                <a href="<?= $controller->link_for("consultation/overview/cancel/{$block->id}/{$slot->id}") ?>" data-dialog="size=auto">
                    <?= Icon::create('consultation+remove')->asImg(tooltip2(_('Sprechstundentermin absagen'))) ?>
                </a>
            <? elseif ($slot->isOccupied()): ?>
                <?= Icon::create('consultation+add', Icon::ROLE_INACTIVE)->asImg(tooltip2(_('Sprechstunde ist bereits belegt'))) ?>
            <? else: ?>
                <a href="<?= $controller->link_for("consultation/overview/book/{$block->id}/{$slot->id}") ?>" data-dialog="size=auto">
                    <?= Icon::create('consultation+add')->asImg(tooltip2(_('Sprechstundentermin reservieren'))) ?>
                </a>
            <? endif; ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
</table>

<? endif; ?>
