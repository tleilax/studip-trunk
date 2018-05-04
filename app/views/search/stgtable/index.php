<?//= $this->render_partial('search/breadcrumb') ?>
<h1><?= _('Fach-Abschluss-Kombinationen') ?></h1>
<table class="default">
    <colgroup>
        <col>
        <? for ($i = count($kategorien); $i > 0; $i--) : ?>
        <col style="text-align: center;">
        <? endfor; ?>
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Studiengänge') ?></th>
            <? foreach ($kategorien as $kategorie): ?>
                <th style="text-align: center;"><?= htmlReady($kategorie->name) ?></th>
            <? endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <? foreach ($stgs as $name => $stg): ?>
        <? //var_dump($fach->abschluesse); exit; ?>
            <tr>
                <td><?= htmlReady($name) ?></td>
                <? foreach ($kategorien as $kat_id => $kategorie): ?>
                    <? // $fach_abschluss_ids = $fach->abschluesse->pluck('id'); ?>
                    <? if ($stg[$kat_id]) : ?>
                <td style="text-align: center;">
                    <a href="<?= $controller->link_for('/studiengang', $stg[$kat_id]) ?>"><?= Icon::create('info-circle-full', 'clickable', array('title' => _('Studiengang anzeigen')))->asImg(); ?></a>
                </td>
                    <? else : ?>
                        <td></td>
                    <? endif; ?>
                <? endforeach; ?>
            </tr>
        <? endforeach; ?>
    </tbody>
</table>