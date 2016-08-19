<?= $this->controller->renderMessages() ?>
<table class="default collapsable">
    <caption>
        <?= _('Abschl�sse mit verwendeten F�chern') ?>
        <span class="actions"><? printf(ngettext('%s Abschluss', '%s Abschl�sse', $count), $count) ?></span>
    </caption>
    <colgroup>
        <col>
        <col style="width: 30%;">
        <col style="width: 5%;">
        <col style="width: 10%">
    <thead>
        <tr class="sortable">
                <?= $controller->renderSortLink('/index', _('Abschluss'), 'name') ?>
                <?= $controller->renderSortLink('/index', _('Abschluss-Kategorie'), 'kategorie_name') ?>
                <?= $controller->renderSortLink('/index', ('F�cher'), 'count_faecher') ?>
            <th> </th>
        </tr>
    </thead>
    <? foreach ($abschluesse as $abschluss) : ?>
    <tbody class="<?= $abschluss->count_faecher ? '' : 'empty' ?> <?= ($abschluss_id ? 'not-collapsed' : 'collapsed') ?>">
    <tr class="header-row">
        <td class="toggle-indicator">
            <? if ($abschluss->count_faecher) : ?>
                <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details', $abschluss->id) ?>"><?= htmlReady($abschluss->name) ?> </a>
            <? else: ?>
                <?= htmlReady($abschluss->name) ?>
            <? endif; ?>
        </td>
        <td class="dont-hide"><?= htmlReady($abschluss->kategorie_name) ?></td>
        <td style="text-align: center;" class="dont-hide"><?= $abschluss->count_faecher ?></td>
        <td class="dont-hide actions" style="white-space: nowrap;">
        <? if (MvvPerm::havePermWrite($abschluss)) : ?>
            <a href="<?=$controller->url_for('/abschluss', $abschluss->id)?>">
                <?= Icon::create('edit', 'clickable', array('title' => _('Abschluss bearbeiten')))->asImg(); ?>
            </a>
        <? endif; ?>
        <? if (MvvPerm::havePermCreate($abschluss)) : ?>
            <? if (!$abschluss->count_faecher) : ?>
            <a href="<?= $controller->url_for('/delete', $abschluss->id) ?>">
                <?= Icon::create('trash', 'clickable', array('title' => _('Abschluss l�schen')))->asImg(); ?>
            </a>
            <? else : ?>
                <?= Icon::create('trash', 'inactive', array('title' => _('Abschluss kann nicht gl�scht werden')))->asImg(); ?>
            <? endif; ?>
        <? endif; ?>
        </td>
    </tr>
    <? if ($abschluss_id == $abschluss->id) : ?>
        <?= $this->render_partial('fachabschluss/abschluesse/details', compact('abschluss')) ?>
    <? endif; ?>
    </tbody>
    <? endforeach; ?>
    <tfoot>
        <tr>
            <td colspan="4" style="text-align: right;">
            <? if ($count > MVVController::$items_per_page) : ?>
            <?
                $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                $pagination->clear_attributes();
                $pagination->set_attribute('perPage', MVVController::$items_per_page);
                $pagination->set_attribute('num_postings', $count);
                $pagination->set_attribute('page', $page);
                $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_abschluesse=%s';
                $pagination->set_attribute('pagelink', $page_link);
                echo $pagination->render('shared/pagechooser');
            ?>
            <? endif; ?>
            </td>
        </tr>
    </tfoot>
 </table>