<?= $controller->renderMessages() ?>
<table class="default collapsable" style="width: 100%;">
    <caption><?= _('Studiengänge gruppiert nach Abschluss-Kategorien') ?></caption>
    <colgroup>
        <col>
        <col style="width: 10%;">
    </colgroup>
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('/index', _('Abschluss-Kategorie'), 'name') ?>
            <?= $controller->renderSortLink('/index', _('Studiengänge'), 'count_studiengaenge', array('style' => 'text-align: center;')) ?>
        </tr>
    </thead>
    <? foreach ($kategorien as $kategorie) : ?>
    <? // skip unknown Abschluesse ?>
    <? if (is_null($kategorie->name)) { continue; } ?>
    <tbody class="<?= ($kategorie->count_studiengaenge ? '' : 'empty') ?> <?= ($kategorie_id == $kategorie->id ? 'not-collapsed' : 'collapsed') ?>">
        <tr class="header-row" id="kategorie_<?= $kategorie->id ?>">
            <td class="toggle-indicator">
                <? if (is_null($kategorie->name) && $kategorie->count_studiengaenge) : ?>
                    <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details', ['parent_id' => $kategorie->id]) ?>"><?= _('Keiner Abschluss-Kategorie zugeordnet') ?></a>
                <? else : ?>
                    <? if ($kategorie->count_studiengaenge) : ?>
                    <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details', ['parent_id' => $kategorie->id]) ?>"><?= htmlReady($kategorie->getDisplayName()) ?> </a>
                    <? else : ?>
                    <?= htmlReady($kategorie->getDisplayName()) ?>
                    <? endif; ?>
                <? endif; ?>
            </td>
            <td style="text-align: center;" class="dont-hide"><?= $kategorie->count_studiengaenge ?></td>
        </tr>
        <? if ($parent_id == $kategorie->id) : ?>
        <tr class="loaded-details nohover">
            <?= $this->render_partial('studiengaenge/studiengaenge/details', compact('parent_id', 'kategorie_id', 'kategorien')) ?>
        </tr>
        <? endif; ?>
    </tbody>
    <? endforeach; ?>
 </table>
