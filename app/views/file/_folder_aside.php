<aside id="folder_aside">
    <?= $folder->getIcon('info') ?>
    <h3><?= htmlReady($folder->name) ?></h3>
    <table class="default">
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
</aside>
