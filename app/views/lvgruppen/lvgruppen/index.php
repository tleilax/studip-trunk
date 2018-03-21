<form method="post">
    <?= CSRFProtection::tokenTag(); ?>
    <table class="default collapsable">
        <caption>
            <?= _('Lehrveranstaltungsgruppen'); ?>
            <span class="actions"><? printf(ngettext('%s LV-Gruppe', '%s LV-Gruppen', $count), $count) ?></span>
        </caption>
        <colgroup>
            <col>
            <col span="4" style="width: 1%;">
        </colgroup>
        <thead>
            <tr class="sortable">
                <?= $controller->renderSortLink('/index', _('Name'), 'name') ?>
                <?= $controller->renderSortLink('/index', _('Veranstaltungen'), 'count_seminare', ['style' => 'text-align: center;']) ?>
                <?= $controller->renderSortLink('/index', _('Archiv'), 'count_archiv', ['style' => 'text-align: center;']) ?>
                <?= $controller->renderSortLink('/index', _('Modulteile'), 'count_modulteile', ['style' => 'text-align: center;']) ?>
                <th></th>
            </tr>
        </thead>
        <? if (count($lvgruppen)) : ?>
            <? foreach ($lvgruppen as $lvgruppe): ?>
                <tbody class="<?= $lvgruppe->count_seminare || true ? '' : 'empty' ?>  <?= ($lvgruppe_id ? 'not-collapsed' : 'collapsed') ?>">
                <tr class="header-row <?= TextHelper::cycle('table_row_even', 'table_row_odd') ?>">
                    <td class="toggle-indicator">
                        <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details', $lvgruppe->id) ?>">
                            <?= htmlReady($lvgruppe->getDisplayName()) ?>
                        </a>
                    </td>
                    <td style="text-align: center;" class="dont-hide"><?= $lvgruppe->count_seminare ?> </td>
                    <td style="text-align: center;" class="dont-hide"><?= $lvgruppe->count_archiv ?> </td>
                    <td style="text-align: center;" class="dont-hide"><?= $lvgruppe->count_modulteile ?> </td>
                    <td class="dont-hide actions" style="white-space: nowrap;">
                    <? $actionMenu = ActionMenu::get() ?>
                    <? if (MvvPerm::get($lvgruppe)->havePermWrite()) : ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('/lvgruppe/' . $lvgruppe->id),
                            _('Lehrveranstaltungsgruppe bearbeiten'),
                            Icon::create('edit', 'clickable', tooltip2(_('Lehrveranstaltungsgruppe bearbeiten'))),
                            ['data-dialog' => 'size=auto']
                        ) ?>

                        <? $actionMenu->addLink(
                            $controller->url_for('shared/log_event/show/Lvgruppe/' . $lvgruppe->id),
                            _('Log-Einträge dieser Lehrveranstaltungsgruppe'),
                            Icon::create('log', 'clickable', tooltip2(_('Log-Einträge dieser Lehrveranstaltungsgruppe'))),
                            ['data-dialog' => 'size=auto']
                        ) ?>
                    <? endif; ?>
                    <? if (MvvPerm::get($lvgruppe)->havePermCreate()) : ?>
                        <? if ($lvgruppe->count_semester == 0 && $lvgruppe->count_modulteile == 0): ?>
                            <? $actionMenu->addButton(
                                'delete',
                                _('Lehrveranstaltungsgruppe löschen'),
                                Icon::create('trash', 'clickable', tooltip2(_('Modulteil löschen'))),
                                [
                                    'formaction'   => $controller->url_for('/delete', $lvgruppe->id),
                                    'data-confirm' => sprintf(_('Wollen Sie wirklich die Lehrveranstaltungsgruppe "%s" löschen?'), $lvgruppe->getDisplayName())
                                ]
                            ) ?>
                        <? endif; ?>
                    <? endif; ?>
                    <?= $actionMenu->render() ?>
                    </td>
                </tr>
                <? if ($lvgruppe_id == $lvgruppe->id) : ?>
                    <tr class="loaded-details nohover">
                        <?= $this->render_partial('lvgruppen/lvgruppen/details', compact('lvgruppe')) ?>
                    </tr>
                <? endif; ?>
            <? endforeach ?>
            </tbody>
        <? else : ?>
            <tbody>
                <tr>
                    <td colspan="6" style="text-align: center">
                        <?= _('Es wurden keine Lehrveranstaltungsgruppen gefunden.') ?>
                    </td>
                </tr>
            </tbody>
        <? endif ?>

        <? if ($count > MVVController::$items_per_page) : ?>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: right;">
                    <?
                    $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                    $pagination->clear_attributes();
                    $pagination->set_attribute('perPage', MVVController::$items_per_page);
                    $pagination->set_attribute('num_postings', $count);
                    $pagination->set_attribute('page', $page);
                    $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_lvgruppen=%s';
                    $pagination->set_attribute('pagelink', $page_link);
                    echo $pagination->render("shared/pagechooser");
                    ?>
                </td>
            </tr>
        <tfoot>
            <? endif; ?>
    </table>
</form>