<?php
$attributes = function (array $attributes) {
    $result = [];
    foreach ($attributes as $key => $value) {
        if ($value === null) {
            $result[] = htmlReady($key);
        } else {
            $result[] = sprintf('%s="%s"', htmlReady($key), htmlReady($value));
        }
    }
    return implode(' ', $result);
};
?>
<ul class="actionmenu">
    <li>
        <div class="action-title">
            <?= _('Aktionen') ?>
        </div>
        <?= Icon::create('action', 'clickable', ['title' => _('Aktionen'), 'class' => 'action-icon']) ?>
        <ul>
        <? foreach ($actions as $action): ?>
            <li>
            <? if ($action['type'] === 'link'): ?>
                <a href="<?= $action['link'] ?>" <?= $attributes($action['attributes']) ?>>
                <? if ($action['icon']): ?>
                    <?= $action['icon'] ?>
                <? endif; ?>
                    <?= htmlReady($action['label']) ?>
                </a>
            <? elseif ($action['type'] === 'multi-person-search'): ?>
                <?= $action['object']->render() ?>
            <? endif; ?>
            </li>
        <? endforeach; ?>
        </ul>
    </li>
</ul>