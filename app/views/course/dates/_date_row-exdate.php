<tr id="date_<?= $date->id ?>" class="ausfall" data-termin-id="<?= htmlReady($date->id) ?>">
    <td data-sort-value="<?= htmlReady($date->date) ?>" class="date_name">
        <?= Icon::create('date', Icon::ROLE_INFO)->asImg(['class' => 'text-bottom']) ?>
        <?= htmlReady($date->getFullname()) ?>
        <?= tooltipIcon($date->content) ?>
    <? if (count($date->dozenten) > 0): ?>
        <br>
        (<?= htmlReady(implode(', ', $date->dozenten->getFullname())) ?>)
    <? endif; ?>
    </td>
    <td></td>
    <td class="hidden-small-down">
        <?= htmlReady($date->getTypeName()) ?>
    </td>
    <td></td>
    <td></td>
    <td class="actions">
        <form action="<?= $controller->url_for("course/timesrooms/undeleteSingle/{$date->id}/1") ?>" method="post">
            <?= Icon::create('trash+decline')->asInput(tooltip2(_('Termin wiederherstellen')) + [
                'data-confirm' => _('Diesen Termin wiederherstellen?'),
            ]) ?>
        </form>
    </td>
</tr>
