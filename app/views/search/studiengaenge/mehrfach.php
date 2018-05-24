<?= $this->render_partial('search/breadcrumb') ?>
<table class="default nohover">
    <caption>
        <?= _('Studiengang') ?>: <?= htmlReady($studiengang->getDisplayName(ModuleManagementModel::DISPLAY_ABSCHLUSS)) ?>
    </caption>
    <thead>
        <tr>
            <th><?= _('Fächer') ?></th>
        <? foreach ($teilNamen as $teilName): ?>
            <th style="text-align: center;"><?= htmlReady($teilName) ?></th>
        <? endforeach; ?>
        </tr>
    </thead>
    <tbody>
    <? foreach ($data as $fach_id => $fach): ?>
        <tr>
            <td>
                <?= htmlReady($fachNamen[$fach_id]) ?>
            </td>
            <? foreach ($teilNamen as $teilId => $teilName): ?>
                <td style="text-align: center;">
                    <? if (isset($fach[$teilId])) : ?>
                    <a href="<?= $controller->url_for($verlauf_url, $fach[$teilId], $teilId, $studiengang_id) ?>">
                        <?= Icon::create('info-circle-full', 'clickable', array('title' => _('Studienverlaufsplan anzeigen')))->asImg(); ?>
                    </a>
                    <? endif; ?>
                </td>
            <? endforeach; ?>
        </tr>
    <? endforeach; ?>
    </tbody>
</table>