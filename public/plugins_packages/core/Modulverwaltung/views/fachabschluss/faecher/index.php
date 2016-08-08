<?= $this->controller->renderMessages() ?>
<table class="default collapsable">
    <caption>
        <?= _('F�cher mit verwendeten Abschl�ssen') ?>
        <span class="actions"><? printf(ngettext('%s Fach', '%s F�cher', $count), $count) ?></span>
    </caption>
    <colgroup>
        <col>
        <col style="width: 5%">
        <col style="width: 10%">
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('/index', _('Fach'), 'name') ?>
            <?= $controller->renderSortLink('/index', _('Abschl�sse'), 'count_abschluesse') ?>
            <th> </th>
        </tr>
    </thead>
    <? foreach ($faecher as $fach): ?>
    <tbody class="<?= $fach->count_abschluesse ? '' : 'empty' ?>  <?= ($fach_id == $fach->id ? 'not-collapsed' : 'collapsed') ?>">
    <tr class="header-row">
        <td class="toggle-indicator">
            <? if ($fach->count_abschluesse) : ?>
                <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details', $fach->id) ?>"><?= htmlReady($fach->name) ?></a>
            <? else: ?>
                <?= htmlReady($fach->name) ?>
            <? endif; ?>
        </td>
        <td class="dont-hide" style="text-align: center;"><?= $fach->count_abschluesse ?> </td>
        <td class="dont-hide actions" style="white-space: nowrap;">
        <? if (MvvPerm::havePermWrite($fach)) : ?>
            <a href="<?= $controller->url_for('/fach', $fach->id) ?>">
                <?= Icon::create('edit', 'clickable', array('title' => _('Fach bearbeiten')))->asImg(); ?>
            </a>
        <? endif; ?>
        <? if (MvvPerm::havePermCreate($fach)) : ?>
            <? if ($fach->count_user == 0 && $fach->count_sem == 0): ?> <a href="<?= $controller->url_for('/delete', $fach->id) ?>">
                <?= Icon::create('trash', 'clickable', array('title' => _('Fach l�schen')))->asImg(); ?>
            </a>
            <? endif;?>
        <? endif; ?>
        </td>
    </tr>
    <? if ($fach_id == $fach->id) : ?>
    <tr class="loaded-details nohover">
        <?= $this->render_partial('fachabschluss/faecher/details', compact('fach')) ?>
    </tr>
    <? endif; ?>
    </tbody>
    <? endforeach ?>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align: right;">
            <? if ($count > MVVController::$items_per_page) : ?>
            <?
                $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                $pagination->clear_attributes();
                $pagination->set_attribute('perPage', MVVController::$items_per_page);
                $pagination->set_attribute('num_postings', $count);
                $pagination->set_attribute('page', $page);
                $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_faecher=%s';
                $pagination->set_attribute('pagelink', $page_link);
                echo $pagination->render('shared/pagechooser');
            ?>
            <? endif; ?>
            </td>
        </tr>
    </tfoot>
</table>
