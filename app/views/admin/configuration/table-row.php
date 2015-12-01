<tr>
    <td>
        <a data-dialog href="<?= $controller->url_for($linkchunk . $field) ?>">
            <?= htmlReady($field) ?>
        </a>
    <? if (!empty($description)): ?>
        <br><small><?= htmlReady($description)?></small>
    <? endif; ?>
    </td>
    <td class="wrap-content">
    <? if ($type === 'string'): ?>
        <em><?= htmlReady($value) ?></em>
    <? elseif ($type === 'integer'): ?>
        <?= (int)$value ?>
    <? elseif ($type === 'boolean'): ?>
        <?if ($value):?>
            <?= Icon::create('accept', 'accept', ['title' => _('TRUE')])->asImg() ?>
        <? else :?>
            <?= Icon::create('decline', 'attention', ['title' => _('FALSE')])->asImg() ?>
        <? endif; ?>
    <? endif; ?>
    </td>
    <td><?= htmlReady($type) ?></td>
    <td class="actions">
        <a data-dialog href="<?= $controller->url_for($linkchunk . $field) ?>">
            <?= Assets::img('icons/16/blue/edit.png',
                            tooltip2(_('Konfigurationsparameter bearbeiten'))) ?>
        </a>
    </td>
</tr>
