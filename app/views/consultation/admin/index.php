<?php
$displayNote = function ($what, $length = 40) {
    $what = trim($what);
    if (!$what) {
        return '';
    }

    if (mb_strlen($what)  < $length) {
        return '<div class="consultation-note">' . $what . '</div>';
    }

    return sprintf(
        '<div class="consultation-note shortened" data-tooltip="%s">%s&hellip;</div>',
        htmlReady($what),
        htmlReady(substr($what, 0, $length))
    );
};
?>

<? if (count($blocks) === 0): ?>

<?= MessageBox::info(sprintf(
    implode('<br>', [
        _('Derzeit sind keine Sprechstundentermine eingetragen.'),
        '<a href="%s" class="button" data-dialog="size=auto">%s</a>',
    ]),
    $controller->link_for('consultation/admin/create'),
    _('Sprechstundenblöcke anlegen')
))->hideClose() ?>

<? else: ?>

<form action="<?= $controller->link_for("consultation/admin/bulk") ?>" method="post">
<table class="default">
    <colgroup>
        <col width="10%">
        <col width="10%">
        <col width="25%">
        <col>
        <col width="48px">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Uhrzeit') ?></th>
            <th><?= _('Status') ?></th>
            <th><?= _('Person') ?></th>
            <th><?= _('Grund') ?></th>
            <th></th>
        </tr>
    </thead>
<? foreach ($blocks as $block): ?>
    <tbody id="block-<?= $block->id ?>">
        <tr>
            <th colspan="4">
                <?= strftime('%A, %x', $block->start) ?>,
                <?= sprintf(
                    _('%s bis %s Uhr'),
                    date('H:i', $block->start),
                    date('H:i', $block->end)
                ) ?>
                (
                    <?= _('Raum') ?> <?= htmlReady($block->room) ?>
                <? if ($block->course): ?>
                    /
                    <a href="<?= URLHelper::getLink('dispatch.php/course/details', ['sem_id' => $block->course_id]) ?>">
                        <?= htmlReady($block->course->getFullName()) ?>
                    </a>
                <? endif; ?>
                )

                <?= $displayNote($block->note, 300) ?>
            </th>
            <th class="actions">
                <?= ActionMenu::get()->addLink(
                    $controller->url_for("consultation/admin/note/{$block->id}"),
                    _('Anmerkung bearbeiten'),
                    Icon::create('comment'),
                    ['data-dialog' => 'size=auto']
                )->addLink(
                    $controller->url_for("consultation/admin/print/{$block->id}"),
                    _('Druckansicht anzeigen'),
                    Icon::create('print'),
                    ['target' => '_blank']
                )->condition($block->has_bookings)->addLink(
                    $controller->url_for("consultation/admin/cancel_block/{$block->id}"),
                    _('Sprechstundentermine absagen'),
                    Icon::create('consultation+remove'),
                    ['data-dialog' => 'size=auto']
                )->condition(!$block->has_bookings)->addButton(
                    'remove',
                    _('Sprechstundentermine entfernen'),
                    Icon::create('trash'),
                    ['formaction'   => $controller->url_for("consultation/admin/remove/{$block->id}")]
                ) ?>
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
            <? if (count($slot->bookings) < $slot->block->size): ?>
                <span class="consultation-free"><?= _('frei') ?></span>
            <? else: ?>
                <span class="consultation-occupied"><?= _('belegt') ?></span>
            <? endif; ?>
            </td>
            <td>
            <? if (count($slot->bookings) === 0): ?>
                &ndash;
            <? else: ?>
                <ul class="default">
                <? foreach ($slot->bookings as $booking): ?>
                    <li>
                        <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => $booking->user->username]) ?>">
                            <?= Avatar::getAvatar($booking->user_id)->getImageTag(Avatar::SMALL) ?>
                            <?= htmlReady($booking->user->getFullName()) ?>
                        </a>
                    </li>
                <? endforeach; ?>
                </ul>
            <? endif; ?>
            </td>
            <td>
                <ul class="default">
                <? foreach ($slot->bookings as $booking): ?>
                    <li>
                    <? if ($booking->reason): ?>
                        <?= htmlReady($booking->reason) ?>
                    <? else: ?>
                        <span class="consultation-no-reason">
                            <?= _('Kein Grund angegeben') ?>
                        </span>
                    <? endif; ?>
                    </li>
                <? endforeach; ?>
                </ul>
            </td>
            <td class="actions">
                <?= ActionMenu::get()->addLink(
                    $controller->url_for("consultation/admin/note/{$block->id}/{$slot->id}"),
                    _('Anmerkung bearbeiten'),
                    Icon::create('comment'),
                    ['data-dialog' => 'size=auto']
                )->condition(count($slot->bookings) < $slot->block->size)->addLink(
                    $controller->url_for("consultation/admin/book/{$block->id}/{$slot->id}"),
                    _('Sprechstundentermin reservieren'),
                    Icon::create('consultation+add'),
                    ['data-dialog' => 'size=auto']
                )->condition(count($slot->bookings) > 0)->addLink(
                    $controller->url_for("consultation/admin/reason/{$block->id}/{$slot->id}/{$slot->bookings->first()->id}"),
                    _('Grund bearbeiten'),
                    Icon::create('edit'),
                    ['data-dialog' => 'size=auto']
                )->condition(count($slot->bookings) > 0)->addLink(
                    $controller->url_for("consultation/admin/cancel_slot/{$block->id}/{$slot->id}"),
                    _('Sprechstundentermin absagen'),
                    Icon::create('consultation+remove'),
                    ['data-dialog' => 'size=auto']
                )->condition(count($slot->bookings) === 0)->addButton(
                    'delete',
                    _('Sprechstundentermin entfernen'),
                    Icon::create('trash'),
                    [
                        'formaction'   => $controller->url_for("consultation/admin/remove/{$block->id}/{$slot->id}"),
                        'data-confirm' => _('Wollen Sie diesen Sprechstundentermin wirklich entfernen?'),
                    ]
                ) ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
</table>
</form>

<? endif; ?>
