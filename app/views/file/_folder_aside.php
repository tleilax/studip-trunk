<aside id="folder_aside">
    <?= $folder->getIcon('info') ?>
    <h3><?= htmlReady($folder->name) ?></h3>
    <table class="default">
        <tr>
            <th><?= _('Erstellt') ?></th>
            <td><?= date('d.m.Y H:i', $folder->mkdate) ?></td>
        </tr>
        <tr>
            <th><?= _('Geändert') ?></th>
            <td><?= date('d.m.Y H:i', $folder->chdate) ?></td>
        </tr>
        <tr>
            <th><?= _('Besitzer/-in') ?></th>
            <td>
            <? if($folder->owner): ?>
            <?= htmlReady($folder->owner->getFullName()) ?>
            <? endif ?>
            </td>
        </tr>
    </table>
</aside>
