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
<nav class="action-menu">
    <div class="action-menu-icon" title="<?= _('Aktionen') ?>">
        <div></div>
        <div></div>
        <div></div>
    </div>
    <div class="action-menu-content">
        <div class="action-menu-title">
            <?= _('Aktionen') ?>
        </div>
        <ul class="action-menu-list">
        <? foreach ($actions as $action): ?>
            <li class="action-menu-item">
            <? if ($action['type'] === 'link'): ?>
                <a href="<?= $action['link'] ?>" <?= $attributes($action['attributes']) ?>>
                <? if ($action['icon']): ?>
                    <?= $action['icon'] ?>
                <? else: ?>
                    <span class="action-menu-no-icon"></span>
                <? endif; ?>
                    <?= htmlReady($action['label']) ?>
                </a>
            <? elseif ($action['type'] === 'multi-person-search'): ?>
                <?= $action['object']->render() ?>
            <? endif; ?>
            </li>
        <? endforeach; ?>
        </ul>
    </div>
</nav>