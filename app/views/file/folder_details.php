<?= $folder->getIcon('info')->asImg('32px') ?>
<h1><?= htmlReady($folder->name) ?></h1>
<table class="default nohover">
    <tr>
        <td><?= _('Erstellt') ?></td>
        <td><?= date('d.m.Y H:i', $folder->mkdate) ?></td>
    </tr>
    <tr>
        <td><?= _('Geändert') ?></td>
        <td><?= date('d.m.Y H:i', $folder->chdate) ?></td>
    </tr>
    <tr>
        <td><?= _('Besitzer/-in') ?></td>
        <td>
        <? if($folder->owner): ?>
        <?= htmlReady($folder->owner->getFullName()) ?>
        <? endif ?>
        </td>
    </tr>
</table>
<h3><?= _('Beschreibung') ?></h3>
<article><?= htmlReady($folder->description); ?></article>
