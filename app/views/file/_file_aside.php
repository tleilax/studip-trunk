<aside id="file_aside">
    <?= Icon::create(
        'file',
        'info',
        []) ?>
    <h3 style="text-align: center; font-size: 140%;"><?= htmlReady($file_ref->name) ?></h3>
    <table class="default">
        <tr>
            <th><?= _('Größe') ?></th>
            <td><?= relSize($file_ref->size, false) ?></td>
        </tr>
        <tr>
            <th><?= _('Erstellt') ?></th>
            <td><?= date('d.m.Y H:i', $file_ref->mkdate) ?></td>
        </tr>
        <tr>
            <th><?= _('Geändert') ?></th>
            <td><?= date('d.m.Y H:i', $file_ref->chdate) ?></td>
        </tr>
        <tr>
            <th><?= _('Besitzer/-in') ?></th>
            <td>
            <? if($file_ref->owner): ?>
            <?= htmlReady($file_ref->owner->getFullName()) ?>
            <? endif ?>
            </td>
        </tr>
    </table>
</aside>
