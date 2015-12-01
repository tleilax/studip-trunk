<div class="smiley-picker">
    <table class="navigation top">
        <tr>
        <? if ($favorites_activated && count($favorites->get()) > 0): ?>
            <td>
                <a href="<?= $controller->url_for('smileys/picker/favorites') ?>">
                    <?= Assets::img('icons/16/' . ($view === 'favorites' ? 'red' : 'blue') . '/star', tooltip2(_('Favoriten'))) ?>
                </a>
            </td>
        <? endif; ?>
            <td style="text-align: right;">
                <a href="<?= $controller->url_for('smileys/picker/all') ?>">
                    <?= Assets::img('icons/16/' . ($view === 'all' ? 'red' : 'blue') . '/smiley', tooltip2(_('alle'))) ?>
                </a>
            </td>
        <? for ($i = 0; $i < 26; $i++):
               $char = chr(ord('a') + $i);
        ?>
            <td <? if ($view === $char) echo 'class="active"'; ?>>
            <? if (isset($characters[$char])): ?>
                <a href="<?= $controller->url_for('smileys/picker/'. $char) ?>">
                    <?= strtoupper($char) ?>
                </a>
            <? else: ?>
                <?= $char ?>
            <? endif; ?>
            </td>
        <? endfor; ?>
        </tr>
    </table>

    <div class="smileys">
<? foreach (array_pad($smileys, $controller::GRID_WIDTH * $controller::GRID_HEIGHT, null) as $smiley): ?>
    <? if ($smiley === null): ?>
        <span class="empty"></span>
    <? else: ?>
        <a class="smiley" href="#" data-code="<?= $smiley->short ?: (':' . $smiley->name . ':') ?>">
            <?= $smiley->html ?>
        </a>
    <? endif; ?>
<? endforeach; ?>
    </div>

    <table class="navigation bottom">
        <tr>
            <td>
            <? if ($page > 0): ?>
                <a href="<?= $controller->url_for('smileys/picker/' . $view . '/0') ?>">
                    <?= Icon::create('arr_eol-left', 'clickable')->asImg(16) ?>
                </a>
                <a href="<?= $controller->url_for('smileys/picker/' . $view . '/' . ($page - 1)) ?>">
                    <?= Icon::create('arr_1left', 'clickable')->asImg(16) ?>
                </a>
            <? else: ?>
                <?= Icon::create('arr_eol-left', 'inactive')->asImg(16) ?>
                <?= Icon::create('arr_1left', 'inactive')->asImg(16) ?>
            <? endif; ?>
            </td>
            <td style="text-align: center;">
                <?= sprintf('Seite %u von %u', $page + 1, $pages + 1) ?>
            </td>
            <td style="text-align: right;">
            <? if ($page < $pages): ?>
                <a href="<?= $controller->url_for('smileys/picker/' . $view . '/' . ($page + 1)) ?>">
                    <?= Icon::create('arr_1right', 'clickable')->asImg(16) ?>
                </a>
                <a href="<?= $controller->url_for('smileys/picker/' . $view . '/' . $pages) ?>">
                    <?= Icon::create('arr_eol-right', 'clickable')->asImg(16) ?>
                </a>
            <? else: ?>
                <?= Icon::create('arr_1right', 'inactive')->asImg(16) ?>
                <?= Icon::create('arr_eol-right', 'inactive')->asImg(16) ?>
            <? endif; ?>
            </td>
        </tr>
    </table>
</div>